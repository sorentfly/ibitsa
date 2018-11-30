<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Privacy.php 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Settings_Privacy extends Engine_Form
{
  public    $saveSuccessful  = FALSE;
  protected $_roles           = array('owner', 'member', 'network', 'registered', 'everyone');
  protected $_item;

  public function setItem(User_Model_User $item)
  {
    $this->_item = $item;
  }

  public function getItem()
  {
    if( null === $this->_item ) {
      throw new User_Model_Exception('No item set in ' . get_class($this));
    }

    return $this->_item;
  }
  
  public function init(){
    $user = $this->getItem();

    $this
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

// 	Init blocklist
    $this->addElement('Hidden', 'blockList', array(
      'label' => 'Blocked Members',
      'description' => 'Adding a person to your block list makes your profile (and all of your other content) unviewable to them. Any connections you have to the blocked person will be canceled. To add someone to your block list, visit that person\'s profile page.',
      'order' => -1
    ));
    
    Engine_Form::addDefaultDecorators($this->blockList);
    
    // Init search
    $this->addElement('Checkbox', 'search', array(
      'label' => 'Do not display me in searches, browsing members, or the Online Members list.',
      'checkedValue' => 0,
      'uncheckedValue' => 1,
    ));

    $this->initPermitions();
    
    // Init submit
    $this->addElement('Button', 'submit_privacy', array(
      'class' => 'save',
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
    
    // Delete profile button
    $this->addElement('Button', 'delete_profile', array(
            'class' => 'skip',
    		'label' => 'Delete profile',
            'onclick' => 'Smoothbox.open(location.protocol + "//" + location.host + jQuery(this).attr("data-href") + "/id/" + en4.core.subject.id)',
            'data-href' => '/user/settings/delete',
    		'type' => 'button',
    		'ignore' => true
    ));
    
    return $this;
  }

  
  var $userPermissions = [
  		User_Model_User::PERMISSION_VIEW                       ,
  		User_Model_User::PERMISSION_VIEW_USERNAME              ,
  		User_Model_User::PERMISSION_VIEW_OLYMPICS              ,
  		User_Model_User::PERMISSION_VIEW_HOME_ADDRESS          ,
  		User_Model_User::PERMISSION_VIEW_SECONDARY_EDUCATION   ,
  		User_Model_User::PERMISSION_VIEW_HIGHER_EDUCATION      ,
  		User_Model_User::PERMISSION_VIEW_CHILDS_INFO           ,
  		User_Model_User::PERMISSION_VIEW_WORK_INFO
  ];
  
  
  private function initPermitions(){
  	$authAllow = Engine_Api::_()->authorization()->context;
  	
  	$maxRole = end($this->_roles);
  	
  	$labels = [
  			User_Model_User::PERMISSION_VIEW                      => 'Hide my page' ,
  			User_Model_User::PERMISSION_VIEW_USERNAME             => 'Hide Name' ,
  			User_Model_User::PERMISSION_VIEW_OLYMPICS             => 'Hide the results of olympiads and events' ,
  			User_Model_User::PERMISSION_VIEW_HOME_ADDRESS         => 'Hide home address' ,
  			User_Model_User::PERMISSION_VIEW_SECONDARY_EDUCATION  => 'Hide information about secondary education' ,
  			User_Model_User::PERMISSION_VIEW_HIGHER_EDUCATION     => 'Hide information about higher education' ,
  			User_Model_User::PERMISSION_VIEW_CHILDS_INFO          => 'Hide information about children' ,
  			User_Model_User::PERMISSION_VIEW_WORK_INFO 			  => 'Hide work place'
  	];
  	
//   	$descriptions = [
//   			User_Model_User::PERMISSION_VIEW                      => 'Ваш профиль будет недоступен для всех пользователей' ,
//   			User_Model_User::PERMISSION_VIEW_USERNAME             => 'Ваше имя и фамилия будет скрыта ото всех' ,
//   			User_Model_User::PERMISSION_VIEW_OLYMPICS             => 'Результаты олимпиад, мероприятия и курсы будут скрыты ото всех' ,
//   			User_Model_User::PERMISSION_VIEW_HOME_ADDRESS         => 'Ваш домашний адрес будет скрыт ото всех' ,
//   			User_Model_User::PERMISSION_VIEW_SECONDARY_EDUCATION  => 'Информация о среднем образовании будет скрыта ото всех' ,
//   			User_Model_User::PERMISSION_VIEW_HIGHER_EDUCATION     => 'Информация о высшем образовании будет скрыта ото всех' ,
//   			User_Model_User::PERMISSION_VIEW_CHILDS_INFO          => 'Информация о детях будет скрыта ото всех' ,
//   			User_Model_User::PERMISSION_VIEW_WORK_INFO 			  => 'Информация о работе скрыта ото всех'
//   	];
  	
  	foreach ($this->userPermissions as $permissionKey){
  		$this->addElement('Checkbox', $permissionKey, array (
  				'label' => $labels[$permissionKey],
  				// 'description' => $descriptions[$permissionKey],
  				'checkedValue' => 0,
  				'uncheckedValue' => 1,
  				'value' => $authAllow->isAllowed($this->getItem(), $maxRole, $permissionKey),
  		));
  	}
  }
  
  
  public function save()
  {
    $auth = Engine_Api::_()->authorization()->context;
    $authLevels = Engine_Api::_()->authorization()->levels;
    $user = $this->getItem();

    
	foreach ($this->userPermissions as $permitionKey){
		$value = $this->getValue($permitionKey);
		if($value){
			$privacy_max_role = count($this->_roles);
		} else {
			$privacy_max_role = 0;
		}
		foreach( $this->_roles as $i => $role ){
			$auth->setAllowed($user, $role, $permitionKey, ($i <= $privacy_max_role) );
		}
	}
	

  }  
} // end public function save()