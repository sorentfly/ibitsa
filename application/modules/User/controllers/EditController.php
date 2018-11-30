<?
require_once('application/libraries/NameCaseLib/Library/NCL.NameCase.ru.php');

class User_EditController extends Core_Controller_Action_User
{

    public function init()
    {
        if (!Engine_Api::_()->core()->hasSubject()) {
            $id = $this->_getParam('id', $this->_getParam('user_id', null)); // Can specifiy custom id
            $subject = null;
            if (null === $id) {
                $subject = Engine_Api::_()->user()->getViewer();
            } else {
                $subject = Engine_Api::_()->getItem('user', $id);
            }

            Engine_Api::_()->core()->setSubject($subject);
        }
        if (!empty($id)) {
            $params = array('params' => array('id' => $id));
        } else {
            $params = array();
        }

        // Set up navigation
        $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('user_edit', array('params' => array('id' => $id)));

        // Set up require's
        $this->_helper->requireUser();
        $this->_helper->requireSubject('user');
        /*!! Внимание - следущая строка разрешает редактирование Subject-а только Viewer-ам с правом 'edit' . Если её убрать - кто угодно сможет редактировать кого угодно, будьте внимательны !!*/
        $this->_helper->requireAuth()->setAuthParams(null, null, 'edit');
        /* !! */
        $this->view->name_case_lib = new NCLNameCaseRu();
    }

    public function profileAction()
    {
        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer(); //Редактор

        $editingUserId = intval($this->getRequest()->getParam('user_id'));
        /* @var User_Model_User $user */
        if ($editingUserId) {
            $user = Engine_Api::_()->getItem('user', $editingUserId);
        } else {
            $user = Engine_Api::_()->core()->getSubject(); //Редактируемый юзер
        }

        $this->view->user = $user;
        Zend_Registry::set('editingUser', $user);
       
        $profileTabs = Engine_Api::_()->user()->getFieldsTabs();
        $fieldTabs = $this->view->fieldTabs = array_keys($profileTabs);

        $profileTabs['user_photo'] = $this->view->translate('Edit photo');

        $this->initSocialProfileTab($user, $profileTabs);
        
        $this->initSettingsTabs($profileTabs);
                
        $this->view->tabname = $this->getRequest()->getParam('tabname', 'personal_information');
        if(!isset($profileTabs[$this->view->tabname])){
        	$this->view->tabname = 'personal_information';
        }

        foreach ($profileTabs as $key => $label) {
            $profileTabs[$key] = [
                'label' => $label,
            ];
        }

        $profileTabs['personal_information']['form'] = $form_1 = $this->view->form_1 = new User_Form_Fields(array(
            'user_id' => $user->user_id,
            'data_type' => '0'
        ));
        
        $form_1->setAttrib('class', 'global_form profile_form')->setAttrib('id', 'personal_information');

        $frozenMessage =  $user->academyStatus() == "teacher_approved"
            ? 'Редактирование личного профиля ограничено в связи с подтверждением Вас в качестве преподавателя.'
            : 'Редактирование личного профиля ограничено в связи с подтверждением корректности информации.' ;
        $frozenScript = ' NOTIFICATION_TOOLTIP.tooltipRight("' . $frozenMessage . '", this); clearTimeout(window.NOTIFICATION_TOOLTIP_TM); window.NOTIFICATION_TOOLTIP_TM = setTimeout(function(){NOTIFICATION_TOOLTIP.hide();},2000);';
        $canFrozenEdit = in_array($viewer->level_id, [1,2]) || !$user->isSelf($viewer);

        if ( !$canFrozenEdit ) foreach ($form_1->getElements() as $element) {
            if (in_array($element->getName(), $user->getFrozenFields())){
                $element->setAttrib('disabled', true)->setAttrib('onmouseover', $frozenScript);
            }
        }
        
        for ($i = 1; $i < count($fieldTabs); $i++) {
            $key = $fieldTabs[$i];
            $profileTabs[$key]['form'] = $tab = new User_Form_Fields(array(
                'user_id' => $user->user_id,
                'data_type' => $i
            ));

            if ( !$canFrozenEdit ) foreach ($tab->getElements() as $element) {
                if (in_array($element->getName(), $user->getFrozenFields())){
                    $element->setAttrib('disabled', true)->setAttrib('onmouseover', $frozenScript);
                }
            }
            $tab->setAttrib('class', 'global_form profile_form')->setAttrib('id', $key);
            if (!$tab->hasFields) {
                unset($profileTabs[$key]);
            }
        }

        $this->view->profile_tabs = $profileTabs;
        
       
        $this->view->unfilled_fields = $user->getUnfilledRequiredFields();
        $this->view->editing_user = $user->user_id; /* Id редактируемого пользователя */
    }
    
