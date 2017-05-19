<?php

namespace SugarCli\Console\Command\Backup;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;

class Common
{
    public static function guessCompression($extension, array $compression_formats)
    {
        foreach ($compression_formats as $format => $format_exts) {
            if (in_array($extension, $format_exts)) {
                return $format;
            }
        }
        return null;
    }

    public static function getCompression(InputInterface $input, $archive_path, array $compression_formats)
    {
        // Check compression arg
        $compression = $input->getOption('compression');
        if ($compression == null) {
            $path_info = new \SplFileInfo($archive_path);
            $compression = self::guessCompression($path_info->getExtension(), $compression_formats);
            if ($compression == null) {
                throw new \InvalidArgumentException(
                    "Could not guess compression. Please set the --compression option."
                );
            }
        } elseif (!array_key_exists($compression, $compression_formats)) {
            throw new \InvalidArgumentException("Invalid compression format '{$compression}'.");
        }
        return $compression;
    }

    public static function addCommonDumpOptions(Command $cmd, $compression_formats)
    {
        $compression_values = implode('|', array_keys($compression_formats));
        $cmd->enableStandardOption('path')
            ->addOption(
                'destination-dir',
                'd',
                InputOption::VALUE_REQUIRED,
                'Destination folder for the achive',
                getenv('HOME') . '/backup'
            )
            ->addConfigOption(
                'backup.prefix',
                'prefix',
                'P',
                InputOption::VALUE_REQUIRED,
                'Prepend to the archive name',
                null,
                true
            )
            ->addOption(
                'compression',
                'c',
                InputOption::VALUE_REQUIRED,
                "Set the compression algorithm. Valid values are ({$compression_values}).",
                'gzip'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Do not run the command only print the tar command'
            )
            ;
    }

    public static function addCommonRestoreOptions(Command $cmd, $compression_formats)
    {
        $compression_values = implode('|', array_keys($compression_formats));
        $cmd->enableStandardOption('path')
            ->addOption(
                'compression',
                'c',
                InputOption::VALUE_REQUIRED,
                "Set the compression algorithm. By default it is guessed from file extention."
                . " Valid values are ({$compression_values})."
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Do not run the command only print the tar command'
            )
            ;
    }
}
