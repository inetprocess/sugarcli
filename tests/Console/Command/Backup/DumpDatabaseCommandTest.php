<?php
namespace SugarCli\Tests\Console\Command\Backup;

use SugarCli\Tests\Console\Command\CommandTestCase;
use Symfony\Component\Filesystem\Filesystem;

class DumpDatabaseCommandTest extends CommandTestCase
{
    public static $cmd_name = 'backup:dump:database';

    public function getSugarPath()
    {
        return __DIR__.'/fake db sugar';
    }

    public function getBackupDir()
    {
        return __DIR__.'/backup dir';
    }

    public function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->getSugarPath());
    }

    public function setUpFakeDBSugar($config)
    {
        $fs = new Filesystem();
        $fs->mirror(__DIR__.'/fake sugar', $this->getSugarPath());
        $sugar_config = <<<EOPHP
<?php

\$sugar_config =
EOPHP;
        $sugar_config .= var_export($config, true);
        $sugar_config .= ';';
        file_put_contents($this->getSugarPath().'/config.php', $sugar_config);
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
                '--path' => __DIR__,
                '--prefix' => 'test',
            ));
    }

    public function testNotExtracted()
    {
        $ret = $this->getCommandTester(self::$cmd_name)
            ->execute(array(
                '--path' => __DIR__,
                '--prefix' => 'test',
            ));
        $this->assertEquals(11, $ret);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Database of type 'unknown' is not supported
     */
    public function testUnknownDbType()
    {
        $config = array(
            'dbconfig' => array(
                'db_type' => 'unknown',
            ),
        );
        $this->setUpFakeDBSugar($config);
        $ret = $this->getCommandTester(self::$cmd_name)
            ->execute(array(
                '--path' => $this->getSugarPath(),
                '--prefix' => 'test',
                '--dry-run' => true,
            ));
    }

    public function commandLineProvider()
    {
        $prefix = "'mysqldump' '--defaults-file=/tmp/php%a%c%c'"
           . " '--events' '--routines' '--single-transaction' '--opt' '--force'"
           . " '".getenv('SUGARCLI_DB_NAME') ."' | ";
        return array(
            // Test case 1
            array($prefix . "'gzip' > '%a.sql.gz'", array()),
            // Test case 2
            array($prefix . "'bzip2' > '%a.sql.bz2'", array('-c' => 'bzip2')),
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
            '--prefix' => 'test',
            '--destination-dir' => $this->getBackupDir(),
            '--dry-run' => null,
        ), $args));
        $this->assertEquals(0, $ret);
        $this->assertStringMatchesFormat($expected_cmd, rtrim($cmd->getDisplay(), "\n"));
    }

    public function testFull()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $ret = $cmd->execute(array_merge(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--prefix' => 'phpunit',
            '--destination-dir' => $this->getBackupDir(),
        ), array()));
        $this->assertEquals(0, $ret);
        $this->assertEquals(1, preg_match('/^.*file \'(.*)\'$/', $cmd->getDisplay(), $matches));
        $dump_name = $matches[1];
        $this->assertFileExists($dump_name);
        $this->assertEquals(realpath($this->getBackupDir()), realpath(dirname($dump_name)));
        unlink($dump_name);
        $this->assertFileNotExists($dump_name);
        $this->assertStringMatchesFormat('phpunit_%a@%a.sql.gz', basename($dump_name));
    }
}
