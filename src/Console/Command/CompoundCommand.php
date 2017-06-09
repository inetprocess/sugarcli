<?php

namespace SugarCli\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompoundCommand extends Command
{
    protected $subcommands = array();
    protected $are_subcommands_fetched = false;

    public function setSubCommands(array $subcommands)
    {
        $this->ignoreValidationErrors();
        foreach ($subcommands as $subcommand) {
            $this->subcommands[$subcommand] = null;
        }
    }

    protected function callCommand(Command $cmd, InputInterface $input, OutputInterface $output)
    {
        $args = array(
            'command' => $cmd->getName(),
        );
        $cmd->mergeApplicationDefinition();
        foreach ($cmd->getDefinition()->getOptions() as $opt_name => $def) {
            if ($input->hasParameterOption('--'.$opt_name)
                || $input->hasParameterOption('-' . $def->getShortcut())
            ) {
                $args['--'.$opt_name] = $input->getOption($opt_name);
            }
        }
        $this->beforeCommandRun($cmd, $input, $output, $args);
        $cmd_input = new ArrayInput($args);
        $cmd_input->setInteractive($input->isInteractive());
        try {
            $cmd_input->bind($cmd->getDefinition());
        } catch (ExceptionInterface $e) {
        }
        return $cmd->run($cmd_input, $output);
    }

    protected function beforeCommandRun(Command $cmd, InputInterface $input, OutputInterface $output, array &$args)
    {
    }

    protected function getSubCommands()
    {
        if ($this->are_subcommands_fetched === false) {
            if (empty($this->subcommands)) {
                throw new \LogicException('Child of '.__CLASS__.' need to call setSubCommands() before execution');
            }
            foreach ($this->subcommands as $cmd_name => $cmd) {
                $this->subcommands[$cmd_name] = $this->getApplication()->find($cmd_name);
            }
            $this->are_subcommands_fetched = true;
        }
        return $this->subcommands;
    }

    protected function mergeDefinitions()
    {
        foreach ($this->getSubCommands() as $cmd_name => $cmd) {
            $this->getDefinition()->addOptions($cmd->getDefinition()->getOptions());
        }
    }

    protected function callSubCommands(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->getSubCommands() as $cmd_name => $cmd) {
            $ret = $this->callCommand($cmd, $input, $output);
            if ($ret !== 0) {
                return $ret;
            }
        }
        return 0;
    }

    public function getSynopsis($short = false)
    {
        $this->mergeDefinitions();
        parent::getSynopsis($short);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->mergeDefinitions($input);
        $input->bind($this->getDefinition());
        return $this->callSubCommands($input, $output);
    }
}
