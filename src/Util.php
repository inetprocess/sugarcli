<?php

namespace SugarCli;

class Util
{

    public static function isPathEmpty($path)
    {
        $dir = dir($path);
        $res = true;
        while (false !== ($entry = $dir->read())) {
            if ($entry != '.' or $entry != '..') {
                $res = false;
                break;
            }
        }
        return $res;
    }

    public static function destroyPath($dir)
    {
        $iter = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $dir,
                FilesystemIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iter as $path) {
            //remove file or folder
            if ($path->isFile()) {
                unlink($path->getPathname());
            } else {
                rmdir($path->getPathname());
            }
        }
        return rmdir($dir);
    }
}

