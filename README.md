#Installation
- Clone the base package into the project directory:
```
git clone git@github.com:tarantuli/medas-package-base.git <directory>
```
- Replace all occurrences of `placeholder` in the code _and_ the file names by the package name
- Run `composer update`
- Delete the cloned `.git` directory, and reinitialize git with `git init`
- Restart PhpStorm
- Create a new repository on GitHub
- Commit the working directory to `master`, then switch to a new branch `dev`, and push that to the remote
