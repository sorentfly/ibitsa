<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Transfer.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Storage_Plugin_Job_Transfer extends Core_Plugin_Job_Abstract
{
  protected function _execute()
  {
    // Prepare tables
    $servicesTable = Engine_Api::_()->getDbTable('services', 'storage');
    $filesTable = Engine_Api::_()->getDbTable('files', 'storage');


    // Prepare
    $service_id = $this->getParam('service_id');
    $position   = $this->getParam('position', 0);
    $progress   = $this->getParam('progress', 0);
    $total      = $this->getParam('total');
    $limit      = $this->getParam('limit', 5);
    $isComplete = false;
    $break      = false;
    
    $service = $servicesTable->getService($service_id);


    // Populate total
    if( null === $total ) {
      $total = $filesTable->select()
        ->from($filesTable->info('name'), new Zend_Db_Expr('COUNT(*)'))
        ->where('service_id != ?', $service_id)
        ->query()
        ->fetchColumn(0)
        ;
      $this->setParam('total', $total);
      if( !$progress ) {
        $this->setParam('progress', 0);
      }
      if( !$position ) {
        $this->setParam('position', 0);
      }
    }

    // Complete if nothing to do
    if( $total <= 0 ) {
      $this->_setWasIdle();
      $this->_setIsComplete(true);
      return;
    }


    // Execute
    $count = 0;

    while( !$break && $count <= $limit ) {

      $file = $filesTable->fetchRow($filesTable->select()
          ->where('service_id != ?', $service_id)
          ->where('file_id >= ?', (int) $position + 1)
          ->order('file_id ASC')
          ->limit(1));

      // Nothing left
      if( !$file ) {
        $break = true;
        $isComplete = true;
      }

      // Main
      else {
        $position = $file->getIdentity();
        $count++;
        $progress++;

        // Transfer
        try {
          $file->move($service);
        } catch( Exception $e ) {
          $this->_addMessage(sprintf('File %d could not be moved for reason: %s', $file->file_id, $e->getMessage()));
        }

        // Cleanup
        unset($file);
      }
      
    }
    
    // Cleanup
    $this->setParam('position', $position);
    $this->setParam('progress', $progress);
    $this->_setIsComplete($isComplete);
  }
}
