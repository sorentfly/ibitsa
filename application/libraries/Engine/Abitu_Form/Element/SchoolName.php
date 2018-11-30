<?

class Abitu_Form_Element_SchoolName extends Zend_Form_Element_Text
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
        $this->setAttrib('maxlength', '256');
        //$this->setAttrib('pattern', '^[-\w\s]+$');
        $this->addValidator('NotEmpty', true);
        $this->addValidator('StringLength', true, array(2, 256));

        if (Zend_Registry::isRegistered('school_country') && Zend_Registry::isRegistered('school_city')) {
            $schools = $this->vkSchoolsRequest(Zend_Registry::get('school_country'), Zend_Registry::get('school_city'));
        } else {
            if (Zend_Registry::isRegistered('school_country')) {
                $schools = $this->vkSchoolsRequest(Zend_Registry::get('school_country'));
            } else {
                $schools = $this->vkSchoolsRequest();
            }
        }

        if ($this->getValue() !== '') {
            Zend_Registry::set($this->getId(), $this->getValue());
            foreach ($schools['response']['items'] as $value) {
                $schools_options[$value['id']] = $value['title'];
            }
        }else{
            $schools_options = [];
        }

        Zend_Registry::set($this->getId() . '_multioptions', $schools_options);

        $this->setDescription('Полное название школы, например, МБОУ СОШ №1');

        $this->getValidator('NotEmpty')->setMessage($this->translate->_('Your school name is empty'), 'isEmpty');
        $this->getValidator('NotEmpty')->setMessage($this->translate->_('Your school name is empty'), 'notEmptyInvalid');
        //$this->getValidator('Regex')->setMessage($this->translate->_('Incorrect school name'), 'regexNotMatch');
        $this->getValidator('StringLength')->setMessage($this->translate->_('School name must have at least %min% characters'), 'stringLengthTooShort');
        $this->getValidator('StringLength')->setMessage($this->translate->_('School name must have at most %max% characters'), 'stringLengthTooLong');

    }

    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('ViewHelper');
            $this->addDecorator('FormSchoolName');
        }
    }

    private function vkSchoolsRequest($country_id = 1, $city_id = 1)
    {
        $count = 300;
        $lang = 'ru';
        if (Zend_Registry::get('Locale')->__toString() === 'en') {
            $lang = 'en';
        }
        
        $cache = Zend_Registry::get('Zend_Cache');
        $cacheKey = 'VK_API_SCHOOLS'.'_'.$country_id.'_'.$city_id.'_'.$lang;
        $schools = $cache->load($cacheKey);
        if ($schools){
            return $schools;
        }
        
        $request = curl_init('https://api.vk.com/method/database.getSchools?country_id=' . $country_id .'&access_token='.VK_SERVICE_TOKEN . '&city_id=' . $city_id . '&count=' . $count . '&offset=0&v=' . $this->vk_app_version . '&lang=' . $lang);

        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($request);
        if (curl_getinfo($request, CURLINFO_HTTP_CODE) !== 200) {
            return false;
        }
        curl_close($request);
        $schools = json_decode($result, true);
        
        $cache->save($schools, $cacheKey, array(), 7*86400);
        return $schools;
    }
}