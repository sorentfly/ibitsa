<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Online.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Model_DbTable_Online extends Engine_Db_Table
{
    public function check(User_Model_User $user)
    {
        if ('cli' === PHP_SAPI || !$user->getIdentity() || time() - strtotime($user->online_date) < 120 /*write online time in two minutes*/) {
            return;
        }
        try{
            //Update user self online
            $user->online_date = date('Y-m-d H:i:s');
            $user->disableHooks(true);
            $user->save();
            $user->disableHooks(false);

            //Put user into online table
            $ipObj = new Engine_IP();
            $sql = 'INSERT IGNORE INTO `' . $this->info('name') . '` (`user_id`, `ip`, `active`) VALUES (?, UNHEX(?), ?)';
            $sql = $this->getAdapter()->quoteInto($sql, $user->getIdentity(), null, 1);
            $sql = $this->getAdapter()->quoteInto($sql, bin2hex($ipObj->toBinary()), null, 1);
            $sql = $this->getAdapter()->quoteInto($sql, date('Y-m-d H:i:s'), null, 1);

            $this->getAdapter()->query($sql);
        }catch (Exception $e){/*SILENCE*/}

        return $this;
    }

    public function gc()
    {
        $this->delete(array('active < ?' => new Zend_Db_Expr('DATE_SUB(NOW(),INTERVAL 20 MINUTE)')));
        return $this;
    }
}