<?

/**
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 *
 */
class User_Model_DbTable_Users extends Core_Model_Item_DbTable_TreeNode
{
    protected $_name = 'users';

    protected $_rowClass = 'User_Model_User';

    /** Типы профиля пользователя
     * @param bool $retrieveVoid
     * @return array (key => название)
     */
    public function getProfileTypes($retrieveVoid = true)
    {
        /* @see \User_Widget_ProfileFieldsController::$status_map */
        $db = $this->getAdapter();
        $translator = Zend_Registry::get('Zend_Translate');
        $profileTypesResponse = $db->query("SHOW COLUMNS FROM engine4_users WHERE Field = 'profile_status' ")
            ->fetchAll();
        preg_match("/^enum\(\'(.*)\'\)$/", $profileTypesResponse [0] ['Type'], $profileTypesMatches);
        $profileTypes = explode("','", $profileTypesMatches [1]); /* Получаем список типов профиля (студент/школьник и т.д.) */

        $profileTypesRenaming = ['schoolboy' => 'school student', 'school_teacher' => 'school teacher'];

        $profileTypesSelect = $retrieveVoid ? ['' => $translator->translate('Who are you?')] : [];
        for ($i = 0; $i < count($profileTypes); $i++) {
            $profileType = $profileTypes [$i];
            $profileTypesSelect [$profileType] = $translator->_(ucfirst(isset($profileTypesRenaming[$profileType]) ? $profileTypesRenaming[$profileType] : $profileType));
        }
        return $profileTypesSelect;
    }

    /**
     * @param $href
     * @return Core_Model_Item_Abstract|Engine_Db_Table_Row|null
     */
    public function getUserByProfileHref($href)
    {
        $hrefParts = explode('/', trim($href));
        if (count($hrefParts) < 2) {
            return null;
        }
        $lastPart = end($hrefParts);

        if (is_numeric($lastPart)) {
            return $this->findRow((int)$lastPart);
        } else {
            return $this->fetchRow($this->select()->where('username = ?', $lastPart));
        }
    }

    /**
     * @param $query
     * @param null|Zend_Db_Select $select
     * @return null|Zend_Db_Select|Zend_Db_Table_Select
     */
    public function getSearchIdsSelect($query, $select = null)
    {
        $db = $this->getAdapter();
        if (is_numeric($query) || (intval($query) && mb_strpos($query, '-') !== false)) {
            $id = (int)$query;
            $fakeRelColumn = new Zend_Db_Expr('1 AS relevancy');
            if ($select !== null) {
                $select->where('deleted = ?', 0)->columns($fakeRelColumn);
            } else {
                $select = $db->select()->from($this->info('name'), ['user_id', $fakeRelColumn])
                    ->where('deleted = ?', 0);
            }
            if (is_numeric($query)) {
                return $select->where($this->info('name') . '.user_id = ?', $id);
            } else {
                return $select->joinLeft('engine4_zftsh_member_data', 'engine4_zftsh_member_data.user_id = ' . $this->info('name') . '.user_id', [])
                    ->where('member_code LIKE ?', $query . '%');
            }
        }

        $matchExpr = Engine_Api::_()->search()->buildSQLMatchQuery(['last_name', 'first_name', 'middle_name'], $query);

        $relevancyField = new Zend_Db_Expr('(' . $matchExpr . ') + IF(first_name LIKE ' . $db->quote(explode(' ', $query)[0] . '%') . ',1,0) AS `relevancy`');

        if (!$select) {
            $select = $db->select()->distinct()->from($this->info('name'), array('user_id', $relevancyField))->where('deleted = ?', 0);
        } else {
            $select->where('deleted = ?', 0)->columns($relevancyField);
        }
        $select->where($matchExpr . (count(explode(' ', $query)) >= 3 ? ' >= 3' : '') )
            ->order('relevancy DESC')
            ->group(array($this->info('name') . '.user_id'));
        return $select;
    }

    //метод для элемента Core_View_Helper_FormAuthUserList
    public function getFormListPresention($usersIds)
    {
        $users = $usersIds ? $this->find($usersIds) : [];
        $result = [];
        foreach ($users as $user) {
            $result[] = $user->getFormListPresention();
        }
        return $result;
    }


    /**
     * @param Zend_Db_Table_Select $select
     * @return Zend_Db_Table_Select
     */
    protected function _extendSelect($select)
    {
        if (!$this->_blockedSelect) {
            $select->where('deleted = ?', 0);
        }
        return $select;
    }


