<?

/**
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Api_Search extends Core_Api_Abstract
{
    /**
     * @var
     */
    protected $_types;

    /**
     * @var array
     */
    protected $searchCategories = [
        'all'         => array('label' => 'Поиск по всему'),
        'courses'     => array('label' => 'Курсы', 'items' => ['academy', 'video', 'course', 'course_teacher', 'course_topic', 'course_post']),
        'people'      => array('label' => 'Люди'),
        'events'      => array('label' => 'Мероприятия', 'items' => ['event', 'event_post', 'event_topic']),
        'conference'  => array('label' => 'Конференции', 'items' => ['conference']),
        'groups'      => array('label' => 'Группы', 'items' => ['group', 'group_post', 'group_topic']),
        'articles'    => array('label' => 'Статьи и пособия', 'items' => ['article', 'folder', 'folder_attachment'])
    ];

    const SQL_MATCH_SPLITTER = '/[\\s@,\\*\\.\\+\\?\\(\\)\\\\]+/';
    const MATCH_MODE_SEPARATE = 1;
    const MATCH_MODE_GROUP = 2;
    const MATCH_MODE_GROUP_BUFF_TITLE = 3;

    public function buildSQLMatchQuery($fields, $query, $mode = self::MATCH_MODE_SEPARATE, $titleColumn = 'title')
    {
        /* @see \Core_Api_Search::getSelect */
        $query = trim($query);
        $exploded = array_filter(preg_split(self::SQL_MATCH_SPLITTER, trim($query)));
        $chunks = [];
        foreach($exploded as &$chunk){
            $chunk = str_replace('"', '\\"', rtrim(trim($chunk), '-'));
            if (mb_strlen(preg_replace(['@\\W+@u'],'',$chunk)) < 2) continue;
            $chunks[] = $chunk;
        }
        if ($mode == self::MATCH_MODE_SEPARATE){
            foreach($chunks as $i=>&$chunk){
                $chunk = ($i == count($chunks)-1) ? $chunk.'*' : '"'. $chunk . '"';
            }
            $queryDest = implode(' ', $chunks);
        }else if($mode == self::MATCH_MODE_GROUP || $mode == self::MATCH_MODE_GROUP_BUFF_TITLE){
            $lastChunk = array_pop($chunks);
            $queryDest =  ($chunks ? '"'.implode(' ', $chunks). '"@3 ' : '').($lastChunk ? $lastChunk.'*' : '');
        }

        $matchExpr = "MATCH(".implode(', ',$fields).") AGAINST (? IN BOOLEAN MODE)";
        if($mode == self::MATCH_MODE_GROUP || $mode == self::MATCH_MODE_GROUP_BUFF_TITLE){
            $matchExpr .= Engine_Db_Table::getDefaultAdapter()->quoteInto(' + IF(CONCAT('.implode(",' ',",$fields).') LIKE ?, 2, 0)', '%'.$query.'%');
        }
        if (in_array($titleColumn, $fields) && $mode == self::MATCH_MODE_GROUP_BUFF_TITLE){
            $matchExpr .= Engine_Db_Table::getDefaultAdapter()->quoteInto(' + IF('.$titleColumn.' LIKE ?, 2, 0)', '%'.$query.'%');
        }
        return Engine_Db_Table::getDefaultAdapter()->quoteInto($matchExpr, $queryDest);
    }


    /**
     * @param Core_Model_Item_Abstract $item
     * @return bool|void
     */
    public function index(Core_Model_Item_Abstract $item)
    {
        // Get info
        $searchHeadItem = $item->getSearchRegulator();
        if (!$searchHeadItem){
            return;
        }
        $filter = function($value){
            return substr(preg_replace('@\\s+@',' ',   str_replace('&nbsp;', '"',str_replace('&quot;', '"',trim($value)))   ), 0, 255);
        };
        $type = $item->getType();
        $id = $item->getIdentity();
        $title = $filter($item->getTitle());
        $description = $filter(html_entity_decode(strip_tags(
            str_replace('&nbsp;', ' ', $item->getDescription())
        )));
        $keywords = $filter($item->getKeywords());
        $hiddenText = $filter($item->getHiddenSearchData());

        // Ignore if no title and no description
        if( !$title && !$description )
        {
            return false;
        }

        // Check if already indexed
        $table = Engine_Api::_()->getDbtable('search', 'core');
        $select = $table->select()
            ->where('type = ?', $type)
            ->where('id = ?', $id)
            ->limit(1);

        $row = $table->fetchRow($select);

        if( null === $row )
        {
            $row = $table->createRow();
            $row->type = $type;
            $row->id = $id;
        }

        $row->title = $title;
        $row->description = $description;
        $row->keywords = $keywords;
        $row->hidden = $hiddenText;
        $row->display = (!isset($searchHeadItem->search) || $searchHeadItem->search) ? '1' : '0';
        /*16 oct 2016*/
        $isTreeItem = ($searchHeadItem instanceof Core_Model_Item_TreeNode);
        $DS = Engine_Api::_()->core()->getNowDomainSettings();

        $row->is_blocked = $isTreeItem ? (int)$searchHeadItem->is_blocked : 0;
        $academy = $isTreeItem ? Engine_Api::_()->zftsh()->getAcademyOf($searchHeadItem) : null;
        $row->academy_id = $academy ? $academy->getIdentity() : 0;
        $row->domain = $isTreeItem ? $searchHeadItem->domain :
            ( ( !isset($DS['domainMark']) ||$DS['domainMark']) ?  Engine_Api::_()->core()->getNowDomainSettings()['key'] : '' );
        /**/

        $row->save();
    }

    /**
     * @param Core_Model_Item_Abstract $item
     * @return $this
     */
    public function unindex(Core_Model_Item_Abstract $item)
    {
        $table = Engine_Api::_()->getDbtable('search', 'core');

        $table->delete(array(
            'type = ?' => $item->getType(),
            'id = ?' => $item->getIdentity(),
        ));

        return $this;
    }

    /**
     * @param $type
     * @param $id
     * @return $this
     */
    public function remove($type, $id)
    {
        $table = Engine_Api::_()->getDbtable('search', 'core');
        $table->delete(array(
            'type = ?' => $type,
            'id = ?' => $id,
        ));

        return $this;
    }

    /**
     * @param $text
     * @param array $filter
     * @return Zend_Paginator
     */
    public function getPaginator($text, $filter = [])
    {
        return Zend_Paginator::factory($this->getSelect($text, $filter));
    }

    /**
     * @param $text
     * @param array $filter
     * @return Zend_Db_Select
     */
    public function getSelect($text, $filter = [])
    {
        // Build base query
        $table = Engine_Api::_()->getDbtable('search', 'core');
        $db = $table->getAdapter();

        $matchExpr  = $this->buildSQLMatchQuery(['title','description', 'keywords', 'hidden'], $text, self::MATCH_MODE_GROUP);
        $matchExprTitle  = $this->buildSQLMatchQuery(['title', 'keywords'], $text);
        $filterExpr = $this->buildSQLMatchQuery(['title','description', 'keywords', 'hidden'], $text);

        $select = $db->select()->distinct()->from( $table->info('name'), ['type', 'id', 'relevancy' => $matchExpr . ' + '. $matchExprTitle] )
            ->where( $filterExpr )
            ->where('is_blocked = 0')
            ->order('relevancy DESC')
            ->group(array('type', 'id'));

        /* NOTE - example search select will be

        SELECT DISTINCT `engine4_core_search`.`type`,
                    `engine4_core_search`.`id`,
                    MATCH(title, description, keywords, hidden) AGAINST ('\"\\\"Прикладные математика физика\\\"\"@3 201*' IN BOOLEAN MODE) + IF(CONCAT(title, ' ', description, ' ', keywords, ' ', hidden) LIKE '%\"Прикладные математика и физика\" 201%', 2, 0) + MATCH(title, keywords) AGAINST ('\"\\\"Прикладные\" \"математика\" \"физика\\\"\" 201*' IN BOOLEAN MODE) AS `relevancy`
        FROM `engine4_core_search`
        WHERE (MATCH(title, description, keywords, hidden) AGAINST ('\"\\\"Прикладные\" \"математика\" \"физика\\\"\" 201*' IN BOOLEAN MODE))
          AND (is_blocked = 0)
        GROUP BY `type`,
                 `id`
        ORDER BY relevancy DESC
        */
        if ( !empty($filter['type']) && is_array($filter['type']) ) {
            $select->where('type IN (?)', $filter['type']);
        } else if( !empty($filter['type']) && isset($this->searchCategories[$filter['type']]['items']) ) {
            $select->where('type IN (?)', $this->searchCategories[$filter['type']]['items']);
        }

        if (empty($filter['showHidden'])){
            $select->where("display = '1'");
        }

        if (!empty($filter['academies'])){
            $select->where('academy_id IN(?)', $filter['academies']);
        }

        if (!empty($filter['domain'])){
            $allDS = Engine_Api::_()->core()->getDomainsSettings();
            if (isset($allDS[$filter['domain']]['domainDisplay']) && $allDS[$filter['domain']]['domainDisplay'] == 'self'){
                $select->where("domain = ?", $filter['domain']);
            }else{
                $select->where("domain = ? or domain = ''", $filter['domain']);
            }
        }

        return $select;
    }

    /**
     * @param Zend_Db_Table_Select $select
     * @param $query
     * @param $postsTable
     * @return Zend_Db_Select
     */
    public function filterTopicsByTextQuery(Zend_Db_Table_Select $select, $query, $postsTable)
    {
        $db = Engine_Db_Table::getDefaultAdapter();

        $topicTableModel = $select->getTable();
        $topicTable = $select->getTable()->info('name');
        $select->setIntegrityCheck(false);

        $select->reset(Zend_Db_Select::ORDER);
        $select
            ->from($topicTable)
            ->from($postsTable, [])
            ->where($postsTable.'.topic_id = '.$topicTable.'.topic_id')
            ->columns(['relevancy' => new Zend_Db_Expr(
                Engine_Api::_()->search()->buildSQLMatchQuery([$topicTable.'.title'], $query, Core_Api_Search::MATCH_MODE_GROUP) . ' + ' .
                Engine_Api::_()->search()->buildSQLMatchQuery([$postsTable.'.body'], $query, Core_Api_Search::MATCH_MODE_GROUP)
            )])
            ->where(
                Engine_Api::_()->search()->buildSQLMatchQuery([$postsTable.'.body'], $query) . ' OR '.
                Engine_Api::_()->search()->buildSQLMatchQuery([$topicTable.'.title'], $query)
            );
        $cols = $topicTableModel->info('cols');//implode(',', )
        $wrapSelect = $topicTableModel->select()->setIntegrityCheck(false)->reset(Zend_Db_Select::FROM)
            ->from(['topics' => $select], $cols)
            ->group($cols)
            ->order('max(relevancy) DESC');
        /* NOTE - example topic-post search select will be (! it is very difficult, because need to hack-distinct rows for paginator !):

        SELECT
      `topics`.`topic_id`,
      `topics`.`event_id`,
      `topics`.`user_id`,
      `topics`.`title`,
      `topics`.`creation_date`,
      `topics`.`modified_date`,
      `topics`.`sticky`,
      `topics`.`closed`,
      `topics`.`view_count`,
      `topics`.`post_count`,
      `topics`.`lastpost_id`,
      `topics`.`lastposter_id`
  FROM
      (SELECT
          `engine4_event_topics`.*,
              MATCH (engine4_event_topics.title) AGAINST ('"Тележка"@3 брусок*' IN BOOLEAN MODE) + IF(CONCAT(engine4_event_topics.title) LIKE '%Тележка и брусок%', 2, 0) + MATCH (engine4_event_posts.body) AGAINST ('"Тележка"@3 брусок*' IN BOOLEAN MODE) + IF(CONCAT(engine4_event_posts.body) LIKE '%Тележка и брусок%', 2, 0) AS `relevancy`
      FROM
          `engine4_event_topics`
      INNER JOIN `engine4_event_posts`
      WHERE
          (engine4_event_topics.event_id = 1500)
              AND (engine4_event_posts.topic_id = engine4_event_topics.topic_id)
              AND (MATCH (engine4_event_posts.body) AGAINST ('"Тележка" брусок*' IN BOOLEAN MODE)
              OR MATCH (engine4_event_topics.title) AGAINST ('"Тележка" брусок*' IN BOOLEAN MODE))) AS `topics`
  GROUP BY `topic_id` , `event_id` , `user_id` , `title` , `creation_date` , `modified_date` , `sticky` , `closed` , `view_count` , `post_count` , `lastpost_id` , `lastposter_id`
  ORDER BY MAX(relevancy) DESC;
         */

        return $wrapSelect;
    }

    /**
     * @return array
     */
    public function getAvailableTypes()
    {
        if( null === $this->_types ) {
            $this->_types = Engine_Api::_()->getDbtable('search', 'core')->getAdapter()
                ->query('SELECT DISTINCT `type` FROM `engine4_core_search`')
                ->fetchAll(Zend_Db::FETCH_COLUMN);
            $this->_types = array_intersect($this->_types, Engine_Api::_()->getItemTypes());
        }

        return $this->_types;
    }

    /**
     * @return array
     */
    public function getSearchCategories()
    {
        return $this->searchCategories;
    }

    /**
     * @return array
     */
    public function getSearchMultiOptions()
    {
        $multiOptions = [];
        foreach($this->searchCategories as $key=>$settings){
            $multiOptions[$key] = $settings['label'];
        }
        return $multiOptions;
    }
}