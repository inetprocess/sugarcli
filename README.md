# SugarCli
SugarCli is a command line tool to install and manage SugarCRM installations.


# Install
Get the latest phar archive at `https://github.com/inetprocess/sugarcli/blob/master/build/sugarcli.phar`. Allow the execution and run it.
```
cd /usr/local/bin/
wget 'https://github.com/inetprocess/sugarcli/releases/download/v1.25.8/sugarcli.phar'
chmod +x ./sugarcli.phar
mv sugarcli.phar sugarcli
sugarcli help
```

Or clone this git repository and use `./bin/sugarcli`.


# Build
Clone the git repository and run
```sh
composer install --no-dev --quiet -o
mkdir build
ulimit -Sn 4096
php -dphar.readonly=0 bin/box build
```
It will build the `sugarcli.phar`  Phar archive in the `build` folder.

## Creating a new phar file and release it as a new version
```
Draft a new release with a new tag and publish this release
On local do, git checkout master
git pull
Verify the latest tag using the command: git describe --tag --always HEAD
The output should contain the latest tag
In the repo codebase, make the necessary changes
Create a new sugarcli.phar file using the build process mentioned above
Create a pull request, update the readme version with the latest release version.
Add the latest phar file to this pull request as well.
Merge the pull request
Navigate to the latest release from browser
Edit the release and upload the latest phar file to this release. Publish it.
Install the latest phar file from this release link.
Check sugarcli version. It should be set to the latest release version.
```

# Configuration
You can save some configurations options in different location. The latter one will override the previous one:
`/etc/sugarclirc`
`$HOME/.sugarclirc`
`./.sugarclirc`

Command line parameters will override these configurations.

## Example
```yaml
---
sugarcrm:
    path: PATH              #Path to Sugarcrm relative to the configuration file
    user_id: USER_ID        #SugarCRM user id to impersonate when running the command
metadata:
    file: FILE              #Path to the metadata file relative to the configuration file
account:
    name: ACCOUNT_NAME      #Name of the account
backup:
    prefix: PREFIX          #Prefix to prepend to name of archive file when creating backups
maintenance:
    page: FILE or CONTENT   #File name or content of maintenance page
    allowed_ips:            #List of ips allowed to by-pass the maintenance page
        - IP1
        - IP2
        - ...
```


# Usage
* `./sugarcli.phar list`: List all commands available
* `./sugarcli.phar namespace:command --help`: Display help for a specific command

See the [USAGE.md](USAGE.md) file for a complete list of commands and the associated help

# Development
## Run tests
Copy the file `phpunit.xml.dist` to `phpunit.xml` and edit the environment variables.

Run the full test suite with `bin/phpunit` or exclude groups to avoid required external resources `bin/phpunit --exclude-group inventory,sugarcrm-db`

__Available groups__:
* inventory
* sugarcrm-db
* sugarcrm-path
* sugarcrm-url

## Generate USAGE.md command documentation
```
bin/sugarcli list --format json | php bin/format_help.php  >| USAGE.md
```

