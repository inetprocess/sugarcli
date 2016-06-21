<?php
/**
 * SugarCLI
 *
 * PHP Version 5.3 -> 5.5
 * SugarCRM Versions 6.5 - 7.7
 *
 * @author Joe Cora
 * @copyright 2016 The New York Times
 *
 * @package nyt/sugarcli-nyt
 *
 * @license Apache License 2.0
 */

namespace SugarCli\Utils;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use SugarCli\Console\Templater;
use SugarCli\Utils\Utils;

class CodeCommandsUtility
{
    // Class members /////////////////////////////////////////////////////
    /*
     * @var Templater $templater            template handler
     */
    protected $templater;
    /*
     * @var Filesystem $fs                  filesystem handler object
     */
    protected $fs;
    /*
     * @var Finder $finder                  file finding object
     */
    protected $finder;

    // Class methods /////////////////////////////////////////////////////
    /*
     * This is the constructor for the CodeCommandsUtility class that sets up dependencies for the class
     *
     * @param Templater $templater          template handling object
     * @param Filesystem $fs                filesystem handler object (optional)
     * @param Finder $finder                file finding object (optional)
     */
    public function __construct(Templater $templater, Filesystem $fs = null, Finder $finder = null)
    {
        // Setup and check if the default dependencies need created
        $this->templater = $templater;

        if ($fs === null) {
            $this->fs = new Filesystem();
        } else {
            $this->fs = $fs;
        }

        if ($finder === null) {
            $this->finder = new Finder();
        } else {
            $this->finder = $finder;
        }
    }

    /*
     * This method handles taking all templates for a particular template type, replacing all placeholder values,
     * and writing the processed file to the appropriate location within the Sugar path.
     *
     * @param string $name                  the name to use for replacements, e.g., module name, field name, etc.
     * @param (TemplateTypeEnum) $type      the type of the specified template as an enumeration
     * @param string $sugarPath             path to a running Sugar location
     * @requires |$name| > 0
     * @requires $type is valid TemplateTypeEnum value
     * @requires $sugarPath is valid Sugar path
     */
    public function writeFilesFromTemplatesForType($name, $type, $sugarPath)
    {
        // Specify replacements placeholder values in templates
        $params = array(
            'module' => $name,
            'module-base' => Utils::baseModuleName($name)
        );

        // Get all templates for the custom module that require parameter replacement, process, and copy to proper
        // location
        $moduleTemplateDir = 'module';

        $this->finder->files()->in($this->templater->getTemplatesPath(). '/'. $moduleTemplateDir)->name('*.twig');

        /** @var SplFileInfo $fileTemplate */
        foreach ($this->finder as $fileTemplate) {
            // Get the template contents and perform replacement, replace placeholder in path, and create processed
            // template file in Sugar path and filename
            $currentTemplatePath = $moduleTemplateDir. '/'. $fileTemplate->getRelativePath();
            $currentTemplateFilename = $fileTemplate->getBasename();

            $currentContent = $this->templater->processTemplate($currentTemplatePath. '/'. $currentTemplateFilename, $params);

            $replacedFilePath = Templater::replaceTemplateName($currentTemplatePath, $type, $name);
            $replacedFileName = Templater::replaceTemplateName($currentTemplateFilename, $type, $name);

            // Create the processed file path and make new file with contents
            $this->fs->mkdir($sugarPath. '/'. $replacedFilePath);
            $this->fs->dumpFile($sugarPath. '/'. $replacedFilePath. '/'. $replacedFileName, $currentContent);
        }
    }
}