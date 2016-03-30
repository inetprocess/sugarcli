<?php

namespace SugarCli\Tests\Console\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Psr\Log\NullLogger;

use Inet\SugarCRM\Application as SugarApp;
use Inet\SugarCRM\EntryPoint;
use SugarCli\Console\Application;

/**
 * @group sugarcrm-path
 */
class SystemQuickRepairCommandTest extends \PHPUnit_Framework_TestCase
{
    public function getEntryPointInstance()
    {
        if (!EntryPoint::isCreated()) {
            $logger = new NullLogger;
            EntryPoint::createInstance(
                new SugarApp($logger, getenv('SUGARCLI_SUGAR_PATH')),
                '1'
            );
            $this->assertInstanceOf('Inet\SugarCRM\EntryPoint', EntryPoint::getInstance());
        }
        return EntryPoint::getInstance();
    }

    public function getCommandTester($cmd_name = 'system:quickrepair')
    {
        $app = new Application();
        $app->configure(
            new ArrayInput(array()),
            new StreamOutput(fopen('php://memory', 'w', false))
        );
        $app->setEntryPoint($this->getEntryPointInstance());
        $cmd = $app->find($cmd_name);

        return new CommandTester($cmd);
    }

    /**
     * @group sugarcrm-slow
     */
    public function testRepair()
    {
        $cmd = $this->getCommandTester();

        $checkFile = getenv('SUGARCLI_SUGAR_PATH') . '/cache/class_map.php';
        $this->assertFileExists($checkFile, 'That file is used to test my repair');
        unlink($checkFile);
        $this->assertFileNotExists($checkFile, 'That file is used to test my repair');
        $result = $cmd->execute(
            array('--path' => getenv('SUGARCLI_SUGAR_PATH')),
            array('verbosity' => OutputInterface::VERBOSITY_VERBOSE)
        );
        $output = $cmd->getDisplay();
        $this->assertEquals(0, $cmd->getStatusCode());
        $this->assertNotEmpty($output);
        $this->assertFileExists($checkFile, 'That file is used to test my repair');
    }
}
