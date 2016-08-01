<?php

namespace SugarCli\tests\Console;

use SugarCli\Console\Templater;
use SugarCli\Console\TemplateTypeEnum;

class TemplaterTest extends \PHPUnit_Framework_TestCase
{
    /*
     * Tests exception missing replacement value
     * @see getTemplatesPath
     */
    public function testGetTemplatesPath()
    {
        $expected_path = __DIR__;

        $tester = new Templater($expected_path);

        $actual_path = $tester->getTemplatesPath();

        $this->assertEquals($expected_path, $actual_path);
    }
    
    /*
     * Tests replacing a placeholder and formatting template name for a module
     * @see Templater::replaceTemplateName
     */
    public function testReplaceTemplateNameModule()
    {
        $module_name = 'module/modules/__module__/__module___sugar.php.twig';
        $replacement = 'Module_Test';
        $expected_name = 'modules/'. $replacement. '/'. $replacement. '_sugar.php';

        $actual_name = Templater::replaceTemplateName($module_name, TemplateTypeEnum::MODULE, $replacement);

        $this->assertEquals($expected_name, $actual_name);
    }

    /*
     * Tests replacing a placeholder and formatting template name for a field
     * @see Templater::replaceTemplateName
     */
    public function testReplaceTemplateNameField()
    {
        $module_name = 'field/bool/custom/Extension/modules/Module_Test/Ext/Vardefs/sugarfield___field__.php.twig';
        $replacement = 'Field_Test';
        $expected_name = 'custom/Extension/modules/Module_Test/Ext/Vardefs/sugarfield_'. $replacement. '.php';

        $actual_name = Templater::replaceTemplateName($module_name, TemplateTypeEnum::FIELD, $replacement);

        $this->assertEquals($expected_name, $actual_name);
    }

    /*
     * Tests replacing a placeholder and formatting template name for a relationship from left module
     * @see Templater::replaceTemplateName
     */
    public function testReplaceTemplateNameRelationshipLeft()
    {
        $module_name = 'relationship-left/custom/Extension/modules/Module_Test/Ext/Vardefs/relationship_to___relationship-left__.php.twig';
        $replacement = 'Relationship_Test';
        $expected_name = 'custom/Extension/modules/Module_Test/Ext/Vardefs/relationship_to_'. $replacement. '.php';

        $actual_name = Templater::replaceTemplateName($module_name, TemplateTypeEnum::RELATIONSHIP_LEFT, $replacement);

        $this->assertEquals($expected_name, $actual_name);
    }

    /*
     * Tests replacing a placeholder and formatting template name for a relationship from right module
     * @see Templater::replaceTemplateName
     */
    public function testReplaceTemplateNameRelationshipRight()
    {
        $module_name = 'relationship-right/custom/Extension/modules/Module_Test/Ext/Vardefs/relationship_to___relationship-right__.php.twig';
        $replacement = 'Relationship_Test';
        $expected_name = 'custom/Extension/modules/Module_Test/Ext/Vardefs/relationship_to_'. $replacement. '.php';

        $actual_name = Templater::replaceTemplateName($module_name, TemplateTypeEnum::RELATIONSHIP_RIGHT, $replacement);

        $this->assertEquals($expected_name, $actual_name);
    }

    /*
     * Tests exception missing template name
     * @see Templater::replaceTemplateName
     */
    public function testReplaceTemplateNameExceptionNoName()
    {
        $this->setExpectedException('BadMethodCallException');

        Templater::replaceTemplateName(null, TemplateTypeEnum::MODULE, 'dummy');
    }

    /*
     * Tests exception invalid template type
     * @see Templater::replaceTemplateName
     */
    public function testReplaceTemplateNameExceptionBadType()
    {
        $this->setExpectedException('BadMethodCallException');

        Templater::replaceTemplateName('dummy', null, 'dummy');
    }

    /*
     * Tests exception missing replacement value
     * @see Templater::replaceTemplateName
     */
    public function testReplaceTemplateNameExceptionNoReplacement()
    {
        $this->setExpectedException('BadMethodCallException');

        Templater::replaceTemplateName('dummy', TemplateTypeEnum::MODULE, null);
    }
}