    private function initSettingsTabs(&$profileTabs){
    	$settingsNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('user_settings');
    	/* @var $item Zend_Navigation_Page_Mvc */ 
    	foreach ($settingsNavigation as $item){
    		$profileTabs['settings_'.$item->getAction()] = $this->view->translate($item->getLabel());
    	}
    	if(isset($profileTabs['settings_general'])){
    		$temp = ['settings_general' => $profileTabs['settings_general']];
    		unset($profileTabs['settings_general']);
    		$profileTabs = $temp + $profileTabs;
    	}
    }
    
    
    private function initSocialProfileTab($user, &$profileTabs){
    	$domSet = Engine_Api::_()->core()->getNowDomainSettings();
    	$settings = Engine_Api::_()->getApi('settings', 'core');
    	
    	if (empty($domSet['login_socials_disabled'])) {
    		
    		$profileTabs['social_profiles'] = $this->view->translate('Profiles in social networks');
    		
    		$baseAuthHref = 'http://' . bitsa_SITE . '/';
    		$domainTo = $domSet['key'] == 'bitsa' ? '' : $domSet['key'];
    		$_SESSION['crossDomainSocialAuthDomain'] = $domainTo;
    		
    		$vkRedirectUri = $baseAuthHref . 'simple_api/social_auth.php?method=vk_linking';
    		$vkPhotoRedirectUri = urlencode($baseAuthHref . 'members/edit/vkphoto');
    		
    		$vkAppId = $settings->getSetting('core.vk.appid');
    		$vkDisplay = $settings->getSetting('core.vk.display'); 			// 	page, popup или mobile
    		$vkApiVersion = $settings->getSetting('core.vk.version'); 		//	Версия API
    		$vkPermissions = $settings->getSetting('core.vk.permissions'); 	//	Подробный список разрешений тут: https://vk.com/dev/permissions
    		
    		$this->view->vk_link = 'https://oauth.vk.com/authorize?' . http_build_query(array('client_id' => $vkAppId, 'scope' => $vkPermissions, 'redirect_uri' => $vkRedirectUri, 'display' => $vkDisplay, 'v' => $vkApiVersion, 'response_type' => 'code'));
    		$this->view->vk_photo_link = 'https://oauth.vk.com/authorize?client_id=' . $vkAppId . '&scope=' . $vkPermissions . '&redirect_uri=' . $vkPhotoRedirectUri . '&display=' . $vkDisplay . '&v=' . $vkApiVersion . '&response_type=code';
    		
    		$miptRedirectUri = urlencode($baseAuthHref . 'simple_api/social_auth.php?method=mipt_auth');
    		$miptClientId = $settings->getSetting('core.mipt.appid');
    		$this->view->mipt_link = 'https://mipt.ru/oauth/authorize.php?response_type=code&state=xyz&scope=userinfo%20email&client_id=' . $miptClientId . '&redirect_uri=' . $miptRedirectUri;
    		
    		$yaClientId = $settings->getSetting('core.ya.clientid');
    		$this->view->ya_link = 'https://oauth.yandex.ru/authorize?response_type=code&client_id=' . $yaClientId;
    		
    		$googleRedirectUri = urlencode($baseAuthHref . 'simple_api/social_auth.php?method=google_auth');
    		$googleClientId = $settings->getSetting('core.google.clientid');
    		
    		$this->view->google_link = 'https://accounts.google.com/o/oauth2/auth?redirect_uri=' . $googleRedirectUri . '&response_type=code&client_id=' . $googleClientId . '&scope=' . urlencode('https://www.googleapis.com/auth/userinfo.email') . '+' . urlencode('https://www.googleapis.com/auth/userinfo.profile');
    		
    		$mailruRedirectUri = urlencode($baseAuthHref . 'simple_api/social_auth.php?method=mailru_auth');
    		$mailruClientId = $settings->getSetting('core.mailru.clientid');
    		$this->view->mailru_link = 'https://connect.mail.ru/oauth/authorize?client_id=' . $mailruClientId . '&response_type=code&redirect_uri=' . $mailruRedirectUri;
    		
    		$fbRedirectUri = urlencode($baseAuthHref . 'simple_api/social_auth.php?method=facebook_auth');
    		$fbClientId = $settings->getSetting('core.facebook.key');
    		$this->view->fb_link = 'https://www.facebook.com/dialog/oauth?response_type=code&client_id=' . $fbClientId . '&redirect_uri=' . $fbRedirectUri;
    		
    		$okRedirectUri = $baseAuthHref . 'simple_api/social_auth.php?method=ok_auth';
    		$this->view->ok_link = 'http://ok.ru/oauth/authorize?client_id=' . $settings->getSetting('core.ok.clientid') . '&scope=VALUABLE_ACCESS&response_type=code&redirect_uri=' . urlencode($okRedirectUri);
    		
    		$twitterRedirectUri = urlencode($baseAuthHref . 'simple_api/social_auth.php?method=twitter_auth');
    		$twitterClientId = $settings->getSetting('core.twitter.clientid');
    		$this->view->twitter_link = 'https://www.twitter.com';
    		
    		
    		$this->view->social_profiles = $this->db->select()
    		->from($this->tb_prefix . 'users_social')
    		->where($this->tb_prefix . 'users_social.user_id = ?', $user->user_id)
    		->query()
    		->fetchAll();
    		
    		
    	}
    }
    
