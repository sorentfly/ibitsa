<?

/**
 *
 * @category Application
 * @package Application
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 */
class Application_Model_DbTable_Tokens extends Engine_Db_Table {
    /**
     * @var string
     */
    protected $_rowClass = 'Application_Model_Token';

    /**
     * @param $token
     * @return Core_Model_Item_Abstract|null|Zend_Db_Table_Row_Abstract
     */
    public function findToken($token){
        $token = $this->fetchRow( $this->select()->where('token = ?', $token) );
        if ($token->isExpired()){
            $token->delete();
            return null;
        }
        return $token;
    }

    /**
     *
     */
    public function gc(){
        $expiredTokens = $this->fetchAll( $this->select()->where('expire <= ?', date('Y-m-d H:i:s')) );
        foreach($expiredTokens as $expired){
            $expired->remove();
        }
    }
}