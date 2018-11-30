<?
require_once('application/libraries/NameCaseLib/Library/NCL.NameCase.ru.php');

class User_Widget_ProfileFieldsController extends Engine_Content_Widget_Abstract
{
    private $db;
    private $tb_prefix;

    public static $status_map = [
        'schoolboy' => array('Школьница', 'Школьник'),
        'student' => array('Студентка', 'Студент'),
        'postgraduate' => array('Аспирантка', 'Аспирант'),
        'school_teacher' => array('Преподаватель школы', 'Преподаватель школы'),
        'teacher' => array('Преподаватель ВУЗа', 'Преподаватель ВУЗа'),
        'representative' => array('Представительница учебного заведения', 'Представитель учебного заведения'),
        'parent' => array('Родитель', 'Родитель'),
        'other' => array('Другой', 'Другой')
    ];
    public function indexAction()
    {
        $LC = Zend_Registry::get('Locale')->__toString();
        $domainSettings = Engine_Api::_()->core()->getNowDomainSettings();
        
        $this->db = Engine_Db_Table::getDefaultAdapter();
        $this->tb_prefix = Engine_Db_Table::getTablePrefix();

        /* @var User_Model_User $viewer */
        $viewer = Engine_Api::_()->user()->getViewer(); /* Тот, кто смотрит */
        /* @var User_Model_User $user */
        $user = Engine_Api::_()->core()->getSubject('user'); /* Тот, кого смотрят */
        if (!$user || !$user->getIdentity()){
            return $this->setNoRender();
        }
        $this->getElement()->removeDecorator('Title');
        
        $this->view->is_authorized = $is_authorized = $viewer->getIdentity() > 0;

        if ($is_authorized) {
            $this->view->viewer = $viewer;
            $is_admin = $viewer->level_id == 1 || $viewer->level_id == 2 || $user->authorization()->isAllowed($viewer, 'extraview');
            $is_self = $user->user_id === $viewer->user_id;
            $private_view = $is_self || $is_admin || /*$isFriend*/ $user->membership()->isMember($viewer, true); /* Возможность прносмотра «скрытых» данных */
            //дополнительный параметр для проверки, является ли просмотрщик рецензентом
            if (!empty($_GET['checkconferencereviewer']) && ($checkitem = Engine_Api::_()->getItemByGuid($_GET['checkconferencereviewer']))){
                /* @var Olympic_Model_DbTable_Conferences $conferences */
                $conferences = Engine_Api::_()->getItemTable('conference');
                if ($conferences->isRecensor($viewer, $checkitem)){
                    $private_view = true;
                }
            }
        } else {
            //Task #463 - http://nat.dc.phystech.edu:3022/redmine/issues/463
            return $this->setNoRender();
            /*
            $is_self = false;
            $is_admin = false;
            $private_view = false;*/
        }

        $this->view->is_self = $is_self;
        $this->view->is_admin = $is_admin;
        $this->view->private_view = $private_view;
        
        $this->view->defaultInfoTab = 'personal_information';
        $user_values = $user->getAllFieldsByCategories();
        /*КОСТЫЛЬ - дополнительная вкладка для ЗФТШ*/
        if (!empty($domainSettings['academyEnabled']) ){
            /** @var Zftsh_Model_DbTable_Membership $zMembership */
            /* @var Zftsh_Model_Academy $academy */
            /* @var Zftsh_Model_DbTable_Academies $academyTable */
            $academyTable = Engine_Api::_()->getItemTable('academy');
            $zMembership = Engine_Api::_()->getDbTable('membership', 'zftsh');
            $this->view->zftsh_academies = $academies = $zMembership->getUserAcademies($user);
            $this->view->isZftshPupil = $isPupil = in_array($user->academyStatus(), ['none','pupil_intramural','pupil_extramural']);
            $this->view->academyCurrentYear = $academyTable->getCurrentYear();

            if ($viewer->hasMethodistRights() && !$private_view){
                $private_view = $this->view->private_view  = Engine_Api::_()->zftsh()->isUserEditableBy($user, $viewer);
            }
            if ($is_authorized && !$isPupil ){
                $this->view->learninfo =  $user->getZftshMemberData('learninfo');
                if ($viewer->hasMethodistRights() || $viewer->isSelf($user)){
                    $this->view->wishes =  $user->getZftshMemberData('wishes');
                }
            }
            $this->view->contact_information =  $user->getZftshMemberData('contact_information');
            $this->view->cell_number = $user->getZftshMemberData('cell_number');

            if ( (count($academies) || !empty($user_values['personal_information']['zftsh_number']['value']))
                  && in_array($user->academyStatus(), ['pupil_intramural','pupil_extramural', 'teacher_new','teacher_approved'])
               ){
                $this->view->member_code = $user->getZftshMemberData('member_code');

                $user_values = array_merge(['zftsh' => [['categoryLabel' => 'Проходимые курсы']]], $user_values);
                $this->view->defaultInfoTab = 'zftsh';
                
                $maxClass = 0;
                $maxYear = 0;
                $this->view->hasFulltimeStudy = 0;
                foreach($academies as $academy){
                    if ($academy->study_form == 'fulltime'){
                        $this->view->hasFulltimeStudy = 1;
                    }
                    if ((int)$academy->school_class > $maxClass ){
                        $maxClass = (int)$academy->school_class;
                    }
                    if ((int)$academy->year > $maxYear ){
                        $maxYear = (int)$academy->year;
                    }
                }
                
                $this->view->zftsh_methodists = [];
                $this->view->zftsh_teachers = [];
                
                foreach($academies as $academy){
                    if ((int)$academy->year != $maxYear){
                        continue;
                    }
                    if($academy->methodist_id && ($methodist = Engine_Api::_()->getItem('user', $academy->methodist_id))){
                        $this->view->zftsh_methodists[$methodist->getIdentity()] = $methodist;
                    }
                    if ($teacher = $zMembership->getTeacher($academy, $user)){
                        $teacher = $zMembership->getTeacher($academy, $user);
                        $this->view->zftsh_teachers[$teacher->getIdentity()] = $teacher;
                    }
                }
                $this->view->zftsh_max_class = $maxClass;
            }
        }
        if (!empty($domainSettings['zftshDefaults'])){
            $this->view->showZftshDocumentsBlock = $showDocs = $viewer && ($user->isSelf($viewer) || $viewer->hasMethodistRights()) && $isPupil;
            if ($showDocs) $this->view->zftsh_documents = $zMembership->getTransferDocuments($user, $academyTable->getCurrentYear());

            $this->view->isPupilThisOrLastYear = $isPupil && array_filter($academies, function($academy)use($academyTable){return $academy->year >= $academyTable->getCurrentYear() - 1;});
        }
        /*КОСТЫЛЬ - END*/
        
        $user_social = $this->db->select()
            ->from($this->tb_prefix . 'users_social')
            ->where($this->tb_prefix . 'users_social.user_id = ' . $user->user_id)
            ->query()->fetchAll();


        if (!empty($user_social[0])) {
            foreach ($user_social[0] as $key => $value) {
                if ($user_social[0][$key] == null || $key === 'user_id') {
                    unset($user_social[0][$key]);
                }
            }
        }



        $university_status_map = array(
            'Applicant' => array('Абитуриентка', 'Абитуриент'),
            "Student (Bachelor's)" => array('Студентка (бакалавр)', 'Студент (бакалавр)'),
            'Student (Specialist)' => array('Студентка (специалист)', 'Студент (специалист)'),
            "Student (Master's)" => array('Студентка (магистр)', 'Студент (магистр)'),
            'Alumnus (Specialist)' => array('Выпускница (специалист)', 'Выпускник (специалист)'),
            "Alumnus (Bachelor's)" => array('Выпускница (бакалавр)', 'Выпускник (бакалавр)'),
            "Alumnus (Master's)" => array('Выпускница (магистр)', 'Выпускник (магистр)'),
            'Postgraduate student' => array('Аспирантка', 'Аспирант'),
            'Candidate of Sciences' => array('Кандидат наук', 'Кандидат наук'),
            'PhD' => array('Доктор наук', 'Доктор наук')
        );
        $study_mode_map = array('Full-time' => 'Дневная', 'Part-time' => 'Вечерняя', 'Distance learning' => 'Заочная');
        if (!$user->profile_status){
            $user->profile_status = 'other';
            $user->save();
        }
        if($user->profile_status && $user->gender === 1) {
            $this->view->profile_status = self::$status_map[$user->profile_status][0];
        } else {
            $this->view->profile_status = self::$status_map[$user->profile_status][1];
        }

        if($private_view === false && $user->birthdate != null) {
            $this->view->user_age = $this->yearsDifference($user->birthdate);
        }
        
        /*КОСТЫЛЬ, ГОВНОКОД */
        if ($LC != 'en' && isset($user_values['higher_education'])){
            $UHE = $user_values['higher_education'];
            if (isset($UHE['university_mode_study'])){
                $key = $UHE['university_mode_study']['value'];
                $UHE['university_mode_study'] = isset($study_mode_map[$key]) ? $study_mode_map[$key] : $key;
            }
            if (isset($UHE['university_current_status']) ){
                $key = $UHE['university_current_status']['value'];
                $UHE['university_current_status'] = isset($university_status_map[$key]) ? ($user->gender == 2 ? $university_status_map[$key][1] : $university_status_map[$key][0]) : $key;
            }
        }
        
        $location_address = '';
        if (!empty($user_values['home_address'])){
            foreach($user_values['home_address'] as $field){
                if ($is_authorized && ($user->user_id !== $viewer->user_id && $viewer->level_id > 3)) {
                    if ($location_address === '' && $field['field_id'] < 22) {
                        $location_address .= $field['value'];
                    } else if ($location_address !== '' && $field['field_id'] < 22) {
                        $location_address .= ', ' . $field['value'];
                    } else if ($location_address !== '' && $field['field_id'] === 22) {
                        $location_address .= 'к.' . $field['value'];
                    }
                } else {
                    if ($location_address === '' && $field['field_id'] < 22) {
                        $location_address .= $field['value'];
                    } else if ($location_address !== '' && $field['field_id'] < 22) {
                        $location_address .= ', ' . $field['value'];
                    } else if ($location_address !== '' && $field['field_id'] === 22) {
                        $location_address .= 'к.' . $field['value'];
                    }
                }
            }
        }
        /*END: КОСТЫЛЬ */

        $this->unsetHiddenUserValues($user, $user_values);
        
        $this->view->user_values = $user_values;
        $this->view->personal_information = isset($user_values['personal_information']) ? $user_values['personal_information'] : array();
        $this->view->location_address = $location_address;

        if (Zend_Registry::get('Locale')->__toString() === 'en') {
            $this->view->maps_url = '//maps.yandex.com';
        } else {
            $this->view->maps_url = '//maps.yandex.ru';
        }

        $name_case_lib = new NCLNameCaseRu();

        switch ($user->gender) {
            case 1: $gender = NCL::$WOMAN;
            break;

            case 2: $gender = NCL::$MAN;
            break;

            default: $gender = null;
        }

        if (!$is_authorized) {
            $this->view->first_name_dative = $name_case_lib->q($user->first_name, NCL::$DATIVE, $gender);
        } elseif ($user !== $viewer->user_id && $is_admin) {
            $this->view->first_name_accusative = $name_case_lib->q($user->first_name, NCL::$ACCUSATIVE, $gender);
            $this->view->first_name_instrumental = $name_case_lib->q($user->first_name, NCL::$INSTRUMENTAL, $gender);
        }

        $this->view->buidInAvatarAndOptions = isset($domainSettings['pages']['user_profile_index']) || Zend_Registry::isRegistered('overloadProfilePageToZftsh');
        
        $this->view->cadastreWidget = new Engine_Content_Element_Widget(array(
            'identity' => 'user.list-cadastre',
            'type' => 'widget',
            'name' => 'user.list-cadastre',
            'order' => 0,
            'request' => new Zend_Controller_Request_Simple(),
            'action' => 'index'
        ));
        

        $this->view->user = $user;
    }

