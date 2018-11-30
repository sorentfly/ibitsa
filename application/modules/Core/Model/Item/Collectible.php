<?

/**
 *
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 */
abstract class Core_Model_Item_Collectible extends Core_Model_Item_Abstract
{

    protected $_collection_type;

    protected $_collection_column_name = 'category_id';


    public function getCollectionIndex()
    {
        return $this->getCollection()->getCollectibleIndex($this);
    }

    /**
     * @return mixed
     */
    public function getNextCollectible()
    {
        return $this->getCollection()->getNextCollectible($this);
    }

    /**
     * @return mixed
     */
    public function getPrevCollectible()
    {
        return $this->getCollection()->getPrevCollectible($this);
    }

    /**
     * @throws Exception
     */
    public function moveUp()
    {
        $table = $this->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();
        try
        {
            $last = $this->getPrevCollectible();
            $temp = $this->order;
            $this->order = $last->order;
            $last->order = $temp;
            $this->save();
            $last->save();
            $db->commit();
        }
        catch (Exception $e)
        {
            $db->rollBack();
            throw $e;
        }
    }


    /**
     * @return Core_Model_Item_Abstract|Core_Model_Item_Collection
     * @throws Core_Model_Item_Exception
     */
    public function getCollection()
    {
        if( !isset($this->collection_id) )
        {
            throw new Core_Model_Item_Exception('If column with collection_id not defined, must override getCollection()');
        }

        return Engine_Api::_()->getItem($this->_collection_type, $this->collection_id);
    }


    # Internal hook

    /**
     * Insert hook
     */
    protected function _insert()
    {
        $collection = $this->getCollection();
        if( $collection && isset($collection->collectible_count) )
        {
            $collection->collectible_count++;
            $collection->save();
        }
        parent::_insert();
    }

    /**
     * Delete hook
     */
    protected function _delete()
    {
        // @todo problems may occur if this is getting deleted with parent
        $collection = $this->getCollection();
        if( $collection && isset($collection->collectible_count) )
        {
            $collection->collectible_count--;
            $collection->save();
        }

        parent::_delete();
    }
}