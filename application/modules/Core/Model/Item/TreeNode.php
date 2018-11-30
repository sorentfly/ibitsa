<?

/**
 *
 * * @property string parent_type
 * * @property integer parent_id
 * * @property integer repost_id
 *
 * * @property int|mixed division_id
 * * @property int division_order
 *
 *
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 *
 */
abstract class Core_Model_Item_TreeNode extends Core_Model_Item_Abstract
{
    /**
     * @var bool
     */
    protected $_repostsAvailable = true;

    public function __construct($config) {
        $this->_repostsAvailable = $this->getType() != 'user';
        parent::__construct($config);
    }

    public function getNoPhotoUrl()
    {
        $treeSettings = Engine_Api::_()->core()->getTreeNodeSettings();
        return isset($treeSettings['item_settings'][$this->getType()]['nophoto'])?$treeSettings['item_settings'][$this->getType()]['nophoto']:DEFAULT_NOPHOTO;
    }

    public function getEditHref()
    {
        $route = $this->getType().'_specific';

        return Zend_Controller_Front::getInstance()->getRouter()
            ->assemble([
                $this->getType(). '_id' => $this->getIdentity(),
                'action' => 'edit',
            ], $route, true);
    }

    /**
     * @param null $recurseType
     * @return $this|bool|Core_Model_Item_Abstract|Core_Model_Item_TreeNode
     * @throws Engine_Exception
     */
    public function getParent($recurseType = null)
    {
        if (!$this->parent_type || !$this->parent_id){
            return false;
        }
        $parent = Engine_Api::_()->getItem($this->parent_type, $this->parent_id);
        if (!$parent)
        {
            return false;
        }
        if ($this->parent_type == 'user'){
            return $parent;//getOwner
        }
        if (! ($parent instanceof self) ){
            throw new Engine_Exception("Parent for instance of Core_Model_Item_TreeNode must be only instance of Core_Model_Item_TreeNode");
        }

        return $this->_repostsAvailable && $parent->repost_id ? $parent->getOriginal() : $parent;
    }

    /**
     * @return $this|Core_Model_Item_Abstract
     */
    public function getOriginal()
    {
        if (!$this->_repostsAvailable){
            return $this;
        }
        if (!$this->repost_id || !($originalItem =  Engine_Api::_()->getItem($this->getType(), $this->repost_id)) ){
            return $this;
        }
        return $originalItem;
    }

    /**
     * @return bool
     */
    public function isSearchable()
    {
        return $this->_repostsAvailable && $this->repost_id ? false : parent::isSearchable();
    }

    /**
     * @param string|User_Model_User $role
     * @param string $action
     * @return mixed
     */
    public function isAllowed($role, $action = 'view')
    {
        $domainSettings = Engine_Api::_()->core()->getNowDomainSettings();

        return Authorization_Api_Core::LEVEL_IGNORE;
    }

    /**
     * @param $html
     * @param $title
     * @param int $order
     * @return Core_Model_Tab
     */
    public function addHtmlTab($html, $title, $order=0)
    {
        $tab = $this->addTab('html', $title, $order);
        $tab->html_append = $html;
        $tab->save();
        return $tab;
    }

    /**
     * @param $widget
     * @param $title
     * @param int $order
     * @return Core_Model_Tab
     */
    public function addTab($widget, $title, $order=0)
    {
        $data = array(
            'widget' => $widget,
            'title'  => $title,
            'order'  => $order,
            'subject' => $this,
        );
        return Engine_Api::_()->getItemTable('tab')->addTab($data);
    }

    /**
     * @return Core_Model_Tab[]
     */
    public function getTabs()
    {
        $tabs = Engine_Api::_()->getItemTable('tab')->getTabsForItem($this);
        if (!count($tabs)){
            return Engine_Api::_()->getItemTable('tab')->setupDefaultItemTabs($this);
        }
        return $tabs;
    }

    /**
     * @param $widget
     * @return Core_Model_Tab
     */
    public function getTab($widget)
    {
        $tabs = Engine_Api::_()->getItemTable('tab')->getTabsForItem($this, array($widget));
        return count($tabs)?$tabs[0]:null;
    }

    /**
     * @param $itemType
     * @param bool $check
     * @return Core_Model_Division[]
     */
    public function getDivisions($itemType, $check = true)
    {
        /* @var $divisionDb Core_Model_DbTable_Divisions */
        $divisionDb = Engine_Api::_()->getItemTable('division');
        $divisions = $divisionDb->getDivisionsForItem($this, $itemType);

        if($check == true){
            $this->checkDevisionConsistance($itemType, $divisions);
        }
        return $divisions->rowArray();
    }

