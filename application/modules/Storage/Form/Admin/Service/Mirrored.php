<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Mirrored.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Storage_Form_Admin_Service_Mirrored extends Storage_Form_Admin_Service_Generic
{
  public function init()
  {
    // Get enabled storage services
    $serviceTable = Engine_Api::_()->getDbtable('services', 'storage');
    $serviceTypesTable = Engine_Api::_()->getDbtable('serviceTypes', 'storage');
    $view = Zend_Registry::get('Zend_View');
    
    $multiOptions = array();
    foreach( $serviceTable->fetchAll(array('enabled = ?' => true)) as $service ) {
      $serviceType = $serviceTypesTable->find($service->servicetype_id)->current();
      $multiOptions[$service->service_id] = $view->translate('%1$s (ID: %2$s)',
          $serviceType->title, $service->service_id);
    }

    if( empty($multiOptions) ) {
      $this->addError('This service requires at least one (two recommended) ' .
          'other enabled services.');
      return;
    }

    // Element: adapter
    $this->addElement('MultiCheckbox', 'services', array(
      'label' => 'Services',
      'description' => 'Services to use. Selecting none will use all enabled services.',
      //'required' => true,
      //'allowEmpty' => false,
      'multiOptions' => $multiOptions,
    ));

    // Element: mirrorImmediately
    $this->addElement('Radio', 'mirrorImmediately', array(
      'label' => 'Copy Immediately?',
      'description' => 'Should files be copied to all mirrors immediately, ' .
          'or copied in the background.',
      'multiOptions' => array(
        1 => 'Immediately',
        0 => 'Background',
      ),
      'value' => 0,
    ));

    parent::init();
  }

  public function isValid($data)
  {
    $valid = parent::isValid($data);

    // Custom valid
    if( $valid ) {
      if( count($this->getElement('services')->options) < 1 ) {
        $this->addError('Requires at least one other enabled service.');
      }
    }

    return $valid;
  }
}