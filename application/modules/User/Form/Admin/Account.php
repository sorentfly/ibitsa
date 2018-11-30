<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Account.php 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Admin_Account extends Zend_Form
{
  public function init()
  {
    $this->setMethod('POST');
    $defaultArgs = array(
      'disableLoadDefaultDecorators' => true,
      'decorators' => array(
        'ViewHelper'
      )
    );
    $identity = $this->getAttrib('user_id');
    $user_object = Engine_Api::_()->user()->getUser($identity);
    $user_id = new Zend_Form_Element_Hidden('user_id');
    $user_id->setValue($identity);

    $email = new Zend_Form_Element_Text('email', $defaultArgs);
    $email->addValidator('emailAddress', true)
        ->addValidator(new Zend_Validate_Db_NoRecordExists(
                Engine_Db_Table::getTablePrefix() . 'users',
                'email'
        ));
    $email->setValue($user_object->email);

    $password = new Zend_Form_Element_Password('password', $defaultArgs);
    $password->addValidator('stringLength', false, array(6, 32));

    $passconf = new User_Form_Element_PasswordConfirm('passconf', $defaultArgs);
    //$passconf->addDecorator('ViewHelper');

    if ($settings->getSetting('user.signup.username', 1) > 0) {
      $username = new Zend_Form_Element_Text('username', $defaultArgs);
      $username->setValue($user_object->username);
      $username->addValidator('stringLength', true, array(6, 32))
          ->addValidator(new Zend_Validate_Db_NoRecordExists(
                  Engine_Db_Table::getTablePrefix() . 'users',
                  'username'
          ));
    }


    $language = new Zend_Form_Element_Select('language', $defaultArgs);
    $language->addMultiOptions(array('English', 'post-English'));
    $language->setValue($user_object->language_id);

    $level_id = new Zend_Form_Element_Select('level_id', $defaultArgs);
    $level_id->setValue($user_object->level_id);
    $levels = Engine_Api::_()->authorization()->getLevelInfo();
    foreach( $levels as $level ) {
      $level_id->addMultiOption($level['level_id'], $level['title']);
    }
    $this->addElements(array(
      $user_id,
      $email,
      $password,
      $passconf,
      $username,
      $level_id,
      $language,
    ));
  }

  public function saveValues()
  {
    $values = $this->getValues();
    $user_model = Engine_Api::_()->user()->getUser($values['user_id']);
    $user_model->email = $values['email'];
    if( !empty($values['username']) ) {
      $user_model->username = $values['username'];
    }
    $user_model->language_id = $values['language_id'];
    $user_model->level_id = $values['level_id'];
    $user_model->saveData();
  }
}