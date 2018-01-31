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

    /**
     * @expectedException Symfony\Component\Process\Exception\ProcessFailedException
     * @expectedExceptionMessage The command "/bin/bash -o pipefail -o xtrace" failed.
     */
    public function testPipeFailure()
    {
        $config = array(
            'dbconfig' => array(
                'db_type' => 'mysql',
                'db_username' => 'unknown',
                'db_password' => 'unknown',
            ),
        );
        $this->setUpFakeDBSugar($config);
        $ret = $this->getCommandTester(self::$cmd_name)
            ->execute(array(
                '--path' => $this->getSugarPath(),
                '--prefix' => 'test',
            ));
    }

    public function commandLineProvider()
    {
        $db_name = getenv('SUGARCLI_DB_NAME');
        $prefix = "'mysqldump' '--defaults-file=/tmp/sugarcli_mysql_defaults.cnf.%a%c%c'"
           . " '--default-character-set=utf8'"
           . " '--events' '--routines' '--single-transaction' '--opt' '--force'"
           . " '$db_name'";
        return array(
            // Test case 1
            array($prefix . " | 'gzip' > '%a.sql.gz'", array()),
            // Test case 2
            array($prefix . " | 'bzip2' > '%a.sql.bz2'", array('-c' => 'bzip2')),
            // Test case 3
            array(
                $prefix . " '--ignore-table=$db_name.users' '--ignore-table=$db_name.config' | 'gzip' > '%a.sql.gz'",
                array('-T' => array('users', 'config'))
            ),
            // Test case 4
            array(
                $prefix . " '--ignore-table=$db_name.activities' %a | 'gzip' > '%a.sql.gz'",
                array('-D' => null)
            ),
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
            '--no-skip-definer' => null,
        ), $args));
        $this->assertEquals(0, $ret);
        $this->assertStringMatchesFormat($expected_cmd, rtrim($cmd->getDisplay(), "\n"));
    }

    public function testFull()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $date = '2017-01-01 00:00:00';
        $this->getApplication()->find(self::$cmd_name)->setDateTime(new \DateTime($date));
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
        $this->assertStringMatchesFormat('phpunit_%a@2017-01-01_00-00-00.sql.gz', basename($dump_name));
    }

    public function testFullPhp()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $date = '2017-01-01 00:00:00';
        $this->getApplication()->find(self::$cmd_name)->setDateTime(new \DateTime($date));
        $ret = $cmd->execute(array_merge(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--prefix' => 'phpunit',
            '--pure-php' => true,
            '--destination-dir' => $this->getBackupDir(),
        ), array()));
        $this->assertEquals(0, $ret);
        $this->assertEquals(1, preg_match('/^.*file \'(.*)\'$/', $cmd->getDisplay(), $matches));
        $dump_name = $matches[1];
        $this->assertFileExists($dump_name);
        $this->assertEquals(realpath($this->getBackupDir()), realpath(dirname($dump_name)));
        unlink($dump_name);
        $this->assertFileNotExists($dump_name);
        $this->assertStringMatchesFormat('phpunit_%a@2017-01-01_00-00-00.sql.gz', basename($dump_name));
    }

    public function testFullCommandNotAvailable()
    {
        $env_path = getenv('PATH');
        putenv('PATH=');
        $cmd = $this->getCommandTester(self::$cmd_name);
        $date = '2017-01-01 00:00:00';
        $this->getApplication()->find(self::$cmd_name)->setDateTime(new \DateTime($date));
        $ret = $cmd->execute(array_merge(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--prefix' => 'phpunit',
            '--destination-dir' => $this->getBackupDir(),
        ), array()));
        $this->assertEquals(0, $ret);
        $this->assertContains('Command mysqldump not found', $cmd->getDisplay());
        $this->assertContains('Some commands where not found, using pure php to execute dump', $cmd->getDisplay());
        $this->assertEquals(1, preg_match('/^.*file \'(.*)\'$/m', $cmd->getDisplay(), $matches));
        $dump_name = $matches[1];
        $this->assertFileExists($dump_name);
        $this->assertEquals(realpath($this->getBackupDir()), realpath(dirname($dump_name)));
        unlink($dump_name);
        $this->assertFileNotExists($dump_name);
        $this->assertStringMatchesFormat('phpunit_%a@2017-01-01_00-00-00.sql.gz', basename($dump_name));
        putenv("PATH=$env_path");
    }
}
