<?php
/**
 * Katalog Model Class
 */

class Katalog extends ObjectModel
{
    public $id_katalog;
    public $title;
    public $description;
    public $image;
    public $file_url;
    public $file_path;
    public $is_new;
    public $position;
    public $active;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'katalogy',
        'primary' => 'id_katalog',
        'fields' => [
            'title' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size' => 255
            ],
            'description' => [
                'type' => self::TYPE_HTML,
                'validate' => 'isString'
            ],
            'image' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 255
            ],
            'file_url' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isUrl',
                'size' => 500
            ],
            'file_path' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 500
            ],
            'is_new' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool'
            ],
            'position' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ],
            'active' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool'
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate'
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate'
            ]
        ]
    ];

    /**
     * Get all active catalogs ordered by position
     */
    public static function getAllActive()
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'katalogy` 
                WHERE `active` = 1 
                ORDER BY `is_new` DESC, `position` ASC';
        
        return Db::getInstance()->executeS($sql);
    }

    /**
     * Get catalog by ID
     */
    public static function getById($id)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'katalogy` 
                WHERE `id_katalog` = ' . (int)$id . ' 
                AND `active` = 1';
        
        return Db::getInstance()->getRow($sql);
    }

    /**
     * Get highest position
     */
    public static function getHighestPosition()
    {
        $sql = 'SELECT MAX(`position`) FROM `' . _DB_PREFIX_ . 'katalogy`';
        return (int)Db::getInstance()->getValue($sql);
    }

    /**
     * Get download URL for catalog
     */
    public function getDownloadUrl()
    {
        if (!empty($this->file_url)) {
            return $this->file_url;
        } elseif (!empty($this->file_path)) {
            return _MODULE_DIR_ . 'katalogy/files/' . $this->file_path;
        }
        return false;
    }

    /**
     * Get image URL
     */
    public function getImageUrl()
    {
        if (!empty($this->image)) {
            return _MODULE_DIR_ . 'katalogy/views/img/katalogy/' . $this->image;
        }
        return false;
    }

    /**
     * Check if catalog has download file
     */
    public function hasDownload()
    {
        return !empty($this->file_url) || !empty($this->file_path);
    }
}