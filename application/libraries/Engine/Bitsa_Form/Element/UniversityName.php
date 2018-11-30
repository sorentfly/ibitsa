<?php

class Bitsa_Form_Element_UniversityName extends Zend_Form_Element_Text
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

        if (Zend_Registry::isRegistered('university_country') && Zend_Registry::isRegistered('university_city')) {
            $universities = $this->vkUniversitiesRequest(Zend_Registry::get('university_country'), Zend_Registry::get('university_city'));
        } else {
            if (Zend_Registry::isRegistered('university_country')) {
                $universities = $this->vkUniversitiesRequest(Zend_Registry::get('university_country'));
            } else {
                $universities = $this->vkUniversitiesRequest();
            }
        }

        if (!empty($universities ['response'] ['items'])) {
            if ($this->getValue() !== '') {
                Zend_Registry::set($this->getId(), $this->getValue());
                foreach ($universities ['response'] ['items'] as $value) {
                    $university_options [$value ['id']] = $value ['title'];
                }
            } else {
                foreach ($universities ['response'] ['items'] as $value) {
                    $university_options [$value ['id']] = $value ['title'];
                }
            }

            Zend_Registry::set($this->getId() . '_multioptions', $university_options);
        }
    }

    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('ViewHelper');
            $this->addDecorator('FormCombobox');
        }

        $this->setAttribs(array(
            'class' => 'university_name'
        ));
    }

    private function vkUniversitiesRequest($country_id = 1, $city_id = 1)
    {
        $count = 300;
        $lang = 'ru';
        if (Zend_Registry::get('Locale')->__toString() === 'en') {
            $lang = 'en';
        }
        if (empty($city_id)) {
            $city_id = 1;
        }
        if (empty($country_id)) {
            $country_id = 1;
        }

        $cache = Zend_Registry::get('Zend_Cache');
        $cacheKey = 'VK_API_University' . '_' . $country_id . '_' . $city_id . '_' . $lang;
        $university = $cache->load($cacheKey);
        if ($university) {
            return $university;
        }

        $request = curl_init('https://api.vk.com/method/database.getUniversities?country_id=' . $country_id .'&access_token='.VK_SERVICE_TOKEN . '&city_id=' . $city_id . '&count=' . $count . '&offset=0&v=' . $this->vk_app_version . '&lang=' . $lang);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($request);
        if (curl_getinfo($request, CURLINFO_HTTP_CODE) !== 200) {
            return false;
        }
        curl_close($request);

        $university = json_decode($result, true);
        $cache->save($university, $cacheKey, array(), 7 * 86400);
        return $university;
    }
}