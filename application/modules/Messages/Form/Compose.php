<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Compose.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 *
 * @category Application_Core
 * @package Messages
 * @copyright Copyright 2006-2010 Webligo Developments
 * @license http://www.socialengine.com/license/
 */
class Messages_Form_Compose extends Engine_Form
{

    protected $_personally;

    public function __construct(array $options = null)
    {
        $this->_personally = empty($options['personally']) ? false : true;
        unset($options['personally']);
        parent::__construct($options);
    }

    public function init()
    {
        if ( $this->_personally ) {
            $this->setDescription('Create your new message with the form below. Your message will be addressed to recipients personally.');
        }
        $this->setAttrib('id', 'messages_compose');
		
		// init to
		$this->addElement('Text', 'to', array (
				'label' => 'Send To',
				'autocomplete' => 'off' 
		));
		
		Engine_Form::addDefaultDecorators($this->to);
		
		// Init to Values
		$this->addElement('Hidden', 'toValues', array (
				'label' => 'Recipients',
				'allowEmpty' => false,
				'order' => 2,
				'validators' => array (
						'NotEmpty' 
				),
				'filters' => array (
						'HtmlEntities' 
				), 
				'decorators' => array()
		));
		Engine_Form::addDefaultDecorators($this->toValues);

        $this->addElement('Dummy', 'contact_information', ['label' => 'Контактная информация', 'content' => '']);
        $this->contact_information->getDecorator('HtmlTag2')->setOption('class', 'form-wrapper hidden-imp');

		// init title
		$this->title = $this->createElement('Text', 'title', array (
				'label' => 'Subject',
				'order' => 3,				
				'filters' => array (
						new Engine_Filter_Censor(),
						new Engine_Filter_HtmlSpecialChars() 
				)
		));	
		$this->hideTitle();
				
		// init body
		$this->addElement('Textarea', 'body', array (
				'label' => 'Message',
				'order' => 4,
				'required' => true,
				'allowEmpty' => false,
				'filters' => array (
						new Engine_Filter_Censor(),
						new Engine_Filter_EnableLinks(), 
						new Engine_Filter_Html(array('useDefaultLists' => true))
				),
				'decorators' => array('ViewHelper')
		));
		
		// init submit
		$this->addElement('Button', 'submit', array (
				'label' => 'Send Message',
				'order' => 5,
				'type' => 'submit',
				'ignore' => true,
				'decorators' => array('ViewHelper'),				
		));
		
		$this->addElement('Button', 'extendedSwitcher', array (
				'label' => 'Расширенное сообщение',
				'order' => 6,
				'type' => 'button',
				'ignore' => true,
				'decorators' => array('ViewHelper'),
		));
		
	}
	
	
	var $toObject = null;
	var $isPopulated = false;

	public function prepopulate($to, $multi) {
		$viewer = Engine_Api::_()->user()
			->getViewer();
        if (! empty($to) && ! empty($multi) && ($multi == 'group' || $multi == 'school') ) {
            // Prepopulate group/event/etc
            $item = Engine_Api::_()->getItem($multi, $to);
            // Potential point of failure if primary key column is something other
            // than $multi . '_id'
            $item_id = $multi . '_id';
            if ($item instanceof Core_Model_Item_Abstract && isset($item->$item_id) && ($item->isOwner($viewer) || $item->authorization()
                        ->isAllowed($viewer, 'edit'))) {
                $this->toObject = $item;
                $this->isPopulated = true;
                $this->toValues->setValue($item->getGuid());
                $this->removeElement('title');
            }
        } else if ( !empty($to) ) {
            if (strpos($to, ',')!==false){
                $ids = array_unique( array_map('intval', explode(',', $to)) );

                /* @var User_Model_DbTable_Users $table */
                $table = Engine_Api::_()->getItemTable('user');
                $users = $table->fetchAllKeyed(['user_id IN (?)' => $ids]);
                $this->isPopulated = count($users);
                $this->toValues->setValue( implode(',', array_keys($users)) );
                $this->toObject = array_values($users);

                $this->removeElement('title');
            }else{
                $multi = null;
                // Prepopulate user
                $toUser = Engine_Api::_()->getItem('user', $to);
                $isMsgable = true ||
                    /*NOTE - правка, сообщения можно всем отправлять*/
                    ('friends' != Engine_Api::_()->authorization()
                            ->getPermission($viewer, 'messages', 'auth') || $viewer->membership()
                            ->isMember($toUser));
                if ($toUser instanceof User_Model_User && (! $viewer->isBlockedBy($toUser) && ! $toUser->isBlockedBy($viewer)) && isset($toUser->user_id) && $isMsgable) {
                    $this->toObject = $toUser;
                    $this->isPopulated = true;
                    $this->toValues->setValue($toUser->getGuid());
                    $this->removeElement('title');

                    $DS = Engine_Api::_()->core()->getNowDomainSettings();
                    if (!empty($DS['zftshDefaults']) && in_array($viewer->academyStatus(), ['methodist','admin']) ){
                        $greetingsName = $toUser->hasMethodistRights() ? $toUser->first_name . ' ' . $toUser->middle_name : $toUser->first_name;
                        $this->body->setValue('Здравствуйте, ' . $greetingsName.'!');
                    }
                }
            }
        }
	}

	public function hideTitle($hidden = true){
		if($hidden){
			$this->title->getDecorator('HtmlTag2')->setOption('class', 'form-wrapper hidden-imp');
		} else {
			$this->title->getDecorator('HtmlTag2')->setOption('class', 'form-wrapper');
		}
	}
	
	public function getToObject() {
		return $this->toObject;
	}

	public function getIsPopulated() {
		return $this->isPopulated;
	}
}