    private function normJsonStr($str)
    {
        $str = preg_replace_callback('/\\\u([a-f0-9]{4})/i', create_function('$m', 'return chr(hexdec($m[1])-1072+224);'), $str);
        return iconv('cp1251', 'utf-8', $str);
    }

    public function editAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $viewer = Engine_Api::_()->user()->getViewer();
        $user_id = $this->getRequest()->getParam('user_id'); //Id редактируемого

        /* @var User_Model_User $user*/
        $user = Engine_Api::_()->getItem('user', $user_id);

        if (!$user || !$user->getIdentity()) {
            echo json_encode(array('status' => 'fail', 'reason' => 'access denied'));
            return;
        }
        //Поля, которые юзер подтвердил - более недоступны ему для редактирования
        $canFrozenEdit = in_array($viewer->level_id, [1,2]) || $viewer->hasMethodistRights() || !$user->isSelf($viewer);
        $frozenFields =  $canFrozenEdit  ? [] : $user->getFrozenFields();


        Zend_Registry::set('editingUser', $user);

        if ('passport' == $this->_getParam('form_name', null)) {
            $form_number = 7;
        } else {
            $form_number = intval( $this->db->fetchOne( $this->db->select()
                ->from($this->tb_prefix . 'user_fields_meta', ['data_type'])
                ->where($this->tb_prefix . "user_fields_meta.type = 'heading'")
                ->where($this->tb_prefix . 'user_fields_meta.name = ?', $this->_getParam('form_name', null))
                ->order($this->tb_prefix . 'user_fields_meta.field_id')
            ) );
        }

        if (!($form_number >= 0 && $form_number <= 7)) {
            echo json_encode(array('status' => 'fail', 'message' => $this->translate->_('Unknown type of editable form')));
            return;
        }

        $form = new User_Form_Fields(array(
            'user_id' => $user_id,
            'data_type' => $form_number
        ));
        $post = $this->getRequest()->getPost();
        $form->populate($post);

        /* NOTE дозаполнение форммы: поскольку замороженные поля с флагом disabled (disabled не отправляется из формы), и одновременно они могут быть обязательными - надо вручную их внести в post */
        $current_values = $this->db->select()
            ->from("{$this->tb_prefix}user_fields_values")
            ->joinInner("{$this->tb_prefix}user_fields_meta", "{$this->tb_prefix}user_fields_values.field_id = {$this->tb_prefix}user_fields_meta.field_id")
            ->where("{$this->tb_prefix}user_fields_values.item_id = " . $user_id)
            ->where("{$this->tb_prefix}user_fields_meta.data_type = " . $form_number)
            ->query()->fetchAll();
        $current_values_keyed = array_combine(array_column($current_values, 'name'), array_column($current_values, 'value'));
        $current_values_keyed['gender'] = $user->gender;

