<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Network.php 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Settings_Network extends Engine_Form
{
  public function init()
  {    
    $this
      ->setAttrib('id', 'network-form')
      ->setAttrib('method', 'POST')
      ->setAttrib('class', '')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    $this->addElement('Text', 'title', array(
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    $this->addElement('Hidden', 'leave_id', array(
      'order' => 990,
    ));
    
    $this->addElement('Hidden', 'join_id', array(
      'order' => 991,
    ));

    //$this->loadDefaultDecorators();
    //$this->removeDecorator('FormContainer');
  }
}