<?php
/**
 * SugarCLI
 *
 * PHP Version 5.3 -> 5.4
 * SugarCRM Versions 6.5 - 7.6
 *
 * @author Rémi Sauvat
 * @author Emmanuel Dyan
 * @copyright 2005-2015 iNet Process
 *
 * @package inetprocess/sugarcrm
 *
 * @license Apache License 2.0
 *
 * @link http://www.inetprocess.com
 */

namespace SugarCli\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Inet\SugarCRM\EntryPoint;

/**
 * Run console application.
 * Configuration files can be found in:
 *   /etc/sugarclirc
 *   $HOME/.sugarclirc
 */
class Application extends BaseApplication
{
    const CONFIG_NAME = 'sugarclirc';

    /**
     * Services container for dependency injection.
     */
    protected $container;

    /**
     * Replicate the autoExit feature even if we use the registerShutdownFunction.
     */
    protected $autoExitOnShutdown = true;

    /**
     * Indicate status of the run in case we have called the registerShutdownFunction
     */
    protected $runOk = false;

    public function setAutoExit($boolean)
    {
        $this->autoExitOnShutdown = (bool) $boolean;
    }

    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);
    }

    /**
     * Load configuration files from
     * - /etc/sugarclirc
     * - /home/<username>/.sugarclirc
     * - Every .sugarclirc files found in parent folders from the current dir.
     * Order is important as the latests paths will override values from firsts paths.
     */
    public function getConfigFilesPaths()
    {
        $paths = array();
        $cur_path = getcwd();
        while ($cur_path != '/' && $cur_path != '.') {
            $paths[] = $cur_path . '/.' . self::CONFIG_NAME;
            $cur_path = dirname($cur_path);
        }
        $paths[] = getenv('HOME') . '/.' . self::CONFIG_NAME;
        $paths[] = '/etc/' . self::CONFIG_NAME;
        return array_reverse($paths);
    }

    /**
     * Init commands
     *
     * @return Command[] An array of default Command instances
     */
    public function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new \SugarCli\Console\Command\Anonymize\AnonymizeConfigCommand();
        $commands[] = new \SugarCli\Console\Command\Anonymize\AnonymizeRunCommand();
        $commands[] = new \SugarCli\Console\Command\CleanLangFilesCommand();
        $commands[] = new \SugarCli\Console\Command\Code\ButtonCommand();
        $commands[] = new \SugarCli\Console\Command\Code\ExecuteFileCommand();
        $commands[] = new \SugarCli\Console\Command\Code\SetupComposerCommand();
        $commands[] = new \SugarCli\Console\Command\ExtractFieldsCommand();
        $commands[] = new \SugarCli\Console\Command\HooksListCommand();
        $commands[] = new \SugarCli\Console\Command\Install\CheckCommand();
        $commands[] = new \SugarCli\Console\Command\Install\GetConfigCommand();
        $commands[] = new \SugarCli\Console\Command\Install\RunCommand();
        $commands[] = new \SugarCli\Console\Command\Inventory\AgentCommand();
        $commands[] = new \SugarCli\Console\Command\Inventory\FacterCommand();
        $commands[] = new \SugarCli\Console\Command\Metadata\DumpCommand();
        $commands[] = new \SugarCli\Console\Command\Metadata\LoadCommand();
        $commands[] = new \SugarCli\Console\Command\Metadata\StatusCommand();
        $commands[] = new \SugarCli\Console\Command\Relationships\RelationshipsDumpCommand();
        $commands[] = new \SugarCli\Console\Command\Relationships\RelationshipsLoadCommand();
        $commands[] = new \SugarCli\Console\Command\Relationships\RelationshipsStatusCommand();
        $commands[] = new \SugarCli\Console\Command\SystemQuickRepairCommand();
        $commands[] = new \SugarCli\Console\Command\SelfUpdateCommand();
        $commands[] = new \SugarCli\Console\Command\User\ListCommand();
        $commands[] = new \SugarCli\Console\Command\User\UpdateCommand();
        return $commands;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function configure(InputInterface $input = null, OutputInterface $output = null)
    {
        if (is_null($output)) {
            $output = new ConsoleOutput();
        }
        $this->container = new ContainerBuilder();
        $this->container->set('console.output', $output);
        $this->container->register('logger', 'Symfony\Component\Console\Logger\ConsoleLogger')
             ->addArgument(new Reference('console.output'));
        $this->container->register('config', 'SugarCli\Console\Config')
             ->addArgument($this->getConfigFilesPaths())
             ->addMethodCall('load');

        ########### SugarCRM
        $this->container->register('sugarcrm.application', 'Inet\SugarCRM\Application')
             ->addArgument(new Reference('logger'))
             ->addArgument('%sugarcrm.path%');
        $this->container->register('sugarcrm.pdo', 'Inet\SugarCRM\Database\SugarPDO')
             ->addArgument(new Reference('sugarcrm.application'));
        ## Register SugarCRM EntryPoint
        $this->container->setDefinition('sugarcrm.entrypoint', new Definition('Inet\SugarCRM\EntryPoint'))
             ->setFactory('Inet\SugarCRM\EntryPoint::createInstance')
             ->addArgument(new Reference('sugarcrm.application'))
             ->addArgument('1');
        ## Register SugarSystem
        $this->container->register('sugarcrm.system', 'Inet\SugarCRM\System')
            ->addArgument(new Reference('sugarcrm.entrypoint'));
    }

    public function setEntryPoint(EntryPoint $entrypoint)
    {
        $this->container->set('sugarcrm.entrypoint', $entrypoint);
    }

    /**
     * Shutdown function. If $runOk is not true, code did not reach the end of the run method.
     * This means the script was interrupted before.
     * We try to fetch the output from the command.
     */
    public static function shutdownFunction($app)
    {
        if (!$app->runOk) {
            $message = 'exit() or die() called before the end of the command.' . PHP_EOL;
            if ($ob_out = ob_get_clean()) {
                $message .= 'Catched output:' . PHP_EOL . $ob_out;
            }
            $e = new \RuntimeException($message);
            $output = $app->getContainer()->get('console.output');
            if ($output instanceof ConsoleOutputInterface) {
                $app->renderException($e, $output->getErrorOutput());
            } else {
                $app->renderException($e, $output);
            }
            // Used to get 100% coverage on unit tests.
            defined('PHPUNIT_SUGARCLI_TESTSUITE') || exit(ExitCode::EXIT_UNKNOWN_ERROR);
        }
    }

    /**
     * Register a shutdown function in case a die or exit is called during code execution.
     * This can happen in SugarCRM code.
     */
    public function registerShutdownFunction()
    {
        register_shutdown_function(array(__CLASS__, 'shutdownFunction'), $this);
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        // AutoExit from symfony is disabled and replaced by our own.
        parent::setAutoExit(false);
        $this->configure($input, $output);

        $exitCode = parent::run($input, $output);
        // We passed the original run command. The shutdown function will not raise any errors.
        $this->runOk = true;

        if ($this->autoExitOnShutdown) {
            if ($exitCode > 255) {
                $exitCode = 255;
            }
            defined('PHPUNIT_SUGARCLI_TESTSUITE') || exit($exitCode);
        }

        return $exitCode;
    }
}
