<?php
namespace SugarCli\Utils;

use Humbug\SelfUpdate\Exception\HttpRequestException;
use Humbug\SelfUpdate\Strategy\GithubStrategy;
use Humbug\SelfUpdate\Updater;

/**
 * Overwride GithubStrategy to use curl for phar download.
 * humbug_get_contents doesn't support redirections on php < 5.6
 */
class CurlGithubStrategy extends GithubStrategy
{
    /**
     * Rewrite this function to use curl
     */
    public function download(Updater $updater)
    {
        if (version_compare(PHP_VERSION, '5.6', '>=')) {
            return parent::download($updater);
        }
        // Hack we need access to private property $remoteUrl
        $reflex = new \ReflectionClass('Humbug\SelfUpdate\Strategy\GithubStrategy');
        $property = $reflex->getProperty('remoteUrl');
        $property->setAccessible(true);
        $remoteUrl = $property->getValue($this);
        $content = self::getCurlContent($remoteUrl);
        file_put_contents($updater->getTempPharFile(), $content);
    }

    public static function getCurlContent($url)
    {
        if (!extension_loaded('curl')) {
            throw new HttpRequestException('The curl extension is not loaded in php. Unable to perform http requests.');
        }
        $req = curl_init($url);
        curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($req, CURLOPT_FOLLOWLOCATION, true);
        $content = curl_exec($req);
        if ($content === false) {
            throw new HttpRequestException(sprintf(
                'cURL Error %s: %s',
                curl_errno($req),
                curl_error($req)
            ));
        }
        return $content;
    }
}
