<?

/**
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 *
 */
abstract class Core_Model_Item_DbTable_TreeNode extends Core_Model_Item_DbTable_Abstract {
    protected $_repostsAvalible = true;
    public function __construct($config = array()) {
        parent::__construct($config);
        $this->_repostsAvalible = ! ($this instanceof User_Model_DbTable_Users);

    }

    protected $_rowClass = 'Core_Model_Item_TreeNode';
    protected $_blockedSelectVal = false;
    protected $_blockedSelect = false;


    public function blockSelect($val = null) {
        if ($val === null)
            return $this->_blockedSelectVal;
        $this->_blockedSelect = $this->_blockedSelectVal = $val;
        return $this;
    }

    protected $_repostIgnoringVal = true;
    protected $_repostIgnoring = true;

    public function repostIgnore($val = null) {
        if ($val === null)
            return $this->_repostIgnoringVal;
        $this->_repostIgnoring = $this->_repostIgnoringVal = $val;
        return $this;
    }

    public function find() {
        $this->_blockedSelect = true;
        $this->_repostIgnoring = false;
        $result = call_user_func_array(array('parent', 'find'), func_get_args()); /* http://stackoverflow.com/questions/3095895/using-call-user-function-to-access-parent-method-in-php */
        $this->_blockedSelect = $this->_blockedSelectVal;
        $this->_repostIgnoring = $this->_repostIgnoringVal;
        return $result;
    }

    public function delete($where) {
        $this->_blockedSelect = true;
        $this->_repostIgnoring = false;
        $result = parent::delete($where);
        $this->_blockedSelect = $this->_blockedSelectVal;
        $this->_repostIgnoring = $this->_repostIgnoringVal;
        return $result;
    }

    public function update(array $data, $where) {
        $this->_blockedSelect = true;
        $this->_repostIgnoring = false;
        $result = parent::update($data, $where);
        $this->_blockedSelect = $this->_blockedSelectVal;
        $this->_repostIgnoring = $this->_repostIgnoringVal;
        return $result;
    }

    public function fetchRow($where = null, $order = null, $offset = null) {
        if (!($where instanceof Zend_Db_Table_Select)) {
            $this->_blockedSelect = true;
            $this->_repostIgnoring = false;
        }
        $result = parent::fetchRow($where, $order, $offset);
        $this->_blockedSelect = $this->_blockedSelectVal;
        $this->_repostIgnoring = $this->_repostIgnoringVal;
        return $result;
    }

    public function select($withFromPart = self::SELECT_WITHOUT_FROM_PART) {
        $select = parent::select($withFromPart);
        return $this->_extendSelect($select);
    }

    protected function _extendSelect($select)
    {
        if (!$this->_blockedSelect) {
            $select->where('is_blocked = ?', 0);
        }
        if ($this->_repostsAvalible && $this->_repostIgnoring) {
            $select->where('repost_id = ?', 0);
        }
        return $select;
    }
    /**
     * @return Zend_Db_Table_Select
     */
    public function getItemSelect($enableReposts = null, $enableBlocked = null, $dbSelect = false) {
        if ($enableBlocked !== null) {
            $this->_blockedSelect = $enableBlocked;
        }
        if ($enableReposts !== null) {
            $this->_repostIgnoring = !$enableReposts;
        }
        if ($dbSelect){
            $select = $this->getAdapter()->select()->from($this->info('name'));
            $select = $this->_extendSelect($select);
        }else{
            $select = $this->select();
        }
        $select->order(array('division_order ASC', 'creation_date DESC'));
        $this->_blockedSelect = $this->_blockedSelectVal;
        $this->_repostIgnoring = $this->_repostIgnoringVal;
        return $select;
    }

    /**
     * @return Zend_Db_Table_Select
     */
    public function getItemSelectWithAudienceRelevation($audienceKey, $audienceParams = array(), $enableReposts = null, $enableBlocked = null, $dbSelect = false) {
        $select = $this->getItemSelect($enableReposts, $enableBlocked, $dbSelect);
        if (isset(self::$nodeAudiences[$audienceKey])) {
            $select->reset(Zend_Db_Select::ORDER);
            $bit = self::$nodeAudiences[$audienceKey]['bit'];
            $select->order( array(new Zend_Db_Expr('IF(audience_bitmask | ' . $bit . ', 1 ,0 ) DESC'), 'division_order ASC', 'creation_date DESC' ) );
        }
        return $select;
    }
    /*AUDIENCE helpers*/
    public static $nodeAudiences = array(
        0 => array('name' => '- Неизвестно -', 'bit' => 0),
        'schoolboy' => array('name' => 'Schoolboy', 'plural' => 'Schoolboys', 'bit' => 2),
        'student' => array('name' => 'Student', 'plural' => 'Students', 'bit' => 1),
        'postgraduate' => array('name' => 'Аспирант', 'plural' => 'Аспиранты', 'bit' => 256),
        'school_teacher' => array('name' => 'School Teacher', 'plural' => 'School Teachers', 'bit' => 64),
        'teacher' => array('name' => 'HS Teacher', 'plural' => 'HS Teachers', 'bit' => 32),
        'representative' => array('name' => 'Representative', 'plural' => 'Representatives', 'bit' => 4),
        'parent' => array('name' => 'Parent', 'plural' => 'Parents', 'bit' => 128),
        'other' => array('name' => 'Other users', 'plural' => 'Another audience', 'bit' => 16),
    );

