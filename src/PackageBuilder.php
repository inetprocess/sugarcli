<?php

namespace SugarCli;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class PackageBuilder
{
    public $version;
    public $prefix;
    public $target_dir;
    public $target_file;
    public $project_dir;

    public $ignore = [];

    public $manifest_data = [];

    public function __construct($project_dir = '.', $target_dir = '.')
    {
        $this->project_dir = rtrim($project_dir);
        $this->target_dir = rtrim($target_dir);
        $this->loadManifest();
    }

    public function setFilename($filename)
    {
        $this->zip_filename;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function setIgnore($ignore)
    {
        $this->ignore = $ignore;
    }

    public function getGitVersion()
    {
        $proc = new Process('git describe --tags --always HEAD', $this->getRealPathFromProject());
        $proc->run();
        return trim($proc->getOutput());
    }

    public function getRealPathFromProject($relative_path = '')
    {
        return realpath($this->project_dir.'/'.$relative_path);
    }

    public function getVersion()
    {
        if (empty($this->version)) {
            $this->version = $this->getGitVersion() ?: 'v0.0';
        }
        return $this->version;
    }

    public function loadManifest()
    {
        $manifest_path = $this->getRealPathFromProject('manifest.php');
        if (!is_file($manifest_path)) {
            throw new \RuntimeException('You must have a manifest.php file');
        }
        require($manifest_path);
        $this->manifest_data['manifest'] = $manifest;
        $this->manifest_data['installdefs'] = $installdefs;
        $this->prefix = $manifest['key'];
    }

    public function generateManifestContent()
    {
        $this->manifest_data['manifest']['version'] = $this->getVersion();
        $this->manifest_data['manifest']['published_date'] = gmdate('Y-m-d H:i:s');

        $this->generateCopyFileArray();

        $content = '<?php' . PHP_EOL;
        $content .= '$manifest = ' .  var_export($this->manifest_data['manifest'], true) . ';' . PHP_EOL;
        $content .= '$installdefs = ' . var_export($this->manifest_data['installdefs'], true) . ';' . PHP_EOL;
        return $content;
    }

    public function generateCopyFileArray()
    {
        $finder = new Finder();
        $finder->files()
            ->in($this->getRealPathFromProject('Files'))
            ->ignoreVCS(true)
            ->ignoreUnreadableDirs(true)
            ->ignoreDotFiles(true)
        ;
        foreach ($this->ignore as $regex) {
            $finder->notPath($regex);
        }

        $fs = new Filesystem();
        $this->manifest_data['installdefs']['copy'] = [];
        foreach ($finder as $file) {
            $from = $fs->makePathRelative($file->getRealPath(), $this->getRealPathFromProject());
            $to = $fs->makePathRelative($file->getRealPath(), $this->getRealPathFromProject('Files'));
            $this->manifest_data['installdefs']['copy'][] = [
                'from' => '<basepath>/'.rtrim($from, '/'),
                'to' => rtrim($to, '/'),
            ];
        }
    }

    public function listStandardFiles()
    {
        $standardFiles = ['LICENSE', 'scripts/post_install.php'];
        $return = [];
        foreach ($standardFiles as $file) {
            $fullPath = $this->getRealPathFromProject($file);
            if (is_file($fullPath)) {
                $return[] = $fullPath;
            } elseif ($file == 'LICENSE') {
                throw new \RuntimeException('You must have a LICENSE file');
            }
        }

        return $return;
    }

    public function listFilesFromManifest()
    {
        $files = [];
        foreach ($this->manifest_data['installdefs']['copy'] as $file) {
            $files[] = str_replace('<basepath>', $this->getRealPathFromProject(), $file['from']);
        }
        $scripts = [
            'pre_execute',
            'post_execute',
            'pre_uninstall',
            'post_uninstall',
        ];
        foreach ($scripts as $script) {
            if (array_key_exists($script, $this->manifest_data['installdefs'])) {
                foreach ($this->manifest_data['installdefs'][$script] as $script) {
                    $files[] = str_replace('<basepath>/', $this->getRealPathFromProject(), $script);
                }
            }
        }

        return $files;
    }

    public function getTargetFileName()
    {
        $filename = $this->zip_filename ?: sprintf('%s-%s.zip', $this->prefix, $this->getVersion());
        return $this->target_dir . '/' . $filename;
    }

    public function createZip()
    {
        if (!class_exists('ZipArchive')) {
            throw new \RuntimeException('The PHP module zip must be installed.');
        }
        $fs = new Filesystem();
        $fs->mkdir($this->target_dir);
        $target_file = $this->getTargetFileName();
        if (is_file($target_file)) {
            unlink($target_file);
        }

        $zip = new \ZipArchive();
        if ($zip->open($target_file, \ZIPARCHIVE::CREATE) !== true) {
            throw new \RuntimeException('Cannot create ZIP file ' . $target_file);
        }
        $zip->addFromString('manifest.php', $this->generateManifestContent());
        $files = array_merge($this->listStandardFiles(), $this->listFilesFromManifest());
        foreach ($files as $file) {
            if (!is_file($file)) {
                throw new \RuntimeException("The file '$file' defined in your manifest does not exist.");
            }
            $zip->addFile($file, rtrim($fs->makePathRelative($file, $this->getRealPathFromProject()), '/'));
        }
        $zip->close();

        if (is_file($target_file) === false) {
            throw new \RuntimeException("Error during creation of the ZIP File");
        }
        return $target_file;
    }
}