        foreach ($form->getElements() as $element) {

            $name = $element->getName();
            if (in_array($name, $frozenFields)){
                $value =  isset($current_values_keyed[$name]) ? $current_values_keyed[$name] : (isset($user->$name) ? $user->$name : '');
                $element->setAttrib('disabled', true)
                    ->setValue( $value )
                    ->clearValidators()
                    ->setRequired(false)
                    ->setAllowEmpty(true);
                $post[$name] = $value;
            }
        }
        /*END: дозаполнение форммы*/

        $isStatusModified = false;
        if ($form_number == 1 && ($country = $form->country->getValue()) == '') {
            $_POST['country'] = 1;
            $form->country->setValue($_POST['country']);
        }

        if ($form_number == 0 && ($status = $form->profile_status->getValue()) != $user->profile_status) {
            $user->profile_status = $status;
            $user->save();
            $isStatusModified = true;
        }

        $formInvalid = !$form->isValid($post);



        //Получаем записываемые значения
        $values = $form->getValues();

        if ($user->profile_status == 'postgraduate') {
            $values['school_class'] = 11;
        }
        if ($user->school_reference_status == 2 && !empty($values['school_reference']) && !$canFrozenEdit){
            unset($values['school_reference']);
        }else if (!empty($values['school_reference'])){
            $user->school_reference_status = 1;
        }

        if ($canFrozenEdit && !empty($values['school_reference_status']) && (!empty($values['school_reference']) ||  Engine_Api::_()->fields()->getValByName($user, 'school_reference')) ){
            $user->school_reference_status = $values['school_reference_status'];
        }
        /* Обрезаем лишние пробелы у всех значений и обрабатываем массивы (Гончаров: это КОСТЫЛЬ а не эталон кода, система заточено под фильтры/валидацию в форме и в БД fields) */
        $validateOneValue = function($key, &$value, $isRecurseDown = false) use ($form, $viewer, &$validateOneValue, $user){
            if (in_array($key, ['learninfo', 'contact_information', 'wishes'])){
                $value = (new Engine_Filter_Html(['useDefaultLists' => true]))->filter($value);
                return true;
            }

            if (is_array($value)) {
                foreach ($value as $i=>$value_lv2) {
                    if ($validateOneValue('array', $value_lv2, true)){
                        $value[$i] = $value_lv2;
                    }else{
                        unset($value[$i]);
                    }
                }
            } else {
                $value = trim(strip_tags($value));
            }

            /* Files upload */

            if (!$isRecurseDown && ($element = $form->getElement($key)) && $element instanceof Zend_Form_Element_File){
                if (isset($value) && strlen($value) > 0) {
                    $params = array(
                        'parent_id' => $viewer->getIdentity(),
                        'parent_type' => 'user',
                    );
                    $storage = Engine_Api::_()->storage();
                    $currentFileValue = Engine_Api::_()->fields()->getValByName($user, $key);
                    if ($currentFileValue && ($old = $storage->get($currentFileValue))  ){
                        $old->delete();
                    }
                    $iMain = $storage->create($form->$key->getFileName(), $params);
                    if ($iMain && $key == 'school_reference'){
                        Engine_Api::_()->fields()->setValByName($user, 'school_reference', $iMain->getIdentity());
                        if (!$user->school_reference_status){
                            $user->school_reference_status = 1;
                            $user->save();
                        }
                    }
                    @unlink($form->$key->getFileName());

                    $value = $iMain->getIdentity();

                } else {
                    return false;
                }
            }
            return true;
        };

        foreach ($values as $key => $value) {
            if ($validateOneValue($key, $value)){
                $values[$key] = $value;
            }else{
                unset($values[$key]);
            }
            if (is_array($value)){
                $values[$key] = $this->normJsonStr(json_encode($value));
            }
        }
        if ($formInvalid){
            /*Выход после валидаыцыии формы тут, так как прежде всего мы загружаем файлы(справку)!*/
            if (!$isStatusModified) {
                echo json_encode(array_merge(array('status' => 'incorrect'), $form->getMessages()));
            } else {
                echo json_encode(array('status' => 'success', 'reload' => 'yes', 'message' => ''));
            }
            return;
        }

