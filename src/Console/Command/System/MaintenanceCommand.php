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

namespace SugarCli\Console\Command\System;

use Inet\SugarCRM\Application as SugarApp;
use SugarCli\Console\Command\AbstractConfigOptionCommand;
use SugarCli\Console\ExitCode;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MaintenanceCommand extends AbstractConfigOptionCommand
{
    const CONFIG_BEGIN_BLOCK = '## BEGIN SugarCli Maintenance ##';
    const CONFIG_END_BLOCK = '## END SugarCli Maintenance ##';
    const DEFAULT_PAGE_HTML = <<<'EOHELP'
<!DOCTYPE html>
<html>
<head>
    <title>Under Maintenance</title>
</head>
<body style='background-color: #008BB9'>
    <div style='text-align: center; font-family:Sans-Serif; color: white;'>
        <span style="font-size: 8em">&#x1F6E0;</span>
        <h1>This website is down for maintenance.</h1>
        <p style='font-size: x-large'>We are performing a scheduled maintenance. Service will be back shortly.</p>
        <h2>Ce site est en cours de maintenance.</h2>
        <p>Nous effectuons une op&eacute;ration de maintenance. Le service sera de nouveau disponible rapidement.</p>
    </div>
</body>
</html>
EOHELP;

    protected function configure()
    {
        $this->setName('system:maintenance')
             ->setDescription('Disallow access to the CRM and show a maintenance page')
             ->setHelp(<<<'EOHELP'
EOHELP
             )
             ->enableStandardOption('path')
             ->addArgument(
                 'action',
                 InputArgument::REQUIRED,
                 'Set the maintenance page on or off <comment>(on|off)</comment>'
             )
             ->addConfigOption(
                 'maintenance.allowed_ips',
                 'allowed-ip',
                 'a',
                 InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED,
                 'Ip allowed to bypass the maintenance page',
                 array()
             )
             ->addConfigOption(
                 'maintenance.page',
                 'page',
                 'P',
                 InputOption::VALUE_REQUIRED,
                 'Page file or content to display for the maintenance',
                 'DEFAULT_PAGE'
             );
    }


    protected function generateHtaccessConfig($page, array $ips)
    {
        $escaped_page = str_replace(
            array("\r", "\n", '"'),
            array('', "\\\n", "'"),
            $page
        );
        $content = self::CONFIG_BEGIN_BLOCK.PHP_EOL;
        $content .= <<<EOF
# This section is manage automatically by `sugarcli system:maintenance` command.
# Do not modify manually or remove the comments.

ErrorDocument 503 "${escaped_page}"

RewriteEngine On

EOF;
        foreach ($ips as $ip) {
            $content .= 'RewriteCond %{HTTP:X-FORWARDED-FOR} !='.$ip.PHP_EOL;
            $content .= 'RewriteCond %{REMOTE_ADDR} !='.$ip.PHP_EOL;
        }
        $content .= 'RewriteRule .* - [R=503,L]'.PHP_EOL;
        $content .= PHP_EOL.self::CONFIG_END_BLOCK.PHP_EOL.PHP_EOL;
        return $content;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = strtolower($input->getArgument('action'));
        if ($action !== 'on' && $action !== 'off') {
            throw new \InvalidArgumentException("Unknown action argument '$action'. Possible values are 'on' or 'off'");
        }
        $path = $input->getOption('path');
        $htaccess_path = $path.'/.htaccess';
        $sugar = $this->getService('sugarcrm.application');
        if (!$sugar->isValid()) {
            $output->writeln('SugarCRM is not present in ' . $path . '.');
            return ExitCode::EXIT_NOT_EXTRACTED;
        }
        $re_match = '/^'.preg_quote(self::CONFIG_BEGIN_BLOCK).'$.*^'
            .preg_quote(self::CONFIG_END_BLOCK).'$'.PHP_EOL.'{0,2}/ms';
        $page = $input->getOption('page');
        if ($page === 'DEFAULT_PAGE') {
            $page = self::DEFAULT_PAGE_HTML;
        }

        if (is_file($htaccess_path)) {
            $htaccess = file_get_contents($htaccess_path);
        } else {
            $output->writeln('No existing .htaccess file found.');
            $htaccess = '';
        }

        if ($action === 'on') {
            $content = $this->generateHtaccessConfig($page, $input->getOption('allowed-ip'));
            $new_htaccess = preg_replace($re_match, $content, $htaccess, -1, $count);
            if ($count >= 1) {
                $output->writeln('Maintenance page was already set up. Replacing configuration anyway.');
            } else {
                $output->writeln('Setting up maintenance page.');
                $new_htaccess = $content.$htaccess;
            }
        } else {
            $new_htaccess = preg_replace($re_match, '', $htaccess, -1, $count);
            if ($count >= 1) {
                $output->writeln('Removing maintenance page.');
            } else {
                $output->writeln('Maintenance page was already absent.');
            }
        }
        file_put_contents($htaccess_path, $new_htaccess);
    }
}
