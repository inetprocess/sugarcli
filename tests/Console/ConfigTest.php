<?php

namespace SugarCli\Tests\Console;

use Symfony\Component\Config\Definition\Processor;

use SugarCli\Console\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider configProvider
     */
    public function testConfig($input)
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
        return array(
            array(array(), array( 'empty.yaml')),
            array(
                array(
                    'sugarcrm' => array(
                        'path' => 'toto',
                        'url' => 'titi',
                    ),
                ),
                array('complete.yaml')
            ),
            array(
                array(
                    'sugarcrm' => array(
                        'path' => 'toto',
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
        $conf = new Config(array(__DIR__ . '/yaml/../yaml/complete.yaml'));
        $conf->load();
        $this->assertEquals('toto', $conf->get('sugarcrm.path'));
        $this->assertEquals('titi', $conf->get('sugarcrm.url'));
        $this->assertEquals(
            array('path' => 'toto', 'url' => 'titi'),
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