    protected function checkDevisionConsistance($subItemType, Engine_Db_Table_Rowset &$divisions){
        /* @var $itemTable Core_Model_Item_DbTable_TreeNode */
        $subItemTable = Engine_Api::_()->getItemTable($subItemType);
        if(! $subItemTable instanceof Core_Model_Item_DbTable_TreeNode ){
            return ;
        }


        $divisionsIds = array ();
        $defaultDivision = null;
        foreach ( $divisions as $division ) {
            $divisionsIds [] = $division->getIdentity();
            $defaultDivision = $division;
        }

        // Выбираем итемы вне дивизионов.
        $outOfDivisionSelect = $subItemTable->getItemSelect(true)
            ->where('parent_id = ?', $this->getIdentity())
            ->where('parent_type = ?', $this->getType());

        if ($divisionsIds){
            $outOfDivisionSelect->where('(division_id not in (?))', $divisionsIds);
        }
        $outOfDivisionItems = $subItemTable->fetchAll($outOfDivisionSelect)->rowArray();
        /* костыль - файлы могут находится так же в дивизионах папок */
        if ($subItemType == 'folder'){
            $attachments = array();
            $attachmentTable = Engine_Api::_()->getItemTable('folder_attachment');
            $select = $attachmentTable->getItemSelect(true)
                ->where('parent_id = ?', $this->getIdentity())
                ->where('parent_type = ?', $this->getType());
            if ($divisionsIds){
                $select->where('(division_id not in (?))', $divisionsIds);
            }
            $attachments = $attachmentTable->fetchAll(
                $select
            )->rowArray();

            $outOfDivisionItems = array_merge($outOfDivisionItems, $attachments);
        }
        /* // костыль */
        if (count($outOfDivisionItems)){
            $db = Engine_Db_Table::getDefaultAdapter();
            self::$ENABLE_DIVISION_AUTOCOUNT = false;
            $db->beginTransaction();
            if (empty($defaultDivision)){
                $defaultDivision = $divisions->createRow();
                $defaultDivision->setFromArray(array(
                    'title'         => Core_Api_Core::DEFAULT_DIVISION_TITLE,
                    'contain_type'  => $subItemType,
                    'subject_type'  => $this->getType(),
                    'subject_id'    => $this->getIdentity(),
                    'order'         => 0,
                    'total_item_count' => 0
                ));
                $defaultDivision->save();

            }

            $itemOrder = $defaultDivision->total_item_count + 1;
            foreach($outOfDivisionItems as $item){
                $item->division_id = $defaultDivision->getIdentity();
                $item->division_order = $itemOrder++;
                $item->save();
            }
            $defaultDivision->totalItemCountRefresh();
            if ($tab = $defaultDivision->getTab()){
                $tab->totalItemCountRefresh();
            }
            $db->commit();
            self::$ENABLE_DIVISION_AUTOCOUNT = true;
        }
    }


    public function getAudiences()
    {
        if (empty($this->audience_bitmask)){
            return [];
        }
        $audiences = array();
        foreach(Core_Model_Item_DbTable_TreeNode::$nodeAudiences as $key=>$audience){
            if ($this->audience_bitmask & $audience['bit']){
                $audiences[] = $key;
            }
        }
        return $audiences;
    }

    /**
     * @return Zend_Db_Table_Select */
    public function getChildsSelect($itemType, $enableReposts = null, $enableBlocked = null)
    {
        /* @var Core_Model_Item_DbTable_TreeNode $table */
        $table =  Engine_Api::_()->getItemTable($itemType);
        $select = $table->getItemSelect($enableReposts, $enableBlocked);
        $select->where('parent_id = ?', $this->getIdentity())
            ->where('parent_type = ?', $this->getType());
        return $select;
    }

    /* @return Core_Model_Item_TreeNode[] */
    public function getAllChilds($withReposts = false, $withBlocked = true)
    {
        $TS = Engine_Api::_()->core()->getTreeNodeSettings();
        $childs = [];
        foreach($TS['nesting'][$this->getType()] as $type){
            $select = $this->getChildsSelect($type, $withReposts, $withBlocked);
            foreach ($select->getTable()->fetchAll($select) as $child){
                /* @var Core_Model_Item_TreeNode $child */
                $childs[$child->getGuid()] = $child;
            }
        }
        return $childs;
    }

