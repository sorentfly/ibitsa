<?

/**
 * Модель ряда группы из вкладки виджета вкладок профиля.
 *
 *
 *
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 *
 */
class Core_Model_DbTable_Auth extends Engine_Db_Table
{
    /**
     * @param User_Model_User $user
     * @param null $type
     * @param int $expires
     * @return Zend_Db_Table_Row_Abstract
     */
    public function createKey(User_Model_User $user, $type = null, $expires = 0)
    {
        $staticSalt = Engine_Api::_()->getApi('settings', 'core')->getSetting('core_secret', php_uname());
        $key = sha1($staticSalt . $user->salt . $user->getIdentity() . uniqid('', true));

        $row = $this->createRow();
        $row->id = $key;
        $row->user_id = $user->getIdentity();
        $row->expires = (int) $expires;
        $row->type = $type;
        $row->save();

        return $row;
    }

    /**
     * @param User_Model_User $user
     * @param $key
     * @param null $type
     * @return $this
     */
    public function checkKey(User_Model_User $user, $key, $type = null)
    {
        // @todo
        return $this;
    }

    /**
     * @param User_Model_User $user
     * @param null $type
     * @param int $expires
     * @return Core_Model_Item_Abstract|null|Zend_Db_Table_Row_Abstract
     */
    public function getKey(User_Model_User $user, $type = null, $expires = 0)
    {
        $select = $this->select()
            ->where('user_id = ?', $user->getIdentity())
        ;

        if( null !== $type ) {
            $select->where('type = ?', $type);
        }

        if( !$expires ) {
            $select->where('expires = ?', 0);
        } else {
            $select->where('expires > ?', time());
        }

        $row = $this->fetchRow($select);

        if( null === $row ) {
            return $this->createKey($user, $type, $expires);
        } else {
            return $row;
        }
    }

    /**
     * @return $this
     */
    public function cleanup()
    {
        $this->delete(array(
            'expires < ?' => time(),
            'expires > ?' => 0,
        ));

        return $this;
    }
}