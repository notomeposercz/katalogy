<?php
/**
 * Katalog Model Class - OPRAVENÁ VERZE
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
        $sql = 'SELECT MAX(`position`) as max_pos FROM `' . _DB_PREFIX_ . 'katalogy`';
        $result = Db::getInstance()->getRow($sql);
        return $result ? (int)$result['max_pos'] : 0;
    }

    /**
     * OPRAVA: Kompletní přepsání opravy duplicitních pozic
     */
    public static function fixDuplicatePositions()
    {
        // Získej všechny katalogy seřazené podle aktuální pozice a ID
        $sql = 'SELECT `id_katalog` FROM `' . _DB_PREFIX_ . 'katalogy` 
                ORDER BY `position` ASC, `id_katalog` ASC';
        $catalogs = Db::getInstance()->executeS($sql);

        if ($catalogs) {
            $position = 1;
            foreach ($catalogs as $catalog) {
                $update_sql = 'UPDATE `' . _DB_PREFIX_ . 'katalogy` 
                              SET `position` = ' . (int)$position . ' 
                              WHERE `id_katalog` = ' . (int)$catalog['id_katalog'];
                Db::getInstance()->execute($update_sql);
                $position++;
            }
        }
        
        return true;
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
     * OPRAVA: Přepsaná metoda pro aktualizaci bez vytváření nových záznamů
     */
    public function update($null_values = false)
    {
        // Zajisti, že máme správné ID
        if (empty($this->id) && !empty($this->id_katalog)) {
            $this->id = $this->id_katalog;
        }

        // Nastav datum aktualizace
        $this->date_upd = date('Y-m-d H:i:s');

        // Zavolej parent update metodu
        return parent::update($null_values);
    }

    /**
     * OPRAVA: Přepsaná metoda save pro správné vytváření/editaci
     */
    public function save($null_values = false, $auto_date = true)
    {
        // Pokud máme ID, jedná se o update
        if (!empty($this->id) || !empty($this->id_katalog)) {
            if (empty($this->id) && !empty($this->id_katalog)) {
                $this->id = $this->id_katalog;
            }
            
            if ($auto_date) {
                $this->date_upd = date('Y-m-d H:i:s');
            }
            
            return $this->update($null_values);
        } else {
            // Nový záznam
            if ($auto_date) {
                $this->date_add = date('Y-m-d H:i:s');
                $this->date_upd = date('Y-m-d H:i:s');
            }
            
            // Nastav pozici pokud není zadána
            if (empty($this->position)) {
                $this->position = self::getHighestPosition() + 1;
            }
            
            return $this->add($null_values, $auto_date);
        }
    }

    /**
     * OPRAVA: Nová metoda pro aktualizaci pozice bez duplicit
     */
    public function updatePosition($way, $position)
    {
        // Načti aktuální pozici
        $current_position = (int)$this->position;
        $target_position = (int)$position;
        
        if ($current_position == $target_position) {
            return true; // Žádná změna
        }

        // Začni transakci
        Db::getInstance()->execute('START TRANSACTION');

        try {
            if ($way) {
                // Posun nahoru (snížení pozice)
                $sql = 'UPDATE `' . _DB_PREFIX_ . 'katalogy` 
                       SET `position` = `position` + 1 
                       WHERE `position` >= ' . (int)$target_position . ' 
                       AND `position` < ' . (int)$current_position . ' 
                       AND `id_katalog` != ' . (int)$this->id;
            } else {
                // Posun dolů (zvýšení pozice)
                $sql = 'UPDATE `' . _DB_PREFIX_ . 'katalogy` 
                       SET `position` = `position` - 1 
                       WHERE `position` <= ' . (int)$target_position . ' 
                       AND `position` > ' . (int)$current_position . ' 
                       AND `id_katalog` != ' . (int)$this->id;
            }
            
            // Proveď posun ostatních
            if (!Db::getInstance()->execute($sql)) {
                throw new Exception('Chyba při posunu ostatních pozic');
            }

            // Nastav novou pozici pro aktuální katalog
            $update_sql = 'UPDATE `' . _DB_PREFIX_ . 'katalogy` 
                          SET `position` = ' . (int)$target_position . ' 
                          WHERE `id_katalog` = ' . (int)$this->id;
            
            if (!Db::getInstance()->execute($update_sql)) {
                throw new Exception('Chyba při nastavení nové pozice');
            }

            // Potvrď transakci
            Db::getInstance()->execute('COMMIT');
            
            // Aktualizuj objekt
            $this->position = $target_position;
            
            return true;
            
        } catch (Exception $e) {
            // Vrať transakci zpět
            Db::getInstance()->execute('ROLLBACK');
            return false;
        }
    }

    /**
     * Získání katalogu pro administraci (včetně neaktivních)
     */
    public static function getAllForAdmin()
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'katalogy` 
                ORDER BY `position` ASC';
        
        return Db::getInstance()->executeS($sql);
    }

    /**
     * Smazání katalogu včetně souborů
     */
    public function delete()
    {
        // Smaž soubory
        if ($this->image) {
            $image_path = _PS_MODULE_DIR_ . 'katalogy/views/img/katalogy/' . $this->image;
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        if ($this->file_path) {
            $file_path = _PS_MODULE_DIR_ . 'katalogy/files/' . $this->file_path;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        // Smaž z databáze
        $result = parent::delete();
        
        // Oprav pozice po smazání
        if ($result) {
            self::fixDuplicatePositions();
        }
        
        return $result;
    }
}