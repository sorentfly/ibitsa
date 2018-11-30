<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Message.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Messages_Model_Message extends Core_Model_Item_Abstract
{
  protected $_searchTriggers = false;
  
  public function getHref($params = array())
  {
    $params = array_merge(array(
      'route' => 'messages_general',
      'reset' => true,
      'action' => 'view',
      'id' => $this->conversation_id,
      'message_id' => $this->getIdentity()
    ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()
      ->assemble($params, $route, $reset);
  }

  public function getAttachment()
  {
    if( !empty($this->attachment_type) && !empty($this->attachment_id) )
    {
      return Engine_Api::_()->getItem($this->attachment_type, $this->attachment_id);
    }
  }
  
	public function hasReadedBy(Messages_Model_Recipient $recipient) {
		$lastViewed = $recipient->getLastViewedTS();
		return empty($lastViewed) || $this->user_id == $recipient->user_id || $lastViewed >= strtotime($this->date);
	}
  
	public function canBeDeletedBy(Messages_Model_Recipient $recipient) {
		$conversation = $this->getConversation();
		$lastViewed = $conversation->getRecipientsLastViewed($recipient);
		return (empty($lastViewed) || $lastViewed < strtotime($this->date)) && $this->user_id == $recipient->user_id;
	}
  
	public function getConversation(){
		$conversation = Engine_Api::_()->getItem('messages_conversation', $this->conversation_id);
		return $conversation;
	}
	
  /*
  public function buildMessage(Messages_Model_Message &$message){
  
  	// 		$user = $this->user($message->user_id);
  	// 		if($lastViewed < strtotime($message->date) && !empty($this->recipient->last_viewed) && $message->user_id != $this->recipient->user_id){
  	// 			$liClass=' class="message_view_new"';
  	// 		} else {
  	// 			$liClass='';
  	// 		}
  	$message->canDelete = true;
  	$message->hasReaded = false;
  }*/
}