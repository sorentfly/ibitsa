<?

class Bitsa_Form_Element_Country extends Zend_Form_Element_Text
{
    private $vk_app_version;
    private $settings;
    private $tb_prefix;
    private $db;
    private $translate;

    public function init()
    {
        $this->translate = Zend_Registry::get('Zend_Translate');
        $this->db = Engine_Db_Table::getDefaultAdapter();
        $this->tb_prefix = Engine_Db_Table::getTablePrefix();
        $this->settings = Engine_Api::_()->getApi('settings', 'core');
        $this->vk_app_version = $this->settings->getSetting('core.vk.version');
        $this->addFilters(array('StringTrim', 'StripTags'));

        $current_counrty_title = '';
        if(Zend_Registry::isRegistered('editingUser'))
        {
            $user = Zend_Registry::get('editingUser');
            if ($user->getIdentity()){
                $current_counrty_title = Engine_Api::_()->fields()->getValByName($user, $this->getId());
            }
        }
        $current_counrty_id = null;
        
        $counrties_options = array(''=>' ');
        if(Zend_Registry::isRegistered('countriesList')) {
            $counrties = Zend_Registry::get('countriesList');
        } else {
            $counrties = $this->db->select()->from($this->tb_prefix . 'countries')->query()->fetchAll();
            Zend_Registry::set('countriesList', $counrties);
        }
        $viewer = Engine_Api::_()->user()->getViewer();

        for ($i = 0; $i < count($counrties); $i++) {
            $counrties_options[$counrties[$i]['id']] =  Zend_Registry::get('Zend_Translate')->getLocale() == 'en' ? $counrties[$i]['english_name'] : $counrties[$i]['russian_name'];

            if ($counrties[$i]['russian_name'] == $current_counrty_title) {
                $current_counrty_id = intval($counrties[$i]['id']);
            }
        }

        if($current_counrty_id == null) {
            $current_counrty_id = '';
        }

        Zend_Registry::set($this->getId(), $current_counrty_id);

        Zend_Registry::set($this->getId() . '_multioptions', $counrties_options);
    }

    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('ViewHelper');
            $this->addDecorator('FormCountry');
        }

        $this->setAttribs(array('class' => 'country'));
    }
}