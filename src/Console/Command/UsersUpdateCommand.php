<?php

namespace SugarCli\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Inet\SugarCRM\Exception\SugarException;
use Inet\SugarCRM\UsersManager;

class UsersUpdateCommand extends AbstractConfigOptionCommand
{
    protected $fields_mapping = array(
        'first-name' => 'first_name',
        'last-name' => 'last_name',
        'email' => 'email1',
    );

    protected function configure()
    {
        $this->setName('users:update')
            ->setAliases(array('users:create'))
            ->setDescription('Create or update a SugarCRM user.')
            ->addConfigOptionMapping('path', 'sugarcrm.path')
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
                'Password of the user.'
            )
            ->addOption(
                'email',
                'e',
                InputOption::VALUE_REQUIRED,
                'Email address of the user.'
            )
            ->addOption(
                'admin',
                'a',
                InputOption::VALUE_REQUIRED,
                'Make the user administrator. <comment>[yes/no]</comment>'
            )
            ->addOption(
                'activated',
                'A',
                InputOption::VALUE_REQUIRED,
                'Make the user active. <comment>[yes/no]</comment>'
            );
    }

    protected function isCreate(InputInterface $input)
    {
        list($prefix, $cmd) = $input->getFirstArgument();
        if (substr_compare("create", $cmd, 0) === 0) {
            return true;
        }
        return $input->getOption('create');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getService('logger');
        $this->setSugarPath($this->getConfigOption($input, 'path'));
        $user_name = $input->getArgument('username');
        $password = $input->getOption('password');
        $admin = $input->getOption('admin');
        $activated = $input->getOption('activated');

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
            } else {
                $um->updateUser($user_name, $additionnal_fields);
            }
            if (!is_null($admin)) {
                $um->setAdmin($user_name, $admin);
            }
            if (!is_null($activated)) {
                $um->setActive($activated);
            }
            if (!is_null($password)) {
                $um->setPassword($user_name, $password);
            }
        } catch (SugarException $e) {
            $logger->error('An error occured with the sugar application.');
            $logger->error($e->getMessage());
            return ExitCode::EXIT_UNKNOWN_SUGAR_ERROR;
        }
    }
}
