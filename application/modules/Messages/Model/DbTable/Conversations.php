<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Conversations.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Messages_Model_DbTable_Conversations extends Engine_Db_Table
{
  protected $_rowClass = 'Messages_Model_Conversation';

  public function getInboxPaginator(User_Model_User $user)
  {
    $paginator = new Zend_Paginator_Adapter_DbTableSelect($this->getInboxSelect($user));
    $paginator->setRowCount($this->getInboxCountSelect($user));
    return new Zend_Paginator($paginator);
  }

  public function getInboxSelect(User_Model_User $user)
  {
    $rName = Engine_Api::_()->getDbtable('recipients', 'messages')->info('name');
    $cName = $this->info('name');
    $select = $this->select()
      ->from($cName)
      ->joinRight($rName, "`{$rName}`.`conversation_id` = `{$cName}`.`conversation_id`", null)
      ->where("`{$rName}`.`user_id` = ?", $user->getIdentity())
      ->where("`{$rName}`.`inbox_deleted` = ?", 0)
      ->order(new Zend_Db_Expr('inbox_updated DESC'));
      ;

    return $select;
  }

  public function getInboxCountSelect(User_Model_User $user)
  {
    $rName = Engine_Api::_()->getDbtable('recipients', 'messages')->info('name');
    $cName = $this->info('name');
    $select = new Zend_Db_Select($this->getAdapter());
    $select
      ->from($cName, new Zend_Db_Expr('COUNT(DISTINCT '.$rName.'.conversation_id) AS zend_paginator_row_count'))
      ->joinRight($rName, "`{$rName}`.`conversation_id` = `{$cName}`.`conversation_id`", null)
      ->where("`{$rName}`.`user_id` = ?", $user->getIdentity())
      ->where("`{$rName}`.`inbox_deleted` = ?", 0)
      ->where("`{$rName}`.`deleted` IS NULL")
      ->join('engine4_messages_messages', 'engine4_messages_messages.conversation_id = '.$rName.'.conversation_id', [])
      ->where("engine4_messages_messages.deleted IS NULL")
      ;
    return $select;
  }

  public function getOutboxPaginator(User_Model_User $user)
  {
    $paginator = new Zend_Paginator_Adapter_DbTableSelect($this->getOutboxSelect($user));
    $paginator->setRowCount($this->getOutboxCountSelect($user));
    return new Zend_Paginator($paginator);
  }

  public function getOutboxSelect(User_Model_User $user)
  {
    $rName = Engine_Api::_()->getDbtable('recipients', 'messages')->info('name');
    $cName = $this->info('name');
    $select = $this->select()
      ->from($cName)
      ->joinRight($rName, "`{$rName}`.`conversation_id` = `{$cName}`.`conversation_id`", null)
      ->where("`{$rName}`.`user_id` = ?", $user->getIdentity())
      ->where("`{$rName}`.`outbox_deleted` = ?", 0)
      ->order(new Zend_Db_Expr('outbox_updated DESC'));
      ;

    return $select;
  }

  public function getOutboxCountSelect(User_Model_User $user)
  {
    $rName = Engine_Api::_()->getDbtable('recipients', 'messages')->info('name');
    $cName = $this->info('name');
    $select = new Zend_Db_Select($this->getAdapter());
    $select
      ->from($cName, new Zend_Db_Expr('COUNT(1) AS zend_paginator_row_count'))
      ->joinRight($rName, "`{$rName}`.`conversation_id` = `{$cName}`.`conversation_id`", null)
      ->where("`{$rName}`.`user_id` = ?", $user->getIdentity())
      ->where("`{$rName}`.`outbox_deleted` = ?", 0)
      ;
    return $select;
  }
    const MAX_RECIPIENTS_TO_KEEP_IN_INBOX = 3;

	public function send(Core_Model_Item_Abstract $user, $recipients, $title, $body, $attachment = null, $personally = false)
    {
        $resource = null;
		
		// Case: single user
		if ($recipients instanceof User_Model_User) {
			$recipients = array (
					$recipients->getIdentity() 
			);
		} 		// Case: group/event members
		else if ($recipients instanceof Core_Model_Item_Abstract && method_exists($recipients, 'membership')) {
			$resource = $recipients;
			$recipients = array ();
			foreach ( $resource->membership()
				->getMembers() as $member ) {
				if ($member->getIdentity() != $user->getIdentity()) {
					$recipients [] = $member->getIdentity();
				}
			}
		} 		// Case: single id
		else if (is_numeric($recipients)) {
			$recipients = array (
					$recipients 
			);
		} 		// Case: array
		else if (is_array($recipients) && ! empty($recipients)) {
			// Ok
		} 		// Whoops
		else {
			throw new Messages_Model_Exception("A message must have recipients");
		}
		
		/* @var  $conversation Messages_Model_Conversation */
		$conversation = null;
		$conversations = array();
		
		//	find single-user dialog to reply to.
		if ( (count($recipients) == 1 || $personally) && $resource === null ) {
		    foreach ( $recipients as $recipient ) {
                /* @var $select Zend_Db_Table_Select */
                $select = $this->select();
                $select->from(array('c' => 'engine4_messages_conversations'), '*');
                $select->joinInner(array('r1' => 'engine4_messages_recipients'), 'r1.conversation_id = c.conversation_id', array());
                $select->joinInner(array('r2' => 'engine4_messages_recipients'), 'r2.conversation_id = c.conversation_id', array());
                $select->where('r1.user_id = ?', $user->getIdentity());
                $select->where('r2.user_id = ?', $recipient);
                $select->where('c.recipients = 1');
                $select->order('c.conversation_id DESC')
                    ->limit(1);

                $conversation = $this->fetchRow($select);
                if ( !empty($conversation) ) {
                    $conversation->reply($user, $body, $attachment);
                    $conversations[$recipient] = $conversation;
                } else {
                    // Create conversation
                    $conversation = $this->createRow();
                    $conversation->setFromArray(array (
                        'user_id' => $user->getIdentity(),
                        'title' => $title,
                        'recipients' => 1,
                        'modified' => date('Y-m-d H:i:s'),
                        'locked' => 0,
                        'resource_type' => null,
                        'resource_id' => 0
                    ));
                    $conversation->save();

                    // Create message
                    $message = Engine_Api::_()->getItemTable('messages_message')->createRow();
                    $message->setFromArray(array(
                        'conversation_id' => $conversation->getIdentity(),
                        'user_id' => $user->getIdentity(),
                        'title' => $title,
                        'body' => $body,
                        'date' => date('Y-m-d H:i:s'),
                        'attachment_type' => ($attachment ? $attachment->getType() : ''),
                        'attachment_id' => ($attachment ? $attachment->getIdentity() : 0),
                    ));
                    $message->save();

                    Engine_Api::_()->getDbtable('recipients', 'messages')
                    ->insert(array (
                        'user_id' => $user->getIdentity(),
                        'conversation_id' => $conversation->getIdentity(),
                        'outbox_message_id' => $message->getIdentity(),
                        'outbox_updated' => date('Y-m-d H:i:s'),
                        'last_viewed' => date('Y-m-d H:i:s'),
                        'outbox_deleted' => 0,
                        'inbox_deleted' => count($recipients) <= self::MAX_RECIPIENTS_TO_KEEP_IN_INBOX ? 0 : 1,
                        'inbox_read' => 1,
                        'deleted' => null,
                        'date' => date('Y-m-d H:i:s')
                    ));

                    Engine_Api::_()->getDbtable('recipients', 'messages')
                    ->insert(array (
                        'user_id' => $recipient,
                        'conversation_id' => $conversation->getIdentity(),
                        'inbox_message_id' => $message->getIdentity(),
                        'inbox_updated' => date('Y-m-d H:i:s'),
                        'last_viewed' => null,
                        'inbox_deleted' => 0,
                        'inbox_read' => 0,
                        'outbox_message_id' => 0,
                        'outbox_deleted' => 1,
                        'deleted' => null,
                        'date' => date('Y-m-d H:i:s')
                    ));

                    $conversations[$recipient] = $conversation;
                }
            }
            return $personally ? $conversations : array_pop($conversations);
		}
		
		// Create conversation
		$conversation = $this->createRow();
		$conversation->setFromArray(array (
				'user_id' => $user->getIdentity(),
				'title' => $title,
				'recipients' => count($recipients),
				'modified' => date('Y-m-d H:i:s'),
				'locked' => ($resource ? 1 : 0),
				'resource_type' => (! $resource ? null : $resource->getType()),
				'resource_id' => (! $resource ? 0 : $resource->getIdentity()) 
		));
		$conversation->save();    
   

    	// Create message
		$message = Engine_Api::_()->getItemTable('messages_message')->createRow();
		$message->setFromArray(array(
		  'conversation_id' => $conversation->getIdentity(),
		  'user_id' => $user->getIdentity(),
		  'title' => $title,
		  'body' => $body,
		  'date' => date('Y-m-d H:i:s'),
		  'attachment_type' => ( $attachment ? $attachment->getType() : '' ),
		  'attachment_id' => ( $attachment ? $attachment->getIdentity() : 0 ),
		));
		$message->save();
			
		Engine_Api::_()->getDbtable('recipients', 'messages')
			->insert(array (
				'user_id' => $user->getIdentity(),
				'conversation_id' => $conversation->getIdentity(),
				'outbox_message_id' => $message->getIdentity(),
				'outbox_updated' => date('Y-m-d H:i:s'),
				'last_viewed' => date('Y-m-d H:i:s'),
				'outbox_deleted' => 0,
				'inbox_deleted' => 0,
				'inbox_read' => 1,
				'deleted' => null,
				'date' => date('Y-m-d H:i:s')
		));
		
		foreach ( $recipients as $recipient_id ) {
			Engine_Api::_()->getDbtable('recipients', 'messages')
				->insert(array (
					'user_id' => $recipient_id,
					'conversation_id' => $conversation->getIdentity(),
					'inbox_message_id' => $message->getIdentity(),
					'inbox_updated' => date('Y-m-d H:i:s'),
					'last_viewed' => null,
					'inbox_deleted' => 0,
					'inbox_read' => 0,
					'outbox_message_id' => 0,
					'outbox_deleted' => 1,
					'deleted' => null,
					'date' => date('Y-m-d H:i:s')
			));
		}

		
		
		return $conversation;
	}


	public function getDialogsPaginator(User_Model_User $user, array $filter = []) {
		/*@var $paginatorAdapter Zend_Paginator_Adapter_DbTableSelect */
		$paginatorAdapter = new Zend_Paginator_Adapter_DbTableSelect($this->getDialogsSelect($user, $filter));		
		return new Zend_Paginator($paginatorAdapter);
	}

	public function getDialogsSelect(User_Model_User $user, array $filter = []) {
		$rName = Engine_Api::_()->getDbtable('recipients', 'messages')->info('name');
		$mName = Engine_Api::_()->getDbtable('messages', 'messages')->info('name');
		$cName = $this->info('name');
		/*@var $select Zend_Db_Table_Select */
		$select = $this->select();
		$select
			->setIntegrityCheck()
				->from(array('c' => $cName))
				->joinRight(array('r' => $rName), "r.conversation_id = c.conversation_id", null)
				->joinInner(array('m' => $mName), "m.conversation_id = c.conversation_id", null)
				
			->where("r.user_id = ?", $user->getIdentity())
			->where("r.deleted is null")
            ->where("r.inbox_deleted = 0")
            ->where("m.deleted is null")
			->group('c.conversation_id')
			->order(new Zend_Db_Expr('max(m.date) DESC'));
		
			
		if(!empty($filter['only_unreaded'])){			
			$select->where('r.inbox_read = ?', 0);		
		}	
		
		if(!empty($filter['pupil_class']) && 
				(	$filter['pupil_class'] == 'other' ||
					is_numeric($filter['pupil_class']) )
				){
			
			$select
				->joinInner(array('r2' => $rName), "r2.conversation_id = c.conversation_id", null)
				->joinInner(array('u2' => 'engine4_users'), "u2.user_id = r2.user_id", null)
				->where('r2.user_id != ?', $user->getIdentity());
			
			if($filter['pupil_class'] == 'other'){
				$select->where('u2.school_class is null');
			} else {
				$select->where('u2.school_class = ?', $filter['pupil_class']);
			}
		}
			
		return $select;
	}
        
	public function getUserToUserConversation($one, $another)
        {
            $db = $this->getAdapter();
            $u2uConvs = $db->fetchCol(
                            $db->select()->from('engine4_messages_recipients',array('conversation_id', 'count(*) as cnt'))
                                ->where('user_id = ?', is_object($one)?$one->getIdentity():$one)
                                ->orWhere('user_id = ?', is_object($another)?$another->getIdentity():$another)
                                ->group('conversation_id')
                                ->having('cnt > 1')
                        );
            
            if (empty($u2uConvs)) return null;
            
            return $this->fetchRow($this->select()->where('conversation_id IN (?)', $u2uConvs)->where('recipients = 1'));
	}

        
	public function getRecipientClassSelectOptions(User_Model_User $user){
		$sql = 'select u.school_class, count(DISTINCT r.conversation_id)
					from engine4_messages_recipients r 
					INNER join engine4_users u on u.user_id = r.user_id
					INNER join engine4_messages_conversations c on c.conversation_id = r.conversation_id
					INNER JOIN engine4_messages_recipients r2 on r2.conversation_id = c.conversation_id
					WHERE r2.user_id = :userId and r.user_id != :userId
					GROUP BY u.school_class';		
		return $this->getAdapter()->fetchPairs($sql,['userId' => $user->getIdentity()]);
	}  
        
}