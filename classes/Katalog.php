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
        $max_position = (int)Db::getInstance()->getValue($sql);
        return $max_position > 0 ? $max_position : 0;
    }

    /**
     * Fix duplicate positions
     */
    public static function fixDuplicatePositions()
    {
        $sql = 'SELECT `id_katalog` FROM `' . _DB_PREFIX_ . 'katalogy` ORDER BY `position` ASC, `id_katalog` ASC';
        $catalogs = Db::getInstance()->executeS($sql);

        if ($catalogs) {
            $position = 1;
            foreach ($catalogs as $catalog) {
                Db::getInstance()->update(
                    'katalogy',
                    ['position' => $position],
                    'id_katalog = ' . (int)$catalog['id_katalog']
                );
                $position++;
            }
        }
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

    /**
     * Update position of catalog
     */
    public function updatePosition($way, $position)
    {
        if (!$res = Db::getInstance()->executeS('
            SELECT `id_katalog`, `position`
            FROM `' . _DB_PREFIX_ . 'katalogy`
            ORDER BY `position` ASC'
        )) {
            return false;
        }

        foreach ($res as $catalog) {
            if ((int)$catalog['id_katalog'] == (int)$this->id) {
                $moved_catalog = $catalog;
            }
        }

        if (!isset($moved_catalog) || !isset($position)) {
            return false;
        }

        // < and > statements rather than BETWEEN operator
        // since BETWEEN is treated differently according to databases
        return (Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'katalogy`
            SET `position`= `position` ' . ($way ? '- 1' : '+ 1') . '
            WHERE `position`
            ' . ($way
                ? '> ' . (int)$moved_catalog['position'] . ' AND `position` <= ' . (int)$position
                : '< ' . (int)$moved_catalog['position'] . ' AND `position` >= ' . (int)$position) . '
            AND `id_katalog` != ' . (int)$moved_catalog['id_katalog'])
        && Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'katalogy`
            SET `position` = ' . (int)$position . '
            WHERE `id_katalog` = ' . (int)$moved_catalog['id_katalog']));
    }
}