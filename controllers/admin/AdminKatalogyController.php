<?php
/**
 * Admin Controller for Katalogy Module
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

        // Add position management
        $this->addRowAction('position');

        return parent::renderList();
    }

    public function renderForm()
    {
        // Přidat zobrazení existujícího obrázku při editaci
        if ($this->object && $this->object->id_katalog) {
            $katalog = new Katalog($this->object->id_katalog);
            if ($katalog->image) {
                $image_url = _MODULE_DIR_ . 'katalogy/views/img/katalogy/' . $katalog->image;
                $this->fields_form['input'][] = [
                    'type' => 'html',
                    'label' => $this->l('Aktuální obrázek'),
                    'name' => 'current_image',
                    'html_content' => '<img src="' . $image_url . '" alt="Aktuální obrázek" style="max-width: 200px; max-height: 200px;" />'
                ];
            }

            if ($katalog->file_path) {
                $file_url = _MODULE_DIR_ . 'katalogy/files/' . $katalog->file_path;
                $this->fields_form['input'][] = [
                    'type' => 'html',
                    'label' => $this->l('Aktuální soubor'),
                    'name' => 'current_file',
                    'html_content' => '<a href="' . $file_url . '" target="_blank" class="btn btn-default">' . $this->l('Stáhnout aktuální soubor') . '</a>'
                ];
            }
        }

        return parent::renderForm();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitAdd' . $this->table)) {
            $this->processAdd();
            return;
        } elseif (Tools::isSubmit('submitEdit' . $this->table)) {
            $this->processEdit();
            return;
        }

        return parent::postProcess();
    }

    public function processAdd()
    {
        $katalog = new Katalog();
        $this->copyFromPost($katalog, $this->table);
        
        if ($katalog->position == 0) {
            $katalog->position = Katalog::getHighestPosition() + 1;
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

    public function processEdit()
    {
        $id = (int)Tools::getValue('id_katalog');
        $katalog = new Katalog($id);

        if (!Validate::isLoadedObject($katalog)) {
            $this->errors[] = $this->l('Katalog nebyl nalezen.');
            return;
        }

        // Zachovat původní hodnoty
        $original_image = $katalog->image;
        $original_file_path = $katalog->file_path;
        $original_position = $katalog->position;

        $this->copyFromPost($katalog, $this->table);

        // Zajistit, že ID zůstane stejné
        $katalog->id_katalog = $id;

        // Zachovat pozici pokud nebyla změněna
        if (empty($katalog->position) || $katalog->position == 0) {
            $katalog->position = $original_position;
        }

        $katalog->date_upd = date('Y-m-d H:i:s');

        if ($katalog->save()) {
            $this->handleFileUploads($katalog, $original_image, $original_file_path);
            $this->confirmations[] = $this->l('Katalog byl úspěšně upraven.');
            $this->redirect_after = self::$currentIndex . '&token=' . $this->token;
        } else {
            $this->errors[] = $this->l('Chyba při ukládání katalogu.');
        }
    }

    private function handleFileUploads($katalog, $original_image = null, $original_file_path = null)
    {
        $updated = false;

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
            $image_name = $katalog->id_katalog . '_' . time() . '.jpg';
            $upload_dir = _PS_MODULE_DIR_ . 'katalogy/views/img/katalogy/';

            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name)) {
                // Smazat starý obrázek pokud existuje
                if ($original_image && file_exists($upload_dir . $original_image)) {
                    unlink($upload_dir . $original_image);
                }
                $katalog->image = $image_name;
                $updated = true;
            }
        } else if ($original_image) {
            // Zachovat původní obrázek pokud se nenahrává nový
            $katalog->image = $original_image;
        }

        // Handle catalog file upload
        if (isset($_FILES['catalog_file']) && $_FILES['catalog_file']['size'] > 0) {
            $file_extension = pathinfo($_FILES['catalog_file']['name'], PATHINFO_EXTENSION);
            $file_name = $katalog->id_katalog . '_catalog_' . time() . '.' . $file_extension;
            $upload_dir = _PS_MODULE_DIR_ . 'katalogy/files/';

            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($_FILES['catalog_file']['tmp_name'], $upload_dir . $file_name)) {
                // Smazat starý soubor pokud existuje
                if ($original_file_path && file_exists($upload_dir . $original_file_path)) {
                    unlink($upload_dir . $original_file_path);
                }
                $katalog->file_path = $file_name;
                $updated = true;
            }
        } else if ($original_file_path) {
            // Zachovat původní soubor pokud se nenahrává nový
            $katalog->file_path = $original_file_path;
        }

        // Uložit pouze pokud byly změny
        if ($updated) {
            $katalog->save();
        }
    }

    public function ajaxProcessUpdatePositions()
    {
        $way = (int)Tools::getValue('way');
        $id = (int)Tools::getValue('id');
        $positions = Tools::getValue($this->table);

        if (is_array($positions)) {
            foreach ($positions as $position => $value) {
                $pos = explode('_', $value);
                if (isset($pos[2])) {
                    $katalog_id = (int)$pos[2];
                    $new_position = (int)$position + 1; // Position starts from 1

                    // Update position directly in database to avoid conflicts
                    Db::getInstance()->update(
                        'katalogy',
                        ['position' => $new_position],
                        'id_katalog = ' . $katalog_id
                    );
                }
            }
        }

        die(json_encode(['success' => true]));
    }

    public function processPosition()
    {
        if (!$this->loadObject(true)) {
            return false;
        }

        if ($this->object->updatePosition((int)Tools::getValue('way'), (int)Tools::getValue('position'))) {
            $this->redirect_after = self::$currentIndex . '&token=' . $this->token . '&conf=5';
        } else {
            $this->errors[] = $this->l('Failed to update the position.');
        }
    }
}