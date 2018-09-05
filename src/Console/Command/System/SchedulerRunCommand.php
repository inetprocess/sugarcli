<?php
/**
 * SugarCLI
 *
 * PHP Version 5.3 -> 7.1
 * SugarCRM Versions 6.5 - 8.1
 *
 * @author Dmytro Zakrutchenko
 * @copyright 2005-2015 iNet Process
 *
 * @package inetprocess/sugarcrm
 *
 * @license Apache License 2.0
 *
 * @link http://www.inetprocess.com
 */

namespace SugarCli\Console\Command\System;

use SugarCli\Console\Command\AbstractConfigOptionCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SchedulerRunCommand extends AbstractConfigOptionCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this->setName('system:scheduler:run')
             ->setDescription('Run schedulers')
             ->setHelp(<<<'EOHELP'
Run schedulers
EOHELP
             )
             ->enableStandardOption('path')
             ->enableStandardOption('user-id')
             ->addOption(
                 'id',
                 'id',
                 InputOption::VALUE_REQUIRED,
                 'Sugar Job Id'
             )->addOption(
                'target',
                'target',
                InputOption::VALUE_REQUIRED,
                'Sugar Job Name'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getService('sugarcrm.entrypoint');

        $sugarId = $input->getOption('id');
        $target = $input->getOption('target');

        $this->checkParams($sugarId, $target);

        if(!empty($sugarId)){
            $scheduler = \BeanFactory::getBean('Schedulers', $sugarId);
            if(is_null($scheduler->id)){
                throw new \InvalidArgumentException(sprintf(
                    'Record with id "%s" is not exist in sugarCRM',
                    $sugarId
                ));
            }
            $this->runScheduler($scheduler->job);
        }

        if(!empty($target)){
            $this->runScheduler($target);
        }

    }

    /**
     * @param $sugarId
     * @param $target
     */
    protected function checkParams($sugarId, $target){
        if(is_null($sugarId) && is_null($target)){
            throw new \InvalidArgumentException(sprintf(
                'Specify job name or id'
            ));
        }

        if(!empty($sugarId) && !empty($target)){
            throw new \InvalidArgumentException(sprintf(
                'You can\'t specify job name and id together'
            ));
        }
    }

    /**
     * Run single scheduler
     * @param $target
     */
    protected function runScheduler($target){
        $this->getService('sugarcrm.entrypoint');

        $job = new \SchedulersJob();
        $job->target = $target;
        $job->assigned_user_id = $GLOBALS['current_user']->id;

        $job->runJob();
    }
}
