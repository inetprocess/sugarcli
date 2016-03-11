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

namespace SugarCli\Utils;

use Inet\Neuralyzer\Guesser;

/**
 * AnonymizeGuesser for SugarCRM
 */
class AnonymizeGuesser extends Guesser
{
    /**
     * New cols mapping defined from outside
     *
     * @var array
     */
    protected $extraColsNameMapping = array();

    /**
     * Returns the version of your guesser
     *
     * @return string
     */
    public function getVersion()
    {
        return '1.0.0';
    }

    /**
     * Returns an array of fieldName => Faker class
     *
     * @return array
     */
    public function getColsNameMapping()
    {
        $origMapping = parent::getColsNameMapping();

        $newMapping = array(
            '.*\..*url' => array(
                'method' => 'url',
            ),
            '.*\.(facebook|twitter|googleplus|website|server_url)' => array(
                'method' => 'url',
            ),
            '.*\.(annual_revenue|employees)' => array(
                'method' => 'randomNumber',
                'params' => array(4),
            ),
            '.*\.(is_template|invalid_email|opt_out|from_addr|reply_to_addr|from_address|to_addrs|cc_addrs|bcc_addrs)' => array(
                'method' => 'boolean',
            ),
            '.*\.email_address_caps' => array(
                'method' => 'email',
            ),
            'accounts.name' => array(
                'method' => 'company',
            ),
            'teams.name' => array(
                'method' => 'domainWord',
            ),
            'teams.name_2' => array(
                'method' => 'domainWord',
            ),
            'users.user_name' => array(
                'method' => 'userName',
            ),
        );
        $newMapping = array_merge($newMapping, $this->extraColsNameMapping);

        return array_merge($origMapping, $newMapping);
    }

    /**
     * Set new mapping from outside
     *
     * @param array $extraColsNameMapping
     */
    public function setExtraColsNameMapping(array $extraColsNameMapping)
    {
        foreach ($extraColsNameMapping as $table => $fields) {
            foreach ($fields as $field => $data) {
                if ($data['method'] === 'randomElement') {
                    $params = array($data['elements']);
                } elseif ($data['method'] === 'randomElements') {
                    $params = array($data['elements'], rand(0, 3));
                }

                $this->extraColsNameMapping["{$table}.{$field}"] = array(
                    'method' => $data['method'],
                    'params' => $params,
                );
            }
        }
    }
}
