<?

/**
 *
 * @category Application_Core
 * @package Messages
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 */
class Messages_Api_Core extends Core_Api_Abstract {

	public function getUnreadMessageCount(User_Model_User $user) {
        /* @var $conversationsDb Messages_Model_DbTable_Conversations */
        $conversationsDb = Engine_Api::_()->getItemTable('messages_conversation');

        $select = $conversationsDb->getInboxCountSelect($user)
                                  ->where('inbox_read = 0');

		return $conversationsDb->getAdapter()->fetchOne($select);
	}
}
