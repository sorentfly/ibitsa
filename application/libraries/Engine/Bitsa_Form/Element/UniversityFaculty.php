<?php
class Bitsa_Form_Element_UniversityFaculty extends Zend_Form_Element_Text
{
    private $settings;
    private $vk_app_version;
    private $translate;
    
    public function init()
    {
        $this->translate = Zend_Registry::get('Zend_Translate');
        $this->settings = Engine_Api::_()->getApi('settings', 'core');
        $this->vk_app_version = $this->settings->getSetting('core.vk.version');
        $this->addFilter('StringTrim');
        $this->addFilter('StripTags');
        
        if($this->getValue() !== '')
        {
            Zend_Registry::set($this->getId(), $this->getValue());            
        }
        
        if (Zend_Registry::isRegistered('university'))
        {
            $faculties = $this->vkFacultiesRequest(Zend_Registry::get('university'));
            foreach ($faculties['response']['items'] as $value)
            {
                $faculties_options[$value['id']] = $value['title'];
            }
            Zend_Registry::set($this->getId() . '_multioptions', $faculties_options);
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
            $this->addDecorator('ViewHelper');
            $this->addDecorator('FormCombobox');            
        }
        
        $this->setAttribs(array('class' => 'university_faculty'));
    }
    
    private function vkFacultiesRequest($university_id = 1)
    {
        $count = 100;
        $lang = 'ru';
        if (Zend_Registry::get('Locale')->__toString() === 'en')
        {
            $lang = 'en';
        }
        
        $cache = Zend_Registry::get('Zend_Cache');
        $cacheKey = 'VK_API_Faculty'.'_'.$university_id.'_'.$lang;
        $faculty = $cache->load($cacheKey);
        if ($faculty){
            return $faculty;
        }

        
        $request = curl_init('https://api.vk.com/method/database.getFaculties?university_id=' . $university_id  .'&access_token='.VK_SERVICE_TOKEN  . '&count=' . $count . '&offset=0&v=' . $this->vk_app_version . '&lang=' . $lang);
        curl_setopt ($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);

        if(curl_getinfo($request, CURLINFO_HTTP_CODE) == 200)
        {
            $result = curl_exec($request); 
        }
        else
        {
            return false;
        }
        curl_close($request);
        $facultys = json_decode($result, true); 
        $cache->save($facultys, $cacheKey, array(), 7*86400);
        return $facultys;
    }
}