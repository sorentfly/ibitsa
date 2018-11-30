<?

/**
 *
 * * @property string user_id
 *
 *
 * @category Application_Core
 * @package Application
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 *
 */
class Application_Model_AllowAccount extends User_Model_User
{
    public function __construct(array $config = [])
    {
        $config['readOnly'] = true;
        $config['table'] = Engine_Api::_()->getItemTable('user');
        $config['is_blocked'] = 0;
        if (!isset($config['data']['user_id'])){
            $config['data']['user_id'] = -1;
        }
        if (!isset($config['data']['is_required_fields_filled'])){
            $config['data']['is_required_fields_filled'] = 1;
        }
        if (!isset($config['data']['checked'])){
            $config['data']['checked'] = 1;
        }

        parent::__construct($config);
    }

    public function getIdentity() {
        return $this->user_id;
    }
}
