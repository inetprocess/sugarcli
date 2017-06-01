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
Clone the git repository and run
```sh
composer install --no-dev --quiet -o
mkdir build
ulimit -Sn 4096
php -dphar.readonly=0 bin/box build
```
It will build the `sugarcli.phar`  Phar archive in the `build` folder.


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
    path: PATH          #Path to Sugarcrm relative to the configuration file
    user_id: USER_ID    #SugarCRM user id to impersonate when running the command
metadata:
    file: FILE          #Path to the metadata file relative to the configuration file
account:
    name: ACCOUNT_NAME  #Name of the account
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

