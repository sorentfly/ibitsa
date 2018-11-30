<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Delete.php 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Settings_Delete extends Engine_Form
{
    public function __construct(array $options = null)
    {
        parent::__construct($options);
    }
    /* @var User_Model_User  */
    protected $user = null;

    public function setUser(User_Model_User $user)
    {
        $this->user = $user;
    }

    public function init()
  {
    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble([]));
    if ($this->user->isSelf(Engine_Api::_()->user()->getViewer())){
        $this->setTitle('Delete Account')->setDescription('Are you sure you want to delete your account?');
    }else{
        $fio = $this->user->getFIO(true);
        $this->setTitle('Блокировка пользователя '.$this->user->getFIO(true))->setDescription('Вы действительно хотите заблокировать(удалить) учётную запись '.$fio.'? Пользователь потеряет возможность входа, но может быть разблокирован администратором.');
    }

    // Element: token
    $this->addElement('Hash', 'token');

    // Element: delete_profile
    $this->addElement('Button', 'submit_delete', array(
      'label' => 'Yes, Delete the Account',
      'type' => 'submit',
      'ignore' => true,
      //'style' => 'color:#D12F19;',
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    // Element: cancel
    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => ' or ',
      'decorators' => array(
        'ViewHelper',
      ),
    ));
    
    // DisplayGroup: buttons
    $this->addDisplayGroup(array(
      'submit_delete',
      'cancel',
    ), 'buttons_delete');
    
    return $this;
  }
}