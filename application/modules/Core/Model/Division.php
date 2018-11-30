<?

/**
 * Модель ряда группы из вкладки виджета вкладок профиля.
 *
 * * @property string title
 * * @property string tab_id
 * * @property string subject_type
 * * @property string subject_id
 *
 * * @property string widget_params_json
 * * @property string total_item_count
 * * @property string contain_type
 *
 *
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 *
 */
class Core_Model_Division extends Core_Model_Item_Abstract
{
    protected $_searchTriggers = false;
    public static $NO_PAGINATOR_COUNT_CACHE = false;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param array $params
     * @return null|string
     */
    public function getHref($params = array())
    {
        $tab = $this->getTab();
        if (!$this->getTab()){
            return null;
        }
        return rtrim($this->getTab()->getHref(), '/').'/'.$this->getIdentity();
    }

    /**
     * @return Core_Model_Tab
     */
    public function getTab()
    {
        return Engine_Api::_()->getItem('tab', $this->tab_id);
    }

    /**
     * @param null $recurseType
     * @return Core_Model_Item_Abstract
     */
    public function getParent($recurseType = NULL)
    {
        return $this->getTab();
    }

    /**
     * @return Core_Model_Item_Abstract|null
     */
    public function getSubject(){
        return !$this->subject_type ? null : Engine_Api::_()->getItem($this->subject_type, $this->subject_id);
    }

    /**
     * @param Core_Model_Item_TreeNode $item
     * @param int $order
     * @param Core_Model_Item_Abstract $forceParent
     * @return Core_Model_Item_TreeNode
     */
    public function addItem(Core_Model_Item_TreeNode $item, $order = 0, $forceParent = null)
    {
        $item->division_id = $this->getIdentity();
        if ($order){
            $item->division_order = $order;
        }else{
            $item->division_order = $this->getMaxItemOrder() + 1;
        }
        if ($forceParent){
            $item->parent_type = $forceParent->getType();
            $item->parent_id   = $forceParent->getIdentity();
        }else{
            $tab = $this->getTab();
            if ($tab){
                $item->parent_type = $tab->subject_type;
                $item->parent_id   = $tab->subject_id;
            }
        }
        $item->save();
        return $item;
    }

    /**
     * @param Core_Model_Item_TreeNode $item
     * @param int $order
     * @param null $forceParent
     * @return Zend_Db_Table_Row_Abstract
     */
    public function addRepost(Core_Model_Item_TreeNode $item, $order = 0, $forceParent = null)
    {
        $iTable = $item->getTable();
        /* clone item fields to array*/
        $itemFields = $item->toArray();
        $primaryKeys = $iTable->info(Zend_Db_Table_Abstract::PRIMARY);
        unset($itemFields[array_pop($primaryKeys)]);
        /*create repost in item table*/
        $repost = $iTable->createRow();

        $repost->setFromArray($itemFields);
        /*set repost special fields*/
        $repost->repost_id = $item->getIdentity();
        $repost->division_id = $this->getIdentity();
        $repost->creation_date = $repost->modified_date = date('Y-m-d H:i:s');
        $repost->is_blocked = 0;
        $repost->setFromArray(array('search' => 0));//set if field exists
        if ($order){
            $repost->division_order = $order;
        }else{
            $repost->division_order = $this->getMaxItemOrder() + 1;
        }
        if ($forceParent){
            $repost->parent_type = $forceParent->getType();
            $repost->parent_id   = $forceParent->getIdentity();
        }else{
            $tab = $this->getTab();
            if ($tab){
                $repost->parent_type = $tab->subject_type;
                $repost->parent_id   = $tab->subject_id;
            }
        }
        /*save*/
        $repost->save();
        return $repost;
    }

    public function getItems($limit = false, $offset = 0)
    {
        $table =  Engine_Api::_()->getItemTable($this->contain_type);
        $select = $table->getItemSelect(true, true);
        $select->where('division_id = ?', $this->getIdentity());
        if ($limit){
            $select->limit($limit, $offset);
        }
        return $table->fetchAll($select);
    }

    protected static $subjectsNotNeededToDivisionIdFilter = array('user','');

    public function getItemPaginator($enableReposts = null, $enableBlocked = null, $query = null) {
        /* @var $itemTable Core_Model_Item_DbTable_TreeNode */
        $itemTable = Engine_Api::_()->getItemTable($this->contain_type);
        $select = $itemTable->getItemSelect($enableReposts, $enableBlocked);
        $subject = $this->getSubject();
        if($subject instanceof Core_Model_Item_TreeNode || $subject instanceof User_Model_User){
            /* @var $subject Core_Model_Item_TreeNode */
            $select = $subject->filterSubItemsSelect($select, $this->contain_type);
        }
        $select = $this-> filterSubItemSelectByDivision($select);
        $paginator = Zend_Paginator::factory($select);
        if (!self::$NO_PAGINATOR_COUNT_CACHE){
            $paginator->getAdapter()
                ->setRowCount(( int ) $this->total_item_count);
        }
        return $paginator;
    }

