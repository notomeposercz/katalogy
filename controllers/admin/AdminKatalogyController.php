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
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        return parent::renderList();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitAdd' . $this->table)) {
            $this->processAdd();
        } elseif (Tools::isSubmit('submitEdit' . $this->table)) {
            $this->processEdit();
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

        $this->copyFromPost($katalog, $this->table);
        $katalog->date_upd = date('Y-m-d H:i:s');

        if ($katalog->save()) {
            $this->handleFileUploads($katalog);
            $this->confirmations[] = $this->l('Katalog byl úspěšně upraven.');
        } else {
            $this->errors[] = $this->l('Chyba při ukládání katalogu.');
        }
    }

    private function handleFileUploads($katalog)
    {
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
            $image_name = $katalog->id . '_' . time() . '.jpg';
            $upload_dir = _PS_MODULE_DIR_ . 'katalogy/views/img/katalogy/';
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name)) {
                $katalog->image = $image_name;
                $katalog->save();
            }
        }

        // Handle catalog file upload
        if (isset($_FILES['catalog_file']) && $_FILES['catalog_file']['size'] > 0) {
            $file_extension = pathinfo($_FILES['catalog_file']['name'], PATHINFO_EXTENSION);
            $file_name = $katalog->id . '_catalog_' . time() . '.' . $file_extension;
            $upload_dir = _PS_MODULE_DIR_ . 'katalogy/files/';
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($_FILES['catalog_file']['tmp_name'], $upload_dir . $file_name)) {
                $katalog->file_path = $file_name;
                $katalog->save();
            }
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
                    $katalog = new Katalog((int)$pos[2]);
                    $katalog->position = (int)$position;
                    $katalog->save();
                }
            }
        }

        die(true);
    }
}