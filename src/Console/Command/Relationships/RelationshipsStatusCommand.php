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

namespace SugarCli\Console\Command\Relationships;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Inet\SugarCRM\Database\Relationship;
use Inet\SugarCRM\Exception\SugarException;
use SugarCli\Console\ExitCode;

class RelationshipsStatusCommand extends AbstractRelationshipsCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('rels:status')
            ->setDescription('Show the state of the relationships table compared to the dump file.')
            ->setHelp(<<<EOH
EOH
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getService('logger');

        $relsFile = $input->getOption('file');

        $style = new OutputFormatterStyle(null, null, array('bold'));
        $output->getFormatter()->setStyle('b', $style);

        if (!is_readable($relsFile)) {
            $logger->error("Unable to access relationships file {$relsFile}.");
            $output->writeln('');
            $output->writeln("Use \"{$this->getProgramName()} rels:dump\" first to dump the current table state.");

            return ExitCode::EXIT_RELS_NOT_FOUND;
        }

        try {
            $meta = new Relationship($logger, $this->getService('sugarcrm.pdo'), $relsFile);

            $relsFromFile = $meta->loadFromFile();
            $relsFromDb = $meta->loadFromDb();
            $diff = $meta->diff($relsFromDb, $relsFromFile);

            if (empty($diff[Relationship::ADD])
              && empty($diff[Relationship::UPDATE])
              && empty($diff[Relationship::DEL])) {
                $output->writeln('<info>Relationships are synced</info>');

                return;
            }

            $this->writeAdd($output, $diff[Relationship::ADD]);
            $this->writeUpdate($output, $diff[Relationship::UPDATE]);
            $this->writeDel($output, $diff[Relationship::DEL]);

            if ($input->getOption('quiet')
                && (
                    !empty($diff[Relationship::ADD])
                    || !empty($diff[Relationship::DEL])
                    || !empty($diff[Relationship::UPDATE])
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

    public function getRelDisplayName(array $relData)
    {
        if (empty($relData['relationship_name'])) {
            throw new SugarException("Enable to find key 'relationship_name' for a rel");
        }

        return $relData['relationship_name'];
    }

    protected function writeAdd(OutputInterface $output, $rels)
    {
        if (empty($rels)) {
            return;
        }

        $cmdName = $this->getProgramName();
        $output->writeln('<b>New Relationships to add in db:</b>');
        $output->writeln("  (use \"{$cmdName} rels:load --add\" to add the new rels in db)");
        $output->writeln("  (use \"{$cmdName} rels:dump --del\" to remove rel from the definition file)");
        $output->writeln('');

        foreach ($rels as $relData) {
            $relName = $this->getRelDisplayName($relData);
            $output->writeln("\t<fg=green>add: {$relName}</fg=green>");
        }
        $output->writeln('');
    }

    protected function writeDel(OutputInterface $output, $rels)
    {
        if (empty($rels)) {
            return;
        }

        $cmdName = $this->getProgramName();
        $output->writeln('<b>Relationships to delete in db:</b>');
        $output->writeln("  (use \"{$cmdName} rels:load --del\" to remove the rels from db)");
        $output->writeln("  (use \"{$cmdName} rels:dump --add\" to add the rels to the definition file)");
        $output->writeln('');

        foreach ($rels as $relData) {
            $relName = $this->getRelDisplayName($relData);
            $output->writeln("\t<fg=red>delete: {$relName}</fg=red>");
        }
        $output->writeln('');
    }

    protected function writeUpdate(OutputInterface $output, $rels)
    {
        if (empty($rels)) {
            return;
        }

        $cmdName = $this->getProgramName();
        $output->writeln('<b>Modified Relationships:</b>');
        $output->writeln("  (use \"{$cmdName} rels:load --update\" to update the rels in db)");
        $output->writeln("  (use \"{$cmdName} rels:dump --update\" to update the definition file)");
        $output->writeln('');

        foreach ($rels as $relData) {
            $data = array();
            foreach ($relData[Relationship::MODIFIED] as $key => $value) {
                $data[] = "$key: " . var_export($value, true);
            }
            $modified_data = '{ ' . implode(', ', $data) . ' }';
            $relName = $this->getRelDisplayName($relData[Relationship::BASE]);
            $output->writeln("\t<fg=yellow>modified: {$relName} {$modified_data}</fg=yellow>");
        }
        $output->writeln('');
    }
}
