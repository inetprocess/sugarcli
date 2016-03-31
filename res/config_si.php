<?php

// Database configuration
$sugar_config_si['setup_db_sugarsales_user'] = '<DB_USER>';
$sugar_config_si['setup_db_sugarsales_password'] = '<DB_PASSWORD>';
$sugar_config_si['setup_db_database_name'] = '<DB_NAME>';
$sugar_config_si['setup_db_host_name'] = 'localhost';

// Elastic search configuration
$sugar_config_si['setup_fts_type'] = 'Elastic';
$sugar_config_si['setup_fts_host'] = 'localhost';
$sugar_config_si['setup_fts_port'] = '9200';

// Sugar Config
$sugar_config_si['setup_site_url'] = '<SITE_URL>';
$sugar_config_si['setup_site_admin_user_name'] = '<SUGAR_ADMIN_USER>';
$sugar_config_si['setup_site_admin_password'] = '<SUGAR_ADMIN_PASSWORD>';
$sugar_config_si['setup_license_key'] = '<SUGAR_LICENSE>';

// Install demo data <yes|no>
$sugar_config_si['demoData'] = 'no';

// Default system configuration
$sugar_config_si['setup_system_name'] = 'SugarCRM - Commercial CRM';
// English (US)
$sugar_config_si['default_language'] = 'en_us';
$sugar_config_si['default_locale_name_format'] = 's f l';

$sugar_config_si['default_currency_iso4217'] = 'USD';
$sugar_config_si['default_currency_name'] = 'US Dollar';
$sugar_config_si['default_currency_symbol'] = '$';
$sugar_config_si['default_currency_significant_digits'] = '2';

$sugar_config_si['default_decimal_seperator'] = '.';
$sugar_config_si['default_number_grouping_seperator'] = ',';

$sugar_config_si['default_date_format'] = 'm/d/Y';
$sugar_config_si['default_time_format'] = 'h:ia';

$sugar_config_si['default_export_charset'] = 'UTF-8';
$sugar_config_si['export_delimiter'] = ',';


/* French configuration */
// $sugar_config_si['default_language'] = 'fr_FR';
// $sugar_config_si['default_locale_name_format'] = 's f l';
//
// $sugar_config_si['default_currency_iso4217'] = 'EUR';
// $sugar_config_si['default_currency_name'] = 'Euro';
// $sugar_config_si['default_currency_symbol'] = '€';
// $sugar_config_si['default_currency_significant_digits'] = '2';
//
// $sugar_config_si['default_decimal_seperator'] = ',';
// $sugar_config_si['default_number_grouping_seperator'] = ',';
//
// $sugar_config_si['default_date_format'] = 'd/m/Y';
// $sugar_config_si['default_time_format'] = 'H:i';
//
// $sugar_config_si['default_export_charset'] = 'UTF-8';
// $sugar_config_si['export_delimiter'] = ',';


// Advanced configuration
$sugar_config_si['setup_db_type'] = 'mysql';
$sugar_config_si['setup_db_pop_demo_data'] = false;

$sugar_config_si['setup_db_create_database'] = 1;
$sugar_config_si['setup_db_create_sugarsales_user'] = false;
$sugar_config_si['setup_db_drop_tables'] = 0;
$sugar_config_si['setup_db_username_is_privileged'] = true;
$sugar_config_si['setup_db_admin_user_name'] = $sugar_config_si['setup_db_sugarsales_user'];
$sugar_config_si['setup_db_admin_password'] = $sugar_config_si['setup_db_sugarsales_password'];

$sugar_config_si['setup_site_sugarbeet_automatic_checks'] = true;
