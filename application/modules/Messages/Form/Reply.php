<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Reply.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 *
 * @category Application_Core
 * @package Messages
 * @copyright Copyright 2006-2010 Webligo Developments
 * @license http://www.socialengine.com/license/
 */
class Messages_Form_Reply extends Engine_Form {

	public function init() {
		$this->
		// ->setAttrib('class', 'global_form_box')
		// ->setDecorators(array('FormElements', 'Form'))
		setAction(Zend_Controller_Front::getInstance()->getRouter()		
			->assemble(array ()));
		$this->setDescription('Написать ответ:');
		
		// Init body
		$this->addElement('Textarea', 'body', array (
				'allowEmpty' => false,
				'required' => true,
				'filters' => array (
						new Engine_Filter_HtmlSpecialChars(),
						new Engine_Filter_Censor(),
						new Engine_Filter_Html(array('useDefaultLists' => true))
				),
				'decorators' => array (
						'ViewHelper' 
				) 
		));

        if (!empty($_POST['body']) && mb_stripos($_POST['body'], '<p>') === false ){
            $this->body->addFilter(new Engine_Filter_EnableLinks());
        }
		
		// init submit
		$this->addElement('Button', 'submit', array (
				'label' => 'Send Reply',
				'type' => 'submit',
				'ignore' => true,
				'decorators' => array (
						'ViewHelper' 
				) 
		));
		
		$this->addElement('Button', 'extendedSwitcher', array (
				'label' => 'Расширенное сообщение',
				'order' => 6,
				'type' => 'button',
				'ignore' => true,
				'decorators' => array (
						'ViewHelper' 
				) 
		));
	}
}