    static public function getAudienceList($oneTitle = true) {
        $ret = array();
        foreach (self::$nodeAudiences as $key => $value) {
            if (!$oneTitle && !$key){
                continue;
            }
            $ret[$key] = $oneTitle ? $value['name'] : $value['plural'];
        }
        return $ret;
    }

    static public function getAudienceBit($audienceKey) {

        if (array_key_exists($audienceKey, self::$nodeAudiences)) {
            return self::$nodeAudiences[$audienceKey]['bit'];
        } else {
            return 0;
        }
    }
    /*TREE NODE helpers*/
    static public function getAddableInParent($parentGuid, User_Model_User $user, $addType) {
        if (!$parentGuid || !$user || !$user->getIdentity()) {
            return false;
        }
        $parent = Engine_Api::_()->getItemByGuid($parentGuid);
        $isAllowedByUser = $parent && ( $parent->getType()=='event' && $user->level_id == 6 || Engine_Api::_()->authorization()->isAllowed($parent, $user, 'edit') );
        if (!$isAllowedByUser) {
            return false;
        }

        $treeCfg = Engine_Api::_()->core()->getTreeNodeSettings();
        if ($addType == 'folder_attachment') $addType = 'folder';
        if (!isset($treeCfg['nesting'][$parent->getType()]) || array_search($addType, $treeCfg['nesting'][$parent->getType()])===false) {
            return false;
        }
        if (isset($treeCfg['nesting_settings'][$parent->getType()][$addType]['addEnable']) && !$treeCfg['nesting_settings'][$parent->getType()][$addType]['addEnable']){
            return false;
        }
        return $parent;
    }
    /* КОСТЫЛЬ: помошник контроллера для редиректа. Всё бы неплохо, но сделан он в модели таблицы, временно, чтобы не размазывать код по Tree Node ещё и на Zend-хелперы. Перенести в Controller Helper */
    static public function redirectToParentWhenAddedItem($controller, $parent, $addedItem, $isAjax = false) {
        $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector');
        $content = Zend_Controller_Action_HelperBroker::getStaticHelper('Content');
        if (!$parent && !Engine_Api::_()->core()->hasSubject() || $controller->getParam('tab')){
            /*Если пользователь перешёл по табу - не нажимая кнопки создания Формы, добавляется параметр tab.
            * Также при создании - редиректит всегда на редактирование созданного итема. */
            $tab = $controller->getParam('tab');
            $editRouteParams = array(
                $addedItem->getType() . '_id' => $addedItem->getIdentity(),
                'action' => 'edit'
            );
            if ($tab){
                $editRouteParams['tab'] = $tab;
            }
            $URL = $controller->view->url($editRouteParams, $addedItem->getType() . '_specific', array('rewrite' => true));
            return $redirector->gotoUrl($URL);
        }
        if ($isAjax){/*редирект - при заборе виджета аяксом - при успешном сейве*/
            $URL = $controller->view->url(array($addedItem->getType() . '_id' => $addedItem->getIdentity(), 'action' => 'edit'), $addedItem->getType() . '_specific', array('rewrite' => true));
            return $redirector->gotoUrl($URL);
        }
        if ($parent) {/*редирект на родителя - при успешном добавлении, если пользователь нажал на кнопку сохранить на самой форме, а не перешёл по табу*/
            //КОСТЫЛЬ
            if ($parent->getType() == 'conference')
            {
                return $redirector->gotoUrl($parent->getHref(), array('prependBase' => false));
            }
            $redirectToItemType = $addedItem->getType() == 'folder_attachment' ? 'folder' : $addedItem->getType();

            $URL = $parent->getEditHref();
            return $redirector->gotoUrl($URL . '?tab=tabs-linking&defaultItem='.$redirectToItemType, array('prependBase' => false));
        }
        $content->setEnabled(false);
        return false;
    }

    static public function checkIsAjax($controller)
    {
        $layout = Zend_Controller_Action_HelperBroker::getStaticHelper('Layout');
        $content = Zend_Controller_Action_HelperBroker::getStaticHelper('Content');
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            $layout->disableLayout();
            $content->setEnabled(false);
            return true;
        }
        return false;
    }
    /*КОНЕЦ КОСТЫЛЯ*/

    public function filterItemsByTextQuery(Zend_Db_Table_Select $select, $query){
        $expressionStr = Engine_Api::_()->search()->buildSQLMatchQuery(['title','description'], $query);
        $relevancyPart = Engine_Api::_()->search()->buildSQLMatchQuery(['title','description'], $query, Core_Api_Search::MATCH_MODE_GROUP_BUFF_TITLE);
        $select->where($expressionStr);

        $select->reset(Zend_Db_Select::ORDER);
        $select->order(new Zend_Db_Expr($relevancyPart.' desc'));


        return $select;
    }

}
