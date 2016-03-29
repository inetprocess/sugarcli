<?php

namespace SugarCli\Tests\TestsUtil;

use Symfony\Component\Filesystem\Filesystem;

class Util
{
    public static function getRelativePath($path)
    {
        $fs = new Filesystem();
        return $fs->makePathRelative(
            $path,
            getcwd()
        );
    }
}
