<?php

namespace SugarCli\Tests\Sugar;

use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;

use SugarCli\Sugar\LangFile;
use SugarCli\Tests\TestsUtil\TestLogger;

class LangFileTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalizeToken($expected, $token)
    {
        $res = LangFile::normalizeToken($token);
        $this->assertEquals($expected, $res);
    }

    public function normalizeProvider()
    {
        return array(
            array(array( ';', ';', -1), ';'),
            array(array( T_VARIABLE, '$test', 2), array( T_VARIABLE, '$test', 2)),
        );
    }

    /**
     * @dataProvider tokenNameProvider
     */
    public function testGetTokenName($expected, $token)
    {
        $res = LangFile::getTokenName($token);
        $this->assertEquals($expected, $res);
    }

    public function tokenNameProvider()
    {
        return array(
            array(';', array( ';', ';', -1)),
            array('T_VARIABLE', array( T_VARIABLE, '$test', 2)),
        );
    }


    /**
     * @dataProvider blockProvider
     */
    public function testParseNextBlock($expected_empty, $expected_end, $expected_var, $src, $test_mode)
    {
        $logger = new ConsoleLogger(new ConsoleOutput());


        $real_content = "<?php\n" . $src;

        $lang_file = new LangFile($real_content, $test_mode, $logger);
        //Skip php open tag
        $lang_file->tokens->next();

        $lang_file->parseNextBlock();

        $this->assertEquals($expected_empty, $lang_file->empty_blocks);
        $this->assertEquals($expected_end, $lang_file->end_blocks);
        $this->assertEquals($expected_var, $lang_file->var_blocks);
    }


    public function blockProvider()
    {
        $php_var = '$test = "foo";' . PHP_EOL;
        return array(
            array(array("\n\n\n"), array(), array(), "\n\n\n", false),
            array(array("\n\n\n"), array(), array(), "\n\n\n", true),
            array(array("/* test */\n"), array(), array(), "/* test */", false),
            array(array(), array(), array('$test' => $php_var), $php_var, false),
            array(array(), array(), array($php_var), $php_var, true),
        );
    }

    /**
     * @dataProvider fileProvider
     */
    public function testGetSortedFile($expected_log, $expected, $src, $test_mode, $sort)
    {
        $logger = new ConsoleLogger(new ConsoleOutput());
        $logger = new TestLogger();
        $lang_file = new LangFile($src, $test_mode, $logger);
        $res = $lang_file->getSortedFile($sort);

        $this->assertEquals($expected, $res);
        $this->assertEquals($expected_log, $logger->getLines());
    }

    public function fileProvider()
    {
        $php_expected = <<<'EOF'
<?php
$GLOBALS['foo'] = array(
    'test' => 'foo',
    'bar' => 'baz',
);
// comment
$GLOBALS['test']['foo'] = 1;
$bar = test;

EOF;

        $php_not_sorted = <<<'EOF'
<?php
$GLOBALS['foo'] = array(
    'test' => 'foo',
    'bar' => 'baz',
);
// comment
$GLOBALS['test']['foo'] = 1;
$bar = test;

EOF;
        $php_org = <<<'EOF'
<?php
$GLOBALS['foo'] = 2;
// comment
$GLOBALS['test']['foo'] = 1;$GLOBALS['foo'] = array(
    'test' => 'foo',
    'bar' => 'baz',
);



$bar = test;
EOF;

        $log = <<<'EOF'
[warning] Found duplicate definition for $GLOBALS['foo'].

EOF;

        return array(
            array('', $php_org, $php_org, true, false),
            array('', $php_org, $php_org, true, true),
            array($log, $php_not_sorted, $php_org, false, false),
            array($log, $php_expected, $php_org, false, true),
        );

    }
}
