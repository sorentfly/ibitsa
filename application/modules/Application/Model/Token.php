<?

/**
 *
 * @category Application
 * @package Application
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 *
 * @property string expire
 */
class Application_Model_Token extends Engine_Db_Table_Row
{
    public function isExpired()
    {
        return time() >= strtotime($this->expire);
    }
}
