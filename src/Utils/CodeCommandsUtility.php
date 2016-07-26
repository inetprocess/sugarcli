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
use SugarCli\Console\TemplateTypeEnum;

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
     * directory structure is created, otherwise the structure already needs to be present.
     *
     * @param array $replacements           an array of replacement values, e.g., module name, field name, etc., that
     *                                          are needed to process the templates for a particular type
     * @param (TemplateTypeEnum) $type      the type of the specified template as an enumeration
     *                                          module - needs "module" key/value in $replacements
     *                                          field - needs "module", "field", and "type" key/value in $replacements
     *                                          relationship - needs "moduleLeft", "moduleRight", and "type" key/value
     *                                              in $replacements (all relationship components)
     * @param string $sugarPath             path to a running Sugar location
     * @requires $sugarPath is valid Sugar path
     */
    public function writeFilesFromTemplatesForType(array $replacements, $type, $sugarPath)
    {
        // Setup template replacement options based on type and confirm needed values are present in replacements array
        $typeName = null;
        $subTypeName = null;

        switch ($type) {
            case TemplateTypeEnum::MODULE:
                $typeName = 'module';

                // Verify required replacements
                if (!isset($replacements['module'])) {
                    throw new \BadMethodCallException('"module" must be specified in replacements array parameter');
                }

                break;
            case TemplateTypeEnum::FIELD:
                $typeName = 'field';

                // Verify required replacements
                if (!isset($replacements['module'])) {
                    throw new \BadMethodCallException('"module" must be specified in replacements array parameter');
                } elseif (!isset($replacements['field'])) {
                    throw new \BadMethodCallException('"field" must be specified in replacements array parameter');
                } elseif (!isset($replacements['type'])) {
                    throw new \BadMethodCallException('"type" must be specified in replacements array parameter');
                }

                $subTypeName = '/'. $replacements['type'];

                // Use the bean name for the module to use in replacements for new fields
                $replacements['module'] = Utils::moduleBeanName($replacements['module']);

                break;
            case TemplateTypeEnum::RELATIONSHIP:
                $typeName = 'relationship';

                // Verify required replacements
                if (!isset($replacements['moduleLeft'])) {
                    throw new \BadMethodCallException('"moduleLeft" must be specified in replacements array parameter');
                } elseif (!isset($replacements['moduleRight'])) {
                    throw new \BadMethodCallException('"moduleRight" must be specified in replacements array parameter');
                } elseif (!isset($replacements['type'])) {
                    throw new \BadMethodCallException('"type" must be specified in replacements array parameter');
                }

                // Prepare the relationship name in the replacements
                $replacements['relationship'] = Utils::conventionalRelationshipName($replacements['moduleLeft'], $replacements['moduleRight']);
                $replacements['module'] = 'Placeholder'; // Module is never replaced in templates for this type

                break;
            case TemplateTypeEnum::RELATIONSHIP_LEFT:
                $typeName = 'relationship-left';

                // Verify required replacements
                if (!isset($replacements['moduleLeft'])) {
                    throw new \BadMethodCallException('"moduleLeft" must be specified in replacements array parameter');
                } elseif (!isset($replacements['moduleRight'])) {
                    throw new \BadMethodCallException('"moduleRight" must be specified in replacements array parameter');
                } elseif (!isset($replacements['type'])) {
                    throw new \BadMethodCallException('"type" must be specified in replacements array parameter');
                }

                // Prepare the relationship name in the replacements
                $replacements['relationship'] = Utils::conventionalRelationshipName($replacements['moduleLeft'], $replacements['moduleRight']);

                // Prepare the relationship side specifics for replacements
                $replacements['module'] = $replacements['moduleLeft'];
                $replacements['moduleBean'] = Utils::moduleBeanName($replacements['moduleLeft']);
                $replacements['relationship-left'] = $replacements['moduleRight'];

                break;
            case TemplateTypeEnum::RELATIONSHIP_RIGHT:
                $typeName = 'relationship-right';

                // Verify required replacements
                if (!isset($replacements['moduleLeft'])) {
                    throw new \BadMethodCallException('"moduleLeft" must be specified in replacements array parameter');
                } elseif (!isset($replacements['moduleRight'])) {
                    throw new \BadMethodCallException('"moduleRight" must be specified in replacements array parameter');
                } elseif (!isset($replacements['type'])) {
                    throw new \BadMethodCallException('"type" must be specified in replacements array parameter');
                }

                // Prepare the relationship name in the replacements
                $replacements['relationship'] = Utils::conventionalRelationshipName($replacements['moduleLeft'], $replacements['moduleRight']);

                // Prepare the relationship side specifics for replacements
                $replacements['module'] = $replacements['moduleRight'];
                $replacements['moduleBean'] = Utils::moduleBeanName($replacements['moduleRight']);
                $replacements['relationship-right'] = $replacements['moduleLeft'];

                break;
            default:
                throw new \BadMethodCallException('You must specify a valid template type, e.g., TemplateTypeEnum::MODULE');
        }

        // Get all templates for the custom module that require parameter replacement, process, and copy to proper
        // location
        $this->finder->files()->in($this->templater->getTemplatesPath(). '/'. $typeName. $subTypeName)->name('*.twig');

        /** @var SplFileInfo $fileTemplate */
        foreach ($this->finder as $fileTemplate) {
            // Get the template contents and perform replacement, replace placeholder in path, and create processed
            // template file in Sugar path and filename
            $currentTemplatePath = $typeName. $subTypeName. '/'. $fileTemplate->getRelativePath();
            $currentTemplateFilename = $fileTemplate->getBasename();

            $currentContent = $this->templater->processTemplate($currentTemplatePath. '/'. $currentTemplateFilename,
                $replacements);

            // Start with type-specific replacements then do module name replacements in path names
            $replacedFilePath = Templater::replaceTemplateName($currentTemplatePath, $type, $replacements[$typeName]);
            $replacedFilePath = Templater::replaceTemplateName($replacedFilePath, TemplateTypeEnum::MODULE,
                $replacements['module']);
            
            // Filename replacement is dependent upon type
            $replacedFileName = Templater::replaceTemplateName($currentTemplateFilename, $type, $replacements[$typeName]);

            // For new modules, create the directory structure, otherwise, throw exception if path does not exist
            $writePath = $sugarPath. '/'. $replacedFilePath;

            if ($type == TemplateTypeEnum::MODULE) {
                // Create the processed file path
                $this->fs->mkdir($writePath);
            } elseif (!$this->fs->exists($writePath)) {
                throw new \DomainException('the path, '. $writePath. ', does not already exist');
            }

            // Create the new file with contents
            $this->fs->dumpFile($writePath. '/'. $replacedFileName, $currentContent);
        }
    }

    /*
     * This method resets the code commands utility object to its original, post-construction state.
     */
    public function reset()
    {
        // Reset the finder object by instantiating a new one
        $this->finder = new Finder();
    }
}