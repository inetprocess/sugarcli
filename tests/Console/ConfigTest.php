<?php

namespace SugarCli\Tests\Console;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Filesystem\Filesystem;

use SugarCli\Tests\TestsUtil\Util;
use SugarCli\Console\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider pathProvider
     */
    public function testGetRelativePath($expected, $conf_path, $sugar_path)
    {
        $conf = new Config();
        $actual = $conf->getRelativePath($conf_path, $sugar_path);
        $this->assertEquals($expected, $actual);
    }

    public function pathProvider()
    {
        $test_path = getcwd() . '/' . 'baz';
        return array(
            //set #0
            array('conf/bar', 'conf/foo.yaml', 'bar'),
            //set #1
            array(Util::getRelativePath('/var/www/'), '/etc/sugarclirc', '/var/www'),
            //set #2
            array(Util::getRelativePath('/var/www/'), 'conf/sugarclirc', '/var/www'),
            //set #3
            array(Util::getRelativePath('/etc/www/'), '/etc/sugarclirc', 'www'),
            //set #4
            array('baz', getcwd() . '/.sugarcli', 'baz'),
        );
    }

    /**
     * @dataProvider configProvider
     */
    public function testValidConfig($input)
    {
        $conf = new Config();
        $proc = new Processor();

        $res = $proc->processConfiguration($conf, array($input));
        $this->assertEquals($input, $res);
    }

    public function configProvider()
    {
        return array(
            array(
                array('sugarcrm' =>
                    array(
                        'url' => 'test',
                        'path' => 'toto'
                    )
                ),
                array('metadata' =>
                    array(
                        'file' => 'foo',
                    ),
                ),
                array('account' =>
                    array(
                        'name' => 'Test Corp.',
                    ),
                ),
            ),
            array(array()),
            array(array('sugarcrm' => array())),
        );
    }

    /**
     * @dataProvider configFailureProvider
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testFailureConfig($input)
    {
        $conf = new Config();
        $proc = new Processor();

        $proc->processConfiguration($conf, array($input));
    }

    public function configFailureProvider()
    {
        return array(
            array(array('foo' => 'bar')),
            array(array('sugarcrm' => array('url' => ''))),
            array(array('sugarcrm' => array('path' => ''))),
            array(array('metadata' => array('file' => ''))),
            array(array('metadata' => array('foo' => 'bar'))),
        );
    }

    /**
     * @dataProvider yamlProvider
     */
    public function testYaml($expected, $yaml_files)
    {
        foreach ($yaml_files as $key => $file) {
            $yaml_files[$key] = __DIR__ . '/yaml/' . $file;
        }
        $config = new Config($yaml_files);
        $config->load();
        $this->assertEquals($expected, $config->get());
    }

    public function yamlProvider()
    {
        $sugar_path = Util::getRelativePath(__DIR__ . '/yaml/toto');
        return array(
            array(array(), array( 'empty.yaml')),
            array(
                array(
                    'sugarcrm' => array(
                        'path' => $sugar_path,
                        'url' => 'titi',
                    ),
                ),
                array('complete.yaml')
            ),
            array(
                array(
                    'sugarcrm' => array(
                        'path' => $sugar_path,
                        'url' => 'bar',
                    ),
                ),
                array('complete.yaml', 'partial.yaml')
            ),
        );
    }

    /**
     */
    public function testGetValue()
    {
        $sugar_path = Util::getRelativePath(__DIR__ . '/yaml/../yaml/toto');
        $relative_path = 'tests/Console/yaml/toto';
        $conf = new Config(array(__DIR__ . '/yaml/../yaml/complete.yaml'));
        $conf->load();
        $this->assertEquals($relative_path, $conf->get('sugarcrm.path'));
        $this->assertEquals('titi', $conf->get('sugarcrm.url'));
        $this->assertEquals(
            array('path' => $relative_path, 'url' => 'titi'),
            $conf->get('sugarcrm')
        );

        $this->assertTrue($conf->has());
        $this->assertTrue($conf->has('sugarcrm'));
        $this->assertTrue($conf->has('sugarcrm.path'));

        $this->assertFalse($conf->has('foo'));
        $this->assertFalse($conf->has('sugarcrm.foo'));
        $this->assertFalse($conf->has('sugarcrm.path.foo'));
    }

    /**
     * @dataProvider wrongPathProvider
     * @expectedException \SugarCli\Console\ConfigException
     */
    public function testGetWrongSection($path)
    {
        $conf = new Config(array(__DIR__ . '/yaml/complete.yaml'));
        $conf->load();
        $conf->get($path);
    }

    public function wrongPathProvider()
    {
        return array(
            array('foo'),
            array('sugarcrm.foo'),
            array('sugarcrm.path.foo'),
        );
    }

    /**
     * @expectedException \SugarCli\Console\ConfigException
     */
    public function testNoLoad()
    {
        $config = new Config();
        $config->get();
    }
}
