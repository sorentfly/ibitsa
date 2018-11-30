<?

/**
 * Модель ряда вкладки виджета вкладок профиля.
 *
 * * @property string subject_type
 * * @property integer subject_id
 * * @property string title
 * * @property string widget
 * * @property integer total_item_count
 *
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 *
 */
class Core_Model_Tab extends Core_Model_Item_Abstract
{
    protected $_searchTriggers = false;// = array('text');

    /**
     * @return string
     */
    public function getTitle()
    {
        $DS = Engine_Api::_()->core()->getNowDomainSettings();
        $translate = Zend_Registry::get('Zend_Translate');
        $title =  $translate->translate($this->title);
        if (!empty($DS['zftshDefaults']) && $this->widget == 'olympic.profile-olympics'){
            /* КОСТЫЛЬ - переименование вкладки олимпиад для ЗФТШ */
            $title = 'Задачи и тесты';
        }
        return $title;
    }

    /**
     * @return Core_Model_Item_TreeNode
     */
    public function getSubject()
    {
        /* @var Core_Model_Item_TreeNode $tmp */
        $tmp = Engine_Api::_()->getItem($this->subject_type, $this->subject_id);
        return $tmp;
    }

    /**
     * @param array $params
     * @return null|string
     */
    public function getHref($params = array())
    {
        $subject = $this->getSubject();
        if (!$subject){
            return null;
        }
        $subjectHref = $subject->getHref();
        return rtrim($subjectHref, '/').'/'.$this->getName();
    }

    /**
     * @return string
     */
    public function getName()
    {
        $wiConf = Engine_Api::_()->core()->getTreeNodeSettings();
        $widgetName = $this->widget;
        if (!empty($wiConf['item_settings'][$this->widget]['pseudo'])){
            $widgetName = $wiConf['item_settings'][$this->widget]['pseudo'];
        }
        return $widgetName;
    }

    /**
     * @return Core_Model_Item_TreeNode
     */
    public function getParent($recurseType = null) {
        /* @var Core_Model_Item_TreeNode $tmp */
        $tmp = Engine_Api::_()->getItem($this->subject_type, $this->subject_id);
        return $tmp;
    }

    /**
     * @param $containType
     * @param $title
     * @param int $order
     * @return Core_Model_Division
     */
    public function addDivision($containType, $title, $order = 0)
    {
        return Engine_Api::_()->getItemTable('division')->addDivision(array(
            'tab_id' => $this->getIdentity(),
            'order'  => $order,
            'title'  => $title,
            'contain_type' => $containType,
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id
        ));
    }

    /**
     * @return Core_Model_Division[]
     */
    public function getDivisions()
    {
        /* @var  Core_Model_DbTable_Divisions $table */
        $table = Engine_Api::_()->getItemTable('division');
        return $table->getDivisionsForTab($this);
    }

    /**
     * @return string
     */
    public function incrementCount()
    {
        $this->total_item_count++;
        $this->save();
        return $this->total_item_count;
    }

    /**
     * @return string
     */
    public function decrementCount()
    {
        $this->total_item_count--;
        $this->save();
        return $this->total_item_count;
    }


    /**
     * @var array
     */
    protected $_paramsDecoded = array();

    /**
     * @var array
     */
    protected static $_paramsDefaults = array('titleCount' => true, 'showSearch' => true);


    /**
     * @return array
     */
    public function getWidgetParams() {
        if (empty($this->widget_params_json) || ! empty($this->_paramsDecoded))
            return array_merge(self::$_paramsDefaults, $this->_paramsDecoded);
        try {
            return ($this->_paramsDecoded = array_merge(self::$_paramsDefaults, Zend_Json::decode($this->widget_params_json)));
        } catch ( Exception $e ) {
            return self::$_paramsDefaults;
        }
    }

    /**
     * @return bool
     */
    public function isItemDisplayWidget()
    {
        return strpos($this->widget, '.')===false && $this->widget!= 'html';
    }

    /**
     *
     */
    public function totalItemCountRefresh() {
        if ($this->isItemDisplayWidget()) {
            if ($this->subject_type == 'user') { /* NOTE: user count refreshing after tab creates, and after division add */
                $division = $this->getSubject()->getDivisions($this->widget);
                if (! count($division)) {
                    return;
                }
                $division = $division [0];
                $division->totalItemCountRefresh();
                $this->total_item_count = $division->total_item_count;
            } else {

                if($this->widget == 'folder'){
                    $itemCount = $this->getItemCountForType($this->widget);
                    $itemCount += $this->getItemCountForFolderAttachments();
                } else if($this->widget == 'folder_attachment'){
                    $itemCount = $this->getItemCountForFolderAttachments();
                } else {
                    $itemCount = $this->getItemCountForType($this->widget);
                }

                $this->total_item_count = $itemCount;
            }
            $this->save();
        }
    }

    /**
     * @param $itemType
     * @return int
     */
    public function getItemCountForType($itemType){
        $db = $this->getTable()->getAdapter();
        $itemTableName = Engine_Api::_()->getItemTable($itemType)->info('name');

        $select = $db->select()
            ->from($itemTableName, array(new Zend_Db_Expr('count(*)')))
            ->where('parent_type = ?', $this->subject_type)
            ->where('parent_id = ?', $this->subject_id)
            ->where('is_blocked = ?', false);

        $itemCount = $db->fetchOne($select);
        return (int)$itemCount;
    }

    /**
     * @return int
     */
    public function getItemCountForFolderAttachments(){
        $db = $this->getTable()->getAdapter();
        $attachmentsTableName = Engine_Api::_()->getItemTable('folder_attachment')->info('name');
        $divisionsTableName = Engine_Api::_()->getItemTable('division')->info('name');

        $select = $db
            ->select()
            ->from(array('a' => $attachmentsTableName), array(new Zend_Db_Expr('count(*)')))
            ->joinInner(array('d' => $divisionsTableName), 'a.division_id = d.division_id', array())
            ->where('d.subject_type = ?', $this->subject_type)
            ->where('d.subject_id = ?', $this->subject_id)
            ->where('d.tab_id = ?', $this->getIdentity())
            ->where('a.parent_type = ?', $this->subject_type)
            ->where('a.parent_id = ?', $this->subject_id)
            ->where('a.is_blocked = ?', false);

        $itemCount = $db->fetchOne($select);

        return (int)$itemCount;
    }

    /**
     * @return bool
     */
    public function isAllowedView(){
        if($this->subject_type == 'user' && in_array($this->widget, ['course', 'event', 'group'])){
            $viewer = Engine_Api::_()->user()->getViewer();
            $subject = $this->getSubject();
            if($subject && !$subject->authorization()->isAllowed($viewer, User_Model_User::PERMISSION_VIEW_OLYMPICS)){
                return false;
            }
        }
        return true;
    }

}