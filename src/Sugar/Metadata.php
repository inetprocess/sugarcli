<?php

namespace SugarCli\Sugar;

use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Manage fields_meta_data table.
 */
class Metadata extends Sugar
{
    const TABLE_NAME = 'fields_meta_data';

    const ADD = 0;
    const DEL = 1;
    const UPDATE = 2;

    const BASE = 0;
    const REMOTE = 1;

    protected $dump_file;

    public function __construct($path = null, LoggerInterface $logger = null, $dump_file = null)
    {
        parent::__construct($path, $logger);
        $this->dump_file = $dump_file;
    }

    public function setDumpFile($dump_file)
    {
        $this->dump_file = $dump_file;
    }


    public function getFromDb()
    {
        $db = $this->getExternalDb();
        $sql = 'SELECT * FROM ' . self::TABLE_NAME;
        $res = $db->query($sql);
        $fields = array();
        foreach ($res->fetchAll() as $row) {
            $fields[$row['id']] = $row;
        }
        ksort($fields);
        return $fields;
    }

    public function getFromFile()
    {
        $fields = Yaml::parse($this->dump_file);
        $res = array();
        foreach ($fields as $field_data) {
            $res[$field_data['id']] = $field_data;
        }
        ksort($res);
        return $res;
    }

    public function diff($base, $new)
    {
        $res = array(
            self::ADD => array(),
            self::DEL => array(),
            self::UPDATE => array()
        );
        $res[self::ADD] = array_diff_key($new, $base);
        $res[self::DEL] = array_diff_key($base, $new);
        // Update array will have common fields with different data.
        $common = array_intersect_key($new, $base);
        foreach ($common as $field_name => $new_field_data) {
            $new_data = array_diff_assoc($new_field_data, $base[$field_name]);
            if (!empty($new_data)) {
                $res[self::UPDATE][$field_name][self::BASE] = $base[$field_name];
                $res[self::UPDATE][$field_name][self::REMOTE] = $new_data;
            }
        }
        return $res;
    }
}

