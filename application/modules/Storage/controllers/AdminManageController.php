<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminManageController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Storage_AdminManageController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    // Make form
    $this->view->formFilter = $formFilter = new Storage_Form_Admin_Manage_Filter();

    // Process form
    if( $formFilter->isValid($this->_getAllParams()) ) {
      if( null === $this->_getParam('type') ) {
        $formFilter->populate(array('type' => 'none'));
      }
      $filterValues = $formFilter->getValues();
    } else {
      $filterValues = array(
        'type' => 'none',
      );
      $formFilter->populate(array('type' => 'none'));
    }
    if( empty($filterValues['order']) ) {
      $filterValues['order'] = 'file_id';
    }
    if( empty($filterValues['direction']) ) {
      $filterValues['direction'] = 'DESC';
    }
    $this->view->filterValues = $filterValues;
    $this->view->order = $filterValues['order'];
    $this->view->direction = $filterValues['direction'];

    // Initialize select
    $table = Engine_Api::_()->getDbtable('files', 'storage');
    $select = $table->select();
    $this->view->total = $total = $table->select()
      ->from($table, new Zend_Db_Expr('COUNT(*)'))
      ->query()
      ->fetchColumn();

    // Add filter values
    if( !empty($filterValues['extension']) ) {
      $select->where('extension = ?', $filterValues['extension']);
    }
    if( !empty($filterValues['mime']) ) {
      list($major, $minor) = explode('/', $filterValues['mime']);
      $select->where('mime_major = ?', $major)
        ->where('mime_minor = ?', $minor);
    }
    if( !empty($filterValues['type']) ) {
      if( $filterValues['type'] == 'none' ) {
        $select->where('(type = ? OR type IS NULL)', '');
      } else {
        $select->where('`type` = ?', $filterValues['type']);
      }
    }
    if( !empty($filterValues['order']) ) {
      if( empty($filterValues['direction']) ) {
        $filterValues['direction'] = 'ASC';
      }
      $select->order($filterValues['order'] . ' ' . $filterValues['direction']);
    }

    // Get paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(24);
    $paginator->setCurrentPageNumber($this->_getParam('page'));

    // Get users and parents
    $users = array();
    $parents = array();
    $serviceTypes = array();
    foreach( $paginator as $file ) {
      try {
        $users[$file->file_id] = Engine_Api::_()->getItem('user', $file->user_id);
      } catch( Exception $e ) {
        
      }
      try {
        $parents[$file->file_id] = Engine_Api::_()->getItem($file->parent_type, $file->parent_id);
      } catch( Exception $e ) {
        // Silence
      }
      if( empty($serviceTypes[$file->service_id]) ) {
        $serviceInfo = Engine_Api::_()->getDbtable('services', 'storage')
          ->select()
          ->where('service_id = ?', $file->service_id)
          ->query()
          ->fetch();
        $serviceTypeInfo = Engine_Api::_()->getDbtable('serviceTypes', 'storage')
          ->select()
          ->where('servicetype_id = ?', $serviceInfo['servicetype_id'])
          ->query()
          ->fetch();
        $serviceTypes[$file->service_id] = $serviceTypeInfo;
      }
    }
    $this->view->users = $users;
    $this->view->parents = $parents;
    $this->view->serviceTypes = $serviceTypes;
  }

  public function viewAction()
  {
    $this->view->file_id = $file_id = $this->_getParam('file_id');
    if( !$file_id ) {
      return;
    }

    $this->view->file = $file = Engine_Api::_()->getItem('storage_file', $file_id);
    if( !$file ) {
      return;
    }
  }
}