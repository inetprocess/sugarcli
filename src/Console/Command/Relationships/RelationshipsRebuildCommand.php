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
use Inet\SugarCRM\System as SugarSystem;
use Symfony\Component\Console\Helper\ProgressIndicator;

class RelationshipsRebuildCommand extends AbstractRelationshipsCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('rels:rebuild')
            ->setDescription('Rebuild the relationships')
            ->setHelp(<<<EOH
EOH
            )->enableStandardOption('user-id');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Rebuild Relationship</comment>: ');
        $progress = new ProgressIndicator($output);

        $progress->start('Starting rebuild relationship...');
        $progress->advance();

        $progress->setMessage('Working...');
        $sugarEP = $this->getService('sugarcrm.entrypoint');
        $sugarSystem = new SugarSystem($sugarEP);
        $sugarSystem->rebuildRelationship($input->getOption('user-id'));
        $progress->finish('<info>rebuild relationship Done.</info>');
    }
}
