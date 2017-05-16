<?php

namespace SugarCli\Tests\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use SugarCli\Console\Command\CompoundCommand;

class CompoundCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Child of SugarCli\Console\Command\CompoundCommand need to call setSubCommands() before execution
     */
    public function testLogicFailure()
    {
        $comp = new CompoundCommand('comp');
        $app = new Application();
        $app->setAutoExit(false);
        $app->add($comp);
        $commandTester = new CommandTester($comp);
        $ret = $commandTester->execute(array(
            'command' => 'comp',
        ));
    }

    public function testDefaults()
    {
        $cmd_1 = new Command('first');
        $cmd_1->addOption('common', 'c', InputOption::VALUE_NONE);
        $cmd_1->addOption('first-opt', null, InputOption::VALUE_NONE);
        $cmd_1->setCode(function (InputInterface $input, OutputInterface $output) {
            $output->writeln('first_command');
            $output->writeln(var_export($input->getOptions(), true));
        });
        $cmd_2 = new Command('second');
        $cmd_2->addOption('common', 'c', InputOption::VALUE_NONE);
        $cmd_2->addOption('second-opt', null, InputOption::VALUE_NONE);
        $cmd_2->setCode(function (InputInterface $input, OutputInterface $output) {
            $output->writeln('second_command');
            $output->writeln(var_export($input->getOptions(), true));
        });
        $comp = new CompoundCommand('comp');
        $comp->setSubCommands(array('first', 'second'));
        $app = new Application();
        $app->setAutoExit(false);
        $app->add($cmd_1);
        $app->add($cmd_2);
        $app->add($comp);
        $commandTester = new CommandTester($comp);
        $ret = $commandTester->execute(array(
            'command' => 'comp',
            '--no-interaction' => null,
            '--first-opt' => null,
            '--common' => null,
            '--second-opt' => null,
            '-v' => null,
        ));
        $this->assertEquals(0, $ret);
        $expected_output = <<<EOF
first_command
array (
  'common' => true,
  'first-opt' => true,
  'help' => false,
  'quiet' => false,
  'verbose' => true,
  'version' => false,
  'ansi' => false,
  'no-ansi' => false,
  'no-interaction' => true,
)
second_command
array (
  'common' => true,
  'second-opt' => true,
  'help' => false,
  'quiet' => false,
  'verbose' => true,
  'version' => false,
  'ansi' => false,
  'no-ansi' => false,
  'no-interaction' => true,
)

EOF;
        $this->assertEquals($expected_output, $commandTester->getDisplay());
    }

    public function testReturnCodeFailure()
    {
        $cmd_1 = new Command('first');
        $cmd_1->setCode(function (InputInterface $input, OutputInterface $output) {
            $output->writeln('first_command');
            return 1;
        });
        $cmd_2 = new Command('second');
        $cmd_2->setCode(function (InputInterface $input, OutputInterface $output) {
            $output->writeln('second_command');
        });
        $comp = new CompoundCommand('comp');
        $comp->setSubCommands(array('first', 'second'));
        $app = new Application();
        $app->setAutoExit(false);
        $app->add($cmd_1);
        $app->add($cmd_2);
        $app->add($comp);
        $commandTester = new CommandTester($comp);
        $ret = $commandTester->execute(array(
            'command' => 'comp',
        ));
        $this->assertEquals(1, $ret);
        $expected_output = <<<EOF
first_command

EOF;
        $this->assertEquals($expected_output, $commandTester->getDisplay());
    }
}
