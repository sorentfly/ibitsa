<?
class Bitsa_Form_Element_Region extends Zend_Form_Element_Text
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
        $this->addFilter('StringTrim');
        $this->setAttrib('type', 'text');
        
        $country = null;

        /*КОСТЫЛЬ - мало того дублирование кода, так ещё и не гибко - при добавлении нового селектора регионов - сюда надо копипастить. Рефакторить бы це*/
        $thisValue = null;
        if(Zend_Registry::isRegistered('editingUser'))
        {
            $user = Zend_Registry::get('editingUser');
            if ($user->getIdentity()){
                $thisValue = Engine_Api::_()->fields()->getValByName($user, $this->getId());
            }
        }
        
        switch($this->getId())
        {
            case 'region':
            if(Zend_Registry::isRegistered('country'))
            {
                $country = Zend_Registry::get('country');
            }
            break;
            case 'school_region':
            if(Zend_Registry::isRegistered('school_country'))
            {
                $country = Zend_Registry::get('school_country');
            }
            break;
            case 'university_region':
            if(Zend_Registry::isRegistered('university_country'))
            {
                $country = Zend_Registry::get('university_country');
            }
            break;
            case 'work_region':
            if(Zend_Registry::isRegistered('work_country'))
            {
                $country = Zend_Registry::get('work_country');
            }
            break;
        }
        
        if (!$country) $country = 1;

        $regions_options = array();
        
        $regions = $this->vkRegionRequest($country);
        if (isset($regions['response'])){
            foreach ($regions['response']['items'] as $value)
            {
                if ($value['title'] == $thisValue){
                    Zend_Registry::set($this->getId(), $value['id']);
                }
                $regions_options[$value['id']] = $value['title'];
            }
        }
        Zend_Registry::set($this->getId() . '_multioptions', $country);
    }
    
    private function vkRegionRequest($country_id = '1', $count = '1000', $lang = 'ru')
    {
        $cache = Zend_Registry::get('Zend_Cache');
        $cacheKey = 'VK_API_REGIONS'.'_'.$country_id.'_'.$count.'_'.$lang;
        $regions = $cache->load($cacheKey);
        if ($regions){
            return $regions;
        }
        if (Zend_Registry::get('Locale')->__toString() === 'en'){ $lang = 'en'; }
        $request = curl_init('https://api.vk.com/method/database.getRegions?country_id=' . $country_id .'&access_token='.VK_SERVICE_TOKEN . '&count=' . $count . '&v=' . $this->vk_app_version . '&lang=' . $lang);
        curl_setopt ($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($request);                 
        curl_close($request);
        $regions = json_decode($result, true);
        
        $cache->save($regions, $cacheKey, array(), 7*86400);
        return $regions;
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
                ->addDecorator('FormRegion');
        }
        
        $this->setAttribs(array('class' => 'region'));
    }
}