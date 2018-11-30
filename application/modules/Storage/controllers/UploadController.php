<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: UploadController.php 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Storage_UploadController extends Core_Controller_Action_Standard
{

  public function uploadAction() 
  {
    $this->view->name = $this->_getParam('name');
    $this->view->data = $this->_getParam('data');
    $this->view->element = $this->_getParam('element');
    $this->view->value = $this->_getParam('value');
  }

  public function saveAction()
  {
    $result = array();
    $result['time'] = date('r');
    $result['addr'] = substr_replace(gethostbyaddr($_SERVER['REMOTE_ADDR']), '******', 0, 6);
    $result['agent'] = $_SERVER['HTTP_USER_AGENT'];    
    if (count($_GET)) {
      $result['get'] = $_GET;
    }
    if (count($_POST)) {
      $result['post'] = $_POST;
    }
    if (count($_FILES)) {
      $result['files'] = $_FILES;
    }
 
 
    // Validation
    
    $error = false;
    
    if (!isset($_FILES['Filedata']) || !is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
      $error = 'Invalid Upload';
    }

    if ($error) {
      
      $return = array(
		      'status' => '0',
		      'error' => $error
		      );
      
    } else {
      
      $return = array(
		      'status' => '1',
		      'name' => $_FILES['Filedata']['name'],
		      'photo_id' => $this->_getParam('photo_id')
		      );
      // Our processing, we get a hash value from the file
      $return['hash'] = md5_file($_FILES['Filedata']['tmp_name']);
  
      // ... and if available, we get image data
      $info = @getimagesize($_FILES['Filedata']['tmp_name']);
      
      if ($info) {
	$return['width'] = $info[0];
	$return['height'] = $info[1];
	$return['mime'] = $info['mime'];
      }
    }
 
// Output
 
  if (isset($_REQUEST['response']) && $_REQUEST['response'] == 'xml') {
    // header('Content-type: text/xml');
  
    // Really dirty, use DOM and CDATA section!
    echo '<response>';
    foreach ($return as $key => $value) {
      echo "<$key><![CDATA[$value]]></$key>";
    }
    echo '</response>';
  } else {
    // header('Content-type: application/json');
    
      echo json_encode($return);
    }
  die();
  }
  
}