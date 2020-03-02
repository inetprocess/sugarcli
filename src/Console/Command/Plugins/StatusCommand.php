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

namespace SugarCli\Console\Command\Plugins;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Inet\SugarCRM\Exception\SugarException;
use SugarCli\SugarCRM\Database\Plugins;
use SugarCli\Console\ExitCode;

class StatusCommand extends AbstractPluginsCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('plugins:status')
            ->setDescription('Show the state of the <info>upgrade_history</info> table compared to a reference file')
            ->setHelp(<<<EOH
Compare the contents of the <info>upgrade_history</info> table with a YAML reference file.
This file should be managed with a version control software (VCS) to keep the various versions.

Use the commands <info>plugins:loadfromfile</info> or <info>plugins:dumptofile</info> to update the database
or the reference file.
EOH
            );
    }

    public function getPluginDisplayName(array $plugin_data)
    {
        if (empty($plugin_data['name'])) {
            throw new SugarException('Enable to find key \'name\' for a plugin.');
        }

        return $plugin_data['name'];
    }

    protected function writeAdd(OutputInterface $output, $plugins)
    {
        if (empty($plugins)) {
            return;
        }
        $prog_name = $this->getProgramName();
        $output->writeln('<b>New plugins to add in db:</b>');
        $output->writeln("  (use \"{$prog_name} plugins:load --add\" to add the new plugins in db)");
        $output->writeln("  (use \"{$prog_name} plugins:dump --del\" to remove plugin from the definition file)");
        $output->writeln('');

        foreach ($plugins as $plugin_data) {
            $plugin_name = $this->getPluginDisplayName($plugin_data);
            $output->writeln("\t<fg=green>add: {$plugin_name}</fg=green>");
        }
        $output->writeln('');
    }

    protected function writeDel(OutputInterface $output, $plugins)
    {
        if (empty($plugins)) {
            return;
        }
        $prog_name = $this->getProgramName();
        $output->writeln('<b>Plugins to delete in db:</b>');
        $output->writeln("  (use \"{$prog_name} plugins:load --del\" to remove the plugins from db)");
        $output->writeln("  (use \"{$prog_name} plugins:dump --add\" to add the plugins to the definition file)");
        $output->writeln('');

        foreach ($plugins as $plugin_data) {
            $plugin_name = $this->getPluginDisplayName($plugin_data);
            $output->writeln("\t<fg=red>delete: {$plugin_name}</fg=red>");
        }
        $output->writeln('');
    }

    protected function writeUpdate(OutputInterface $output, $plugins)
    {
        if (empty($plugins)) {
            return;
        }
        $prog_name = $this->getProgramName();
        $output->writeln('<b>Modified plugins:</b>');
        $output->writeln("  (use \"{$prog_name} plugins:load --update\" to update the plugins in db)");
        $output->writeln("  (use \"{$prog_name} plugins:dump --update\" to update the definition file)");
        $output->writeln('');

        foreach ($plugins as $plugin_data) {
            $data = array();
            foreach ($plugin_data[Plugins::MODIFIED] as $key => $value) {
                $data[] = "$key: " . var_export($value, true);
            }
            $modified_data = '{ ' . implode(', ', $data) . ' }';
            $plugin_name = $this->getPluginDisplayName($plugin_data[Plugins::BASE]);
            $output->writeln("\t<fg=yellow>modified: {$plugin_name} {$modified_data}</fg=yellow>");
        }
        $output->writeln('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getService('logger');

        $plugins_file = $input->getOption('plugins-file');

        $style = new OutputFormatterStyle(null, null, array('bold'));
        $output->getFormatter()->setStyle('b', $style);

        if (!is_readable($plugins_file)) {
            $logger->warning("Unable to access plugins file {$plugins_file}.");
            $output->writeln('');
            $output->writeln("Use \"{$this->getProgramName()} plugins:dump\" first to dump the current table state.");

            return ExitCode::EXIT_PLUGINS_NOT_FOUND;
        }

        try {
            $meta = new Plugins($logger, $this->getService('sugarcrm.pdo'), $plugins_file);

            $dump_plugins = $meta->loadFromFile();
            $db_plugins = $meta->loadFromDb();
            $diff = $meta->diff($db_plugins, $dump_plugins);

            if (empty($diff[Plugins::ADD])
              && empty($diff[Plugins::UPDATE])
              && empty($diff[Plugins::DEL])) {
                $output->writeln('<info>Plugins are synced</info>');

                return;
            }

            $this->writeAdd($output, $diff[Plugins::ADD]);
            $this->writeUpdate($output, $diff[Plugins::UPDATE]);
            $this->writeDel($output, $diff[Plugins::DEL]);

            if ($input->getOption('quiet')
                && (
                    !empty($diff[Plugins::ADD])
                    || !empty($diff[Plugins::DEL])
                    || !empty($diff[Plugins::UPDATE])
                )
            ) {
                return ExitCode::EXIT_STATUS_MODIFICATIONS;
            }
        } catch (SugarException $e) {
            $logger->error('An error occured.');
            $logger->error($e->getMessage());

            return ExitCode::EXIT_UNKNOWN_SUGAR_ERROR;
        }
    }
}
