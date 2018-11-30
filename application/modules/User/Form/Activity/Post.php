<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Post.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Activity_Post extends Engine_Form
{
  public function init()
  {
    $this->addElement('text', 'body', array(
      'required' => true,
      //'decorators' => array('ViewHelper'),
      'filters' => array(
        'HtmlEntities',
        new Engine_Filter_Censor(),
      )
    ));

    $this->addElement('button', 'submit', array(
      'type' => 'submit',
      'label' => 'Post',
      'ignore' => true,
      //'decorators' => array('ViewHelper')
    ));

    //if( $this->_activityObject )
    //{
      $this->addElement('hidden', 'subject_type', array(
        //'decorators' => array('ViewHelper'),
        'validators' => array(
          array(
            'Alpha',
            false
          )
        )
      ));

      $this->addElement('hidden', 'subject_id', array(
        //'decorators' => array('ViewHelper'),
        'validators' => array(
          array(
            'Int',
            false
          )
        )
      ));
    //}

    $this->addElement('hidden', 'return_url', array(
        'value' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array())
    ));

    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array('controller' => 'edit', 'action' => 'post'), 'user_extended'));
  }

  public function setActivityObject(Core_Model_Item_Abstract $object)
  {
    $this->subject_type->setValue($object->getType());
    $this->subject_id->setValue($object->getIdentity());
    return $this;
  }
}
