<?php
class Zend_Filter_VkIdToName implements Zend_Filter_Interface
{
    private $settings;
    private $vk_app_version;
    private $translate;
    protected $_options = array(
        'field_name'      => null,
        'country_id' => 1,
        'city_id'   => 1
    );

    public function __construct($options = null)
    {
        $this->translate = Zend_Registry::get('Zend_Translate');
        $this->settings = Engine_Api::_()->getApi('settings', 'core');
        $this->vk_app_version = $this->settings->getSetting('core.vk.version');
        if (null !== $options)
        {
            $this->setOptions($options);
        }
    }

    public function getOptions()
    {
        return $this->_options;
    }

    public function setOptions(array $options = null)
    {
        $this->_options = $options + $this->_options;
        return $this;
    }

    public function filter($value)
    {
        switch ($this->_options->field_name)
        {
            case 'country':
            {
                 $method = 'database.getCountriesById';
                 $params = 'country_ids=' . $value;
            }
                break;
            case 'city':
            {
                $method = 'database.getCitiesById';
                $params = 'city_ids=' . $value;
            }
                break;
            case 'school':
            {
                if(Zend_Registry::isRegistered('school_country'))
                {
                    $this->_options->country_id = Zend_Registry::get('school_country');
                }
                
                if(Zend_Registry::isRegistered('school_city'))
                {
                    $this->_options->city_id = Zend_Registry::get('school_city');
                }
                
                $method = 'database.getCitiesById';
                $params = 'country_id=' . $this->_options->country_id . '&city_id=' . $this->_options->city_id . '&q=' . $value;
            }
                break;
            case 'university':
            {
                    
            }
                break;
            case 'faculty':
            {
                    
            }
                break;
        }
        $result = $this->vkRequest($method, $params);
        return $result['response']['title'];
    }

    private function vkRequest($method, $params)
    {
        $request = curl_init('https://api.vk.com/method/' . $method . '?' . $params  .'&access_token='.VK_SERVICE_TOKEN . '&v=' . $this->vk_app_version);
        curl_setopt ($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($request); 
        $http_code = curl_getinfo($request, CURLINFO_HTTP_CODE);
        curl_close($request);
        if($http_code !== 200)
        {            
            return false;
        }
        return json_decode($result, true);         
    }

}