    public function getItemPaginatorWithAudienceRelevation($audienceKey, $audienceParams = array(), $enableReposts = null, $enableBlocked = null) {
        /* @var $itemTable Core_Model_Item_DbTable_TreeNode */
        $itemTable = Engine_Api::_()->getItemTable($this->contain_type);
        $select = $itemTable->getItemSelectWithAudienceRelevation($audienceKey, $audienceParams, $enableReposts, $enableBlocked);
        $subject = $this->getSubject();

        if($subject instanceof Core_Model_Item_TreeNode || $subject instanceof User_Model_User){
            /* @var $subject Core_Model_Item_TreeNode */
            $select = $subject->filterSubItemsSelect($select, $this->contain_type);
        }
        $select = $this-> filterSubItemSelectByDivision($select);

        $paginator = Zend_Paginator::factory($select);
        if (!self::$NO_PAGINATOR_COUNT_CACHE){
            $paginator->getAdapter()
                ->setRowCount(( int ) $this->total_item_count);
        }
        return $paginator;
    }


    public function filterSubItemSelectByDivision(Zend_Db_Table_Select &$select){
        if (! in_array($this->subject_type, self::$subjectsNotNeededToDivisionIdFilter)) {
            $select->where('division_id = ?', $this->getIdentity());
        }
        if (! $this->subject_type) {
            $select->where('is_favorite = ?', 1);
        }
        return $select;
    }

    public function incrementCount()
    {
        if ($tab = $this->getTab()){
            $tab->incrementCount();
        }
        $this->total_item_count++;
        $this->save();
        return $this->total_item_count;
    }

    public function decrementCount()
    {
        if ($tab = $this->getTab()){
            $tab->decrementCount();
        }
        $this->total_item_count--;
        $this->save();
        return $this->total_item_count;
    }
    public function getItemsTable()
    {
        return Engine_Api::_()->getItemTable($this->contain_type);
    }
    public function getMaxItemOrder(){
        $db = Engine_Db_Table::getDefaultAdapter();
        //contain_type
        $select = $db->select()->from($this->getItemsTable()->info('name'), array(new Zend_Db_Expr('MAX(`division_order`)')))
            ->where('division_id = ?', $this->getIdentity());
        return $db->fetchOne($select);
    }
    public function getRenderer()
    {
        /*беёрм параметр из самого дивизиона*/
        $wparams = $this->getWidgetParams();
        if (isset($wparams['view'])){
            return $wparams['view'];
        }
        /*беёрм параметр из item_settings , и если там его нет  - ставим default вид */
        $treeSettings = Engine_Api::_()->core()->getTreeNodeSettings();
        return isset($treeSettings['item_settings'][$this->contain_type]['view'])?$treeSettings['item_settings'][$this->contain_type]['view']:'default';
    }

    public function totalItemCountRefresh(){
        $itemTable = Engine_Api::_()->getItemTable($this->contain_type);
        $select = $itemTable->getItemSelect(true, null, true);
        if ($this->subject_type == 'user'){
            $user = Engine_Api::_()->getItem('user', $this->subject_id);
            $select = $user->filterSubItemsSelect($select, $this->contain_type, false);
        }
        if (!in_array($this->subject_type, self::$subjectsNotNeededToDivisionIdFilter) ){
            $select->where('division_id = ?', $this->getIdentity());
        }
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns(new Zend_Db_Expr('count(1)'));
        /*echo($select->__toString());
        die();*/
        $itemsOfDivision =  $itemTable->getAdapter()->fetchOne($select);
        $this->total_item_count = $itemsOfDivision;
        $this->save();
        return $this->total_item_count;
    }

    protected $_paramsDecoded = array();
    protected static $_paramsDefaults = array('titleCount' =>true);
    public function getWidgetParams()
    {
        if (empty($this->widget_params_json) || !empty($this->_paramsDecoded)) return  array_merge(self::$_paramsDefaults, $this->_paramsDecoded);
        try{
            return ( $this->_paramsDecoded = array_merge(self::$_paramsDefaults, Zend_Json::decode($this->widget_params_json)) );
        }catch(Exception $e){
            return self::$_paramsDefaults;
        }
    }

    public function setWidgetParams($params)
    {
        $params = array_merge($this->getWidgetParams() ,$params);
        $this->_paramsDecoded = $params;
        $this->widget_params_json =  Zend_Json::encode($params);
        $this->save();
    }

    protected function _delete()
    {
        if (!$this->_disableHooks){
            foreach($this->getItems() as $item){
                if ($item->repost_id){
                    $item->delete();
                }else{
                    $item->is_blocked = 1;
                    $item->save();
                }
            }
        }
        parent::_delete();
    }
}