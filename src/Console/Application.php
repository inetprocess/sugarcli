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
use Symfony\Component\Console\Command\Command;
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

    public function getHelp()
    {
        return parent::getHelp() . <<<'EOF'


<comment>Configuration:</comment>
  You can set configuration options in a <info>yaml</info> file named <info>.sugarclirc</info>.
  SugarCli will look for a <info>.sugarclirc</info> file in any of the parent folders of the current
  directory. The deepest file overrides the previous ones.

  The following options are available:
  <info>sugarcrm:
      path: PATH             </info>Path to Sugarcrm relative to the configuration file<info>
      user_id: USER_ID       </info>SugarCRM user id to impersonate when running the command<info>
  metadata:
      file: FILE             </info>Path to the metadata file relative to the configuration file<info>
  account:
      name: ACCOUNT_NAME     </info>Name of the account<info>
  backup:
      prefix: PREFIX         </info>Prefix to prepend to name of archive file when creating backups</info>
  </info>
EOF;
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
        do {
            $paths[] = rtrim($cur_path, '/') . '/.' . self::CONFIG_NAME;
            $previous = $cur_path;
            $cur_path = dirname($cur_path);
        } while ($previous != $cur_path);
        $paths[] = getenv('HOME') . '/.' . self::CONFIG_NAME;
        $paths[] = '/etc/' . self::CONFIG_NAME;
        return array_reverse($paths);
    }

    /**
     * Init commands
     *
     * @return Command[] An array of default Command instances
     */
    public function registerAllCommands()
    {
        $commands[] = new \SugarCli\Console\Command\Anonymize\AnonymizeConfigCommand();
        $commands[] = new \SugarCli\Console\Command\Anonymize\AnonymizeRunCommand();
        $commands[] = new \SugarCli\Console\Command\Backup\DumpAllCommand();
        $commands[] = new \SugarCli\Console\Command\Backup\DumpDatabaseCommand();
        $commands[] = new \SugarCli\Console\Command\Backup\DumpFilesCommand();
        $commands[] = new \SugarCli\Console\Command\Backup\RestoreAllCommand();
        $commands[] = new \SugarCli\Console\Command\Backup\RestoreDatabaseCommand();
        $commands[] = new \SugarCli\Console\Command\Backup\RestoreFilesCommand();
        $commands[] = new \SugarCli\Console\Command\CleanLangFilesCommand();
        $commands[] = new \SugarCli\Console\Command\Code\ButtonCommand();
        $commands[] = new \SugarCli\Console\Command\Code\ExecuteFileCommand();
        $commands[] = new \SugarCli\Console\Command\Code\SetupComposerCommand();
        $commands[] = new \SugarCli\Console\Command\Database\CleanCommand();
        $commands[] = new \SugarCli\Console\Command\Database\ExportCSV();
        $commands[] = new \SugarCli\Console\Command\Database\MassUpdateCommand();
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
        $commands[] = new \SugarCli\Console\Command\SelfUpdateCommand();
        $commands[] = new \SugarCli\Console\Command\System\MaintenanceCommand();
        $commands[] = new \SugarCli\Console\Command\System\QuickRepairCommand();
        $commands[] = new \SugarCli\Console\Command\User\ListCommand();
        $commands[] = new \SugarCli\Console\Command\User\UpdateCommand();
        foreach ($commands as $command) {
            $this->add($command);
        }
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
        $this->container->setParameter('sugarcrm.user-id', '1');
        $this->container->register('sugarcrm.application', 'Inet\SugarCRM\Application')
             ->addArgument(new Reference('logger'))
             ->addArgument('%sugarcrm.path%');
        $this->container->register('sugarcrm.pdo', 'Inet\SugarCRM\Database\SugarPDO')
             ->addArgument(new Reference('sugarcrm.application'));
        ## Register SugarCRM EntryPoint
        $this->container->setDefinition('sugarcrm.entrypoint', new Definition('Inet\SugarCRM\EntryPoint'))
             ->setFactory('Inet\SugarCRM\EntryPoint::createInstance')
             ->addArgument(new Reference('sugarcrm.application'))
             ->addArgument('%sugarcrm.user-id%');
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
            $app->exitWithException($e);
        }
    }

    public function exitWithException(\Exception $e)
    {
        $this->runOk = true;
        $output = $this->getContainer()->get('console.output');
        if ($output instanceof ConsoleOutputInterface) {
            $this->renderException($e, $output->getErrorOutput());
        } else {
            $this->renderException($e, $output);
        }
        // Used to get 100% coverage on unit tests.
        defined('PHPUNIT_SUGARCLI_TESTSUITE') || exit(ExitCode::EXIT_UNKNOWN_ERROR);
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
        # Register symfony commands once the container is set up
        $this->registerAllCommands();

        $exitCode = parent::run($input, $this->getContainer()->get('console.output'));
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

    /**
     * @override To limit access as root user
     */
    public function doRunCommand(Command $command, InputInterface $input, OutputInterface $output)
    {
        if ($this->isRunByRoot() && !$this->isWhitelistedForRoot($command)) {
            $output->writeln('<error>You are not allowed to run this command as root.</error>');
            return ExitCode::EXIT_COMMAND_AS_ROOT_DENIED;
        }
        return parent::doRunCommand($command, $input, $output);
    }

    public function isRunByRoot()
    {
        if (extension_loaded('posix')) {
            return (posix_geteuid() === 0);
        }
        // @codeCoverageIgnoreStart
        // We don't know so we will let the user run the application
        return false;
        // @codeCoverageIgnoreEnd
    }

    public function getWhitelistedRootCommands()
    {
        return array(
            'help',
            'list',
            'self-update',
        );
    }

    public function isWhitelistedForRoot(Command $command)
    {
        return in_array($command->getName(), $this->getWhitelistedRootCommands());
    }
}