    /*delete polymorph*/
    public static $ENABLE_DIVISION_AUTOCOUNT = true;
    public function delete()
    {
        if ($this->_repostsAvailable){
            if ($this->repost_id){
                /* запрещая хуки, мы тем самым запрещаем удаление дочерних итемов. */
                $this->disableHooks();
            }else{
                /* при удалении оригинала - необходимо тереть все его репосты. */
                $repostsOfThis = $this->_getTable()->fetchAll( $this->_getTable()->select()->where('repost_id = ?', $this->getIdentity()) );
                foreach($repostsOfThis as $repost){
                    $repost->delete();
                }
                /*дочерние итемы нужно блокировать*/
                foreach ($this->getAllChilds() as $child){
                    $child->is_blocked = $this->is_blocked;
                    $child->save();
                }
            }
        }
        if (self::$ENABLE_DIVISION_AUTOCOUNT &&  !$this->is_blocked && $this->division_id && ($division = Engine_Api::_()->getItem('division', $this->division_id)) ){
            $division->decrementCount();
        }
        return parent::delete();
    }
    /*pre update*/
    protected function _update()
    {

        parent::_update();
        if (!self::$ENABLE_DIVISION_AUTOCOUNT) return;

        if (!empty($this->_modifiedFields['is_blocked']) && $this->_cleanData['is_blocked'] != $this->is_blocked){
            if ($division = Engine_Api::_()->getItem('division', $this->division_id) ){
                if ($this->is_blocked){
                    $division->decrementCount();
                }else{
                    $division->incrementCount();
                }
            }
            foreach ($this->getAllChilds() as $child){
                $child->is_blocked = $this->is_blocked;
                $child->save();
            }
        }

        if (!empty($this->_modifiedFields['division_id']) && $this->_cleanData['division_id'] != $this->division_id){
            $db = Engine_Db_Table::getDefaultAdapter();
            $primaryKeys = $this->getTable()->info(Zend_Db_Table_Abstract::PRIMARY);
            $oldDivisionId = $db->fetchOne(
                $db->select()->from($this->getTable()->info('name'), array('division_id'))
                    ->where(array_pop($primaryKeys).' = ?', $this->getIdentity())
            );
            if ($oldDivision = Engine_Api::_()->getItem('division', $oldDivisionId)){
                $oldDivision->decrementCount();
            }
            if ($newDivision = Engine_Api::_()->getItem('division', $this->division_id) ){
                $newDivision->incrementCount();
            }
        }
    }
    /*pre insert*/
    protected function _insert()
    {
        $this->is_blocked = 0; /*No able to insert blocked items*/
        parent::_insert();
        //
        $owner = $this->getOwner();
        if (!$this->repost_id && $owner && $owner->getIdentity()){
            /*also calling in Core_Model_DbTable_Membership::_checkActive , Core_Model_DbTable_Membership::removeMember*/
            $owner->updateTabCount($this->getType());
        }
        //
        if (!self::$ENABLE_DIVISION_AUTOCOUNT) return;
        if ($this->division_id && ($division = Engine_Api::_()->getItem('division', $this->division_id)) ){
            $division->incrementCount();
        }
    }


    /**
     *
     * Получение обязательных полей, с возможностью наследования
     *
     * @param string $userProfileType - возвращать только поля выбранного типо пользователя
     * @param string $checkParent - проверять поля родителя, если у этого объекта поля не установлены
     * @return array
     */
    private $_requiredFieldsCache;
    public function getRequiredFields($userProfileType = null, $checkParent = true, $cutFlagIgnore = true){
        if(!isset($this->_requiredFieldsCache)){
            $fields = $this->require_fields ? Zend_Json::decode($this->require_fields) : [];

            if(empty($fields) || !is_array($fields)){
                if ($checkParent){
                    if($this->parent_type == $this->getType()){
                        $parent = $this->getParent();
                    }
                    if(!empty($parent)){
                        return $parent->getRequiredFields($userProfileType, true);
                    }
                }
                if ($userProfileType){
                    return [];
                }
                $default = [];
                foreach(array_merge(['all' => '' ], Engine_Api::_()->getItemTable('user')->getProfileTypes(false)) as $key=>$__)
                {
                    $default[$key] = [];
                }
                return $default;
            }
            $this->_requiredFieldsCache = $fields;
        } else {
            $fields = $this->_requiredFieldsCache;
        }

        if($userProfileType !== null){
            if( !isset($fields[$userProfileType]) ){
                return null;
            }
            $limitations = isset($fields['all']) ? $fields['all'] : [];

            if( isset($fields[$userProfileType]) ){
                $limitations = array_merge_recursive($limitations, $fields[$userProfileType]);
            }
            if ($cutFlagIgnore){
                foreach ($limitations as $field=>$limitation){
                    $limitations[$field] = str_replace('_cut', '', $limitation);
                }
            }
            return $limitations;
        } else {
            return $fields;
        }
    }

