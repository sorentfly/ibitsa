<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Edit.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Admin_Manage_Edit extends Engine_Form
{
  protected $_userIdentity;
  
  public function setUserIdentity($userIdentity)
  {
    $this->_userIdentity = (int) $userIdentity;
    return $this;
  }
  
  public function init()
  {
    $this
      ->setAttrib('id', 'admin_members_edit')
      ->setTitle('Edit Member')
      ->setDescription('You can change the details of this member\'s account here.')
      ->setAction($_SERVER['REQUEST_URI']);

    // init email
    $this->addElement('Text', 'email', array(
      'label' => 'Email Address',
      'validators' => array(
        array('NotEmpty', true),
        array('EmailAddress', true),
        array('Db_NoRecordExists', true, array(
          Engine_Db_Table::getTablePrefix() . 'users', 'email', array(
            'field' => 'user_id',
            'value' => (int) $this->_userIdentity
        )))
      ),
      'filters' => array(
        'StringTrim'
      )
    ));

    // init username
    if( Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.username', 1) > 0 ) {
      $this->addElement('Text', 'username', array(
        'label' => 'Username'
      ));
    }

    // init password
    $this->addElement('Password', 'password', array(
      'label' => 'Password',
    ));
    $this->addElement('Password', 'password_conf', array(
      'label' => 'Password Again',
    ));

    // Init level
    $levelMultiOptions = array(); //0 => ' ');
    $levels = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll();
    foreach( $levels as $row ) {
      $levelMultiOptions[$row->level_id] = $row->getTitle();
    }
    $this->addElement('Select', 'level_id', array(
      'label' => 'Member Level',
      'multiOptions' => $levelMultiOptions
    ));

    // Init approved
    $this->addElement('Checkbox', 'approved', array(
      'label' => 'Approved?',
      'checkedValue' => 1,
      'uncheckedValue' => 0
    ));

    // Init verified
    $this->addElement('Checkbox', 'verified', array(
      'label' => 'Verified?',
      'checkedValue' => 1,
      'uncheckedValue' => 0
    ));

    // Init enabled
    $this->addElement('Checkbox', 'enabled', array(
      'label' => 'User is enabled',
      'checkedValue' => 1,
      'uncheckedValue' => 0
    ));

    // Buttons
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => ' or ',
      'onclick' => 'parent.Smoothbox.close();',
      'decorators' => array(
        'ViewHelper'
      )
    ));

    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
    $button_group = $this->getDisplayGroup('buttons');
    $button_group->addDecorator('DivDivDivWrapper');
  }
}