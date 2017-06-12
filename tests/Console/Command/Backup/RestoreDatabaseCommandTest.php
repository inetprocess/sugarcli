<?php
namespace SugarCli\Tests\Console\Command\Backup;

use SugarCli\Tests\Console\Command\CommandTestCase;
use Inet\SugarCRM\Database\SugarPDO;
use Inet\SugarCRM\Application as SugarApp;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Psr\Log\NullLogger;

class RestoreRestoreCommandTest extends CommandTestCase
{
    public static $cmd_name = 'backup:restore:database';

    public function getSugarPath()
    {
        return __DIR__.'/fake sugar';
    }

    public function getBackupDir()
    {
        return __DIR__.'/backup dir';
    }

    public function getArchiveFile()
    {
        return $this->getBackupDir() . '/phpunit.sql.gz';
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Archive file "UNKNOWN" not found
     */
    public function testInvalidArchive()
    {
        $ret = $this->getCommandTester(self::$cmd_name)
            ->execute(array(
                '--path' => $this->getSugarPath(),
                '--archive' => 'UNKNOWN',
                '--dry-run' => null,
            ));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid compression format 'foo'.
     */
    public function testInvalidCompression()
    {
        $ret = $this->getCommandTester(self::$cmd_name)
            ->execute(array(
                '--compression' => 'foo',
                '--path' => $this->getSugarPath(),
                '--archive' => __DIR__,
                '--dry-run' => null,
            ));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Could not guess compression. Please set the --compression option.
     */
    public function testCompressionNotGuessed()
    {
        $ret = $this->getCommandTester(self::$cmd_name)
            ->execute(array(
                '--path' => $this->getSugarPath(),
                '--archive' => __DIR__,
                '--dry-run' => null,
            ));
    }

    /**
     * @expectedException Symfony\Component\Process\Exception\ProcessFailedException
     * @expectedExceptionMessage The command "/bin/bash -o pipefail -o xtrace" failed.
     */
    public function testPipeFailure()
    {
        $ret = $this->getCommandTester(self::$cmd_name)
            ->execute(array(
                '--path' => getenv('SUGARCLI_SUGAR_PATH'),
                '--archive' => __FILE__,
                '--compression' => 'gzip',
            ));
    }

    public function commandLineProvider()
    {
        $prefix = "'--stdout' '--decompress' '" . $this->getArchiveFile() . "' | "
            ."'mysql' '--defaults-file=%a%c%c' '--default-character-set=utf8'"
            ." '--one-database' '" . getenv('SUGARCLI_DB_NAME') . "'";
        return array(
            // Test case 1
            array("'gzip' " . $prefix, array()),
            // Test case 2
            array("'bzip2' " . $prefix, array('--compression' => 'bzip2')),
            array("'gzip' " . $prefix . " '--force'", array('--force' => null)),
        );
    }

    /**
     * @dataProvider commandLineProvider
     */
    public function testCommandLine($expected_cmd, $args)
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $ret = $cmd->execute(array_merge(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--archive' => $this->getArchiveFile(),
            '--dry-run' => null,
        ), $args));
        $this->assertEquals(0, $ret);
        $this->assertStringMatchesFormat($expected_cmd, rtrim($cmd->getDisplay(), "\n"));
    }

    public function testFull()
    {
        $pdo = new SugarPDO(new SugarApp(new NullLogger(), getenv('SUGARCLI_SUGAR_PATH')));
        $pdo->query('DROP TABLE IF EXISTS sugarcli_test_accounts;');
        $pdo->query('DROP TABLE IF EXISTS sugarcli_test_contacts;');
        $cmd = $this->getCommandTester(self::$cmd_name);
        $ret = $cmd->execute(array_merge(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--archive' => $this->getArchiveFile(),
        ), array()));
        $this->assertEquals(0, $ret);
        $res = $pdo->query('SELECT * from sugarcli_test_accounts;');
        $this->assertGreaterThan(0, $res->rowCount());
        $pdo->query('DROP TABLE IF EXISTS sugarcli_test_accounts;');
        $pdo->query('DROP TABLE IF EXISTS sugarcli_test_contacts;');
    }
}
