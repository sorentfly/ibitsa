<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Local.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Storage_Form_Admin_Service_Local extends Storage_Form_Admin_Service_Generic
{
  public function init()
  {
    // Element: path
    $this->addElement('Text', 'path', array(
      'label' => 'Path Prefix',
      'description' => 'This is prepended to the file path on upload. Defaults ' 
          . 'to "public". Must be relative to the SocialEngine path.',
    ));
    
    // Element: baseUrl
    $this->addElement('Text', 'baseUrl', array(
      'label' => 'Base URL',
      'description' => 'This is the base URL used for generating the file ' . 
          'URLs. Should only be used for pull-type CDNs.',
      'filters' => array(
        'StringTrim',
      ),
    ));
    
    parent::init();
  }
}