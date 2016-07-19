# SugarCli
SugarCli is a command line tool to install and manage SugarCRM installations.


# Installing
Get the latest phar archive at `https://getsugarcli.inetprocess.fr/sugarcli.phar`. Allow the execution and run it.
```
wget 'https://getsugarcli.inetprocess.fr/sugarcli.phar'
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



# Development
## Run tests
Copy the file `phpunit.xml.dist` to `phpunit.xml` and edit the environment variables.

Run the full test suite with `bin/phpunit` or exclude groups to avoid required external resources `bin/phpunit --exclude-group inventory,sugarcrm-db`

__Available groups__:
* inventory
* sugarcrm-db
* sugarcrm-path
* sugarcrm-url



# Commands
* [Clean language files](#clean-language-files)
* [Install a SugarCRM](#install-a-sugarcrm)
* [Manage `fields_meta_data` and `relationships` tables](#manage-fields_meta_data-table)
* [Inventory](#inventory)
* [User Management](#user-management)
* [System](#system)
* [Logic Hooks](#logic-hooks)
* [Vardefs Extractor](#vardefs-extractor)
* [Code Generator](#code-generator)
* [Data Anonymization](#data-anonymization)



## Clean language files
The main command is `./sugarcli.phar clean:langfiles`
#### Parameters
```bash
--no-sort           Do not sort the files contents. It will still remove duplicates. Useful for testing.
-t, --test          Try to rewrite the files without modifying the contents. Imply --no-sort.
-p, --path=PATH     Path to SugarCRM installation.
```
#### Test run
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

#### `install:config:get` - Parameters
```bash
-c, --config=CONFIG   Write to this file instead of config_si.php. [default: "config_si.php"]
-f, --force           Overwrite existing file
```

#### `install:check` - Parameters
```bash
-p, --path=PATH       Path to SugarCRM installation.
```

### Run the installer
`./sugarcli.phar install:run [-f|--force] [-s|--source[="..."]] [-c|--config[="..."]] path url`

You need to specify an installation path and the public url for your sugar installation.

The installer will extract a SugarCRM installation package named sugar.zip or specified with the `--source` option.

It will use the `--config` option to use for the installation.

#### `install:run` - Parameters
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


## Manage `fields_meta_data` and `relationships` tables
Two groups of commands are available to export and sync the content of the fields_meta_data table (custom fields from studio)
and relationships (default and custom relationships).

The first has `metadata` as a prefix and the second has `rels`

By default the metadata definition file will be `<sugar_path>/../db/fields_meta_data.yaml` and the relationships will be `<sugar_path>/../db/relationships.yaml`.

You can override it with the `--metadata-file` parameter for all the `metadata` sub-commands and `--file` parameter for all the `rels` subcommands.

The main command are then `./sugarcli.phar metadata` and `./sugarcli.phar rels`

Subcommands are :
```bash
./sugarcli.phar metadata:loadfromfile
./sugarcli.phar metadata:dumptofile
./sugarcli.phar metadata:status

./sugarcli.phar rels:loadfromfile
./sugarcli.phar rels:dumptofile
./sugarcli.phar rels:status

```

The following explanations are made for `metadata` but are similar for `rels`

### Load definition to the database
`sugarcli {type}:loadfromfile`
Load fields defined in the meta data file to update the database.

#### `metadata:loadfromfile` Parameters
```bash
-s, --sql                          Print the sql queries that would have been executed.
-f, --force                        Really execute the SQL queries to modify the database.
-a, --add                          Add new fields from the file to the DB.
-d, --del                          Delete fields not present in the metadata file from the DB.
-u, --update                       Update the DB for modified fields in metadata file.
-p, --path=PATH                    Path to SugarCRM installation.
-m, --metadata-file=METADATA-FILE  Path to the metadata file. (default: "<sugar_path>/../db/fields_meta_data.yaml")
```

#### `rels:loadfromfile` Parameters
```bash
-s, --sql             Print the sql queries that would have been executed.
-f, --force           Really execute the SQL queries to modify the database.
-a, --add             Add new fields from the file to the DB.
-d, --del             Delete fields not present in the relationships file from the DB.
-u, --update          Update the DB for modified fields in relationships file.
-p, --path=PATH       Path to SugarCRM installation.
    --file=FILE       Path to the rels file. (default: "<sugar_path>/../db/relationships.yaml")