    private function yearsDifference($endDate)
    {
        $startDateTime = new DateTime();
        $endDateTime = new DateTime($endDate);
        $interval = $startDateTime->diff($endDateTime);
        return $interval->y;
    }
    
    private function unsetHiddenUserValues(User_Model_User $user, &$userValues){
    	$categoryToPermitionMap = [
	    	'childs_info' => User_Model_User::PERMISSION_VIEW_CHILDS_INFO,
	    	'higher_education' => User_Model_User::PERMISSION_VIEW_HIGHER_EDUCATION,
	    	'home_address' => User_Model_User::PERMISSION_VIEW_HOME_ADDRESS,
	    	'personal_information' => User_Model_User::PERMISSION_VIEW_USERNAME,
	    	'secondary_education' => User_Model_User::PERMISSION_VIEW_SECONDARY_EDUCATION,
	    	// 'students_info' => User_Model_User::  ,
	    	'work_info' => User_Model_User::PERMISSION_VIEW_WORK_INFO,
    	];
    	$viewer = Engine_Api::_()->user()->getViewer();
    	
    	foreach ($categoryToPermitionMap as $categoryKey => $permitionKey){
    		if(array_key_exists($categoryKey, $userValues) && !$user->authorization()->isAllowed($viewer, $permitionKey)){
    			unset($userValues[$categoryKey]);
    		}
    	}
    	
    }
}
