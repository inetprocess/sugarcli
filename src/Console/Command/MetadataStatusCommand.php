<?php

namespace SugarCli\Console\Command;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

use SugarCli\Sugar\Metadata;
use SugarCli\Sugar\SugarException;
use SugarCli\Console\ExitCode;

class MetadataStatusCommand extends AbstractMetadataCommand
{
    protected function configure()
    {
        $this->setName('metadata:status')
            ->setDescription('Show the state of the fields_meta_data table compared to the dump file.')
            ->setHelp(<<<EOH
EOH
            );
    }

    public function getFieldDisplayName($field_data)
    {
        if (empty($field_data['name']) || empty($field_data['custom_module'])) {
            throw new SugarException('Enable to find key \'name\' or \'custom_module\' for a field.');
        }
        return $field_data['custom_module'] . '.' . $field_data['name'];
    }

    protected function writeAdd(OutputInterface $output, $fields)
    {
        if (empty($fields)) {
            return;
        }
        $prog_name = $this->getProgramName();
        $output->writeln('<b>New fields to add in db:</b>');
        $output->writeln("  (use \"{$prog_name} metadata:load --add\" to add the new fields in db)");
        $output->writeln("  (use \"{$prog_name} metadata:dump --del\" to remove field from the definition file)");
        $output->writeln('');

        foreach ($fields as $field_data) {
            $field_name = $this->getFieldDisplayName($field_data);
            $output->writeln("\t<fg=green>add: {$field_name}</fg=green>");
        }
        $output->writeln('');
    }

    protected function writeDel(OutputInterface $output, $fields)
    {
        if (empty($fields)) {
            return;
        }
        $prog_name = $this->getProgramName();
        $output->writeln('<b>Fields to delete in db:</b>');
        $output->writeln("  (use \"{$prog_name} metadata:load --del\" to remove the fields from db)");
        $output->writeln("  (use \"{$prog_name} metadata:dump --add\" to add the fields to the definition file)");
        $output->writeln('');

        foreach ($fields as $field_data) {
            $field_name = $this->getFieldDisplayName($field_data);
            $output->writeln("\t<fg=red>delete: {$field_name}</fg=red>");
        }
        $output->writeln('');
    }

    protected function writeUpdate(OutputInterface $output, $fields)
    {
        if (empty($fields)) {
            return;
        }
        $prog_name = $this->getProgramName();
        $output->writeln('<b>Modified fields:</b>');
        $output->writeln("  (use \"{$prog_name} metadata:load --update\" to update the fields in db)");
        $output->writeln("  (use \"{$prog_name} metadata:dump --update\" to update the definition file)");
        $output->writeln('');

        foreach ($fields as $field_data) {
            $data = array();
            foreach ($field_data[Metadata::MODIFIED] as $key => $value) {
                $data[] = "$key: " . var_export($value, true);
            }
            $modified_data = "{ " . implode(', ', $data) . " }";
            $field_name = $this->getFieldDisplayName($field_data[Metadata::BASE]);
            $output->writeln("\t<fg=yellow>modified: {$field_name} {$modified_data}</fg=yellow>");
        }
        $output->writeln('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getApplication()->getContainer()->get('logger');

        $path = $this->getDefaultOption($input, 'path');
        $metadata_file = $this->getMetadataOption($input);

        $style = new OutputFormatterStyle(null, null, array('bold'));
        $output->getFormatter()->setStyle('b', $style);

        if (!is_readable($metadata_file)) {
            $logger->error("Unable to access metadata file {$metadata_file}.");
            $output->writeln('');
            $output->writeln("Use \"{$this->getProgramName()} metadata:dump\" first to dump the current table state.");
            return ExitCode::EXIT_METADATA_NOT_FOUND;
        }

        try {
            $meta = new Metadata($path, $logger, $metadata_file);

            $dump_fields = $meta->getFromFile();
            $db_fields = $meta->getFromDb();
            $diff = $meta->diff($db_fields, $dump_fields);

            $this->writeAdd($output, $diff[Metadata::ADD]);
            $this->writeUpdate($output, $diff[Metadata::UPDATE]);
            $this->writeDel($output, $diff[Metadata::DEL]);

            if (
                $input->getOption('quiet')
                && (
                !empty($diff[Metadata::ADD])
                || !empty($diff[Metadata::DEL])
                || !empty($diff[Metadata::UPDATE])
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

