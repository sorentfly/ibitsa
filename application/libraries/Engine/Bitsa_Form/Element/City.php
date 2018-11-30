<?
class Bitsa_Form_Element_City extends Zend_Form_Element_Text
{
    private $settings;
    private $vk_app_version;
    private $translate;
    
    public function init()
    {
        $this->settings = Engine_Api::_()->getApi('settings', 'core');
        $this->vk_app_version = $this->settings->getSetting('core.vk.version');
        $this->translate = Zend_Registry::get('Zend_Translate');
        
        $this->addFilters(array('StringTrim', 'StripTags'));
        
        $country = null;
        $region = null;
        $cities_options = array();
        /*КОСТЫЛЬ - мало того дублирование кода, так ещё и не гибко - при добавлении нового селектора регионов - сюда надо копипастить. Рефакторить бы це*/
        switch($this->getId())
        {
            case 'city':
            if(Zend_Registry::isRegistered('country'))
            {
                $country = Zend_Registry::get('country');
                $region = Zend_Registry::isRegistered('region') ? Zend_Registry::get('region') : null;
            }
            break;
            case 'school_city':
            if(Zend_Registry::isRegistered('school_country'))
            {
                $country = Zend_Registry::get('school_country');
                $region = Zend_Registry::isRegistered('school_region') ? Zend_Registry::get('school_region') : null;
            }
            break;
            case 'university_city':
            if(Zend_Registry::isRegistered('university_country'))
            {
                $country = Zend_Registry::get('university_country');
                $region = Zend_Registry::isRegistered('university_region') ? Zend_Registry::get('university_region') : null; 
            }
            break;
            case 'work_city':
            if(Zend_Registry::isRegistered('work_country'))
            {
                $country = Zend_Registry::get('work_country');
                $region = Zend_Registry::isRegistered('work_region') ? Zend_Registry::get('work_region') : null; 
            }
            break;
        }
        if (!$country) $country = 1;
        if (!$region) $region = 0;
        
        Zend_Registry::set($this->getId() . '_multioptions', $country.'_' .$region);
    }
  
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled())
        {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators))
        {
            $this->addDecorator('ViewHelper');
            $this->addDecorator('FormCity');            
        }
        
        $this->setAttribs(array('class' => 'city'));
    }     
}