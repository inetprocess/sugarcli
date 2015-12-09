<?php
/**
 * SugarCLI
 *
 * PHP Version 5.3 -> 5.6
 * SugarCRM Versions 6.5 - 7.6
 *
 * @author Rémi Sauvat
 * @author Emmanuel Dyan
 * @copyright 2005-2015 iNet Process
 *
 * @package inetprocess/sugarcli
 *
 * @license GNU General Public License v2.0
 *
 * @link http://www.inetprocess.com
 */

namespace SugarCli\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Guzzle\Service\Client as GClient;
use Guzzle\Http\Exception\RequestException;
use Inet\SugarCRM\Application;
use Inet\SugarCRM\Exception\SugarException;
use SugarCli\Console\ExitCode;
use SugarCli\Inventory\Agent;
use SugarCli\Inventory\Facter\ArrayFacter;
use SugarCli\Inventory\Facter\MultiFacterFacter;
use SugarCli\Inventory\Facter\SugarFacter;
use SugarCli\Inventory\Facter\SystemFacter;

class InventoryAgentCommand extends AbstractInventoryCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('inventory:agent')
            ->setDescription('Gather facts and send report to Inventory server.')
            ->addArgument(
                'server',
                InputArgument::REQUIRED,
                'Url of the inventory server.'
            )
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'Username for server authentication.'
            )
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'Password for server authentication.'
            )
            ->addConfigOption(
                'account-name',
                'a',
                InputOption::VALUE_REQUIRED,
                'Name of the account.'
            )
            ->addConfigOptionMapping('account-name', 'account.name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getService('logger');
        $this->setSugarPath($this->getConfigOption($input, 'path'));

        $account_name = $this->getConfigOption($input, 'account-name');

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
