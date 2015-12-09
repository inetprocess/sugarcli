# SugarCli
SugarCli is a command line tool to install and manage SugarCRM installations.


# Installing
Get the phar archive at `http://apt.inetprocess.fr/pub/sugarcli.phar`. Allow the execution and run it.
```
wget 'http://apt.inetprocess.fr/pub/sugarcli.phar'
chmod +x ./sugarcli.phar
./sugarcli.phar
```

Or clone this git repository and use `./bin/sugarcli`.


# Building
Clone the git repository and run `php -dphar.readonly=0 bin/compile`.
It will build the `sugarcli.phar` at the top of the git project.


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
    path: path/to/sugar
    url: http://external.url
```


# Usage
`./sugarcli.phar --help`: This will give you the help and list of available commands.

## Clean language files.
The main command is `./sugarcli.phar clean:langfiles`
### Parameters
```bash
--no-sort           Do not sort the files contents. It will still remove duplicates. Useful for testing.
-t, --test          Try to rewrite the files without modifying the contents. Imply --no-sort.
-p, --path=PATH     Path to SugarCRM installation.
```
### Test run
`./sugarcli.phar clean:langfiles --test path/to/sugar`

This will parse the custom languages files from sugar. It should return the files as is.


### Clean without sorting.
`./sugarcli.phar clean:langfiles --no-sort path/to/sugar`

This will clean the lang files by removing unecessary whitespaces and remove duplicates in variables definitions.

### Clean and sort
`./sugarcli.phar clean:langfiles path/to/sugar`

This will clean and sort the language files. All defined variables will be sorted by name.


## Install a SugarCRM
The main command is `./sugarcli.phar install`

Subcommands are :
```bash
./sugarcli.phar install:config:get
./sugarcli.phar install:check
./sugarcli.phar install:run
```

### Configure your installation
`./sugarcli.phar install:config:get` will create a `config_si.php` in the current directory.

This provides default settings for the installer. You will need to complete some require parameters like db information, usernames and passwords. Required fields are in the form `<VALUE>`.

### `install:config:get` - Parameters
```bash
-c, --config=CONFIG   Write to this file instead of config_si.php. [default: "config_si.php"]
-f, --force           Overwrite existing file
```

### `install:check` - Parameters
```bash
-p, --path=PATH       Path to SugarCRM installation.
```

### Run the installer
`./sugarcli.phar install:run [-f|--force] [-s|--source[="..."]] [-c|--config[="..."]] path url`

You need to specify an installation path and the public url for your sugar installation.

The installer will extract a SugarCRM installation package named sugar.zip or specified with the `--source` option.

It will use the `--config` option to use for the installation.

### `install:run` - Parameters
```bash
-f, --force           Force installer to remove target directory if present.
-s, --source=SOURCE   Path to SugarCRM installation package. [default: "sugar.zip"]
-c, --config=CONFIG   PHP file to use as configuration for the installation. [default: "config_si.php"]
-p, --path=PATH       Path to SugarCRM installation.
```


### Examples
```bash
./sugarcli.phar install:config:get
nano config_si.php
./sugarcli.phar install:run -v ~/www/sugar7 http://myserver.example.org/sugar7 --source ~/sugar_package/SugarPro-Full-7.2.2.1.zip
```
Use `-v` or `-vv` to add more verbose output.

## Manage `fields_meta_data` table.
By default the metadata definition file will be `<sugar_path>/../db/fields_meta_data.yaml`.

You can override it with the `--metadata-file` parameter for all the sub-commands.

The main command is `./sugarcli.phar metadata`

Subcommands are :
```bash
./sugarcli.phar metadata:loadfromfile
./sugarcli.phar metadata:dumptofile
./sugarcli.phar metadata:status
```

### Load definition to the database.
`sugarcli metadata:load`
Load fields defined in the meta data file to update the database.

### `metadata:loadfromfile` Parameters
```bash
-s, --sql                          Print the sql queries that would have been executed.
-f, --force                        Really execute the SQL queries to modify the database.
-a, --add                          Add new fields from the file to the DB.
-d, --del                          Delete fields not present in the metadata file from the DB.
-u, --update                       Update the DB for modified fields in metadata file.
-p, --path=PATH                    Path to SugarCRM installation.
-m, --metadata-file=METADATA-FILE  Path to the metadata file. (default: "<sugar_path>/../db/fields_meta_data.yaml")
```

### Write metadata to a file.
`sugarcli metadata:dump`

You can dump the current DB fields meta data contents into the definition file.

You can also use the `--add`, `--del`, `--update` flags to only add, delete or update fields.

The fields specified after the command line will allow you to act only on specific fields.

### `metadata:dumptofile` Parameters
```bash
-a, --add                          Add new fields from the DB to the definition file.
-d, --del                          Delete fields not present in the DB from the metadata file.
-u, --update                       Update the metadata file for modified fields in the DB.
-p, --path=PATH                    Path to SugarCRM installation.
-m, --metadata-file=METADATA-FILE  Path to the metadata file. (default: "<sugar_path>/../db/fields_meta_data.yaml")
```

### Get the Status
`sugarcli metadata:status -p path/to/sugar`

This will show which fields are differing between the definition file and the database.

### `metadata:status` Parameters
```bash
-p, --path=PATH                    Path to SugarCRM installation.
-m, --metadata-file=METADATA-FILE  Path to the metadata file. (default: "<sugar_path>/../db/fields_meta_data.yaml")
```


## Inventory
The main command is `./sugarcli.phar inventory`

Subcommands are :
```bash
./sugarcli.phar inventory:facter
./sugarcli.phar inventory:agent
```

### Get Facts about your environment.
`./sugarcli.phar inventory:facter --path <sugracrm_path> --format yml` will give you a yaml file with various information about
the system and the sugarcrm instance.

### `inventory:facter` Parameters
```bash
-F, --custom-fact=CUSTOM-FACT  Add or override facts. Format: path.to.fact:value (multiple values allowed)
-f, --format=FORMAT            Specify the output format. (json|yml|xml). [default: "yml"]
-p, --path=PATH                Path to SugarCRM installation.
```

### Report information to an inventory server.
`./sugarcli.phar inventory:agent --path <sugarcrm_path> --account-name 'Name of client' <inventory_url> <username> <password>`

This will send all the gathered facts to the inventory server.

### `inventory:agent` Parameters
```bash
-F, --custom-fact=CUSTOM-FACT    Add or override facts. Format: path.to.fact:value (multiple values allowed)
-p, --path=PATH                  Path to SugarCRM installation.
-a, --account-name=ACCOUNT-NAME  Name of the account.
```


## User management
The main command is `./sugarcli.phar user`

Subcommands are :
```bash
./sugarcli.phar user:update
./sugarcli.phar user:create
./sugarcli.phar user:list
```

### Update a user
`./sugarcli.phar user:update --path <sugarcrm_path> --first-name=Admin --last-name='Test' myNewLogin` will update the user
myNewLogin and set the first and last name.

### `user:update` Parameters
```bash
-c, --create                 Create the user instead of updating it. Optional if called with users:create.
-f, --first-name=FIRST-NAME  First name of the user.
-l, --last-name=LAST-NAME    Last name of the user.
-P, --password=PASSWORD      Password of the user.
-a, --admin=ADMIN            Make the user administrator. [yes/no]
-A, --active=ACTIVE          Make the user active. [yes/no]
-p, --path=PATH              Path to SugarCRM installation.
```

### Create a new user
`./sugarcli.phar user:create --path <sugarcrm_path> --password=mypasword --admin=yes myNewLogin` will create a new admin user
with login myNewLogin and password mypasword.

### `user:create` Parameters
```bash
-c, --create                 Create the user instead of updating it. Optional if called with users:create.
-f, --first-name=FIRST-NAME  First name of the user.
-l, --last-name=LAST-NAME    Last name of the user.
-P, --password=PASSWORD      Password of the user.
-a, --admin=ADMIN            Make the user administrator. [yes/no]
-A, --active=ACTIVE          Make the user active. [yes/no]
-p, --path=PATH              Path to SugarCRM installation.
```


### List users of an instance.
`./sugarcli.phar user:list --path <sugarcrm_path>` will give you a nice output of the users.

You can also limit the result to a specific username (`--username`)  and change the output format (`--format`) to json, yml or xml.

### `user:list` Parameters
```bash
-u, --username=USERNAME  Login of the user.
-f, --format=FORMAT      Output format. (text|json|yml|xml) [default: "text"]
-F, --fields=FIELDS      List of comma separated field name. [default: "id,user_name,is_admin,status,first_name,last_name"]
-l, --lang=LANG          Lang for display. [default: "en_us"]
-p, --path=PATH          Path to SugarCRM installation.
```


## System
The main command is `./sugarcli.phar system`

Subcommands are:
```bash
./sugarcli.phar system:quickrepair
```

### Do a Quick Repair & Rebuild
`./sugarcli.phar system:quickrepair --path <sugarcrm_path>` will do a basic Quick Repair & Rebuild of your SugarCRM instance.

You can also use `--database` to see if Vardefs are synchronized with the Database.

If they are not in sync you can run the queries by adding `--force`.

Finally, if you want to have the full output from SugarCRM, add the verbose (`--verbose`) option.

### `system:quickrepair` Parameters
```bash
-d, --database        Manage database changes.
-f, --force           Really execute the SQL queries (displayed by using -v).
-p, --path=PATH       Path to SugarCRM installation.
```
### Example:
The command `./sugarcli.phar system:quickrepair --database` has that type of output:
```
Reparation:
 - Repair Done.

Database Messages:
Database tables are synced with vardefs
```


## Logic Hooks
The main command is `./sugarcli.phar hooks`

Subcommands are:
```bash
./sugarcli.phar hooks:list
```
### List the existing logic hooks for a module
`./sugarcli.phar hooks:list --path <sugarcrm_path> --module <module>` will generate of list of hooks for the specified module.

That command lists the hooks with, for each, its Weight, description, the file where the class is defined, the method called, and where it's defined.

You can also use `--compact` to have the basic informations about hooks (Weight / Description / Method).
### Parameters
```
-m, --module=MODULE   Module's name.
    --compact         Activate compact mode
-p, --path=PATH       Path to SugarCRM installation.
```
### Example
The command `./sugarcli.phar hooks:list --module Contacts --compact` gives that type of output, for a module with no Hooks:
```
+-----------+-------------+--------+
| Hooks definition for Contacts    |
+-----------+-------------+--------+
| Weight    | Description | Method |
+-----------+-------------+--------+
| No Hooks for that module         |
+-----------+-------------+--------+
```