    public function getUserSearchSelect($filter, $columns = null, $order = null)
    {
        $db = $this->getAdapter();
        $hasMemberCodeColumn = false;
        if ($columns && in_array('member_code', $columns)) {
            $hasMemberCodeColumn = true;
            $columns = array_diff($columns, ['member_code']);
        }
        $select = $db->select()->from($this->info('name'), $columns)
            ->where('search = 1')
            ->where('is_required_fields_filled = 1')
            ->where('deleted = 0');

        if (!empty($filter['name']) && trim($filter['name'])) {
            $select = $this->getSearchIdsSelect($filter['name'], $select);
        }

        if (!empty($filter['email'])) {
            $select->where('email LIKE ?', '%' . $filter['email'] . '%');
        }

        if (!empty($filter['phone'])) {
            /* @var Core_Api_String $string */
            $string = Engine_Api::_()->getApi('string', 'core');
            $select->where('mobilephone_numbers LIKE ?', '%' . $string->replacePhoneToNumbers($filter['phone']) . '%');
        }

        $select->order($order ? $order : [
            'IF(photo_id, 1, 0) DESC',
            'online_date DESC'
        ]);

        if (!empty($filter['age_from'])) {
            $dateFrom = date('Y-m-d', time() - (intval($filter['age_from']) - 1) * 365.25 * 86400);
            $select->where('birthdate IS NOT NULL AND birthdate <= ?', $dateFrom);
        }
        if (!empty($filter['age_to'])) {
            $dateTo = date('Y-m-d', time() - intval($filter['age_to']) * 365.25 * 86400);
            $select->where('birthdate IS NOT NULL AND birthdate >= ?', $dateTo);
        }

        if (!empty($filter['gender'])) {
            $select->where('gender = ?', (int)$filter['gender']);
        }

        if (!empty($filter['country'])) {
            $select->from('engine4_user_fields_values_country', [])
                ->where('engine4_user_fields_values_country.item_id = ' . $this->info('name') . '.user_id')
                ->where('engine4_user_fields_values_country.country = ?', $filter['country']);
        }

        if (!empty($filter['region'])) {
            $select->from('engine4_user_fields_values_region', [])
                ->where('engine4_user_fields_values_region.item_id = ' . $this->info('name') . '.user_id')
                ->where('engine4_user_fields_values_region.region = ?', $filter['region']);
        }

        if (!empty($filter['city'])) {
            $select->from('engine4_user_fields_values_city', [])
                ->where('engine4_user_fields_values_city.item_id = ' . $this->info('name') . '.user_id')
                ->where('engine4_user_fields_values_city.city = ?', $filter['city']);
        }

        if (!empty($filter['online'])) {
            $select->where('online_date >= ?', date('Y-m-d H:i:s', time() - 900 /*15 minutes*/));
        }

        if (isset($filter['school_reference_status']) && $filter['school_reference_status'] !== '') {
            $select->where('school_reference_status = ?', $filter['school_reference_status']);
        }

        $DS = Engine_Api::_()->core()->getNowDomainSettings();
        if (!empty($DS['academyNamespace']) && !empty($filter[$DS['academyNamespace']])) {
            $select->where($DS['academyNamespace'] . ' = ?', $filter[$DS['academyNamespace']]);
        }


        if ($hasMemberCodeColumn) {
            if (mb_strpos($select->__toString(), 'engine4_zftsh_member_data') !== false) {
                $select->columns('engine4_zftsh_member_data.member_code');
            } else {
                $select->joinLeft('engine4_zftsh_member_data', 'engine4_zftsh_member_data.user_id = ' . $this->info('name') . '.user_id', ['member_code']);
            }
        }

        return $select;
    }

    /**
     * @param $object_type
     * @return array
     */
    public function getExtraUserRightsObjectIds($object_type)
    {
        $db = Engine_Api::_()->getItemTable('user')->getAdapter();
        $select = $db->select()->from('engine4_user_extra_allow_objects', [$object_type ? 'object_id' : 'CONCAT(object_type, "_", object_id)', 'rights' => 'IF(edit, "edit", "view")'])
            ->where('edit = 1 or view = 1');
        if ($object_type){
            $select->where('object_type = ?', $object_type);
        }
        return $db->fetchPairs($select);
    }
}