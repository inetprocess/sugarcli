<?php

namespace SugarCli\Tests\TestsUtil;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Psr\Log\NullLogger;

use Inet\SugarCRM\Application as SugarApp;
use Inet\SugarCRM\EntryPoint;
use SugarCli\Console\Application;

use Symfony\Component\Filesystem\Filesystem;

class Util
{
    public static function getRelativePath($path)
    {
        $fs = new Filesystem();
        return rtrim($fs->makePathRelative(
            $path,
            getcwd()
        ), '/');
    }

    public static function getEntryPointInstance()
    {
        if (!EntryPoint::isCreated()) {
            $logger = new NullLogger;
            EntryPoint::createInstance(
                new SugarApp($logger, getenv('SUGARCLI_SUGAR_PATH')),
                '1'
            );
        }
        return EntryPoint::getInstance();
    }

    public static function getTester($cmd_name)
    {
        $app = new Application();
        $app->configure(
            new ArrayInput(array()),
            new StreamOutput(fopen('php://memory', 'w', false))
        );
        $app->setEntryPoint(self::getEntryPointInstance());
        $app->registerAllCommands();
        $command = $app->find($cmd_name);
        return (object) array(
            'tester' => new CommandTester($command),
            'app' => $app,
            'command' => $command,
        );
    }
}
