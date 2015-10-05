# SugarCli

SugarCli is a command line tool to install and manage SugarCRM installations.

## Installing

Get the phar archive at `http://apt.inetprocess.fr/pub/sugarcli.phar`. Allow the execution and run it.
```
wget 'http://apt.inetprocess.fr/pub/sugarcli.phar'
chmod +x ./sugarcli.phar
./sugarcli.phar
```

Or clone this git repository and use `./bin/sugarcli`.

## Building

Clone the git repository and run `php -dphar.readonly=0 bin/compile`.
It will build the `sugarcli.phar` at the top of the git project.

## Configuration
You can save some configurations options in different location. The latter one will override the previous one.
`/etc/sugarclirc`
`$HOME/.sugarclirc`
`./.sugarclirc`

Command line parameters will override these configurations.

### Example
```yaml
---
sugarcrm:
    path: path/to/sugar
    url: http://external.url
```

## Usage

`./sugarcli.phar --help`: This will give you the help and list of available commands.

### Clean language files.

#### Test run
`./sugarcli.phar clean:langfiles --test path/to/sugar`
This will parse the custom languages files from sugar. It should return the files as is.

#### Clean without sorting.
`./sugarcli.phar clean:langfiles --no-sort path/to/sugar`
This will clean the lang files by removing unecessary whitespaces and remove duplicates in variables definitions.

#### Clean and sort
`./sugarcli.phar clean:langfiles path/to/sugar`
This will clean and sort the language files.
All defined variables will be sorted by name.

### Install a SugarCRM

#### Configure your installation
`./sugarcli.phar install:config:get` will create a `config_si.php` in the current directory.
This provides default settings for the installer. You will need to complete some require parameters
like db information, usernames and passwords. Required fields are in the form `<VALUE>`.

#### Run the installer
`./sugarcli.phar install:run [-f|--force] [-s|--source[="..."]] [-c|--config[="..."]] path url`
You need to specify an installation path and the public url for your sugar installation.
The installer will extract a SugarCRM installation package named sugar.zip or specified with the `--source` option.
It will use the `--config` option to use for the installation.

#### Examples
```
./sugarcli.phar install:config:get
nano config_si.php
./sugarcli.phar install:run -v ~/www/sugar7 http://myserver.example.org/sugar7 --source ~/sugar_package/SugarPro-Full-7.2.2.1.zip
```
Use `-v` or `-vv` to add more verbose output.

### Manage `fields_meta_data` table.
By default the metadata definition file will be `<sugar_path>/../db/fields_meta_data.yaml`.
You can override it with the `--metadata-file` parameter for all the sub-commands.

#### Status
`sugarcli metadata:status -p path/to/sugar`
This will show which fields are differing between the definition file and the database.

#### Write metadata to a file.
`sugarcli metadata:dump`
You can dump the current DB fields meta data contents into the definition file.
You can also use the `--add`, `--del`, `--update` flags to only add, delete or update fields.
The fields specified after the command line will allow you to act only on specific fields.

#### Load definition to the database.
`sugarcli metadata:load`
Load fields defined in the meta data file to update the database.


### Inventory

#### Get Facts about your environment.

`./sugarcli.phar inventory:facter --path <sugracrm_path> --format yml` will give you a yaml file with various information about
the system and the sugarcrm instance.


#### Report information to an inventory server.

`./sugarcli.phar inventory:agent --path <sugarcrm_path> --account-name 'Name of client' <inventory_url> <username> <password>`
This will send all the gathered facts to the inventory server.


### User management

#### List users of an instance.

`./sugarcli.phar user:list --path <sugarcrm_path>` will give you a nice output of the users.
You can also limit the result to a specific username (`--username`)  and change the output format (`--format`) to json, yml or xml.

#### Create a new user
`./sugarcli.phar user:create --path <sugarcrm_path> --password=mypasword --admin=yes myNewLogin` will create a new admin user
with login myNewLogin and password mypasword.


#### Update a user
`./sugarcli.phar user:update --path <sugarcrm_path> --first-name=Admin --last-name='Test' myNewLogin` will update the user
myNewLogin and set the first and last name.

