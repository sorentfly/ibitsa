<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminServicesController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Storage_AdminServicesController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    // Initialize select
    $table = Engine_Api::_()->getDbtable('services', 'storage');
    $select = $table->select();

    // Get paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(10);
    $paginator->setCurrentPageNumber($this->_getParam('page'));

    // Get service types
    $serviceTypesTable = Engine_Api::_()->getDbtable('serviceTypes', 'storage');
    $serviceTypes = array();
    foreach( $paginator as $item ) {
      if( empty($serviceTypes[$item->servicetype_id]) ) {
        $serviceTypes[$item->servicetype_id] = $serviceTypesTable
            ->find($item->servicetype_id)->current();
      }
    }
    $this->view->serviceTypes = $serviceTypes;

    // Get number of files and size used for each service?
    $serviceFileInfo = array();
    $filesTable = Engine_Api::_()->getItemTable('storage_file');
    foreach( $paginator as $item ) {
      $serviceFileInfo[$item->service_id] = $filesTable->select()
        ->from($filesTable, array(
          new Zend_Db_Expr('COUNT(file_id) as count'),
          new Zend_Db_Expr('SUM(size) as size')))
        ->where('service_id = ?', $item->service_id)
        ->query()
        ->fetch();
    }
    $this->view->serviceFileInfo = $serviceFileInfo;

    // Get active transfers?
    $jobsTable = Engine_Api::_()->getDbtable('jobs', 'core');
    $this->view->activeJobs = $activeJobs =
        $jobsTable->getActiveJobs(array('jobtype' => 'storage_transfer'));
  }

  public function createAction()
  {
    $this->view->form = $form = new Storage_Form_Admin_Service_Create();

    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    $values = $form->getValues();

    // Process
    $serviceTable = Engine_Api::_()->getDbtable('services', 'storage');
    $serviceTable->insert(array(
      'servicetype_id' => $values['servicetype_id'],
      'enabled' => false,
      'default' => false,
    ));
    $serviceIdentity = $serviceTable->getAdapter()->lastInsertId();

    // Redirect
    return $this->_helper->redirector->gotoRoute(array('action' => 'edit',
      'service_id' => $serviceIdentity, 'justCreated' => true));
  }

  public function editAction()
  {
    // Check params
    $justCreated = $this->_getParam('justCreated');
    
    $serviceIdentity = $this->_getParam('service_id');
    if( !$serviceIdentity ) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'index',
          'service_id' => null, 'justCreated' => null));
    }

    $serviceTable = Engine_Api::_()->getDbtable('services', 'storage');
    $service = $serviceTable->find($serviceIdentity)->current();
    if( !$service ) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'index',
          'service_id' => null, 'justCreated' => null));
    }

    $serviceTypesTable = Engine_Api::_()->getDbtable('serviceTypes', 'storage');
    $serviceType = $serviceTypesTable->find($service->servicetype_id)->current();
    if( !$serviceType ) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'index',
          'service_id' => null, 'justCreated' => null));
    }

    // Get form class
    if( !empty($serviceType['form']) ) {
      $formClass = $serviceType['form'];
    } else {
      $formClass = 'Storage_Form_Admin_Service_Generic';
    }
    Engine_Loader::loadClass($formClass);

    // Make form
    $this->view->form = $form = new $formClass();
    $form->setTitle($this->view->translate('Edit Storage Service: %s (ID: %d)',
        $serviceType->title, $service->service_id));

    // Populate form
    $config = null;
    if( !empty($service->config) ) {
      $config = Zend_Json::decode($service->config);
      if( !is_array($config) ) {
        $config = null;
      } else {
        $config = $this->_flattenParams($config);
      }
    }

    $form->populate($service->toArray());
    if( !empty($config) ) {
      $form->populate($config);
    }

    // Check method
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }


    // Process
    $values = $form->getValues();
    $values = $this->_expandParams($values);

    $db = $serviceTable->getAdapter();
    $db->beginTransaction();

    try {

      $service->enabled = (bool) $values['enabled'];
      unset($values['enabled']);

      if( empty($values) ) {
        $service->config = null;
      } else {
        $service->config = Zend_Json::encode($values);
      }

      $service->save();

      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    $form->addNotice('Your changes have been saved.');
  }

  public function deleteAction()
  {
    // No service ID?
    if( null === ($service_id = $this->_getParam('service_id')) ) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    }

    // Make form
    $this->view->form = $form = new Core_Form_Confirm(array(
      'title' => 'Delete Storage Service?',
      'description' => 'This will delete the storage service.',
      'submitLabel' => 'Delete',
      'cancelHref' => $this->view->url(array('action' => 'index', 'service_id' => null)),
    ));

    // Check method
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }


    // Process
    $serviceTable = Engine_Api::_()->getDbtable('services', 'storage');

    $db = $serviceTable->getAdapter();
    $db->beginTransaction();

    try {

      $service = $serviceTable->find($service_id)->current();

      if( $service ) {
        $service->delete();
      }

      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    return $this->_helper->redirector->gotoRoute(array('action' => 'index', 'servie_id' => null));
  }

  public function setDefaultAction()
  {
    $serviceIdentity = $this->_getParam('service_id');
    if( !$serviceIdentity ) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'index',
          'service_id' => null, 'justCreated' => null));
    }

    $serviceTable = Engine_Api::_()->getDbtable('services', 'storage');
    $service = $serviceTable->find($serviceIdentity)->current();
    if( !$service ) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'index',
          'service_id' => null, 'justCreated' => null));
    }

    $serviceTypesTable = Engine_Api::_()->getDbtable('serviceTypes', 'storage');
    $serviceType = $serviceTypesTable->find($service->servicetype_id)->current();
    if( !$serviceType ) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'index',
          'service_id' => null, 'justCreated' => null));
    }

    $db = $serviceTable->getAdapter();
    $db->beginTransaction();

    try {
      // Set this as default
      $service->default = true;
      $service->save();

      // Set everything else as not default
      $serviceTable->update(array(
        'default' => false,
      ), array(
        'service_id != ?' => $service->service_id,
      ));

      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    $this->view->status = true;
    $this->view->defaultService = $service->service_id;
  }

  public function transferAction()
  {
    // No service ID?
    if( null === ($service_id = $this->_getParam('service_id')) ) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    }

    // Make form
    $this->view->form = $form = new Core_Form_Confirm(array(
      'title' => 'Transfer Files?',
      'description' => 'This will begin transferring stored files from other ' .
          'services to this storage service.',
      'submitLabel' => 'Transfer',
      'cancelHref' => $this->view->url(array('action' => 'index', 'service_id' => null)),
    ));
    
    $jobsTable = Engine_Api::_()->getDbtable('jobs', 'core');

    // Check for existing active job?
    $activeJobs = $jobsTable->getActiveJobs(array('jobtype' => 'storage_transfer'));
    if( $activeJobs->count() > 0 ) {
      return $form->addError('There is already a pending job to transfer files.');
    }

    // Check
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process
    $jobsTable->addJob('storage_transfer', array(
      'service_id' => $service_id,
    ));

    $form->addNotice('A job has been created to transfer the files.');
  }


  protected function _flattenParams($params)
  {
    foreach( array_keys($params) as $key ) {
      $value = $params[$key];
      if( is_array($value) ) {
        foreach( $value as $k => $v ) {
          $params[$key . '_' . $k] = $v;
        }
      }
    }
    return $params;
  }

  protected function _expandParams($params)
  {
    foreach( array_keys($params) as $key ) {
      $value = $params[$key];
      if( false !== strpos($key, '_') ) {
        list($p, $c) = explode('_', $key, 2);
        if( !isset($params[$p]) ) {
          $params[$p] = array();
        }
        $params[$p][$c] = $value;
        unset($params[$key]);
      }
    }
    return $params;
  }
}