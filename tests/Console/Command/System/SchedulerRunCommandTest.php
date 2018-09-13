<?php

namespace SugarCli\Tests\Console\Command\System;

use Inet\SugarCRM\Application as SugarApp;
use SugarCli\Tests\Console\Command\CommandTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Inet\SugarCRM\Database\SugarPDO;
use Psr\Log\NullLogger;

/**
 * @group sugarcrm-path
 */
class SchedulerRunCommandTest extends CommandTestCase
{
    protected $sugarJobName = 'function::cleanJobQueue';

    /**
     * If send to many options you have to receive error
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage You can't specify job name and id together
     * @group errors
     */
    public function testTooManyOptions()
    {
        $cmd = $this->getCommandTester('system:scheduler:run');

        $result = $cmd->execute(
            array(
                '--path' => getenv('SUGARCLI_SUGAR_PATH'),
                '--id' => 'sugarId',
                '--target' => $this->sugarJobName,
            ),
            array('verbosity' => OutputInterface::VERBOSITY_VERBOSE)
        );

    }

    /**
     * If send to many options you have to receive error
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Specify a job name or id
     * @group errors
     */
    public function testTooFewOptions()
    {
        $cmd = $this->getCommandTester('system:scheduler:run');

        $result = $cmd->execute(
            array(
                '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            ),
            array('verbosity' => OutputInterface::VERBOSITY_VERBOSE)
        );

    }

    /**
     * If set sugar id which is not exist in sugar you have to receive error
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Record with id "sugarId" does not exist in sugarCRM
     * @group errors
     */
    public function testWrongSugarId()
    {
        $cmd = $this->getCommandTester('system:scheduler:run');

        $result = $cmd->execute(
            array(
                '--path' => getenv('SUGARCLI_SUGAR_PATH'),
                '--id' => 'sugarId'
            ),
            array('verbosity' => OutputInterface::VERBOSITY_VERBOSE)
        );

    }


    /**
     * Correct run with option - target
     * @group correct
     */
    public function testCorrectRunWithTarget()
    {

        $this->createTestJobQueueRecord();

        $cmd = $this->getCommandTester('system:scheduler:run');

        $result = $cmd->execute(
            array(
                '--path' => getenv('SUGARCLI_SUGAR_PATH'),
                '--target' => $this->sugarJobName
            ),
            array('verbosity' => OutputInterface::VERBOSITY_VERBOSE)
        );

        $countTestJobQueueRecord = $this->getCountTestJobQueueRecord();

        $this->assertEquals(0, $countTestJobQueueRecord);
    }

    /**
     * Correct run with option - sugar id
     * @group correct
     */
    public function testCorrectRunWithSugarId()
    {
        $sugarId = $this->getTestSchedulerSugarId();

        if(empty($sugarId)){
            return true;
        }

        $this->createTestJobQueueRecord();

        $cmd = $this->getCommandTester('system:scheduler:run');

        $result = $cmd->execute(
            array(
                '--path' => getenv('SUGARCLI_SUGAR_PATH'),
                '--id' => $sugarId
            ),
            array('verbosity' => OutputInterface::VERBOSITY_VERBOSE)
        );

        $countTestJobQueueRecord = $this->getCountTestJobQueueRecord();

        $this->assertEquals(0, $countTestJobQueueRecord);
    }

    /**
     * Creates test record in job_queue
     */
    private function createTestJobQueueRecord()
    {
        $id = time();
        $sql = "INSERT INTO `job_queue` (`id`, `name`, `deleted`, `date_entered`, `date_modified`, `scheduler_id`, `execute_time`, `status`, `resolution`, `message`, `target`, `data`, `requeue`, `retry_count`, `failure_count`, `job_delay`, `client`, `percent_complete`, `job_group`, `module`, `fallible`, `rerun`, `interface`, `assigned_user_id`) VALUES
('$id', 'JustForTest', 0, '1970-01-01 00:00:01', '1970-01-01 00:00:01', '1', '1970-01-01 00:00:01', 'done', 'success', NULL, '$this->sugarJobName', NULL, 0, NULL, NULL, 0, '1', NULL, NULL, NULL, 0, 0, 1, '1')";
        $pdo = new SugarPDO(new SugarApp(new NullLogger(), getenv('SUGARCLI_SUGAR_PATH')));
        $pdo->query($sql);
    }

    /**
     * Returns how many not deleted records we have
     * @return int
     */
    private function getCountTestJobQueueRecord()
    {
        $sql = "SELECT `id` FROM `job_queue` WHERE `name`='JustForTest' AND `deleted`=0";
        $pdo = new SugarPDO(new SugarApp(new NullLogger(), getenv('SUGARCLI_SUGAR_PATH')));
        $query = $pdo->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        return count($result);
    }

    private function getTestSchedulerSugarId(){
        $sql = "SELECT `id` FROM `schedulers` WHERE `job`='$this->sugarJobName' AND `deleted`=0";
        $pdo = new SugarPDO(new SugarApp(new NullLogger(), getenv('SUGARCLI_SUGAR_PATH')));
        $query = $pdo->prepare($sql);
        $query->execute();
        $result = $query->fetch(\PDO::FETCH_ASSOC);
        if(!empty($result)){
            return $result['id'];
        }
        return '';
    }
}
