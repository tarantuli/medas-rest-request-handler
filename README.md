# medas-rest-request-handler

Part of the [Medas framework](https://github.com/tarantuli/medas-core).

## Description

A REST API layer built on `medas-http-request-handler` and `medas-entity-manager`. It provides URL filter parsing, entity serialization/deserialization, response builders, bearer token authentication, and predefined query support — the shared infrastructure used across entity-backed REST endpoints.

**Key components:**

| Component                   | Purpose                                                                                                                                                |
|-----------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------|
| `FilterParser`              | Parses URL query parameters into entity manager `Selector` elements — conditions, sorts, pagination                                                    |
| `SelectorBuilder`           | Wraps `FilterParser` with entity metadata lookups and ownership filter application                                                                     |
| `RestSerializer`            | Serializes entity values for API responses (UUID→string, DateTime→RFC3339, enums, relations) and deserializes incoming values back to typed PHP values |
| `CollectionResponseBuilder` | Builds a `CollectionResponse` (array of serialized entities)                                                                                           |
| `EntityResponseBuilder`     | Builds an `EntityResponse` (single serialized entity)                                                                                                  |
| `BearerTokenAuthenticator`  | Listens to `AuthenticationVote`; resolves the current user from a bearer token                                                                         |
| `PredefinedQueryManager`    | Discovers and dispatches named `PredefinedQuery` implementations                                                                                       |
| `ArgumentDeserializer`      | Registered as an `ArgumentProcessor`; deserializes incoming route/body arguments using entity type metadata                                            |

**Filter query syntax** — comparison operators are embedded directly in the parameter name as `field<operator>=value`:

| Parameter                        | Default operator | Effect                                |
|----------------------------------|------------------|---------------------------------------|
| `page=N`                         | —                | Page number (1-based)                 |
| `per_page=N`                     | —                | Results per page (max 1000)           |
| `multisort=field:asc,other:desc` | —                | Multi-column sort                     |
| `predefined=query-name`          | —                | Apply a named `PredefinedQuery`       |
| `field=value`                    | —                | Equality filter                       |
| `field*=value`                   | `*`              | Contains filter                       |
| `field^=value`                   | `^`              | Starts-with filter                    |
| `field$=value`                   | `$`              | Ends-with filter                      |
| `field!=value`                   | `!`              | Not-equal filter                      |
| `field∈=a,b,c`                   | `∈`              | IN filter                             |
| `field∉=a,b,c`                   | `∉`              | NOT IN filter                         |
| `field>>=N`                      | `>>`             | Greater-than filter                   |
| `field<<=N`                      | `<<`             | Less-than filter                      |
| `field>=N`                       | `>`              | ≥ filter                              |
| `field<=N`                       | `<`              | ≤ filter                              |
| `related_store.field=value`      | —                | Filter on a related entity's property |

All comparison operators are configurable and can be disabled by setting them to `null`.

## Configuration options

| Option                                                   | Default      | Description                                             |
|----------------------------------------------------------|--------------|---------------------------------------------------------|
| `rest-request-handler.default-page-size`                 | `25`         | Default number of results per page                      |
| `rest-request-handler.page-query-name`                   | `page`       | Query parameter name for page number                    |
| `rest-request-handler.per-page-query-name`               | `per_page`   | Query parameter name for page size                      |
| `rest-request-handler.multisort-query-name`              | `multisort`  | Query parameter name for multi-column sort              |
| `rest-request-handler.predefined-query-name`             | `predefined` | Query parameter name for predefined queries             |
| `rest-request-handler.users-class`                       | none         | FQCN of the user entity for bearer token authentication |
| `rest-request-handler.comparison-operators.contains`     | `*`          | Operator for contains filter                            |
| `rest-request-handler.comparison-operators.starts-with`  | `^`          | Operator for starts-with filter                         |
| `rest-request-handler.comparison-operators.ends-with`    | `$`          | Operator for ends-with filter                           |
| `rest-request-handler.comparison-operators.is-not`       | `!`          | Operator for not-equal filter                           |
| `rest-request-handler.comparison-operators.is-in`        | `∈`          | Operator for IN filter                                  |
| `rest-request-handler.comparison-operators.is-not-in`    | `∉`          | Operator for NOT IN filter                              |
| `rest-request-handler.comparison-operators.is-more-than` | `>>`         | Operator for greater-than filter                        |
| `rest-request-handler.comparison-operators.is-less-than` | `<<`         | Operator for less-than filter                           |
| `rest-request-handler.comparison-operators.is-at-least`  | `>`          | Operator for ≥ filter                                   |
| `rest-request-handler.comparison-operators.is-at-most`   | `<`          | Operator for ≤ filter                                   |

## Usage

### Package developer context

Register the package:

```php
use Medas\RestRequestHandler\RestRequestHandlerPackage;

RestRequestHandlerPackage::instance();
```

**Building a collection endpoint:**

```php
use Medas\Core\Interfaces\HttpRequestHandler;
use Medas\EntityManager\Repository;
use Medas\HttpRequestHandler\ResponseTypes\Response;
use Medas\RestRequestHandler\{Filtering\SelectorBuilder, Responses\CollectionResponseBuilder};
use Medas\HttpRequestHandler\Request\Request;
use Medas\Core\Attributes\Service;

#[Service]
readonly class GetInvoicesHandler implements HttpRequestHandler
{
    public function __construct(
        private CollectionResponseBuilder $responseBuilder,
        private Repository                $repository,
        private SelectorBuilder           $selectorBuilder,
        private Request                   $request,
    ) {}

    public function handle(string $method, string $endpoint): Response
    {
        $selector = $this->selectorBuilder->build(
            entity: Invoice::class,
            filters: $this->request->uri->query,
        );

        $invoices = $this->repository->fetch($selector);

        return $this->responseBuilder->build($invoices, normalizer: null);
    }
}
```

A request like `GET /invoices?status=draft&page=2&per_page=10` will produce a filtered, paginated result automatically. Using comparison operators: `GET /invoices?amount>>=100` returns invoices with an amount greater than 100, and `GET /invoices?name^=Acme` returns invoices whose name starts with "Acme".

**Building a single-entity endpoint:**

```php
use Medas\RestRequestHandler\Responses\EntityResponseBuilder;
use Medas\EntityManager\EntityManager;
use Medas\Core\Interfaces\{HasId, Uuid};

#[Service]
readonly class GetInvoiceHandler implements HttpRequestHandler
{
    public function __construct(
        private EntityManager         $entityManager,
        private EntityResponseBuilder $responseBuilder,
        private Uuid                  $id,  // injected from the route
    ) {}

    public function handle(string $method, string $endpoint): Response
    {
        $invoice = $this->entityManager->get(Invoice::class, $this->id);

        return $this->responseBuilder->build($invoice, normalizer: null);
    }
}
```

**Custom normalizer:**

When the default (all public properties) serialization is not enough, implement `Normalizer`:

```php
use Medas\RestRequestHandler\Interfaces\Normalizer;
use Medas\Core\Attributes\Service;

#[Service]
readonly class InvoiceNormalizer implements Normalizer
{
    public function normalize(object $entity): array
    {
        /** @var Invoice $entity */
        return [
            'id'          => $entity->id,
            'status'      => $entity->status,
            'amount'      => $entity->amountCents / 100,
            'customer_id' => $entity->customer->id(),
        ];
    }
}
```

Pass it to the response builder:

```php
return $this->responseBuilder->build($invoice, $this->invoiceNormalizer);
```

**Predefined queries** — named query presets the client can activate by name:

```php
use Medas\RestRequestHandler\Filtering\PredefinedQueries\PredefinedQuery;
use Medas\EntityManager\Selector\{Conditions\WhereIs, Element};
use Medas\EntityManager\Selector\Operants\{Property, Value};
use Medas\Core\Attributes\Service;

#[Service]
readonly class OpenInvoicesQuery implements PredefinedQuery
{
    public function name(): string
    {
        return 'open';
    }

    /** @return Element[] */
    public function elements(): array
    {
        return [
            new WhereIs(new Property('status'), new Value('draft')),
        ];
    }
}
```

Client request: `GET /invoices?predefined=open`

`PredefinedQuery` implementations are discovered automatically via `ImplementorFinder`.

**Bearer token authentication:**

```yaml
rest-request-handler:
  users-class: MyApp\Entities\User
```

With `users-class` set and a `AuthenticationTokenController` registered (e.g., via `medas-api-keys`'s `NamedTokenManager`), every request with a valid `Authorization: Bearer <token>` header will have `$request->authentication->user` populated with the resolved user entity. No additional listener registration is needed — `BearerTokenAuthenticator` listens to `AuthenticationVote` automatically.

**`RestSerializer` type conversions:**

| PHP type (outbound) | JSON representation       |
|---------------------|---------------------------|
| `Uuid`              | Hyphenated string         |
| `bool`              | `0` / `1` integer         |
| `DateTimeInterface` | RFC 3339 extended string  |
| `Collection`        | Array of serialized items |
| `HasId` entity      | Serialized id value       |

| JSON type (inbound)           | PHP type               |
|-------------------------------|------------------------|
| UUID string                   | `Uuid`                 |
| UUID string for relation      | Resolved entity object |
| Array of UUIDs for collection | Typed `Collection`     |
| Integer                       | `int`                  |
| Boolean                       | `bool`                 |
| RFC 3339 string               | `\DateTime`            |

### Backend user context

**Configuring query parameter names:**

```yaml
rest-request-handler:
  page-query-name: page
  per-page-query-name: per_page
  multisort-query-name: sort
  predefined-query-name: q
  default-page-size: 50
```

**Configuring comparison operators** — override any operator symbol or disable one entirely by setting it to `null`:

```yaml
rest-request-handler:
  comparison-operators:
    contains: "*"
    starts-with: "^"
    ends-with: "$"
    is-not: "!"
    is-in: "∈"
    is-not-in: "∉"
    is-more-than: ">>"
    is-less-than: "<<"
    is-at-least: ">"
    is-at-most: "<"
```

**Ownership filters** — if an entity is annotated with `#[AddOwnershipFilter]`, `SelectorBuilder` automatically applies the ownership filter to every query, ensuring users can only see their own records without any additional handler code.
