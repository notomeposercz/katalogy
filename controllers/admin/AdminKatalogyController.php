<?php
/**
 * Admin Controller for Katalogy Module - FINÁLNÍ VERZE
 */

require_once(_PS_MODULE_DIR_ . 'katalogy/classes/Katalog.php');

class AdminKatalogyController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'katalogy';
        $this->className = 'Katalog';
        $this->lang = false;
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->identifier = 'id_katalog';

        parent::__construct();

        $this->fields_list = [
            'id_katalog' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ],
            'image' => [
                'title' => $this->l('Obrázek'),
                'align' => 'center',
                'image' => 'katalogy',
                'orderby' => false,
                'search' => false,
                'class' => 'fixed-width-xs'
            ],
            'title' => [
                'title' => $this->l('Název'),
                'width' => 'auto'
            ],
            'description' => [
                'title' => $this->l('Popis'),
                'width' => 'auto',
                'maxlength' => 100
            ],
            'is_new' => [
                'title' => $this->l('Nový'),
                'align' => 'center',
                'type' => 'bool',
                'class' => 'fixed-width-xs'
            ],
            'position' => [
                'title' => $this->l('Pozice'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'position' => 'position'
            ],
            'active' => [
                'title' => $this->l('Aktivní'),
                'align' => 'center',
                'type' => 'bool',
                'class' => 'fixed-width-xs'
            ],
            'date_add' => [
                'title' => $this->l('Vytvořeno'),
                'align' => 'center',
                'type' => 'datetime',
                'class' => 'fixed-width-lg'
            ]
        ];

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Smazat vybrané'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Smazat vybrané položky?')
            ]
        ];

        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Katalog'),
                'icon' => 'icon-folder-open'
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Název'),
                    'name' => 'title',
                    'required' => true,
                    'size' => 50
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->l('Popis'),
                    'name' => 'description',
                    'rows' => 5,
                    'cols' => 50
                ],
                [
                    'type' => 'file',
                    'label' => $this->l('Obrázek'),
                    'name' => 'image',
                    'desc' => $this->l('Nahrajte náhledový obrázek katalogu')
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('URL katalogu'),
                    'name' => 'file_url',
                    'size' => 100,
                    'desc' => $this->l('Zadejte URL pro stažení katalogu (pokud je katalog na externím serveru)')
                ],
                [
                    'type' => 'file',
                    'label' => $this->l('Soubor katalogu'),
                    'name' => 'catalog_file',
                    'desc' => $this->l('Nebo nahrajte soubor katalogu (PDF, DOC, atd.)')
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Nový katalog'),
                    'name' => 'is_new',
                    'values' => [
                        [
                            'id' => 'is_new_on',
                            'value' => 1,
                            'label' => $this->l('Ano')
                        ],
                        [
                            'id' => 'is_new_off',
                            'value' => 0,
                            'label' => $this->l('Ne')
                        ]
                    ]
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Pozice'),
                    'name' => 'position',
                    'size' => 5,
                    'desc' => $this->l('Pořadí zobrazení (nižší číslo = vyšší pozice)')
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Aktivní'),
                    'name' => 'active',
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Ano')
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Ne')
                        ]
                    ]
                ]
            ],
            'submit' => [
                'title' => $this->l('Uložit')
            ]
        ];

        $this->_orderBy = 'position';
        $this->_orderWay = 'ASC';

        // Enable position management
        $this->position_identifier = 'id_katalog';
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        return parent::renderList();
    }

    public function renderForm()
    {
        // Správné zobrazení existujícího obrázku při editaci
        if (isset($this->object) && $this->object->id) {
            $katalog = new Katalog($this->object->id);
            
            if ($katalog->image) {
                $image_url = _MODULE_DIR_ . 'katalogy/views/img/katalogy/' . $katalog->image;
                
                $current_image_input = [
                    'type' => 'html',
                    'label' => $this->l('Aktuální obrázek'),
                    'name' => 'current_image_display',
                    'html_content' => '<img src="' . $image_url . '" alt="Aktuální obrázek" style="max-width: 200px; max-height: 200px;" />' .
                                     '<input type="hidden" name="existing_image" value="' . $katalog->image . '" />'
                ];
                
                array_splice($this->fields_form['input'], 2, 0, [$current_image_input]);
            }

            if ($katalog->file_path) {
                $file_url = _MODULE_DIR_ . 'katalogy/files/' . $katalog->file_path;
                $current_file_input = [
                    'type' => 'html',
                    'label' => $this->l('Aktuální soubor'),
                    'name' => 'current_file_display',
                    'html_content' => '<a href="' . $file_url . '" target="_blank" class="btn btn-default">' . 
                                     $this->l('Stáhnout aktuální soubor') . '</a>' .
                                     '<input type="hidden" name="existing_file_path" value="' . $katalog->file_path . '" />'
                ];
                
                array_splice($this->fields_form['input'], 5, 0, [$current_file_input]);
            }
        }

        return parent::renderForm();
    }

    public function postProcess()
    {
        // Správné zpracování editace vs. přidání
        if (Tools::isSubmit('submit' . $this->table)) {
            $id = (int)Tools::getValue('id_katalog');
            
            if ($id > 0) {
                return $this->processUpdate();
            } else {
                return $this->processAdd();
            }
        }

        return parent::postProcess();
    }

    public function processAdd()
    {
        $katalog = new Katalog();
        $this->copyFromPost($katalog, $this->table);
        
        if (empty($katalog->position) || $katalog->position == 0) {
            $katalog->position = $this->getNextPosition();
        }

        $katalog->date_add = date('Y-m-d H:i:s');
        $katalog->date_upd = date('Y-m-d H:i:s');

        if ($katalog->save()) {
            $this->handleFileUploads($katalog);
            $this->confirmations[] = $this->l('Katalog byl úspěšně přidán.');
            $this->redirect_after = self::$currentIndex . '&token=' . $this->token;
        } else {
            $this->errors[] = $this->l('Chyba při ukládání katalogu.');
        }
    }

    public function processUpdate()
    {
        $id = (int)Tools::getValue('id_katalog');
        $katalog = new Katalog($id);

        if (!Validate::isLoadedObject($katalog)) {
            $this->errors[] = $this->l('Katalog nebyl nalezen.');
            return false;
        }

        $original_image = $katalog->image;
        $original_file_path = $katalog->file_path;
        $original_position = $katalog->position;

        $this->copyFromPost($katalog, $this->table);

        $katalog->id = $id;
        $katalog->id_katalog = $id;

        // Zachování souborů pokud se nenahrávají nové
        if (empty($_FILES['image']['tmp_name']) && Tools::getValue('existing_image')) {
            $katalog->image = Tools::getValue('existing_image');
        }

        if (empty($_FILES['catalog_file']['tmp_name']) && Tools::getValue('existing_file_path')) {
            $katalog->file_path = Tools::getValue('existing_file_path');
        }

        if (empty($katalog->position) || $katalog->position == 0) {
            $katalog->position = $original_position;
        }

        $katalog->date_upd = date('Y-m-d H:i:s');

        if ($katalog->update()) {
            $this->handleFileUploads($katalog, $original_image, $original_file_path);
            $this->confirmations[] = $this->l('Katalog byl úspěšně upraven.');
            $this->redirect_after = self::$currentIndex . '&token=' . $this->token;
        } else {
            $this->errors[] = $this->l('Chyba při ukládání katalogu.');
        }
    }

    /**
     * DRAG & DROP POZICE - Hlavní metoda pro AJAX
     */
    public function ajaxProcessUpdatePositions()
    {
        $positions = Tools::getValue($this->table);

        if (is_array($positions)) {
            foreach ($positions as $position => $value) {
                $pos = explode('_', $value);
                if (isset($pos[2]) && is_numeric($pos[2])) {
                    $katalog_id = (int)$pos[2];
                    $new_position = (int)$position + 1; // JS počítá od 0, DB od 1
                    
                    $sql = 'UPDATE `' . _DB_PREFIX_ . 'katalogy` 
                           SET `position` = ' . (int)$new_position . ' 
                           WHERE `id_katalog` = ' . (int)$katalog_id;
                    
                    Db::getInstance()->execute($sql);
                }
            }
        }

        die(json_encode(['success' => true]));
    }

    /**
     * Zpracování pozice pomocí šipek nahoru/dolů
     */
    public function processPosition()
    {
        if (!$this->loadObject(true)) {
            return false;
        }

        $way = (int)Tools::getValue('way');
        $id_katalog = (int)$this->object->id;
        $current_position = (int)$this->object->position;
        
        if ($way) {
            // Nahoru - snížit pozici
            $new_position = max(1, $current_position - 1);
        } else {
            // Dolů - zvýšit pozici
            $new_position = $current_position + 1;
        }

        // Prohození pozic
        $swap_sql = 'UPDATE `' . _DB_PREFIX_ . 'katalogy` 
                   SET `position` = ' . (int)$current_position . ' 
                   WHERE `position` = ' . (int)$new_position . ' 
                   AND `id_katalog` != ' . (int)$id_katalog;
        Db::getInstance()->execute($swap_sql);

        // Nastavení nové pozice
        $update_sql = 'UPDATE `' . _DB_PREFIX_ . 'katalogy` 
                     SET `position` = ' . (int)$new_position . ' 
                     WHERE `id_katalog` = ' . (int)$id_katalog;
        
        if (Db::getInstance()->execute($update_sql)) {
            $this->redirect_after = self::$currentIndex . '&token=' . $this->token . '&conf=5';
        } else {
            $this->errors[] = $this->l('Nepodařilo se aktualizovat pozici.');
        }
    }

    private function handleFileUploads($katalog, $original_image = null, $original_file_path = null)
    {
        $updated = false;

        // Zpracování nahrání obrázku
        if (isset($_FILES['image']) && $_FILES['image']['size'] > 0 && $_FILES['image']['error'] == 0) {
            $image_name = $katalog->id . '_' . time() . '.jpg';
            $upload_dir = _PS_MODULE_DIR_ . 'katalogy/views/img/katalogy/';

            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name)) {
                if ($original_image && $original_image != $image_name && file_exists($upload_dir . $original_image)) {
                    unlink($upload_dir . $original_image);
                }
                $katalog->image = $image_name;
                $updated = true;
            }
        }

        // Zpracování nahrání souboru katalogu
        if (isset($_FILES['catalog_file']) && $_FILES['catalog_file']['size'] > 0 && $_FILES['catalog_file']['error'] == 0) {
            $file_extension = pathinfo($_FILES['catalog_file']['name'], PATHINFO_EXTENSION);
            $file_name = $katalog->id . '_catalog_' . time() . '.' . $file_extension;
            $upload_dir = _PS_MODULE_DIR_ . 'katalogy/files/';

            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($_FILES['catalog_file']['tmp_name'], $upload_dir . $file_name)) {
                if ($original_file_path && $original_file_path != $file_name && file_exists($upload_dir . $original_file_path)) {
                    unlink($upload_dir . $original_file_path);
                }
                $katalog->file_path = $file_name;
                $updated = true;
            }
        }

        if ($updated) {
            $katalog->update();
        }
    }

    private function getNextPosition()
    {
        $sql = 'SELECT MAX(`position`) as max_pos FROM `' . _DB_PREFIX_ . 'katalogy`';
        $result = Db::getInstance()->getRow($sql);
        return (int)$result['max_pos'] + 1;
    }

    /**
     * Oprava duplicitních pozic
     */
    private function fixDuplicatePositions()
    {
        $sql = 'SELECT `id_katalog` FROM `' . _DB_PREFIX_ . 'katalogy` ORDER BY `position` ASC, `id_katalog` ASC';
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
    }
}