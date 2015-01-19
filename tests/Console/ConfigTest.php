<?php

namespace SugarCli\Console;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Helper\HelperSet;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function assertUnsortedArray($test, $expected, $actual)
    {
        foreach ($expected as $expected_key => $expected_value) {
            $test->assertArrayHasKey($expected_key, $actual);
            $actual_value = $actual[$expected_key];
            if (is_array($actual_value)) {
                $test->assertUnsortedArray($this, $expected_value, $actual_value);
            } else {
                $test->assertEquals($expected_value, $actual_value);
            }
        }
    }

    /**
     * @dataProvider configProvider
     */
    public function testConfig($input)
    {
        $conf = new Config();
        $proc = new Processor();

        $res = $proc->processConfiguration($conf, array($input));
        $this->assertUnsortedArray($this, $input, $res);


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

    public function testHelper()
    {
        $config = new Config();
        $helperSet = new HelperSet(array($config));
        $this->assertEquals($config, $helperSet->get('config'));
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
        $this->assertUnsortedArray($this, $expected, $config->get());
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
        $this->assertUnsortedArray(
            $this,
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

