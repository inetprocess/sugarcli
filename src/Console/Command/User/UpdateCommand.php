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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Inet\SugarCRM\Exception\BeanNotFoundException;
use Inet\SugarCRM\UsersManager;
use SugarCli\Console\ExitCode;
use SugarCli\Console\Command\AbstractConfigOptionCommand;

class UpdateCommand extends AbstractConfigOptionCommand
{
    protected $fields_mapping = array(
        'first-name' => 'first_name',
        'last-name' => 'last_name',
        'email' => 'email1',
    );

    protected function configure()
    {
        $this->setName('user:update')
            ->setAliases(array('user:create'))
            ->setDescription('Create or update a SugarCRM user.')
            ->enableStandardOption('path')
            ->enableStandardOption('user-id')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'Login of the user.'
            )
            ->addOption(
                'create',
                'c',
                InputOption::VALUE_NONE,
                'Create the user instead of updating it. Optional if called with users:create.'
            )
            ->addOption(
                'first-name',
                'f',
                InputOption::VALUE_REQUIRED,
                'First name of the user.'
            )
            ->addOption(
                'last-name',
                'l',
                InputOption::VALUE_REQUIRED,
                'Last name of the user.'
            )
            ->addOption(
                'password',
                'P',
                InputOption::VALUE_REQUIRED,
                'Password of the user. [UNSAFE]'
            )
            ->addOption(
                'ask-password',
                null,
                InputOption::VALUE_NONE,
                'Ask for user password.'
            )
            ->addOption(
                'email',
                'e',
                InputOption::VALUE_REQUIRED,
                'Principal email address of the user.'
            )
            ->addOption(
                'admin',
                'a',
                InputOption::VALUE_REQUIRED,
                'Make the user administrator. <comment>[yes/no]</comment>'
            )
            ->addOption(
                'active',
                'A',
                InputOption::VALUE_REQUIRED,
                'Make the user active. <comment>[yes/no]</comment>'
            );
    }

    protected function isCreate(InputInterface $input)
    {
        $cmd = explode(':', $input->getFirstArgument(), 2);
        if (substr_compare('create', $cmd[1], 0, strlen($cmd[1])) === 0) {
            return true;
        }

        return $input->getOption('create');
    }

    public function getBoolean($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getService('logger');
        $path = $input->getOption('path');
        $user_name = $input->getArgument('username');
        $password = $input->getOption('password');
        $admin = $input->getOption('admin');
        $active = $input->getOption('active');

        if ($input->getOption('ask-password')) {
            $helper = $this->getHelper('question');
            $question = new Question("Please enter the new password for user $user_name: ", null);
            defined('PHPUNIT_SUGARCLI_TESTSUITE') || $question->setHidden(true);
            defined('PHPUNIT_SUGARCLI_TESTSUITE') || $question->setHiddenFallback(true);
            $password = $helper->ask($input, $output, $question);
        }

        $additionnal_fields = array();
        foreach ($this->fields_mapping as $option => $field_name) {
            $value = $input->getOption($option);
            if (!is_null($value)) {
                $additionnal_fields[$field_name] = $value;
            }
        }
        try {
            $um = new UsersManager($this->getService('sugarcrm.entrypoint'));
            if ($this->isCreate($input)) {
                $um->createUser($user_name, $additionnal_fields);
                // Users are active by default.
                if (is_null($active)) {
                    $active = true;
                }
            } else {
                $um->updateUser($user_name, $additionnal_fields);
            }
            if (!is_null($admin)) {
                $um->setAdmin($user_name, $this->getBoolean($admin));
            }
            if (!is_null($active)) {
                $um->setActive($user_name, $this->getBoolean($active));
            }
            if (!is_null($password)) {
                $um->setPassword($user_name, $password);
            }
        } catch (BeanNotFoundException $e) {
            $logger->error("User '{$user_name}' doesn't exists on the SugarCRM located at '{$path}'.");

            return ExitCode::EXIT_USER_NOT_FOUND;
        }
    }
}
