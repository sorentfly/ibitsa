<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Twitter.php 9382 2011-10-14 00:41:45Z john $
 * @author     John Boehr <john@socialengine.com>
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Model_DbTable_AccessTokens extends Engine_Db_Table
{
    protected $_name = 'user_access_tokens';
    protected $_rowClass = "User_Model_AccessToken";

    /**
     * @return null|User_Model_AccessToken
     */
    public function getToken(User_Model_User $user)
    {
        return $this->fetchRow( $this->select()->where('user_id = ?', $user->getIdentity())->order('expire DESC') );
    }

    /**
     * @param $access_token
     * @return User_Model_User
     */
    public function getViewerByToken($access_token)
    {
        $user = $this->fetchRow( $this->select()->where('token = ?', $access_token) );
        return Engine_Api::_()->getItem('user', $user->user_id);
    }

    /**
     * @return null|User_Model_AccessToken
     */
    public function createNew(User_Model_User $user)
    {
        if (!$user->getIdentity()) return null;

        $token = md5(time().$user->getIdentity().$user->salt);
        $this->delete(['user_id = ?' => $user->getIdentity(), 'expire <= ?' => date('Y-m-d H:i:s', time() + 10)]);//garbage collect for user
        /* @var User_Model_AccessToken $token */
        $token = $this->createRow([
            'token'     =>  $token,
            'user_id'   =>  $user->getIdentity(),
            'expire'    =>  date('Y-m-d H:i:s', strtotime('+1 day'))
        ]);
        $token->save();
        return $token;
    }

    public function gc(){
        $expiredTokens = $this->fetchAll( $this->select()->where('expire <= ?', date('Y-m-d H:i:s', time() + 10)) );
        foreach($expiredTokens as $expired){
            $expired->delete();
        }
    }
}