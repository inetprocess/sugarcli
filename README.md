# SugarCli

SugarCli is a command line tool to install and manage SugarCRM installations.

## Installing

Get the packaged version at. Allow the execution and run it.
```
chmod +x ./sugarcli.phar
./sugarcli.phar
```

Or clone this git repository and use `./bin/sugarcli`.

## Building

Clone the git repository and run `php -dphar.readonly=0 bin/compile`. It will build the `sugarcli.phar` at the top of the git project.

## Usage

`./sugarcli.phar --help`: This will give you the help and list of available commands.


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
