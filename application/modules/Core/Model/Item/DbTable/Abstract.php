<?

/**
 *
 * * @property string _rowClass
 *
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 *
 */
abstract class Core_Model_Item_DbTable_Abstract extends Engine_Db_Table
{
    protected $_itemType;

    protected $_localItemCache = array();

    public function __construct($config = array())
    {
        if( !isset($this->_rowClass) ) {
            $this->_rowClass = Engine_Api::_()->getItemClass($this->getItemType());
        }

        // @todo stuff
        parent::__construct($config);
    }

    public function getItemType()
    {
        if( null === $this->_itemType )
        {
            // Try to singularize item table class
            $segments = explode('_', get_class($this));
            $pluralType = array_pop($segments);
            $type = rtrim($pluralType, 's');
            if( !Engine_Api::_()->hasItemType($type) ) {
                $type = rtrim($pluralType, 'e');
                if( !Engine_Api::_()->hasItemType($type) ) {
                    throw new Core_Model_Item_Exception('Unable to get item type from dbtable class: '.get_class($this));
                }
            }

            // Make sure we have a column matching
            $prop = $type . '_id';
            if( !in_array($prop, $this->info('cols')) )
            {
                throw new Core_Model_Item_Exception('Unable to get item type from dbtable class: '.get_class($this));
            }

            // Cool
            $this->_itemType = $type;
        }

        return $this->_itemType;
    }

    /**
     * @param $identity
     * @return Core_Model_Item_Abstract
     */
    public function getItem($identity)
    {
        if( !array_key_exists((int) $identity, $this->_localItemCache) )
        {
            $this->_localItemCache[$identity] = $this->find($identity)->current();
        }

        return $this->_localItemCache[$identity];
    }

    /**
     * @param array $identities
     * @return Core_Model_Item_Abstract[]
     */
    public function getItemMulti(array $identities)
    {
        $todo = array();
        foreach( $identities as $identity )
        {
            if( !array_key_exists((int) $identity, $this->_localItemCache) )
            {
                $todo[] = $identity;
            }
        }


        if( count($todo) > 0 )
        {
            /* @var $item Core_Model_Item_Abstract */
            foreach( $this->find($todo) as $item )
            {
                $this->_localItemCache[$item->getIdentity()] = $item;
            }
        }

        $ret = array();
        foreach( $identities as $identity )
        {
            $ret[] = $this->_localItemCache[$identity];
        }

        return $ret;
    }


    /**
     * @param null $where
     * @param null $order
     * @param null $offset
     * @return Core_Model_Item_Abstract
     */
    public function fetchRow($where = null, $order = null, $offset = null) {
        $item = parent::fetchRow($where, $order, $offset);
        if ($item && method_exists($item, 'getGuid'))
            Engine_Api::_()->putItemCache($item->getGuid(), $item);
        return $item;
    }


    /**
     * @param null $where
     * @param null $order
     * @param null $count
     * @param null $offset
     * @return Core_Model_Item_Abstract[]
     */
    public function fetchAllKeyed($where = null, $order = null, $count = null, $offset = null)
    {
        $all = $this->fetchAll($where, $order, $count, $offset);
        $keyed = [];
        foreach($all as $row){
            /* @var Core_Model_Item_Abstract $row */
            $keyed[$row->getIdentity()] = $row;
        }
        return $keyed;
    }


    /**
     * @param null $where
     * @param null $order
     * @param null $count
     * @param null $offset
     * @return Engine_Db_Table_Rowset
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $items = parent::fetchAll($where, $order, $count, $offset);
        foreach($items as $item){
            if ($item && method_exists($item, 'getGuid'))
                Engine_Api::_()->putItemCache($item->getGuid(), $item);
        }
        return $items->rewind(0);
    }
}