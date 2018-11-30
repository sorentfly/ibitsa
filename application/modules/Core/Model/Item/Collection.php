<?

/**
 *
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 */
class Core_Model_Item_Collection extends Core_Model_Item_Abstract implements Countable
{
    protected $_collectible_type;

    protected function getCollectionColumnName()
    {
        if (!empty($this->_collection_column_name)) {
            return $this->_collection_column_name;
        }
        return "collection_id";
    }

    public function getCollectibles($reverse = false)
    {
        $table = Engine_Api::_()->getItemTable($this->_collectible_type);
        $primary = current($table->info("primary"));
        $select = $table->select()
            ->where($this->getCollectionColumnName() . ' = ?', $this->getIdentity());

        if( $reverse )
        {
            $select->order($primary . ' DESC');
        }

        return $select->getTable()->fetchAll($select);
    }

    public function getCollectiblesSelect()
    {
        $table = Engine_Api::_()->getItemTable($this->_collectible_type);
        $orderCol = ( in_array('order', $table->info('cols')) ? 'order' : current($table->info('primary')) );
        $select = $table->select()
            ->where($this->getCollectionColumnName() . ' = ?', $this->getIdentity())
            ->order($orderCol.' ASC');
        return $select;
    }

    public function getCollectiblesPaginator()
    {
        return Zend_Paginator::factory($this->getCollectiblesSelect());
    }

    public function getCollectibleIndex($collectible, $reverse = false)
    {
        if( is_numeric($collectible) )
        {
            $collectible = Engine_Api::_()->getItem($this->_collectible_type, $collectible);
        }
        if( !($collectible instanceof Core_Model_Item_Collectible))
        {
            throw new Core_Model_Item_Exception('Improper argument passed to getNextCollectible');
        }

        if( isset($collectible->collection_index) )
        {
            return $collectible->collection_index;
        }


        $table = $collectible->getTable();
        $col = current($table->info("primary"));
        $select = $table->select()
            ->from($table->info('name'), $col)
            ->where($this->getCollectionColumnName() . ' = ?', $this->getIdentity())
        ;

        // Order supported
        if( isset($collectible->order) )
        {
            $select->order('order ASC');
        }
        // Identity
        else
        {
            $select->order($col.' ASC');
        }

        $i = 0;
        $index = 0;
        //$this->store()->collectible_index = array();
        foreach( $table->fetchAll($select) as $row )
        {
            if( $row->$col == $collectible->getIdentity() )
            {
                $index = $i;
            }
            $i++;
            //$this->store()->collectible_index[$row->$col] = $i++;
        }
        //}

        //$index = $this->store()->collectible_index[$collectible->getIdentity()];
        return ( $reverse ? $this->count() - $index : $index );
    }

    public function getCollectibleByIndex($index = 0, $reverse = false)
    {
        $table = Engine_Api::_()->getItemTable($this->_collectible_type);
        $hasOrder = ( in_array('order', $table->info('cols')));

        // Check index bounds
        $count = $this->count();
        if( $index >= $count )
        {
            $index -= $count;
        }
        else if( $index < 0 )
        {
            $index += $count;
        }

        $select = $table->select()
            ->where($this->getCollectionColumnName() . ' = ?', $this->getIdentity())
            ->limit(1, (int) $index)
        ;

        if( $hasOrder )
        {
            $select->order('order '.($reverse ? 'DESC' : 'ASC'));
        }
        else
        {
            $col = current($table->info("primary"));
            $select->order($col.' '.($reverse ? 'DESC' : 'ASC'));
        }

        $rowset = $table->fetchAll($select);
        if( null === $rowset )
        {
            // @todo throw?
            return null;
        }

        return $rowset->current();
    }

    public function getFirstCollectible()
    {
        return $this->getCollectibleByIndex(0);
    }

    public function getLastCollectible()
    {
        return $this->getCollectibleByIndex(0, true);
    }

    public function getPrevCollectible($collectible)
    {
        return $this->getCollectibleByIndex($this->getCollectibleIndex($collectible)-1);
    }

    public function getNextCollectible($collectible)
    {
        return $this->getCollectibleByIndex($this->getCollectibleIndex($collectible)+1);
    }

    public function count()
    {
        if( isset($this->store()->collectible_count) )
        {
            return $this->store()->collectible_count;
        }

        $table = Engine_Api::_()->getItemTable($this->_collectible_type);
        $select = new Zend_Db_Select($table->getAdapter());
        $select->from($table->info('name'), new Zend_Db_Expr('COUNT(*) as count'))
            ->where($this->getCollectionColumnName() . " = ?", $this->getIdentity());
        $data = $table->getAdapter()->fetchRow($select);

        return $this->store()->collectible_count = (int) $data['count'];;
    }

    public function getCount()
    {
        trigger_error('Deprecated', E_USER_WARNING);
        return $this->count();
    }

    protected function _delete()
    {
        foreach( $this->getCollectibles() as $collectible )
        {
            $collectible->delete();
        }
        parent::_delete();
        return;
    }


    // Order stuff

    public function getHighestOrder()
    {
        $table = Engine_Api::_()->getItemTable($this->_collectible_type);
        if( !in_array('order', $table->info('cols')) )
        {
            throw new Core_Model_Item_Exception('Unable to use order as order column doesn\'t exist');
        }

        $select = new Zend_Db_Select($table->getAdapter());
        $select
            ->from($table->info('name'), new Zend_Db_Expr('MAX(`order`) as max_order'))
            ->where($this->getCollectionColumnName() . ' = ?', $this->getIdentity())
        ;

        $data = $select->query()->fetch();
        $next = (int) @$data['max_order'];
        return $next;
    }

    public function setOrders($ids = null, $consecutive = true)
    {
        $table = Engine_Api::_()->getItemTable($this->_collectible_type);
        if( !in_array('order', $table->info('cols')) )
        {
            throw new Core_Model_Item_Exception('Unable to use order as order column doesn\'t exist');
        }

        $ids = (array) $ids;
        if( empty($ids) ) $consecutive = false;
        $col = current($table->info('primary'));
        $select = $table->select()
            ->where($this->getCollectionColumnName() . ' = ?', $this->getIdentity())
            ->order('order ASC')
        ;

        // Only re-ordering a chunk
        if( $consecutive && !empty($ids) )
        {
            $select->where($col.' IN(?)', $ids);
        }

        $collectibles = $table->fetchAll($select);

        // Non-consecutive unspecified
        if( empty($ids) )
        {
            $i = 0;
            foreach( $collectibles as $collectible )
            {
                $collectible->order = $i++;
                $collectible->save();
            }
        }

        // specified non-consecutive
        else if( $consecutive )
        {
            $i = count($ids);
            foreach( $table->fetchAll($select) as $collectible )
            {
                $order = array_search($collectible->getIdentity(), $ids);
                if( !$order )
                {
                    $order = $i++;
                }
                $collectible->order = $order;
                $collectible->save();
            }
        }

        // specified consecutive
        else
        {
            // Build index
            $orderIndex = array();
            foreach( $collectibles as $collectible )
            {
                $orderIndex[] = $collectible->order;
            }
            sort($orderIndex);

            foreach( $collectibles as $collectible )
            {
                $index = array_search($collectible->getIdentity(), $ids);
                $collectible->order = $orderIndex[$index];
                $collectible->save();
            }
        }

        return $this;
    }
}