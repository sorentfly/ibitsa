<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Conversation.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Messages_Model_Conversation extends Core_Model_Item_Abstract
{
  protected $_searchTriggers = false;

  public function getHref($params = array())
  {
    $params = array_merge(array(
      'route' => 'messages_general',
      'reset' => true,
      'action' => 'view',
      'id' => $this->getIdentity(),
    ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()
      ->assemble($params, $route, $reset);
  }

  public function getDescription()
  {
    // Get body of last message
    $messagesTable = Engine_Api::_()->getDbtable('messages', 'messages');
    $messagesSelect = $messagesTable->select()
      ->where('conversation_id = ?', $this->conversation_id)
      ->order('message_id DESC')
      ->limit(1)
      ;

    $message = $messagesTable->fetchRow($messagesSelect);
    if( null !== $message ) {
      return $message->body;
      // @todo generally should not use nl2br in non-view code
      //return nl2br(html_entity_decode($message->body));
    }

   return '';
  }

  public function hasResource()
  {
    return ( !empty($this->resource_type) && !empty($this->resource_id) );
  }

  public function getResource()
  {
    if( $this->hasResource() ) {
      return Engine_Api::_()->getItem($this->resource_type, $this->resource_id);
    }
  }
  
  public function hasRecipient(User_Model_User $user, $active = true)
  {
    $table = Engine_Api::_()->getDbtable('recipients', 'messages');
    $select = $table->select()
      ->where('user_id = ?', $user->getIdentity())
      ->where('conversation_id = ?', $this->getIdentity())
      ->limit(1);
    if($active){
    	$select->where('deleted is null');
    }
    $row = $table->fetchRow($select);
    return ( null !== $row );
  }
  

  public function getRecipients()
  {
    if( empty($this->store()->recipients) )
    {
      $ids = array();
      foreach( $this->getRecipientsInfo() as $row )
      {
        $ids[] = $row->user_id;
      }
      $this->store()->recipients = Engine_Api::_()->getItemMulti('user', $ids);
    }

    return $this->store()->recipients;
  }

  public function getRecipientInfo(User_Model_User $user){
    return $this->getRecipientsInfo()->getRowMatching('user_id', $user->getIdentity());
  }

  public function getRecipient($userId){
  	return $this->getRecipientsInfo()->getRowMatching('user_id', $userId);
  }

    public function getRecipientExcept(User_Model_User $user){
        $allRecipients = $this->getRecipientsInfo()->rowArray();
        usort($allRecipients, function($b, $a) use($user){
            if ($a->user_id == $user->getIdentity()) return -1;
            if ($b->user_id == $user->getIdentity()) return 1;
            return $a->inbox_read > $b->inbox_read ? 1 : ($a->inbox_read < $b->inbox_read ? -1 : 0);
        });
        return $allRecipients ? array_shift($allRecipients) : null;
    }
  
  /**
   * @return Engine_Db_Table_Rowset 
   */
  public function getRecipientsInfo()
  {
    if( empty($this->store()->recipientsInfo) )
    {
      $table = Engine_Api::_()->getDbtable('recipients', 'messages');
      $select = $table->select()
        ->where('conversation_id = ?', $this->getIdentity())
        ->order('if(user_id = '.$this->user_id.', 1, 0) desc')
        ->order('if(deleted is null, 0, 1) asc')
      	->order('user_id asc');
      $this->store()->recipientsInfo = $table->fetchAll($select);
    }

    return $this->store()->recipientsInfo;
  }

  public function reply(User_Model_User $user, $body, $attachment)
  {
    $notInConvo = true;
    $recipients = $this->getRecipients();
    $recipientsInfo = $this->getRecipientsInfo();
    foreach( $recipients as $recipient )
    {
      if( $recipient->isSelf($user) )
      {
        $notInConvo = false;
      }
    }

    if( $notInConvo )
    {
      throw new Messages_Model_Exception('Specified user not in convo');
    }

    $messagesTable = Engine_Api::_()->getItemTable('messages_message');

    // Update this
    $this->modified = date('Y-m-d H:i:s');
    $this->save();

    // Insert message
    $message = $messagesTable->createRow();
    $message->setFromArray(array(
      'conversation_id' => $this->getIdentity(),
      'user_id' => $user->getIdentity(),
      'title' => '',
      'body' => $body,
      'date' => date('Y-m-d H:i:s'),
      'attachment_type' => ( $attachment ? $attachment->getType() : '' ),
      'attachment_id' => ( $attachment ? $attachment->getIdentity() : 0 ),
    ));
    $message->save();

    // Update sender's outbox
    Engine_Api::_()->getDbtable('recipients', 'messages')->update(array(
      'outbox_message_id' => $message->getIdentity(),
      'outbox_updated' => date('Y-m-d H:i:s'),
      'outbox_deleted' => 0
    ), array(
      'user_id = ?' => $user->getIdentity(),
      'conversation_id = ?' => $this->getIdentity(),
    ));

    // Update recipients' inbox
    Engine_Api::_()->getDbtable('recipients', 'messages')->update(array(
      'inbox_message_id' => $message->getIdentity(),
      'inbox_updated' => date('Y-m-d H:i:s'),
      'inbox_deleted' => 0,
      'inbox_read' => 0
    ), array(
      'user_id != ?' => $user->getIdentity(),
      'conversation_id = ?' => $this->getIdentity(),
    ));

    unset($this->store()->messages);

    return $message;
  }

  public function setAsRead(User_Model_User $user)
  {
    Engine_Api::_()->getDbtable('recipients', 'messages')->update(array(
      'inbox_read' => 1
    ), array(
      'user_id = ?' => $user->getIdentity(),
      'conversation_id = ?' => $this->getIdentity()
    ));

    return $this;
  }

  /**
   * 
   * @param User_Model_User $user
   * @throws Messages_Model_Exception
   * @return Zend_Db_Table_Rowset;
   */
	public function getMessages(User_Model_User $user, $startFrom = null, $count = 20) {
		if (empty($this->store()->messages)) {
			if (! $this->hasRecipient($user)) {
				throw new Messages_Model_Exception('Specified user not in convo');
			}
			
			$table = Engine_Api::_()->getItemTable('messages_message');
			$db = Engine_Db_Table::getDefaultAdapter();
			/* @var $select Zend_Db_Table_Select */
			$select = $table->select()
				->where('conversation_id = ?', $this->getIdentity())
				->where('deleted is null')
				->order('message_id desc');
			if(!empty($count)){
				$select->limit($count);
			}
 			if(!empty($startFrom)){
 				$select->where('message_id < ?', $startFrom);
 			}
 			
 			$selectMinId = $db->select()->from($select, 'min(message_id)');
 			$minId = $db->fetchOne($selectMinId);
 			if (is_numeric($minId)){
 				$select->where('message_id >= ?', $minId);
 			}
 						
 			$select->reset(Zend_Db_Select::ORDER)->order('message_id asc');
 			
			/*@var $messages Engine_Db_Table_Rowset */
			$messages = $table->fetchAll($select);
	
			$this->store()->messages = $messages;
		}
		
		return $this->store()->messages;
	}

	public function getMessageLeftCount($startFrom){
		if (empty($this->store()->messageLeftCount)) {
		
			$table = Engine_Api::_()->getItemTable('messages_message');
			$db = Engine_Db_Table::getDefaultAdapter();
			
			$select = $db->select()
				->from($table->info('name'), 'count(*)')
				->where('conversation_id = ?', $this->getIdentity())
				->where('deleted is null');
			if(!empty($count)){
				$select->limit($count);
			}
 			if(!empty($startFrom)){
 				$select->where('message_id < ?', $startFrom);
 			}
			
			$this->store()->messageLeftCount = $db->fetchOne($select);
		}
			
		return $this->store()->messageLeftCount;
	}
	
	
  public function getInboxMessage(User_Model_User $user)
  {
    $recipient = $this->getRecipientInfo($user);
    if( empty($recipient->inbox_message_id) || $recipient->inbox_message_id == 'NULL' )
    {
      return null;
    }
    
    return Engine_Api::_()->getItem('messages_message', $recipient->inbox_message_id);
  }

  public function getOutboxMessage(User_Model_User $user)
  {
    $recipient = $this->getRecipientInfo($user);
    if( empty($recipient->outbox_message_id) || $recipient->outbox_message_id == 'NULL' )
    {
      return null;
    }
    
    return Engine_Api::_()->getItem('messages_message', $recipient->outbox_message_id);
  }
  /* @return Messages_Model_Message */
  public function getLastMessage(User_Model_User $user) {
		if (empty($this->store()->lastMessage)) {
			if (! $this->hasRecipient($user)) {
				throw new Messages_Model_Exception('Specified user not in convo');
			}
			
			$table = Engine_Api::_()->getItemTable('messages_message');
			$select = $table->select()
                                ->setIntegrityCheck(false)
                                ->from('engine4_messages_recipients',  null)
                                ->where('engine4_messages_recipients.conversation_id = engine4_messages_messages.conversation_id')
				->where('engine4_messages_recipients.user_id = ?', $user->getIdentity())
                                ->where('engine4_messages_recipients.deleted is null')
                                ->where('engine4_messages_messages.deleted is null')
                                ->where('engine4_messages_messages.conversation_id = ?', $this->getIdentity())
				->order('message_id DESC')
                                ->limit(1);
			$this->store()->lastMessage = $table->fetchRow($select);
		}
		
		return $this->store()->lastMessage;
	}
	
	public function getRecipientsLastViewed(Messages_Model_Recipient $exclude = null){
		if (!isset($this->store()->recipientsLastViewed)) {
			$recipients = $this->getRecipientsInfo();
			/* @var $recipient Messages_Model_Recipient */
			$recipientsLastViewed = 0;
			foreach ($recipients as $recipient){
				if($exclude !== null && $recipient->user_id == $exclude->user_id){
					continue;
				}
				$lastViewed = $recipient->getLastViewedTS();
				if(!empty($lastViewed)){
					$recipientsLastViewed = max($lastViewed, $recipientsLastViewed);
				}
			}
			$this->store()->recipientsLastViewed = $recipientsLastViewed;
		} else {
			$recipientsLastViewed = $this->store()->recipientsLastViewed;
		}
		if ($recipientsLastViewed === 0) return null;
		else return $recipientsLastViewed;
	}
	
	
	public function addRecipient($userId){		
		/* @var $recipientDb Messages_Model_DbTable_Recipients */
		$recipientDb =  Engine_Api::_()->getDbtable('recipients', 'messages');
		$recipient = $recipientDb->fetchRow('conversation_id = '.$this->getIdentity().' and user_id = '.$userId);
		if(empty($recipient)){
			$insert = array(
					'user_id' => $userId,
					'conversation_id' => $this->getIdentity(),
					'inbox_message_id' => null,
					'inbox_updated' => date('Y-m-d H:i:s'),
					'last_viewed' => null,
					'inbox_deleted' => 0,
					'inbox_read' => 0,
					'outbox_message_id' => 0,
					'outbox_deleted' => 0,
					'deleted' => null,
					'date' => date('Y-m-d H:i:s'),
					
			);
			$recipientDb->insert($insert);
		} else {
			$update = array(
				'deleted' =>  null,
			);
			$recipientDb->update($update, 'conversation_id = '.$this->getIdentity().' and user_id = '.$userId);
		}
		$this->adjustRecipientCount();
	}
	
	
	public function removeRecipient($userId){
		/* @var $recipientDb Messages_Model_DbTable_Recipients */		
		$recipientDb =  Engine_Api::_()->getDbtable('recipients', 'messages');
		$update = array(
			'deleted' =>  date('Y-m-d H:i:s'),
		);
		$recipientDb->update($update, 'conversation_id = '.$this->getIdentity().' and user_id = '.$userId);
		$this->adjustRecipientCount();
	}
	
	private function adjustRecipientCount(){
		$tb_prefix = Engine_Db_Table::getTablePrefix();
		$db = Engine_Db_Table::getDefaultAdapter();
		$cId = $this->getIdentity();
		$sql = 'update '.$tb_prefix.'messages_conversations set recipients = (
				select count(*) from '.$tb_prefix.'messages_recipients r
				where r.conversation_id = '.$cId.' and r.deleted is null
				)
				where '.$tb_prefix.'messages_conversations.conversation_id = '.$cId;
		try{
			$db->query($sql);
		} catch (Exception $e){}		
	}
	
}