<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminLevelController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Authorization_AdminLevelController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('authorization_admin_main', array(), 'authorization_admin_main_manage');

    $this->view->formFilter = $formFilter = new Authorization_Form_Admin_Level_Filter();
    $page = $this->_getParam('page', 1);

    $table = Engine_Api::_()->getDbtable('levels', 'authorization');
    $select = $table->select();

    if( $formFilter->isValid($this->_getAllParams()) ) {
      $values = $formFilter->getValues();

      $select = $table->select()
       ->order( !empty($values['orderby']) ? $values['orderby'].' '.$values['orderby_direction'] : 'level_id DESC' );
      
      if( $values['orderby'] && $values['orderby_direction'] != 'ASC') {
        $this->view->orderby = $values['orderby'];
      }
    }

    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setCurrentPageNumber( $page );


    // Sanity check levels?
    $defaultLevelDuplicates = $table->select()
      ->from($table)
      ->where('flag = ?', 'default')
      ->query()
      ->fetchAll();

    // Check for multiple default levels?
    if( count($defaultLevelDuplicates) != 1 ) {
      // Remove where type != 'user'
      foreach( array_keys($defaultLevelDuplicates) as $key ) {
        $level = $defaultLevelDuplicates[$key];
        if( $level['type'] != 'user' ) {
          $table->update(array(
            'flag' => '',
          ), array(
            'level_id = ?' => $level['level_id'],
            'flag = ?' => 'default',
          ));
          unset($defaultLevelDuplicates[$key]);
        }
        if( count($defaultLevelDuplicates) <= 0 ) {
          $newDefaultLevelId = $table->select()
            ->from($table, 'level_id')
            ->where('type = ?', 'user')
            ->limit(1)
            ->query()
            ->fetchColumn();
        } else {
          $newDefaultLevelId = array_shift($defaultLevelDuplicates);
          $newDefaultLevelId = $newDefaultLevelId['level_id'];
        }
        if( $newDefaultLevelId ) {
          $table->update(array(
            'flag' => 'default',
          ), array(
            'level_id = ?' => $newDefaultLevelId,
          ));
        }
      }
      return $this->_helper->redirector->gotoRoute(array());
    }
  }

  public function createAction()
  {
    $this->view->form = $form = new Authorization_Form_Admin_Level_Create();

    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {

      $table = Engine_Api::_()->getDbtable('levels', 'authorization');
      $db = $table->getAdapter();
      $db->beginTransaction();

      try
      {
        $values = $form->getValues();
        
        $level = $table->createRow();
        $level->setFromArray($values);
        $level->save();

        //@todo duplicate the settings of given parent value
        // does this go into the authorization_permission table?
        // $values['parent'];
        // select permission for the parent level
        $permissionTable = Engine_Api::_()->getDbtable('permissions', 'authorization');
        $select = $permissionTable->select()->where('level_id = ?', $values['parent']);
        $parent_permissions = $table->fetchAll($select);


        // create permissions
        foreach( $parent_permissions as $parent )
        {
          $permissions = $permissionTable->createRow();
          $permissions->setFromArray($parent->toArray());
          $permissions->level_id = $level->level_id;
          $permissions->save();
        }

        // Commit
        $db->commit();

        // Redirect
        return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
        //$this->_helper->redirector->gotoRoute(array());
      }

      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }

    }
  }

  public function deleteAction()
  {
    $this->view->form = $form = new Authorization_Form_Admin_Level_Delete();
    $id = $this->_getParam('id', null);

    // check to make sure the level is not default
    $this->view->level = $level = Engine_Api::_()->getItem('authorization_level', $id);

    if($level->flag){
      return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    }

    if( $id )
    {
      $form->level_id->setValue($id);
    }

    if( $this->getRequest()->isPost() )
    {
      $table = Engine_Api::_()->getDbtable('levels', 'authorization');
      $db = $table->getAdapter();
      $db->beginTransaction();

      try
      {
        // remove all permissions associated with this levle
        $level->removeAllPermissions();

        // reallocate users to default level
        $level->reassignMembers();

        // delete level
        $level->delete();

        // commit
        $db->commit();

        return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
      }

      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }
    }
  }

  public function editAction()
  {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('authorization_admin_main', array(), 'authorization_admin_main_level');
    
    // Get level id
    if( null !== ($id = $this->_getParam('id')) ) {
      $this->view->level = $level = Engine_Api::_()->getItem('authorization_level', $id);
    } else {
      $this->view->level = $level = Engine_Api::_()->getItemTable('authorization_level')->getDefaultLevel();
      $id = $level->level_id;
    }
   
    $this->view->form = $form = new Authorization_Form_Admin_Level_Edit(array(
      'public' => ( in_array($level->type, array('public')) ),
      'moderator' => ( in_array($level->type, array('admin', 'moderator')) ),
    ));
    $permissionsTable = Engine_Api::_()->getDbtable('permissions', 'authorization');
    
    // Populate
    $form->populate($level->toArray());
    $form->populate($permissionsTable->getAllowed('user', $id, array_keys($form->getValues())));

    $messagesAuth = $permissionsTable->getAllowed('messages', $id, 'auth');
    $form->populate(array(
      'messages_auth' => $messagesAuth,
    ));

    $form->getElement('title')->setValue($level->title);


    // Check method/valid
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }


    // Process
    $values = $form->getValues();
    $level->title = $values['title'];
    $level->description = $values['description'];
    $level->save();

    // get messages
    $messageAuth = $values['messages_auth'];
    unset($values['messages_auth']);

    // set level specific settings for profile, activity and html comments
    $permissionsTable->setAllowed('user', $level->level_id, $values);

    $permissionsTable->setAllowed('messages', $level->level_id, array(
      'create' => ( $messageAuth == 'everyone' || $messageAuth == 'friends' ),
      'auth' => $messageAuth,
    ));

    // show changes saved message
    $form->addNotice('Your changes have been saved.');
  }

  public function deleteselectedAction()
  {
    // $this->view->form = $form = new Announcement_Form_Admin_Edit();
    $this->view->ids = $ids = $this->_getParam('ids', null);
    $confirm = $this->_getParam('confirm', false);
    $this->view->count = count(explode(",", $ids));

    // $announcement = Engine_Api::_()->getItem('announcement', $id);

    // Save values
    if( $this->getRequest()->isPost() && $confirm == true )
    {
      $ids_array = explode(",", $ids);

      foreach ($ids_array as $id){
        $level = Engine_Api::_()->getItem('authorization_level', $id);

        // make sure the ID is not part of the ones that cannot be deleted
        if( !$level->flag ) {
          // remove all permissions associated with this levle
          $level->removeAllPermissions();

          // reallocate users to default level
          $level->reassignMembers();

          // delete level
          $level->delete();
        }
      }

      //$announcement->delete();
      return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    }
  }

  public function setDefaultAction()
  {
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    // Get level
    if( !($id = $this->_getParam('level_id')) ||
        !($level = Engine_Api::_()->getItem('authorization_level', $id)) ) {
      return;
    }
    $this->view->level = $level;

    $table = Engine_Api::_()->getItemTable('authorization_level');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {
      // Remove default
      $table->update(array(
        'flag' => '',
      ), array(
        'flag = ?' => 'default',
      ));
      
      // set the current item to default
      $level->flag = 'default';
      $level->save();
      
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }
  }
}