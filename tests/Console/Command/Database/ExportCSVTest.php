<?php

namespace SugarCli\Tests\Console\Command\Database;

use SugarCli\Tests\Console\Command\CommandTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * @group sugarcrm-path
 */
class ExportCSVTest extends CommandTestCase
{
    public static $cmd_name = 'database:export:csv';
    public function getExportDir()
    {
        return __DIR__ . '/export';
    }

    public function setUp()
    {
        $fs = new Filesystem();
        $fs->mkdir($this->getExportDir(), 0700);
        $finder = new Finder();
        $finder->files()
            ->in($this->getExportDir())
            ->name('*.csv');
        $fs->remove($finder);
    }

    public function testExportTable()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            'database' => getenv('SUGARCLI_DB_NAME'),
            '--include' => array('users'),
            '--output-dir' => $this->getExportDir(),
        ));
        $this->assertEquals(0, $cmd->getStatusCode());
        $this->assertFileExists($this->getExportDir() . '/users.csv');
    }

    public function testSugarConnexion()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            'database' => getenv('SUGARCLI_DB_NAME'),
            '--include' => array('users'),
            '--output-dir' => $this->getExportDir(),
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
        ));
        $this->assertEquals(0, $cmd->getStatusCode());
        $this->assertFileExists($this->getExportDir() . '/users.csv');
    }

    public function testExportQuery()
    {
        $out_file = $this->getExportDir() . '/query.csv';
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            'database' => getenv('SUGARCLI_DB_NAME'),
            '--input-file' => __DIR__ . '/export_admin.sql',
            '--output-file' => $out_file,
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--csv-option' => array('delimiter=,'),
        ));
        $this->assertEquals(0, $cmd->getStatusCode());
        $this->assertFileExists($out_file);
        $this->assertFileEquals(__DIR__ . '/exported_admin.csv', $out_file);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /Input file '.*' not found or empty\./
     */
    public function testExportEmptyInputFile()
    {
        $out_file = $this->getExportDir() . '/query.csv';
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            'database' => getenv('SUGARCLI_DB_NAME'),
            '--input-file' => '/dev/null',
            '--output-file' => $out_file,
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--csv-option' => array('delimiter=,'),
        ));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /Input file '.*' not found or empty\./
     */
    public function testInputFileNotFound()
    {
        $out_file = $this->getExportDir() . '/query.csv';
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            'database' => getenv('SUGARCLI_DB_NAME'),
            '--input-file' => '/unknown_file',
            '--output-file' => $out_file,
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--csv-option' => array('delimiter=,'),
        ));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /File '.*' already exists, will not override unless --force is specified./
     */
    public function testOverrideFailure()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            'database' => getenv('SUGARCLI_DB_NAME'),
            '--include' => array('users'),
            '--output-dir' => $this->getExportDir(),
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
        ));
        $this->assertEquals(0, $cmd->getStatusCode());
        $this->assertFileExists($this->getExportDir() . '/users.csv');

        $cmd->execute(array(
            'database' => getenv('SUGARCLI_DB_NAME'),
            '--include' => array('users'),
            '--output-dir' => $this->getExportDir(),
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
        ));
        $this->assertEquals(0, $cmd->getStatusCode());
        $this->assertFileExists($this->getExportDir() . '/users.csv');
    }

    public function testExcludes()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            'database' => getenv('SUGARCLI_DB_NAME'),
            '--include' => array('users', 'accounts', 'accounts_contacts'),
            '--exclude' => array('users', '*_contacts'),
            '--output-dir' => $this->getExportDir(),
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
        ));
        $this->assertEquals(0, $cmd->getStatusCode());
        $this->assertFileExists($this->getExportDir() . '/accounts.csv');
        $this->assertFileNotExists($this->getExportDir() . '/accounts_contacts.csv');
        $this->assertFileNotExists($this->getExportDir() . '/users.csv');
    }

    public function testMyCnfParsing()
    {
        $my_cnf = $this->getExportDir() . '/my.cnf';
        $my_cnf_content = array(
            '[client]',
            'user=' . getenv('SUGARCLI_DB_USER'),
            'password=' . getenv('SUGARCLI_DB_PASSWORD'),
            'host=' . getenv('SUGARCLI_DB_HOST'),
            'port=' . getenv('SUGARCLI_DB_PORT'),
        );
        file_put_contents($my_cnf, implode(PHP_EOL, $my_cnf_content));
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            'database' => getenv('SUGARCLI_DB_NAME'),
            '--include' => array('users'),
            '--output-dir' => $this->getExportDir(),
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--no-sugar' => null,
            '--db-my-cnf' => $my_cnf,
        ));
        $this->assertEquals(0, $cmd->getStatusCode());
        $this->assertFileExists($this->getExportDir() . '/users.csv');
        $fs = new Filesystem();
        $fs->remove($my_cnf);
        $this->assertFileNotExists($my_cnf);
    }

    public function testDSN()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            'database' => getenv('SUGARCLI_DB_NAME'),
            '--include' => array('users'),
            '--output-dir' => $this->getExportDir(),
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--no-sugar' => null,
            '--db-user' => getenv('SUGARCLI_DB_USER'),
            '--db-password' => getenv('SUGARCLI_DB_PASSWORD'),
            '--db-dsn' => 'mysql:charset=utf8'
                . ';host=' . getenv('SUGARCLI_DB_HOST')
                . ';port=' . getenv('SUGARCLI_DB_PORT'),
        ));
        $this->assertEquals(0, $cmd->getStatusCode());
        $this->assertFileExists($this->getExportDir() . '/users.csv');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No tables to export.
     */
    public function testNoTables()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            'database' => getenv('SUGARCLI_DB_NAME'),
            '--include' => array('users'),
            '--exclude' => array('users'),
            '--output-dir' => $this->getExportDir(),
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
        ));
        $this->assertEquals(0, $cmd->getStatusCode());
        $this->assertFileExists($this->getExportDir() . '/accounts.csv');
        $this->assertFileNotExists($this->getExportDir() . '/accounts_contacts.csv');
        $this->assertFileNotExists($this->getExportDir() . '/users.csv');

    }
}