```


### Write definition to a file
`sugarcli {type}:dump`

You can dump the current DB fields_meta_data (or relationships) contents into the definition file.

You can also use the `--add`, `--del`, `--update` flags to only add, delete or update fields (or relationships).

The fields specified after the command line will allow you to act only on specific fields (or relationships).

#### `metadata:dumptofile` Parameters
```bash
-a, --add                          Add new fields from the DB to the definition file.
-d, --del                          Delete fields not present in the DB from the metadata file.
-u, --update                       Update the metadata file for modified fields in the DB.
-p, --path=PATH                    Path to SugarCRM installation.
-m, --metadata-file=METADATA-FILE  Path to the metadata file. (default: "<sugar_path>/../db/fields_meta_data.yaml")
```

#### `rels:dumptofile` Parameters
```bash
-a, --add             Add new relationships from the DB to the definition file.
-d, --del             Delete relationships not present in the DB
-u, --update          Update the relationships in the DB.
-p, --path=PATH       Path to SugarCRM installation.
    --file=FILE       Path to the rels file. (default: "<sugar_path>/../db/relationships.yaml")
```


### Get the Status
`sugarcli {type}:status -p path/to/sugar`

This will show which fields are differing between the definition file and the database.

#### `metadata:status` Parameters
```bash
-p, --path=PATH                    Path to SugarCRM installation.
-m, --metadata-file=METADATA-FILE  Path to the metadata file. (default: "<sugar_path>/../db/fields_meta_data.yaml")
```

#### `rels:status` Parameters
```bash
-p, --path=PATH       Path to SugarCRM installation.
    --file=FILE       Path to the rels file. (default: "<sugar_path>/../db/relationships.yaml")
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

#### `inventory:facter` Parameters
```bash
-F, --custom-fact=CUSTOM-FACT  Add or override facts. Format: path.to.fact:value (multiple values allowed)
-f, --format=FORMAT            Specify the output format. (json|yml|xml). [default: "yml"]
-p, --path=PATH                Path to SugarCRM installation.
```

### Report information to an inventory server.
`./sugarcli.phar inventory:agent --path <sugarcrm_path> --account-name 'Name of client' <inventory_url> <username> <password>`

This will send all the gathered facts to the inventory server.

#### `inventory:agent` Parameters
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

#### `user:update` Parameters
```bash
-c, --create                 Create the user instead of updating it. Optional if called with users:create.
-f, --first-name=FIRST-NAME  First name of the user.
-l, --last-name=LAST-NAME    Last name of the user.
-P, --password=PASSWORD      Password of the user [UNSAFE].
    --ask-password           Ask for user password.
-a, --admin=ADMIN            Make the user administrator. [yes/no]
-A, --active=ACTIVE          Make the user active. [yes/no]
-p, --path=PATH              Path to SugarCRM installation.
```

### Create a new user
`./sugarcli.phar user:create --path <sugarcrm_path> --password=mypasword --admin=yes myNewLogin` will create a new admin user
with login myNewLogin and password mypasword.

#### `user:create` Parameters
```bash
-c, --create                 Create the user instead of updating it. Optional if called with users:create.
-f, --first-name=FIRST-NAME  First name of the user.
-l, --last-name=LAST-NAME    Last name of the user.
-P, --password=PASSWORD      Password of the user [UNSAFE].
    --ask-password           Ask for user password.
-a, --admin=ADMIN            Make the user administrator. [yes/no]
-A, --active=ACTIVE          Make the user active. [yes/no]
-p, --path=PATH              Path to SugarCRM installation.
```


### List users of an instance.
`./sugarcli.phar user:list --path <sugarcrm_path>` will give you a nice output of the users.

You can also limit the result to a specific username (`--username`)  and change the output format (`--format`) to json, yml or xml.

#### `user:list` Parameters
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

#### `system:quickrepair` Parameters
```bash
-d, --database        Manage database changes.
-f, --force           Really execute the SQL queries (displayed by using -v).
-p, --path=PATH       Path to SugarCRM installation.
```
#### Example:
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
#### Parameters
```
-m, --module=MODULE   Module's name.
    --compact         Activate compact mode
-p, --path=PATH       Path to SugarCRM installation.
```
#### Example
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


## Vardefs Extractor
### Extract fields and relationships for a module
`./sugarcli.phar extract:fields --path <sugarcrm_path> --module <module>` will extract all the fields defined for a module, with theirs parameters (Label, content of dropdowns, dbType, etc ...) and write 2 csv files containing the data.
#### Parameters
```
-m, --module=MODULE   Module's name.
    --lang=LANG       SugarCRM Language [default: "fr_FR"]
-p, --path=PATH       Path to SugarCRM installation.
```


## Code Generator
The main command is `./sugarcli.phar code`

Subcommands are:
```bash
./sugarcli.phar code:setupcomposer
./sugarcli.phar code:button
./sugarcli.phar code:execute:file
```

### Install composer in custom/
`./sugarcli.phar code:setupcomposer --path <sugarcrm_path> --do` will create a new Util to use composer's autoloader and create a composer.json file that contains, by default, libsugarcrm autoloaded for Unit Tests.
#### Parameters
```
    --do                Create the files
-r, --reinstall     Reinstall the files
    --no-quickrepair    Do not launch a Quick Repair
-p, --path=PATH     Path to SugarCRM installation.
```


### Create a new button in a view
`./sugarcli.phar code:button --path <sugarcrm_path> --module <module>` will create a new button in a record view of the module <module>

