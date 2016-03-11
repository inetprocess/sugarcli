Changelog
=========

1.9.0
----
* Added new commands `anonymize:*` to anonymize a SugarCRM database
* Added a new command `code:button` to add a button to a recordview of any module + the JS corresponding
* Added a new command `code:setupcomposer` to install composer.json in custom/
* Added a new command `extract:fields` to extract the list of fields and relationships from a module as a CSV file
* Added new commands `rels:*` to manage the `relationships` table like `fields_metadata`
* Changed the license to GPL

1.8.1
----
* Fixes from analysis tools reports.
* Update libsugarcrm to 1.1.10-beta
* Improvements to `user:list`

1.8.0
----
* The code has been cleaned to be published on GitHub (First public Release)
* Inventory is now an independant library
* README updated

1.7.2
-----
* Better error message on die() from SugarCRM.
* Ouput for `system:quickrepair`.
* Option to execute SQL from `system:quickrepair`.

1.7.1
-----
* Small fixes with older versions of sugar.
* Enable `E_NOTICE` and `E_STRICT` for tests.

1.7.0
-----
* Add command `hooks:list` to display a list of hooks for a module.
* Add command `system:quickrepair` to do a quick repair and rebuild of SugarCRM.

1.6.0
-----
* Add commands `user:*` to manage sugarcrm users.
* Fix Division by zero warning.

1.5.3
-----
* Fix PHP notices from Facters Lsb and Linfo.

1.5.2
-----
* Use Linfo library for system facts to remove dependency on `facter` command. This can be used on any OS.

1.5.1
-----
* Add `fqdn` fact to Hostname provider. `fqdn` doesn't depend on the `facter` command anymore.
* Fix missing files in compile script.

1.5.0
-----
* Add `inventory:agent` command.
* Add configuration option `account.name` for option `--account-name` in `inventory:*` commands.
* Add configuration option `metadata.file` for option `--metadata-file` in `metadata:*` commands.

1.4.2
-----
* Fix error with `--path` option for `install:run` command.

1.4.1
-----
* Moved all sugar related work to inetprocess/sugarcrm external library.

1.4.0
-----
* Add `inventory:*` commands
* `inventory:facter`: Get facts about the system and a sugarcrm instance

1.3.4
-----
* Set charset for mysql connection.

1.3.3
-----
* Fix #5756 : Use a single DB connection to Sugar.

1.3.2
-----
* Fix missing ressource file for command `install:config:get`
* Fix issue where dbconfig array in config.php was not complete.

1.3.1
------
* `metadata:*`: Better error handling.
* Tests: Facility for tests with database.

1.3.0
-----
* Use a config file for some parameters.
* `metadata:*`: Manage `fields_meta_data` table.

1.2.2
-----
* `install:config:get`: Fix generating invalid config.

1.2.1
-----
* `clean:langfiles`: Fix issue with spaces after php open tag.
* `clean:langfiles`: Add warning for duplicates variables definitions.

1.2.0
-----
* Reworked langfile cleaner with php token parser.

1.1.0
-----
* `clean:langfiles` command.
* version and changelog.

1.0.0
-----
* First release.
* `install` commands.
* `bin/compile` to compile the command into a phar archive.
