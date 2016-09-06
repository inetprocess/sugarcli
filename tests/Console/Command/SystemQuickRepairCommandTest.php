<?php

namespace SugarCli\Tests\Console\Command;

use Symfony\Component\Console\Output\OutputInterface;
use SugarCli\Tests\Console\Command\CommandTestCase;

/**
 * @group sugarcrm-path
 */
class SystemQuickRepairCommandTest extends CommandTestCase
{
    /**
     * @group sugarcrm-slow
     */
    public function testRepair()
    {
        $cmd = $this->getCommandTester('system:quickrepair');

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
