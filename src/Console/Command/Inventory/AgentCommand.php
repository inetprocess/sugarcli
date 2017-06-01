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

namespace SugarCli\Console\Command\Inventory;

use Guzzle\Http\Exception\RequestException;
use Guzzle\Service\Client as GClient;
use Inet\Inventory\Agent;
use Inet\Inventory\Facter\ArrayFacter;
use Inet\Inventory\Facter\MultiFacterFacter;
use Inet\Inventory\Facter\SugarFacter;
use Inet\Inventory\Facter\SystemFacter;
use Inet\SugarCRM\Exception\SugarException;
use SugarCli\Console\ExitCode;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AgentCommand extends AbstractInventoryCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('inventory:agent')
            ->setDescription('Gather facts and sends a report to an Inventory server')
            ->setHelp(<<<'EOHELP'
Sends all facts gathered on the system and the SugarCRM instance to an Inventory server.
EOHELP
            )
            ->addArgument(
                'server',
                InputArgument::REQUIRED,
                'Url of the inventory server'
            )
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'Username for server authentication'
            )
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'Password for server authentication'
            )
            ->addConfigOption(
                'account.name',
                'account-name',
                'a',
                InputOption::VALUE_REQUIRED,
                'Name of the account'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getService('logger');

        $account_name = $input->getOption('account-name');

        try {
            $client = new GClient(
                $input->getArgument('server'),
                array('request.options' => array('auth' => array(
                    $input->getArgument('username'),
                    $input->getArgument('password'),
                )))
            );
            $agent = new Agent($logger, $client, $account_name);
            $agent->setFacter(new MultiFacterFacter(array(
                new SystemFacter(),
                new ArrayFacter($this->getCustomFacts($input, 'system'))
            )), Agent::SYSTEM);
            $agent->setFacter(
                new MultiFacterFacter(array(
                    new SugarFacter(
                        $this->getService('sugarcrm.application'),
                        $this->getService('sugarcrm.pdo')
                    ),
                    new ArrayFacter($this->getCustomFacts($input, 'sugarcrm'))
                )),
                Agent::SUGARCRM
            );
            $agent->sendAll();
            $output->writeln('Successfuly sent report to inventory server.');
        } catch (RequestException $e) {
            $logger->error('An error occured while contacting the inventory server.');
            $logger->error($e->getMessage());

            return ExitCode::EXIT_INVENTORY_ERROR;
        } catch (SugarException $e) {
            $logger->error('An error occured with the sugar application.');
            $logger->error($e->getMessage());

            return ExitCode::EXIT_UNKNOWN_SUGAR_ERROR;
        }
    }
}