        if ($form_number == 0) {
            $gender = $values['gender'];
            $mobilephone = $values['mobilephone'];
            $mobilephone_numbers = '';
            for ( $i = 0; $i < strlen($mobilephone); $i++ ) {
                if ( is_numeric($mobilephone[$i]) ) {
                    $mobilephone_numbers .= $mobilephone[$i];
                }
            }
            if ( in_array(substr($mobilephone_numbers, 0, 1), [7, 8]) ) {
                $mobilephone_numbers = substr($mobilephone_numbers, 1);
            }
            $homephone = $values['homephone'];
            $homephone_numbers = '';
            for ( $i = 0; $i < strlen($homephone); $i++ ) {
                if ( is_numeric($homephone[$i]) ) {
                    $homephone_numbers .= $homephone[$i];
                }
            }
            if ( in_array(substr($homephone_numbers, 0, 1), [7, 8]) ) {
                $homephone_numbers = substr($homephone_numbers, 1);
            }
            $profile_status = $values['profile_status'];
            $first_name = $values['first_name'];
            $middle_name = $values['middle_name'];
            $last_name = $values['last_name'];
            $birthdate = date('Y-m-d', strtotime($values['birthdate']));
            $citizenship = $values['citizenship'];
        }

        $this->db->beginTransaction();
        try {
            /*reset is_required_fields_filled cause user can change profile_status*/
            $user->is_required_fields_filled = 0;
            $user->save();

            //Коды регионов
            $regionCodesWrite = [];
            foreach ($values as $key => $value) {
                if (strpos($key, 'region')!==false){
                    $regionCodesWrite[$key] = User_Plugin_RegionCodes::getRegionData($value, $values[str_replace('region', 'country', $key)]);
                }
            }

            //Перезапись существующих значений в engine4_user_fields_values
            for ($i = 0; $i < count($current_values); $i++) {
                $existsValue = $current_values[$i];
                foreach ($values as $key => $value) {
                    if ($existsValue['name'] == $key && !in_array($key ,$frozenFields)  ) {
                        if ($existsValue['value'] !== $value && $value !== '') {
                            $this->db->update('engine4_user_fields_values', array('value' => $value), 'item_id = ' . $user_id . ' AND field_id=' . $existsValue['field_id']);
                        } else if ($existsValue['value'] !== $value && $value === '') {
                            $this->db->delete('engine4_user_fields_values', 'item_id = ' . $user_id . ' AND field_id=' . $existsValue['field_id']);
                        }

                        if ($existsValue['field_id'] == 38){
                            $user->school_class = $value;
                        }
                        unset($values[$key]);
                    }
                }
            }

            //запись новых значений в engine4_user_fields_values
            $field_map = $this->db->select()
                ->from($this->tb_prefix . 'user_fields_meta')
                ->where("{$this->tb_prefix}user_fields_meta.data_type = ?", $form_number)
                ->where("{$this->tb_prefix}user_fields_meta.type != 'heading'")
                ->query()->fetchAll();

            for ($i = 0; $i < count($field_map); $i++) {
                foreach ($values as $key => $value) {
                    if ($field_map[$i]['name'] == $key && $value !== '' && !in_array($key ,$frozenFields)) {
                        try {
                            $this->db->insert('engine4_user_fields_values', array('item_id' => $user_id, 'field_id' => $field_map[$i]['field_id'], 'value' => $value));
                        } catch (Exception $e) {
                            /*SILENCE - костыль, нет времени выяснить почему происходит дубликат ключа*/
                        }
                    }
                }
            }

            foreach($regionCodesWrite as $key => $regionData){
                    $value = $regionData ? $regionData['num'] : '';
                    $user->fields()->setValByName($key.'_code', $value);
                    if ($key == 'region'){
                        $user->region_code = $value;
                    }
            }
            //конец - engine4_user_fields_values

            //Запись специальных полей в ЗФТШ
            if ($form_number == 0){
                $specialZftshFields = ['member_code', 'learninfo', 'contact_information', 'wishes', 'cell_number'];
                foreach($specialZftshFields as $field){
                    if (isset($values[$field])){
                        $user->setZftshMemberData($field, $values[$field]);
                    }
                }
            }

            if ($form_number == 0) {
                $updateGeneralData = [
                    'displayname' => $first_name . ' ' . $last_name,
                    'gender' => $gender,
                    'first_name' => $first_name,
                    'middle_name' => $middle_name,
                    'last_name' => $last_name,
                    'mobilephone' => $mobilephone,
                    'mobilephone_numbers' => $mobilephone_numbers,
                    'homephone' => $homephone,
                    'homephone_numbers' => $homephone_numbers,
                    'birthdate' => $birthdate,
                    'citizenship' => $citizenship,
                    'profile_status' => $profile_status,
                    'modified_date' => date('Y-m-d H:i:s')
                ];

                if (in_array('first_name',$frozenFields) || in_array('last_name',$frozenFields)){
                    unset($updateGeneralData['displayname']);
                }
                $user->setFromArray(array_diff_key($updateGeneralData, array_flip($frozenFields)));
            } else {
                $user->modified_date = date('Y-m-d H:i:s');
            }
            $user->save();

            $unfilledFields = $user->getUnfilledRequiredFields();
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            echo json_encode(array('status' => 'fail', 'message' => $this->translate->_('Database transaction error')));
            throw $e;
        }