That command automatically add buttons, their label and the JS triggered by the button to views, from a name.

The file affected are :
* custom/Extension/modules/<module>/Ext/Language/<current_lang>.php
* custom/modules/<module>/clients/base/views/record/record.php
* custom/modules/<module>/clients/base/views/record/record.js

#### Parameters
```
-m, --module=MODULE   Module name.
-a, --action=ACTION   Action: "add" / "delete" [default: "add"]
    --name=NAME       Button Name
-t, --type=TYPE       For now only "dropdown" [default: "dropdown"]
-j, --javascript      [EXPERIMENTAL] Also create the JS
-p, --path=PATH       Path to SugarCRM installation.
```

As described, the --javascript is experimental. If you don't have a record.js file that should work well, else you have to check the generated file to make sure it didn't break anything.


### Execute a php file from the SugarCRM context
`./sugracli.phar code:execute:file --path <sugarcrm_path> [--user-id='1'] <test.php>` will execute the file `test.php` by loading
first the sugarcrm environment. So the script can directly use the classes and db from sugar.
You can also set the user_id from the command line to have another one than the default administrator.



## Data Anonymization
The main command is `./sugarcli.phar anonymize`

If you need to anonymize the data from your database (**Be careful that command will overwrite the data directly in the database**)
to give a dump to a partner or a developer, you can use sugarcli to do the work, because:
* It connects directly to the SugarCRM DB
* It is able to generate a configuration file automatically, without destroying the system tables (config / relationships, etc...)
* Because it uses (Faker)[https://github.com/fzaninotto/Faker] to generate a data that looks *almost* real.

Subcommands are:
```bash
./sugarcli.phar anonymize:config
./sugarcli.phar anonymize:run
```
### Generate a configuration for your current SugarCRM
`./sugarcli.phar anonymize:config --path <sugarcrm_path>` generate a yaml file that contains a full configuration, for all table that have been found and tries to guess what Faker method sugarcli has to use for each field of each table.

#### Parameters
```
    --file=FILE                  Path to the configuration file [default: "../db/anonymization.yml"]
    --ignore-table=IGNORE-TABLE  Table to ignore. Can be repeated (multiple values allowed)
    --ignore-field=IGNORE-FIELD  Field to ignore. Can be repeated (multiple values allowed)
-p, --path=PATH                  Path to SugarCRM installation.


```
#### Example
The command `./sugarcli.phar anonymize:config` creates a file that looks like:
```yaml
guesser_version: 1.0.0
entities:
    accounts:
        cols:
            name: { method: company }
            description: { method: sentence, params: [20] }
            facebook: { method: url }
            twitter: { method: url }
            googleplus: { method: url }
            account_type: { method: randomElement, params: [['', Analyst, Competitor, Customer, Integrator]] }
            industry: { method: randomElement, params: [['', Apparel, Banking, Biotechnology, Chemicals]] }
            annual_revenue: { method: randomNumber, params: [4] }
            phone_fax: { method: phoneNumber }
            billing_address_street: { method: streetAddress }
            billing_address_city: { method: city }
            billing_address_state: { method: state }
            billing_address_postalcode: { method: postcode }
            billing_address_country: { method: country }
            rating: { method: sentence, params: [8] }
            phone_office: { method: phoneNumber }
            phone_alternate: { method: phoneNumber }
            website: { method: url }
....
```

As you can see, the commands uses different methods to guess the type of faker method to use:
* If it's a dropdown and it get the list of values from the vardefs
* If it's a field that contains a known word it uses the right method (example .*_city = city)
* Else it uses the dbType (varchar = sentence)

You can change the content of the file once generated to match your criteras.


### Run the anonymization
**Be careful that command will overwrite the data directly in the database**

`./sugarcli.phar anonymize:run --path <sugarcrm_path>` does the job !

#### Parameters
```
    --file=FILE       Path to the configuration file [default: "../db/anonymization.yml"]
    --force           Run the queries
    --remove-deleted  Remove all records with deleted = 1. Won't be launched if --force is not set
    --clean-cstm      Clean all records in _cstm that are not in the main table. Won't be launched if --force is not set
    --sql             Display the SQL of UPDATE queries
    --table=TABLE     Anonymize only that table (repeat for multiple values) (multiple values allowed)
-p, --path=PATH       Path to SugarCRM installation.

```
#### Example
The command `./sugarcli.phar anonymize:run --table=accounts --force` gives an output that looks like:
```
Be careful, the anonymization is going to start
That will overwrite every data in the Database !

If you are sure, please type "yes" in uppercase
YES
Anonymizing accounts
 50/50 [============================] 100%

Emptying accounts_audit
Emptying bugs_audit
Emptying campaigns_audit
Emptying cases_audit
Emptying contacts_audit
Emptying contracts_audit
....

Done in 0.42 sec (consuming 40.5Mb)

....
```
