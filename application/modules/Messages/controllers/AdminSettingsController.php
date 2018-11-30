<?

/**
 *
 * @category Application_Core
 * @package Messages
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 */
class Messages_AdminSettingsController extends Core_Controller_Action_Admin
{
  public function levelAction()
  {
    // Get level id
    if( null !== ($id = $this->_getParam('id')) ) {
      $level = Engine_Api::_()->getItem('authorization_level', $id);
    } else {
      $level = Engine_Api::_()->getItemTable('authorization_level')->getDefaultLevel();
    }

    if( !$level instanceof Authorization_Model_Level ) {
      throw new Engine_Exception('missing level');
    }

    $level_id = $id = $level->level_id;
    
    // Make form
    $this->view->form = $form = new Messages_Form_Admin_Level();
    $form->level_id->setValue($id);

    // Process form
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {

    }
  }
}