        $unfilled = array();
        $categories = array();
        foreach ($unfilledFields as $field) {
            $unfilled[] = $this->view->translate($field['label']);
            $categories[] = $field['category'];
        }

        echo json_encode(array(
            'status' => 'success',
            'requiredAlert' => $unfilledFields ? array('label' => implode(', ', $unfilled), 'categories' => array_unique($categories)) : false,
            'message' => $this->translate->_('Your changes have been saved'
            )));
    }

    /* Загрузка фото из соц. сети */
    public function vkphotoAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $user = Engine_Api::_()->user()->getViewer();

        $settings = Engine_Api::_()->getApi('settings', 'core');
        $vkAppId = $settings->getSetting('core.vk.appid');
        $vkAppKey = $settings->getSetting('core.vk.key');

        $redirectUri = $this->base_href . 'members/edit/vkphoto';
        if (!isset($_GET['code'])) {
            return $this->forward('success', 'utility', 'core', array(
                'messages' => array(Zend_Registry::get('Zend_Translate')->_('Загрузка фото отклонена приложением.')),
                'smoothboxClose' => 2000,
            ));
        }
        $accessTokenRequest = curl_init('https://oauth.vk.com/access_token?' . http_build_query(array('client_id' => $vkAppId, 'client_secret' => $vkAppKey, 'code' => $_GET['code'], 'redirect_uri' => $redirectUri)));
        curl_setopt_array($accessTokenRequest, array(CURLOPT_SSL_VERIFYPEER => false, CURLOPT_RETURNTRANSFER => 1));
        $accessTokenInfo = json_decode(curl_exec($accessTokenRequest), true);
        curl_close($accessTokenRequest);

        if (!isset($accessTokenInfo['user_id']) || !isset($accessTokenInfo['access_token'])) {
            return $this->forward('success', 'utility', 'core', array(
                'messages' => array(Zend_Registry::get('Zend_Translate')->_('Приложение не предоставило данных о пользователе.')),
                'smoothboxClose' => 2000,
            ));
        }
        $photoRequest = curl_init('https://api.vk.com/method/users.get?user_ids=' . $accessTokenInfo['user_id'] . '&fields=photo_50,photo_100,photo_200_orig,photo_400_orig,photo_max,photo_max_orig&access_token=' . $accessTokenInfo['access_token']);
        curl_setopt_array($photoRequest, array(CURLOPT_SSL_VERIFYPEER => false, CURLOPT_RETURNTRANSFER => 1,));
        $photoResponse = json_decode(curl_exec($photoRequest), true);
        curl_close($photoRequest);
        if (empty($photoResponse) || empty($photoResponse['response'])) {
            return $this->forward('success', 'utility', 'core', array(
                'messages' => array(Zend_Registry::get('Zend_Translate')->_('Приложение не предоставило фотографию пользователя.')),
                'smoothboxClose' => 2000,
            ));
        }

        $photoData = $photoResponse['response'][0];
        $photoUrl = '';

        if (isset($photoData['photo_max_orig'])) {
            $photoUrl = $photoData['photo_max_orig'];
        } else if (isset($photoData['photo_max'])) {
            $photoUrl = $photoData['photo_max'];
        } else if (isset($photoData['photo_400_orig'])) {
            $photoUrl = $photoData['photo_400_orig'];
        } else if (isset($photoData['photo_200'])) {
            $photoUrl = $photoData['photo_200'];
        } else if (isset($photoData['photo_200_orig'])) {
            $photoUrl = $photoData['photo_200_orig'];
        } else if (isset($photoData['photo_100'])) {
            $photoUrl = $photoData['photo_100'];
        }


        $photoUrl = str_replace('https', 'http', $photoUrl);

        $filePaths = explode('/', $photoUrl);
        $fileNameWithExtension = str_replace('?', '', $filePaths[count($filePaths) - 1]);

        $nameParts = explode('.', $fileNameWithExtension);
        if (count($nameParts) > 1) {
            unset($nameParts[count($nameParts) - 1]);
        }


        $fileBinary = @file_get_contents($photoUrl);
        if (!$fileBinary) {
            sleep(1);
            $fileBinary = @file_get_contents($photoUrl);
        }
        sleep(1);
        $fileInfo = @getimagesize($photoUrl);
        if (!$fileInfo) {
            sleep(1);
            $fileInfo = @getimagesize($photoUrl);
        }


        if ($fileInfo['mime'] === 'image/jpeg' || $fileInfo['mime'] === 'image/png' || $fileInfo['mime'] === 'image/gif') {
            $fileExtension = str_replace('image/', '', $fileInfo['mime']);
            $fileName = implode('', $nameParts);

            $fileName .= '.' . $fileExtension;

            $fileServerPath = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'temporary' . DIRECTORY_SEPARATOR . $fileName;
            @file_put_contents($fileServerPath, $fileBinary);

            if (!file_exists($fileServerPath)){
                sleep(1);
                $fileServerPath = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'temporary' . DIRECTORY_SEPARATOR . 'x'. $fileName;
                file_put_contents($fileServerPath, $fileBinary);
            }
            $user->setPhoto(array('tmp_name' => $fileServerPath, 'name' => $fileName), $user->user_id);
            unlink($fileServerPath);
        }

        ?>
        <script>
            if (window.opener && (window.opener.location.pathname === "/members/edit/profile/user_photo" || window.opener.location.pathname === "/members/edit/profile/user_photo/")) {
                window.opener.photoSocial(<?=json_encode(array('status' => true, 'photo_src' => $user->getPhotoUrl('thumb.profile'), 'thumb_src' => $user->getPhotoUrl('thumb.icon')));?>);
                window.close();
            } else {
                window.location = "/members/edit/profile/user_photo/";
            }
        </script>
        <?
        return;
    }


    public function photoAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $viewer = Engine_Api::_()->user()->getViewer();
        $current_user_id = $viewer->user_id; //Id редактора               
        $user_id = $this->getRequest()->getParam('user_id'); //Id редактируемого

        $user = Engine_Api::_()->getItem('user', intval($user_id));
        $_REQUEST['action'] = empty($_REQUEST['action']) ? '' : $_REQUEST['action'];
        $_REQUEST['imageurl'] = empty($_REQUEST['imageurl']) ? '' : $_REQUEST['imageurl'];
        //
        switch ($_REQUEST['action']) {
            case 'resize': { /* Обрезка аватара */
                $storage = Engine_Api::_()->storage();

                $iProfile = $storage->get($user->photo_id, 'thumb.profile');
                $iSquare = $storage->get($user->photo_id, 'thumb.icon');

                // Read into tmp file
                if (count(explode(':', $_REQUEST['coordinates'])) < 4) {
                    echo(json_encode(array('status' => false, 'thumb_src' => $user->getPhotoUrl('thumb.icon'))));
                    return;
                }
                list($x, $y, $w, $h) = explode(':', $_REQUEST['coordinates']);

                $image = Engine_Image::factory();
                $image->open(APPLICATION_PATH . '/' . $iProfile->storage_path)
                    ->resample($x + .1, $y + .1, $w - .1, $h - .1, 48, 48)
                    ->write(APPLICATION_PATH . '/' . $iSquare->storage_path);

                echo(json_encode(array('status' => true, 'thumb_src' => $user->getPhotoUrl('thumb.icon'))));
            }
                break;

            case 'remove': { /* Удаление фото */
                $user->photo_id = 0;
                $user->save();

                echo(json_encode(array('status' => true, 'message' => $this->translate->_('Your photo has been removed.'))));
            }
                break;

            default: { /* Добавление фото */
                if (trim($_REQUEST['imageurl']) !== '') { /* Добавление фото по url */
                    $fileUrl = trim($_REQUEST['imageurl']);
                    $filePaths = explode('/', $fileUrl);
                    $fileNameWithExtension = str_replace('?', '', $filePaths[count($filePaths) - 1]);

                    $nameParts = explode('.', $fileNameWithExtension);
                    if (count($nameParts) > 1) {
                        unset($nameParts[count($nameParts) - 1]);
                    }


                    $fileBinary = file_get_contents($fileUrl);
                    $fileInfo = getimagesize($fileUrl);

                    if (!($fileInfo['mime'] === 'image/jpeg' || $fileInfo['mime'] === 'image/png' || $fileInfo['mime'] === 'image/gif')) {
                        echo(json_encode(array('status' => false, 'message' => 'Не удалось сохранить фото')));
                        return false;
                    }

                    $fileExtension = str_replace('image/', '', $fileInfo['mime']);
                    $fileName = implode('', $nameParts);

                    $fileName .= '.' . $fileExtension;

                    $fileServerPath = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'temporary' . DIRECTORY_SEPARATOR . $fileName;

                    file_put_contents($fileServerPath, $fileBinary);

                    $user->setPhoto(array('tmp_name' => $fileServerPath, 'name' => $fileName), intval($user_id));
                    unlink($fileServerPath);


                    echo(json_encode(array('status' => true, 'photo_src' => $user->getPhotoUrl('thumb.profile'), 'thumb_src' => $user->getPhotoUrl('thumb.icon'))));
                } else {
                    $photoForm = new User_Form_Edit_Photo();

                    if (!$this->getRequest()->isPost()) {
                        echo json_encode(array('status' => false, 'message' => 'Empty request'));
                        return false;
                    }

                    if (!$photoForm->isValid($this->getRequest()->getPost())) {
                        echo json_encode(array_merge(array('status' => false), $photoForm->getMessages()));
                        return false;
                    }

                    if ($photoForm->Filedata->getValue() == null) {
                        echo json_encode(array('status' => false, 'message' => 'No filedata'));
                        return false;
                    }

                    $user->setPhoto($photoForm->Filedata);
                    echo(json_encode(array('status' => true, 'photo_src' => $user->getPhotoUrl('thumb.profile'), 'thumb_src' => $user->getPhotoUrl('thumb.icon'))));
                }
            }
        }
    }

    public function backAction() /* Возвращение к олимпиадам */
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $_SESSION['first_visit'] = false;
        ob_clean();
        header('Location: //' . $_SERVER['HTTP_HOST']);
        return;
    }

    public function endAction() /* Завершение заполнения профиля */
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $_SESSION['first_visit'] = false;
        ob_clean();
        if ($_SESSION['signup_referer'] != null) {
            $redirect_url = $_SESSION['signup_referer'];
            $_SESSION['signup_referer'] = null;
            if (_ENGINE_SSL) {
                $redirect_url = str_replace('http:', 'https:', $redirect_url);
            }
            header('Location: ' . $redirect_url);
        } else {
            header('Location: //' . $_SERVER['HTTP_HOST']);
        }
        return;
    }

    /* Очистка строки статуса */
    public function clearStatusAction()
    {
        $this->view->status = false;

        if ($this->getRequest()->isPost()) {
            $viewer = Engine_Api::_()->user()->getViewer();
            $viewer->status = '';
            $viewer->status_date = null;
            $viewer->save();

            $this->view->status = true;
        }
    }

}
