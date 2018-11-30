<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: IndexController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Storage_IndexController extends Core_Controller_Action_Standard
{
  public function serveAction()
  {
    $file_id = $this->_getParam('file');
    $file = Engine_Api::_()->getItem('storage_file', $file_id);

    if( $file && ($file instanceof Storage_Model_File) && $file->getIdentity() )
    {
      Engine_Api::_()->core()->setSubject($file);
    }
    if( !$this->_helper->requireSubject('storage_file')->isValid() ) return;

    // Set body and headers
    $mime = $file->mime_major . '/' . $file->mime_minor;
    $this->getResponse()->setHeader('Content-Type', $mime, true);
    if( 'production' === APPLICATION_ENV ) {
      $this->getResponse()->setHeader('Expires', "Sun, 1 Jan 2012 00:00:00 GMT", true);
      $this->getResponse()->setHeader('Cache-Control', "max-age=172800, public", true);
      $this->getResponse()->setHeader('Pragma', null, true);
    }
    $this->getResponse()->setBody($file->read());

    // Disable layout and viewrenderer
    $this->_helper->layout->disableLayout(true);
    $this->_helper->viewRenderer->setNoRender(true);
  }
}