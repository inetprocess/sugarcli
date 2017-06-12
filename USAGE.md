Commands list
=============

* [help](#help)
* [list](#list)
* [self-update](#self-update)
* [selfupdate](#selfupdate)

**anonymize:**

* [anonymize:config](#anonymizeconfig)
* [anonymize:run](#anonymizerun)

**backup:**

* [backup:dump:all](#backupdumpall)
* [backup:dump:database](#backupdumpdatabase)
* [backup:dump:files](#backupdumpfiles)
* [backup:restore:all](#backuprestoreall)
* [backup:restore:database](#backuprestoredatabase)
* [backup:restore:files](#backuprestorefiles)

**clean:**

* [clean:langfiles](#cleanlangfiles)

**code:**

* [code:button](#codebutton)
* [code:execute:file](#codeexecutefile)
* [code:setupcomposer](#codesetupcomposer)

**database:**

* [database:clean](#databaseclean)
* [database:export:csv](#databaseexportcsv)

**extract:**

* [extract:fields](#extractfields)

**hooks:**

* [hooks:list](#hookslist)

**install:**

* [install:check](#installcheck)
* [install:config:get](#installconfigget)
* [install:run](#installrun)

**inventory:**

* [inventory:agent](#inventoryagent)
* [inventory:facter](#inventoryfacter)

**metadata:**

* [metadata:dumptofile](#metadatadumptofile)
* [metadata:loadfromfile](#metadataloadfromfile)
* [metadata:status](#metadatastatus)

**rels:**

* [rels:dumptofile](#relsdumptofile)
* [rels:loadfromfile](#relsloadfromfile)
* [rels:status](#relsstatus)

**system:**

* [system:quickrepair](#systemquickrepair)

**user:**

* [user:create](#usercreate)
* [user:list](#userlist)
* [user:update](#userupdate)

Commands details
=============

help
----

Displays help for a command

**Usage**: `help [--xml] [--format FORMAT] [--raw] [--] [<command_name>]`

The `help` command displays help for a given command:

  `php bin/sugarcli help list`

You can also output the help in other formats by using the **--format** option:

  `php bin/sugarcli help --format=xml list`

To display the list of available commands, please use the `list` command.

### Arguments
* `command_name`	The command name

### Options
* `    --xml`	To output help as XML
* `    --format=FORMAT`	The output format (txt, xml, json, or md) **[default: `txt`]**
* `    --raw`	To output raw command help

list
----

Lists commands

**Usage**: `list [--xml] [--raw] [--format FORMAT] [--] [<namespace>]`

The `list` command lists all commands:

  `php bin/sugarcli list`

You can also display the commands for a specific namespace:

  `php bin/sugarcli list test`

You can also output the information in other formats by using the **--format** option:

  `php bin/sugarcli list --format=xml`

It's also possible to get raw list of commands (useful for embedding command runner):

  `php bin/sugarcli list --raw`

### Arguments
* `namespace`	The namespace name

### Options
* `    --xml`	To output list as XML
* `    --raw`	To output raw command list
* `    --format=FORMAT`	The output format (txt, xml, json, or md) **[default: `txt`]**

self-update
-----------

Update the `sugarcli.phar` with the latest stable version

**Usage**: `self-update [-r|--rollback]`
`selfupdate`

### Options
* `-r, --rollback`	Rollback to the previous version of `sugracli.phar`

anonymize:config
----------------

Generate a configuration for the Anonymizer

**Usage**: `anonymize:config [-p|--path PATH] [--user-id USER-ID] [-f|--file FILE] [-T|--ignore-table IGNORE-TABLE] [-F|--ignore-field IGNORE-FIELD]`

Generate a full yaml configuration file for all tables found in the SugarCRM instance database.
It guesses the transformations to apply based on the SugarCRM metadata.
* **Dropdown:** Get the list of values from the vardefs
* **Known column name:** Uses the right generation method (example .*_city = city)
* **DB type:** Exemple: varchar = sentence

To actually anonymize the data, run the `anonymize:run` command with the generated configuration file
You can also modify the file for your need before running the command.

**Example:**
```
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
### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `    --user-id=USER-ID`	SugarCRM user id to impersonate when running the command **[config: sugarcrm.user_id]** **[default: `1`]**
* `-f, --file=FILE`	Output configuration to this file **[default: `../db/anonymization.yml`]**
* `-T, --ignore-table=IGNORE-TABLE`	Table to ignore **(multiple values allowed)**
* `-F, --ignore-field=IGNORE-FIELD`	Field to ignore **(multiple values allowed)**

anonymize:run
-------------

Run the Anonymizer

**Usage**: `anonymize:run [-p|--path PATH] [--user-id USER-ID] [-f|--file FILE] [--force] [--remove-deleted] [--clean-cstm] [--sql] [-t|--table TABLE]`

Run the anonymization process base on the configuration file that can be generated
with `anonymize:config`.

**Be careful this command will overwrite the data directly in the database.**

This command is useful to anonymize the data before giving the database to a developer or partner because:
* It connects directly to the SugarCRM DB
* It is able to generate a configuration file automatically, without destroying the system tables
  (config / relationships, etc...)
* It uses [Faker](https://github.com/fzaninotto/Faker) to generate a data that looks *almost* real


### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `    --user-id=USER-ID`	SugarCRM user id to impersonate when running the command **[config: sugarcrm.user_id]** **[default: `1`]**
* `-f, --file=FILE`	Path to the configuration file **[default: `../db/anonymization.yml`]**
* `    --force`	Run the queries
* `    --remove-deleted`	Remove all records with `deleted = 1`, requires `--force` to be set
* `    --clean-cstm`	Clean all records in _cstm that are not in the main table, requires `--force` to be set
* `    --sql`	Display the SQL queries that would be run
* `-t, --table=TABLE`	Anonymize only that table **(multiple values allowed)**

backup:dump:all
---------------

Create backups of files and database of SugarCRM

**Usage**: ``

See help of commands `backup:dump:database` and `backup:dump:files` for more information.

### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `-d, --destination-dir=DESTINATION-DIR`	Destination folder for the achive **[default: `/home/sugarcli/backup`]**
* `-P, --prefix=PREFIX`	Prepend to the archive name **[config: backup.prefix]**
* `-c, --compression=COMPRESSION`	Set the compression algorithm. Valid values are (gzip|bzip2). **[default: `gzip`]**
* `    --dry-run`	Do not run the command only print the tar command
* `-T, --ignore-table=IGNORE-TABLE`	Tables to ignore. **(multiple values allowed)**
* `-D, --ignore-for-dev`	Ignore tables not useful for a dev environement
* `-U, --ignore-upload`	Ignore files in upload/ folder and `*-restore`
* `-C, --ignore-cache`	Ignore cache folder

backup:dump:database
--------------------

Create a backup file of SugarCRM database

**Usage**: `backup:dump:database [-p|--path PATH] [-d|--destination-dir DESTINATION-DIR] [-P|--prefix PREFIX] [-c|--compression COMPRESSION] [--dry-run] [-T|--ignore-table IGNORE-TABLE] [-D|--ignore-for-dev]`

Backup the SugarCRM database in to a compressed SQL dump.
The prefix can be set in the configuration file `.sugarclirc` like this:
```
backup:
    prefix: my_prefix

```
The tables not dumped with `--ignore-for-dev` are:
* `activities`
* `activities_users`
* `fts_queue`
* `inbound_email`
* `job_queue`
* `outbound_email`
* `tracker`
* `tracker_perf`
* `tracker_queries`
* `tracker_sessions`
* `tracker_tracker_queries`


### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `-d, --destination-dir=DESTINATION-DIR`	Destination folder for the achive **[default: `/home/sugarcli/backup`]**
* `-P, --prefix=PREFIX`	Prepend to the archive name **[config: backup.prefix]**
* `-c, --compression=COMPRESSION`	Set the compression algorithm. Valid values are (gzip|bzip2). **[default: `gzip`]**
* `    --dry-run`	Do not run the command only print the tar command
* `-T, --ignore-table=IGNORE-TABLE`	Tables to ignore. **(multiple values allowed)**
* `-D, --ignore-for-dev`	Ignore tables not useful for a dev environement

backup:dump:files
-----------------

Create a backup archive of SugarCRM files

**Usage**: `backup:dump:files [-p|--path PATH] [-d|--destination-dir DESTINATION-DIR] [-P|--prefix PREFIX] [-c|--compression COMPRESSION] [--dry-run] [-U|--ignore-upload] [-C|--ignore-cache]`

Backup the SugarCRM files in to a compressed tar archive.

The prefix can be set in the configuration file `.sugarclirc` like this:
```
backup:
    prefix: my_prefix

```
### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `-d, --destination-dir=DESTINATION-DIR`	Destination folder for the achive **[default: `/home/sugarcli/backup`]**
* `-P, --prefix=PREFIX`	Prepend to the archive name **[config: backup.prefix]**
* `-c, --compression=COMPRESSION`	Set the compression algorithm. Valid values are (gzip|bzip2). **[default: `gzip`]**
* `    --dry-run`	Do not run the command only print the tar command
* `-U, --ignore-upload`	Ignore files in upload/ folder and `*-restore`
* `-C, --ignore-cache`	Ignore cache folder

backup:restore:all
------------------

Restore both the database and files of a SugarCRM instance

**Usage**: ``

Restore a complete SugarCRM instance from archive files.
The `--archive` file must point to the files dump and the database dump must start with the same name.

### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `-c, --compression=COMPRESSION`	Set the compression algorithm. By default it is guessed from file extention. Valid values are (gzip|bzip2).
* `    --dry-run`	Do not run the command only print the tar command
* `-a, --archive=ARCHIVE`	Dump file to extract
* `    --overwrite`	Overwrite files in place if it already exists.
* `-f, --force`	Force import even errors are encountered

backup:restore:database
-----------------------

Restore a database from a previous backup

**Usage**: `backup:restore:database [-p|--path PATH] [-c|--compression COMPRESSION] [--dry-run] [-a|--archive ARCHIVE] [-f|--force]`

### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `-c, --compression=COMPRESSION`	Set the compression algorithm. By default it is guessed from file extention. Valid values are (gzip|bzip2).
* `    --dry-run`	Do not run the command only print the tar command
* `-a, --archive=ARCHIVE`	Dump file to extract
* `-f, --force`	Force import even errors are encountered

backup:restore:files
--------------------

Restore files from a previous backup

**Usage**: `backup:restore:files [-p|--path PATH] [-c|--compression COMPRESSION] [--dry-run] [-a|--archive ARCHIVE] [--overwrite]`

### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `-c, --compression=COMPRESSION`	Set the compression algorithm. By default it is guessed from file extention. Valid values are (gzip|bzip2).
* `    --dry-run`	Do not run the command only print the tar command
* `-a, --archive=ARCHIVE`	Dump file to extract
* `    --overwrite`	Overwrite files in place if it already exists.

clean:langfiles
---------------

Sort and clean PHP arrays in language files to make it easier for vcs programs

**Usage**: `clean:langfiles [-p|--path PATH] [--no-sort] [-t|--test]`

Sort and clean PHP arrays in language files for dropdown lists.
Makes it easier for VCS programs to track real changes and avoid conflicts.
It is recommended to have a clean working directory before executing this command.

### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `    --no-sort`	Do not sort the files contents. It will still remove duplicates. Useful for testing
* `-t, --test`	Try to rewrite the files without modifying the contents, imply `--no-sort`, useful to make sure the parsing is working correctly

code:button
-----------

Add or delete a button in a module

**Usage**: `code:button [-p|--path PATH] [--user-id USER-ID] [-m|--module MODULE] [-a|--action ACTION] [-b|--name NAME] [-t|--type TYPE] [-j|--javascript]`

Creates a new button in the record view menu for the module.
Automatically add buttons, their label and the JS triggered by the button to views, from a name.

The affected files are:
* `custom/Extension/modules/<module>/Ext/Language/<current_lang>.php`
* `custom/modules/<module>/clients/base/views/record/record.php`
* `custom/modules/<module>/clients/base/views/record/record.js`

The `--javascript` is experimental. If you do not already have a `record.js` file
that should work well, else you have to check the generated file to make sure it didn't break anything.


### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `    --user-id=USER-ID`	SugarCRM user id to impersonate when running the command **[config: sugarcrm.user_id]** **[default: `1`]**
* `-m, --module=MODULE`	Module name
* `-a, --action=ACTION`	Action: "add" / "delete" **[default: `add`]**
* `-b, --name=NAME`	Button Name
* `-t, --type=TYPE`	For now only "dropdown" **[default: `dropdown`]**
* `-j, --javascript`	**[EXPERIMENTAL]** Also create the JS

code:execute:file
-----------------

Execute a php file using a SugarCRM loaded context

**Usage**: `code:execute:file [-p|--path PATH] [--user-id USER-ID] [--] <file>`

Execute a PHP file after first loading the sugarcrm environment. The script can directly use the classes
and database from sugar. You can also set the `--user-id` from the command line to have another user
than the default administrator.

### Arguments
* `file`	PHP file to execute

### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `    --user-id=USER-ID`	SugarCRM user id to impersonate when running the command **[config: sugarcrm.user_id]** **[default: `1`]**

code:setupcomposer
------------------

Check that composer is setup to be used with SugarCRM

**Usage**: `code:setupcomposer [-p|--path PATH] [--do] [-r|--reinstall] [--no-quickrepair]`

Create a new Util to use composer's autoloader and create a composer.json file
that contains, by default, libsugarcrm autoloaded for Unit Tests.

### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `    --do`	Create the files
* `-r, --reinstall`	Reinstall the files
* `    --no-quickrepair`	Do not launch a Quick Repair

database:clean
--------------

Remove deleted records as well as data in audit and lost records in _cstm tables

**Usage**: `database:clean [-p|--path PATH] [--user-id USER-ID] [--remove-deleted] [--clean-cstm] [--clean-history] [--clean-activities] [--table TABLE]`

### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `    --user-id=USER-ID`	SugarCRM user id to impersonate when running the command **[config: sugarcrm.user_id]** **[default: `1`]**
* `    --remove-deleted`	Remove all records with deleted = 1. Won't be launched if --force is not set
* `    --clean-cstm`	Clean all records in _cstm that are not in the main table. Won't be launched if --force is not set
* `    --clean-history`	Clean *_audit, job_queue and trackers
* `    --clean-activities`	Clean activities_* and trackers
* `    --table=TABLE`	Clean only that table (repeat for multiple values) **(multiple values allowed)**

database:export:csv
-------------------

Export mysql tables as csv files

**Usage**: `database:export:csv [-p|--path PATH] [--no-sugar] [-u|--db-user DB-USER] [-P|--db-password DB-PASSWORD] [-d|--db-dsn DB-DSN] [--db-my-cnf DB-MY-CNF] [-o|--output-dir OUTPUT-DIR] [-f|--force] [-i|--include INCLUDE] [-e|--exclude EXCLUDE] [-I|--input-file INPUT-FILE] [-O|--output-file OUTPUT-FILE] [-c|--csv-option CSV-OPTION] [--] <database>`

### Arguments
* `database`	Database to use for the export.

### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `    --no-sugar`	Do not use sugar database credentials
* `-u, --db-user=DB-USER`	Database user name.
* `-P, --db-password=DB-PASSWORD`	Database password.
* `-d, --db-dsn=DB-DSN`	DSN string for usage by PDO. By default will try to fetch parameters from `~/.my.cnf`.
* `    --db-my-cnf=DB-MY-CNF`	MySQL configuration file to read for database connexion
* `-o, --output-dir=OUTPUT-DIR`	CSV files will be exported to this directory as TABLE_NAME.csv. **[default: `.`]**
* `-f, --force`	Overwrite existing CSV files.
* `-i, --include=INCLUDE`	Export only the tables matching this pattern. **(multiple values allowed)**
* `-e, --exclude=EXCLUDE`	Exclude the tables matching this pattern. Overrides `table` parameter. **(multiple values allowed)**
* `-I, --input-file=INPUT-FILE`	Export the query read from this file instead of tables.
* `-O, --output-file=OUTPUT-FILE`	When exporting a query, specify this fully qualified file name.
* `-c, --csv-option=CSV-OPTION`	Specify option for csv export. Ex: -c 'delimiter=,' **(multiple values allowed)**

extract:fields
--------------

Export fields and relationships definitions to CSV files

**Usage**: `extract:fields [-p|--path PATH] [--user-id USER-ID] [-m|--module MODULE] [--lang LANG]`

Extract all fields and relationships defined for a module with their parameters (label, dropdown list, dbType, ...)
and write the data to 2 CSV files.

**Example:**
    `sugarcli extract:fields --module Accounts`

### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `    --user-id=USER-ID`	SugarCRM user id to impersonate when running the command **[config: sugarcrm.user_id]** **[default: `1`]**
* `-m, --module=MODULE`	Module's name
* `    --lang=LANG`	SugarCRM Language **[default: `en_us`]**

hooks:list
----------

List hooks of the SugarCRM instance

**Usage**: `hooks:list [-p|--path PATH] [--user-id USER-ID] [-m|--module MODULE] [-c|--compact]`

List the hooks defined for the module. For each hook display the following information:

* **Weight**	Order of execution
* **Description**	Short description
* **File**	File containing the source code for the hook
* **Class**	PHP Class name
* **Method**	Method called when the hook is triggered
* **Defined In**	File where the hook is configured

### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `    --user-id=USER-ID`	SugarCRM user id to impersonate when running the command **[config: sugarcrm.user_id]** **[default: `1`]**
* `-m, --module=MODULE`	List hooks from this module
* `-c, --compact`	Activate compact mode output

install:check
-------------

Check if SugarCRM is installed and configured

**Usage**: `install:check [-p|--path PATH]`

Check if SugarCRM is installed and configured.
Return code is `11` if Sugar is not extracted.
Return code is `12` if Sugar is not installed.

### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**

install:config:get
------------------

Write a default config_si.php file in the current folder

**Usage**: `install:config:get [-c|--config CONFIG] [-f|--force]`

This provides default settings for the installer.
You will need to complete some required parameters like database information, usernames and passwords.
Required fields are in the form `<VALUE>`.

### Options
* `-c, --config=CONFIG`	Write to this file instead of config_si.php. **[default: `config_si.php`]**
* `-f, --force`	Overwrite existing file

install:run
-----------

Extract and install SugarCRM

**Usage**: `install:run [-p|--path PATH] [-u|--url URL] [-f|--force] [-s|--source SOURCE] [-c|--config CONFIG]`

You need to specify an installation path and the public url for your sugar installation.
The installer will extract a SugarCRM installation package named `sugar.zip`
or specified with the `--source` option.
It will use the `--config` option to use for the installation.
**Examples:**
```
    sugarcli install:config:get
    nano config_si.php
    sugarcli install:run -v ~/www/sugar7 http://myserver.example.org/sugar7 --source ~/package/SugarPro-Full-7.2.2.1.zip

```
Use `-v` or `-vv` to make the output more verbose.

### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `-u, --url=URL`	**[DEPRECATED]** This option does nothing and is only kept for backward compatibility
* `-f, --force`	Force the installer to remove the target directory if present
* `-s, --source=SOURCE`	Path to SugarCRM installation package **[default: `sugar.zip`]**
* `-c, --config=CONFIG`	PHP file to use as configuration for the installation **[default: `config_si.php`]**

inventory:agent
---------------

Gather facts and sends a report to an Inventory server

**Usage**: `inventory:agent [-F|--custom-fact CUSTOM-FACT] [-p|--path PATH] [-a|--account-name ACCOUNT-NAME] [--] <server> <username> <password>`

Sends all facts gathered on the system and the SugarCRM instance to an Inventory server.

### Arguments
* `server`	Url of the inventory server
* `username`	Username for server authentication
* `password`	Password for server authentication

### Options
* `-F, --custom-fact=CUSTOM-FACT`	Add or override facts **Format: path.to.fact:value** **(multiple values allowed)**
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `-a, --account-name=ACCOUNT-NAME`	Name of the account **[config: account.name]**

inventory:facter
----------------

Get facts from the system and a Sugar instance

**Usage**: `inventory:facter [-F|--custom-fact CUSTOM-FACT] [-p|--path PATH] [-f|--format FORMAT] [--] [<source>]...`

Output various informations about the system and the sugarcrm instance.

Use the `--format` option to specify your prefered output format.

### Arguments
* `source`	Specify facts source (all|system|sugarcrm)

### Options
* `-F, --custom-fact=CUSTOM-FACT`	Add or override facts **Format: path.to.fact:value** **(multiple values allowed)**
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `-f, --format=FORMAT`	Specify the output format **(json|yml|xml)** **[default: `yml`]**

metadata:dumptofile
-------------------

Dump the contents of the table `fields_meta_data` in a reference file to track modifications

**Usage**: `metadata:dumptofile [-p|--path PATH] [-m|--metadata-file METADATA-FILE] [-a|--add] [-d|--del] [-u|--update] [--] [<fields>]...`

Update the reference YAML file based on the `fields_meta_data`. This file should be managed with a VCS.
You can filter which modification you whish to apply with the options `--add,--del,--update` or by setting
the fields name after the options.

**Examples:**
Write to the file only new fields present in the database:
    `sugarcli metadata:dumptofile --add --force`
Delete fields in the file which are not present in the database:
    `sugarcli metadata:dumptofile --del --force`
Only apply modifications for the status_c field in the Accounts module:
    `sugarcli metadata:dumptofile Accounts.status_c`

### Arguments
* `fields`	Filter the command to only apply to this list of fields

### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `-m, --metadata-file=METADATA-FILE`	Path to the metadata file **[config: metadata.file]** **[default: `<SUGAR_PATH>/../db/fields_meta_data.yaml`]**
* `-a, --add`	Add new fields from the DB to the definition file
* `-d, --del`	Delete fields not present in the DB from the metadata file
* `-u, --update`	Update the metadata file for modified fields in the DB

metadata:loadfromfile
---------------------

Load into the table `fields_meta_data` contents from a reference file

**Usage**: `metadata:loadfromfile [-p|--path PATH] [-m|--metadata-file METADATA-FILE] [-s|--sql] [-f|--force] [-a|--add] [-d|--del] [-u|--update] [--] [<fields>]...`

Update the `fields_meta_data` table to reflect the data in the reference YAML file.
Will not do anything by default. Use `--force` to actually execute sql queries to impact the database.
You can filter which modification you whish to apply with the options `--add,--del,--update` or by setting
the fields name after the options.

**Examples:**
Load only new fields:
    `sugarcli metadata:loadfromfile --add --force`
Only delete fields which are not present in the reference file:
    `sugarcli metadata:loadfromfile --del --force`
Only apply modifications for the `status_c` field in the `Accounts` module:
    `sugarcli metadata:loadfromfile Accounts.status_c`

### Arguments
* `fields`	Filter the command to only apply to this list of fields

### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `-m, --metadata-file=METADATA-FILE`	Path to the metadata file **[config: metadata.file]** **[default: `<SUGAR_PATH>/../db/fields_meta_data.yaml`]**
* `-s, --sql`	Print the sql queries that would have been executed
* `-f, --force`	Really execute the SQL queries to modify the database
* `-a, --add`	Add new fields from the file to the DB
* `-d, --del`	Delete fields not present in the metadata file from the DB
* `-u, --update`	Update the DB for modified fields in metadata file

metadata:status
---------------

Show the state of the `fields_meta_data` table compared to a reference file

**Usage**: `metadata:status [-p|--path PATH] [-m|--metadata-file METADATA-FILE]`

Compare the contents of the `fields_meta_data` table with a YAML reference file.
This file should be managed with a version control software (VCS) to keep the various versions.

Use the commands `metadata:loadfromfile` or `metadata:dumptofile` to update the database
or the reference file.

### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `-m, --metadata-file=METADATA-FILE`	Path to the metadata file **[config: metadata.file]** **[default: `<SUGAR_PATH>/../db/fields_meta_data.yaml`]**

rels:dumptofile
---------------

Dump the contents of the table relationships for db migrations

**Usage**: `rels:dumptofile [-p|--path PATH] [--file FILE] [-a|--add] [-d|--del] [-u|--update]`

Manage the of the dump file based on the relationships table.

### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `    --file=FILE`	Path to the rels file. **[config: rels.file]** **[default: `<SUGAR_PATH>/../db/relationships.yaml`]**
* `-a, --add`	Add new relationships from the DB to the definition file.
* `-d, --del`	Delete relationships not present in the DB
* `-u, --update`	Update the relationships in the DB.

rels:loadfromfile
-----------------

Load the contents of the table relationships from a file

**Usage**: `rels:loadfromfile [-p|--path PATH] [--file FILE] [-s|--sql] [-f|--force] [-a|--add] [-d|--del] [-u|--update]`

This command modify the database based on a dump file.
Will not do anything by default. Use --force to actually
execute sql queries to impact the database.

### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `    --file=FILE`	Path to the rels file. **[config: rels.file]** **[default: `<SUGAR_PATH>/../db/relationships.yaml`]**
* `-s, --sql`	Print the sql queries that would have been executed.
* `-f, --force`	Really execute the SQL queries to modify the database.
* `-a, --add`	Add new fields from the file to the DB.
* `-d, --del`	Delete fields not present in the relationships file from the DB.
* `-u, --update`	Update the DB for modified fields in relationships file.

rels:status
-----------

Show the state of the relationships table compared to the dump file

**Usage**: `rels:status [-p|--path PATH] [--file FILE]`

### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `    --file=FILE`	Path to the rels file. **[config: rels.file]** **[default: `<SUGAR_PATH>/../db/relationships.yaml`]**

system:quickrepair
------------------

Do a quick repair and rebuild

**Usage**: `system:quickrepair [-p|--path PATH] [--user-id USER-ID] [--no-database] [-f|--force] [-r|--rm-cache]`

Execute a quick repair and rebuild. Use this command to apply modifications done to the source files.
By default it will print the SQL queries SugarCRM has generated to update the database, use `--force`
to execute thoses queries. You should also use `--force` after an update to the `fields_meta_data`
with the `metadata:loadfromfile` command.

Sometimes after some deep files modifications like VCS branch changes, the cache is obsolete
and you will get an error when you try to repair. In this case use the `--rm-cache` option to delete
the cache folder and compiled files from the Extension framework, namely `custom/application/Ext`
and `custom/modules/*/Ext`.

### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `    --user-id=USER-ID`	SugarCRM user id to impersonate when running the command **[config: sugarcrm.user_id]** **[default: `1`]**
* `    --no-database`	Do not check for database changes
* `-f, --force`	Really execute the SQL queries (displayed by using -d)
* `-r, --rm-cache`	Remove the cache folder and all it's contents before the repair

user:list
---------

List users in the SugarCRM instance

**Usage**: `user:list [-p|--path PATH] [--user-id USER-ID] [-u|--username USERNAME] [-f|--format FORMAT] [-F|--fields FIELDS] [-l|--lang LANG] [-r|--raw]`

### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `    --user-id=USER-ID`	SugarCRM user id to impersonate when running the command **[config: sugarcrm.user_id]** **[default: `1`]**
* `-u, --username=USERNAME`	List only this user
* `-f, --format=FORMAT`	Output format **(text|json|yml|xml)** **[default: `text`]**
* `-F, --fields=FIELDS`	Comma sperated list of fields to display **[default: `id,user_name,is_admin,status,first_name,last_name`]**
* `-l, --lang=LANG`	Language used to display **[default: `en_us`]**
* `-r, --raw`	Show raw data, no language conversion is done

user:update
-----------

Create or update a SugarCRM user

**Usage**: `user:update [-p|--path PATH] [--user-id USER-ID] [-c|--create] [-f|--first-name FIRST-NAME] [-l|--last-name LAST-NAME] [-P|--password PASSWORD] [--ask-password] [-e|--email EMAIL] [-a|--admin ADMIN] [-A|--active ACTIVE] [--] <username>`
`user:create`

Create or update a SugarCRM user.
Option `--password` should be avoided as the password would be kept in shell history.

**Examples:**
Create a new admin user:
    `sugarcli user:create -f John -l Doe --ask-password --admin yes jdoe`
Alternative:
    `sugarcli user:update --create -f John -l Doe --ask-password --admin yes jdoe`
Disable a user:
    `sugarcli user:update --active no jdoe`

### Arguments
* `username`	Login of the user

### Options
* `-p, --path=PATH`	Path to SugarCRM installation **[config: sugarcrm.path]**
* `    --user-id=USER-ID`	SugarCRM user id to impersonate when running the command **[config: sugarcrm.user_id]** **[default: `1`]**
* `-c, --create`	Create the user instead of updating it, optional if called with `user:create`
* `-f, --first-name=FIRST-NAME`	Set first name
* `-l, --last-name=LAST-NAME`	Set last name
* `-P, --password=PASSWORD`	Set password **[UNSAFE use `--ask-password` instead]**
* `    --ask-password`	Ask for password on stdin
* `-e, --email=EMAIL`	Set principal email address
* `-a, --admin=ADMIN`	Set as administrator **[yes|no]**
* `-A, --active=ACTIVE`	Set as active **[yes|no]**

