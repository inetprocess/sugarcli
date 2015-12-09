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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\Table;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Exception\UnsupportedFormatException;
use Inet\SugarCRM\Exception\BeanNotFoundException;
use Inet\SugarCRM\Bean as BeanManager;
use Inet\SugarCRM\UsersManager;
use SugarCli\Console\ExitCode;

class UserListCommand extends AbstractConfigOptionCommand
{
    const BOOL_TRUE = "\xE2\x9C\x94";
    const BOOL_FALSE = "\xE2\x9C\x95";

    protected function configure()
    {
        $this->setName('user:list')
            ->setDescription('List users of the SugarCRM instance.')
            ->addConfigOptionMapping('path', 'sugarcrm.path')
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
            );
    }

    /**
     * Fetch values for fields name from bean
     *
     * @param $pretty if true, will return the display name from the language.
     * @param $lang language to use in pretty mode. Default to en_us.
     *
     * @return An array of key => value pairs.
     */
    public function beanToArray(array $fields_name, \SugarBean $bean, $pretty = true, $lang = 'en_us')
    {
        if ($pretty) {
            $bm = new BeanManager($this->getService('sugarcrm.entrypoint'));
            $md = $bm->getModuleFields('Users', $lang);
        }
        $fields = array();
        foreach ($fields_name as $field_name) {
            $key = $field_name;
            $value = $bean->$field_name;
            if ($pretty) {
                $key = $md[$field_name]['vname'];
                switch ($md[$field_name]['type']) {
                    case 'enum':
                        if (isset($md[$field_name]['options_list'][$value])) {
                            $value = $md[$field_name]['options_list'][$value];
                        }
                        break;
                    case 'bool':
                        $value = $value ? self::BOOL_TRUE : self::BOOL_FALSE;
                        break;
                }
            }
            $fields[$key] = $value;
        }

        return $fields;
    }

    public function beanListToArray(array $fields_name, array $bean_list, $pretty = true, $lang = 'en_us')
    {
        $ret = array();
        foreach ($bean_list as $bean) {
            $ret[] = $this->beanToArray($fields_name, $bean, $pretty, $lang);
        }

        return $ret;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getService('logger');
        $path = $this->getConfigOption($input, 'path');
        $this->setSugarPath($path);
        $user_name = $input->getOption('username');
        $lang = $input->getOption('lang');
        $fields = explode(',', $input->getOption('fields'));
        $bean_list = array();
        try {
            if (!empty($user_name)) {
                $um = new UsersManager($this->getService('sugarcrm.entrypoint'));
                $bean_list[] = $um->getUserBeanByName($user_name);
            } else {
                $bm = new BeanManager($this->getService('sugarcrm.entrypoint'));
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
            $fields_data = $this->beanListToArray($fields, $bean_list, true, $lang);
            $table->setHeaders(array_keys($fields_data[0]));
            $table->setRows($fields_data);
            $table->render();
        } else {
            $serial = SerializerBuilder::create()->build();
            try {
                $output->write($serial->serialize($this->beanListToArray($fields, $bean_list), $format));
            } catch (UnsupportedFormatException $e) {
                $output->write("<comment>Format $format is not supported.</comment>\n");

                return ExitCode::EXIT_FORMAT_ERROR;
            }
        }
    }
}