    public function getCutRequiredFields($userProfileType, $checkParent = true)
    {
        $rFields = $this->getRequiredFields($userProfileType, $checkParent, false);

        $cuts = [];
        foreach($rFields as $field=>$limitation){
            if (mb_strpos($limitation, '_cut')!==false){
                $cuts[] = $field;
            }
        }
        return $cuts;
    }

    public static function mergeRequiredFields($source, $destination, $ignoreDestFilter = false)
    {
        if (!$destination) return $source;
        foreach($destination as $key => $value){
            if (empty($source[$key]) || mb_strpos($key, '_')===0 ){
                if (mb_strpos($key, '_filter')===0  && $ignoreDestFilter){
                    continue;
                }
                $source[$key] = $value;
            }else if ($source[$key] == 'displayed' && in_array($value, ['required', 'confirm', 'soft_confirm'])){
                $source[$key] = $value;
            }else if ($source[$key] == 'required' &&  in_array($value, ['confirm', 'soft_confirm']) ){
                $source[$key] = $value;
            }else if ($source[$key] == 'confirm' && $value == 'soft_confirm'){
                $source[$key] = $value;
            }
        }
        return $source;
    }

    public function isProfileTypeAvailable($profileType, $checkParent = true){
        $fields = $this->getRequiredFields($profileType, $checkParent);
        return $fields !== null;
    }

    public static function isFilterMatchedRequiredField($filter, $value)
    {
        if (!$filter) return true;

        $value = mb_strtolower(trim($value));
        $chunks = explode('--', $filter, 2);
        if (count($chunks)>1){
            $chunks = array_map('mb_strtolower', array_map('trim', $chunks));
            //{от}--{до}
            $min = trim($chunks[0]); $max = trim($chunks[1]);

            if ( strtotime($max)>86400 && strtotime($min)>86400 ){
                //тип - дата
                return strtotime($value) >= strtotime($min) && strtotime($value) <= strtotime($max);
            }else if ( is_numeric($max) && is_numeric($min) ){
                //тип - число
                return intval($value) >= intval($min) && intval($value) <= intval($max);
            }else{
                //тип - строка
                return $value >= $min && $value <= $max;
            }
        }else{
            //поиск в массиве
            $chunks = array_map('mb_strtolower', array_map('trim', explode(',,', $filter) ));
            return in_array($value, $chunks);
        }
    }

    /* @var Core_Model_Item_TreeNode $checkFieldFilterInvalidObject */
    public static $checkFieldFilterInvalidObject = null;

    public function checkFieldFilterValid(User_Model_User $user, $recursed)
    {
        if (!$user->getIdentity()){
            self::$checkFieldFilterInvalidObject = $this;
            return false;
        }
        if (!$this->__isset('require_fields')) return true;
        if (!$this->isProfileTypeAvailable($user->profile_status)){
            self::$checkFieldFilterInvalidObject = $this;
            return false;
        }

        $requiredFields = $this->getRequiredFields($user->profile_status);
        $form = new Core_Form_Join($requiredFields ? $requiredFields : []);
        $form->populate($user->getAllFieldValues());

        if (!$form->isValid( $form->getValues() )){
            self::$checkFieldFilterInvalidObject = $this;
            return false;
        }
        if ( $recursed && ($parent = $this->getParent()) && $parent instanceof Core_Model_Item_TreeNode){
            return $parent->checkFieldFilterValid($user, true);
        }
        return true;
    }

    // Overide this on concreet item classes
    public function filterSubItemsSelect($select, $subItemType /*reserved for child classes*/, $domainRule = null){
        if ($domainRule === null) $domainRule = Engine_Api::_()->core()->getDomainFilter();
        if ($domainRule){
            $select->where('domain IN (?)', $domainRule);
        }
        $select->where('parent_id = ?', $this->getIdentity());
        $select->where('parent_type = ?', $this->getType());
        if ($domainRule){
            $select->where('domain IN (?)', is_array($domainRule)?$domainRule:array($domainRule));
        }
        return $select;
    }

    public function getBannerImage()
    {
        $image = parent::getBannerImage();
        if (!$image && ($academy = Engine_Api::_()->zftsh()->getAcademyOf($this))){
            $image = $academy->getBannerImage();
        }
        return $image;
    }

    public function preprocessProfileTabs($profileTabs)
    {
        /* Profile tabs are not modified by default */
        return false;
    }

};
