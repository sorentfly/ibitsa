<?php
class Bitsa_Form_Element_UniversityMajor extends Zend_Form_Element_Text
{
    //private $translate;
    private $settings;
    private $vk_app_version;
    
    public function init()
    {
        //$this->translate = Zend_Registry::get('Zend_Translate');
        $this->settings = Engine_Api::_()->getApi('settings', 'core');
        $this->vk_app_version = $this->settings->getSetting('core.vk.version');
        
        $this->addFilters(array('StringTrim','StripTags'));
        
        if(Zend_Registry::isRegistered('faculty_id'))
        {
            
        }
        
        
        
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
            $this->addDecorator('ViewHelper')
                ->addDecorator('FormCombobox');
        }
        
        $this->setAttribs(array('class' => 'university_major'));
    }
}