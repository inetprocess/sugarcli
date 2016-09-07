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

namespace SugarCli\Console\Command\User;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\Table;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Exception\UnsupportedFormatException;
use Inet\SugarCRM\Exception\BeanNotFoundException;
use Inet\SugarCRM\Bean as BeanManager;
use Inet\SugarCRM\UsersManager;
use SugarCli\Console\ExitCode;
use SugarCli\Console\Command\AbstractConfigOptionCommand;

class ListCommand extends AbstractConfigOptionCommand
{
    const BOOL_TRUE = "\xE2\x9C\x94";
    const BOOL_FALSE = "\xE2\x9C\x95";

    protected function configure()
    {
        $this->setName('user:list')
            ->setDescription('List users of the SugarCRM instance.')
            ->enableStandardOption('path')
            ->enableStandardOption('user-id')
            ->addOption(
                'username',
                'u',
                InputOption::VALUE_REQUIRED,
                'Login of the user.'
            )
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_REQUIRED,
                'Output format. (text|json|yml|xml)',
                'text'
            )->addOption(
                'fields',
                'F',
                InputOption::VALUE_REQUIRED,
                'List of comma separated field name.',
                'id,user_name,is_admin,status,first_name,last_name'
            )->addOption(
                'lang',
                'l',
                InputOption::VALUE_REQUIRED,
                'Lang for display.',
                'en_us'
            )->addOption(
                'raw',
                'r',
                InputOption::VALUE_NONE,
                'Display raw data.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getService('logger');
        $path = $input->getOption('path');
        $user_name = $input->getOption('username');
        $lang = $input->getOption('lang');
        $fields = explode(',', $input->getOption('fields'));
        $pretty = !$input->getOption('raw');
        $bm = new BeanManager($this->getService('sugarcrm.entrypoint'));
        $bean_list = array();
        try {
            if (!empty($user_name)) {
                $um = new UsersManager($this->getService('sugarcrm.entrypoint'));
                $bean_list[] = $um->getUserBeanByName($user_name);
            } else {
                $bean_list = $bm->getList('Users');
            }
        } catch (BeanNotFoundException $e) {
            $logger->error("User '{$user_name}' doesn't exists on the SugarCRM located at '{$path}'.");

            return ExitCode::EXIT_USER_NOT_FOUND;
        }
        $format = $input->getOption('format');
        if ($format === 'text') {
            // Output table
            $table = new Table($output);
            $table->setStyle('borderless');
            $fields_data = $bm->beanListToArray($fields, $bean_list, $pretty, $lang);
            $table->setHeaders(array_keys($fields_data[0]));
            $table->setRows($fields_data);
            $table->render();
        } else {
            $serial = SerializerBuilder::create()->build();
            try {
                $output->write($serial->serialize(
                    $bm->beanListToArray($fields, $bean_list, $pretty, $lang),
                    $format
                ));
            } catch (UnsupportedFormatException $e) {
                $output->write("<comment>Format $format is not supported.</comment>\n");

                return ExitCode::EXIT_FORMAT_ERROR;
            }
        }
    }
}
