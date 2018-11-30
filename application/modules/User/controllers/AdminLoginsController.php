<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminLoginsController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_AdminLoginsController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    // Get navigation
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
        ->getNavigation('core_admin_banning', array(), 'user_admin_banning_logins');
    
    // Get select
    $table = Engine_Api::_()->getDbtable('logins', 'user');
    $select = $table->select()
      ->order('login_id DESC');

    // Get paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(50);
    $paginator->setCurrentPageNumber($this->_getParam('page'));

    // Preload users
    $identities = array();
    foreach( $paginator as $item ) {
      if( !empty($item->user_id) ) {
        $identities[] = $item->user_id;
      }
    }
    $identities = array_unique($identities);

    $users = array();
    if( !empty($identities) ) {
      foreach( Engine_Api::_()->getItemMulti('user', $identities) as $user ) {
        $users[$user->getIdentity()] = $user;
      }
    }
    $this->view->users = $users;
  }

  public function clearAction()
  {
    $this->view->form = $form = new Core_Form_Confirm(array(
      'description' => 'Are you sure you want to clear the login history?',
      'submitLabel' => 'Clear History',
      'cancelHref' => 'javascript:parent.Smoothbox.close()',
      'useToken' => true,
    ));
    
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Delete everything
    $table = Engine_Api::_()->getDbtable('logins', 'user');
    $table->delete('1');

    // Forward
    return $this->forward('success', 'utility', 'core', array(
      'smoothboxClose' => true,
      'parentRefresh' => true,
      'format' => 'smoothbox',
      'messages' => array('History has been cleared.'),
    ));
  }
}
