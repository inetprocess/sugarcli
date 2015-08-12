Changelog
=========

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
