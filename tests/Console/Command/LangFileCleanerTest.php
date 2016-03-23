<?php
namespace SugarCli\Tests\Console\Command;

class LangFileCleaner extends CommandTestCase
{
    public function testDefault()
    {
        $ret = $this->getCommandTester('clean:langfiles')
            ->execute(array(
                '--path' => __DIR__ . '/Install/install/fake_sugar',
                '--test' => '',
            ));
        $this->assertEquals(0, $ret);
    }

    public function testNotExtracted()
    {
        $ret = $this->getCommandTester('clean:langfiles')
            ->execute(array(
                '--path' => __DIR__,
                '--test' => '',
            ));
        $this->assertEquals(11, $ret);
    }
}
