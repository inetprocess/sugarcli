<?php

namespace SugarCli\Util;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem as BaseFS;
use Symfony\Component\Finder\Finder;

class Filesystem extends BaseFS
{
    public function isEmpty($files)
    {
        if (!$files instanceof \Traversable) {
            $files = new \ArrayObject(is_array($files) ? $files : array($files));
        }
        foreach ($files as $file) {
            if (!$this->exists($file)) {
                throw new FileNotFoundException(null, 0, null, $file);
            }
            if (is_file($file)) {
                $file_info = new \SplFileInfo($file);
                return $file_info->getSize() === 0;
            } elseif (is_dir($file)) {
                $finder = new Finder();
                $finder->in($file);
                $it = $finder->getIterator();
                $it->rewind();
                return !$it->valid();
            } else {
                throw new IOException(
                    sprintf('File "%s" is not a directory or a regular file.', $file),
                    0,
                    null,
                    $file
                );
            }
        }
    }
}
