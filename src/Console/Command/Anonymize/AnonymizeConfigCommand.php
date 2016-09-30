<?php
/**
 * SugarCLI
 *
 * PHP Version 5.3 -> 5.4
 * SugarCRM Versions 6.5 - 7.6
 *
 * @author Rémi Sauvat
 * @author Emmanuel Dyan
 * @copyright 2005-2015 iNet Process
 *
 * @package inetprocess/sugarcrm
 *
 * @license Apache License 2.0
 *
 * @link http://www.inetprocess.com
 */

namespace SugarCli\Console\Command\Anonymize;

use SugarCli\Console\Command\AbstractConfigOptionCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AnonymizeConfigCommand extends AbstractConfigOptionCommand
{
    /**
     * Table to ignore by default
     *
     * @var array
     */
    protected $ignoredTables = array(
        '.*_audit',
        'acl_.*',
        'address_book',
        'address_book_list_items',
        'campaign_log',
        'campaign_trkrs',
        'config',
        'currencies',
        'custom_fields',
        'dashboards',
        'eapm',
        'email_cache',
        'expressions',
        'fields_meta_data',
        'fts_queue',
        'job_queue',
        'oauth_tokens',
        'relationships',
        'upgrade_history',
        'schedulers.*',
        'team_sets.*',
        'tracker.*',
        'upgrade_history',
        'user_preferences',
        'users_feeds',
        'vcals',
        'versions',
        'weblogichooks',
        'workflow.*',
    );

    /**
     * Fields to protect. Format table.field (regex accepted)
     *
     * @var array
     */
    protected $ignoreFields = array(
        '.*\.id',
        '.*\.id_c',
        '.*\..*_id',
        '.*\.created_by',
        '.*\.date_created',
        '.*\.date_entered',
        '.*\.date_modified',
        '.*\.deleted',
        '.*\.file_mime_type',
        '.*\.modified_by',
        '.*\.module_name',
        '.*\.parent_type',
        '.*\.primary_account',
        // Rels
        '.*_c\..*_(ida|idb)',
        // Users
        'users.(is_admin|status|sugar_login|user_hash|external_auth_only|portal_only|is_group)',
    );

    protected function configure()
    {
        $this->setName('anonymize:config')
            ->setDescription('Generate a configuration for the Anonymizer')
            ->enableStandardOption('path')
            ->enableStandardOption('user-id')
            ->addOption(
                'file',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to the configuration file',
                '../db/anonymization.yml'
            )->addOption(
                'ignore-table',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Table to ignore. Can be repeated'
            )->addOption(
                'ignore-field',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Field to ignore. Can be repeated'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pdo = $this->getService('sugarcrm.pdo');

        // Get the list of fields in SugaCRM for each module, with the table name as key
        $dropdowns = $this->getModulesDropdowns();
        $guesser = new \SugarCli\Utils\AnonymizeGuesser;
        $guesser->setExtraColsNameMapping($dropdowns);

        $writer = new \Inet\Neuralyzer\Configuration\Writer;
        // Merge all the ways to get the tables to ignore
        $writer->setIgnoredTables(array_merge(
            $this->ignoredTables,
            $this->getRelsTables($pdo),
            $input->getOption('ignore-table')
        ));

        // Define the fields to ignore
        $writer->protectCols(true);
        $writer->setProtectedCols(array_merge(
            $this->ignoreFields,
            $input->getOption('ignore-field')
        ));

        $data = $writer->generateConfFromDB($pdo, $guesser);
        // go to my current folder
        $writer->save($data, $input->getOption('file'));

        $output->writeln('<comment>Configuration written to ' . $input->getOption('file') . '</comment>');

        // DANS LE RUN NE PAS OUBLIER DE VIDER LES _AUDIT
    }

    protected function getRelsTables(\PDO $pdo)
    {
        $data = $pdo->query('SELECT relationship_name, join_table FROM relationships');
        $relsTables = array();
        foreach ($data as $res) {
            $table = empty($res['join_table']) ? $res['relationship_name'] : $res['join_table'];
            // Pluralize tables names
            $relsTables[] = $table;
            $relsTables[] = $this->pluralize($table);
        }

        return $relsTables;
    }

    protected function pluralize($table)
    {
        $parts = explode('_', $table);
        foreach ($parts as $key => $part) {
            if (substr($part, -1) !== 's') {
                $parts[$key] = "{$part}s";
            }
        }

        $table = implode('_', $parts);

        return $table;
    }

    protected function getModulesDropdowns()
    {
        $bm = new \Inet\SugarCRM\Bean($this->getService('sugarcrm.entrypoint'));
        $beansList = $bm->getBeansList();

        $dropdowns = array();
        foreach ($beansList as $module => $moduleSingular) {
            $wontRetrieve = false;
            $moduleFields = array();
            // Try to retrieve with multiple manners and catch the message
            try {
                $moduleFields = $bm->getModuleFields($module);
            } catch (\Exception $e) {
                try {
                    $moduleFields = $bm->getModuleFields($moduleSingular);
                } catch (\Exception $e) {
                    $wontRetrieve = $e->getMessage();
                }
                $wontRetrieve = $e->getMessage();
            }

            if ($wontRetrieve !== false) {
                $this->getService('logger')->info(
                    __METHOD__ . ": Won't retrieve fields from $module (message = $wontRetrieve)"
                );
                continue;
            }

            foreach ($moduleFields as $field) {
                if (!in_array($field['type'], array('enum', 'multienum'))) {
                    continue;
                }

                $dropdowns[$field['Table']][$field['name']] = array(
                    'elements' => is_array($field['options_list']) ? array_keys($field['options_list']) : array(),
                    'method' => $field['type'] === 'enum' ? 'randomElement' : 'randomElements',
                );
            }
        }

        return $dropdowns;
    }
}
