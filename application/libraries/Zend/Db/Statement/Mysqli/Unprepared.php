<?php
/**
 * ENGINE
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Statement
 */

/**
 * @see Zend_Db_Statement_Mysqli
 */
// require_once 'Zend/Db/Statement/Mysqli.php';



/**
 * Extends for Mysqli (unprepared)
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Statement
 */
class Zend_Db_Statement_Mysqli_Unprepared extends Zend_Db_Statement_Mysqli
{
    /**
     * The unprepared sql string.
     *
     * @var string
     */
    protected $_sql_unprepared;
    
    /**
     * @param  string $sql
     * @return void
     */
    public function _prepare($sql)
    {
        // @todo this will not cause an error here since its not being prepared
        $this->_sql_unprepared = $sql;
        $this->_stmt = null;
        
        /*
        $mysqli = $this->_adapter->getConnection();
        $this->_stmt = $mysqli->prepare($sql);

        if ($this->_stmt === false || $mysqli->errno) {
            // require_once 'Zend/Db/Statement/Mysqli/Exception.php';
            throw new Zend_Db_Statement_Mysqli_Exception("Mysqli prepare error: " . $mysqli->error);
        }
        */
    }

    /**
     * Closes the cursor and the statement.
     *
     * @return bool
     */
    public function close()
    {
      if( $this->_meta )
      {
        $r = $this->_meta->close();
        $this->_meta = null;
        return $r;
      }
      return false;
    }

    /**
     * Closes the cursor, allowing the statement to be executed again.
     *
     * @return bool
     */
    public function closeCursor()
    {
      if( $this->_meta )
      {
        $r = $this->_meta->close();
        $this->_meta = null;
        return $r;
      }
      return false;
    }

    /**
     * Returns the number of columns in the result set.
     * Returns null if the statement has no result set metadata.
     *
     * @return int The number of columns.
     */
    public function columnCount()
    {
        if (isset($this->_meta) && $this->_meta) {
            return $this->_meta->field_count;
        }
        return 0;
    }

    /**
     * Retrieves the error code, if any, associated with the last operation on
     * the statement handle.
     *
     * @return string error code.
     */
    public function errorCode()
    {
        // Does not affect unprepared statements
        return false;
    }

    /**
     * Retrieves an array of error information, if any, associated with the
     * last operation on the statement handle.
     *
     * @return array
     */
    public function errorInfo()
    {
        if (!$this->_meta) {
            return false;
        }

        $mysqli = $this->_adapter->getConnection();

        return array(
            null,
            $mysqli->errno,
            $mysqli->error,
        );
    }

    /**
     * Executes a prepared statement.
     *
     * @param array $params OPTIONAL Values to bind to parameter placeholders.
     * @return bool
     * @throws Zend_Db_Statement_Mysqli_Exception
     */
    public function _execute(array $params = null)
    {
        $mysqli = $this->_adapter->getConnection();
        $sql = $this->_sql_unprepared;

        // if no params were given as an argument to execute(),
        // then default to the _bindParam array
        if ($params === null) {
            $params = $this->_bindParam;
        }

        // send $params as input parameters to the statement
        if ($params) {
            // @todo force typing using param junk?
            //array_unshift($params, str_repeat('s', count($params)));
            //array_unshift($params);
            //var_dump($params);
            $sql = $this->_adapter->quoteInto($sql, $params);
        }

        // execute the statement
        $retval = $mysqli->real_query($sql);
        if ($retval === false) {
            /**
             * @see Zend_Db_Statement_Mysqli_Exception
             */
            // require_once 'Zend/Db/Statement/Mysqli/Exception.php';
            throw new Zend_Db_Statement_Mysqli_Exception("Mysqli statement (unprepared) execute error : " . $mysqli->error);
        }

        // store metadata (note this also contains the result set for unprepared statements unfortunately)
        $this->_meta = $mysqli->store_result();

        // statements that have no result set do not return metadata
        if ($this->_meta !== false) {

            // get the column names that will result
            // @todo make sure we don't need this
            $this->_keys = array();
            foreach ($this->_meta->fetch_fields() as $col) {
                $this->_keys[] = $this->_adapter->foldCase($col->name);
            }

            // set up a binding space for result variables
            $this->_values = array_fill(0, count($this->_keys), null);
        }
        return $retval;
    }


    /**
     * Fetches a row from the result set.
     *
     * @param int $style  OPTIONAL Fetch mode for this fetch operation.
     * @param int $cursor OPTIONAL Absolute, relative, or other.
     * @param int $offset OPTIONAL Number for absolute or relative cursors.
     * @return mixed Array, object, or scalar depending on fetch mode.
     * @throws Zend_Db_Statement_Mysqli_Exception
     */
    public function fetch($style = null, $cursor = null, $offset = null)
    {
      if( !$this->_meta ) {
        return false;
      }

      // make sure we have a fetch mode
      if ($style === null) {
          $style = $this->_fetchMode;
      }

      // Get the next result
      $row = false;
      switch ($style) {
        case Zend_Db::FETCH_NUM:
          $row = $this->_meta->fetch_row();
          break;
        case Zend_Db::FETCH_ASSOC:
          $row = $this->_meta->fetch_assoc();
          break;
        case Zend_Db::FETCH_BOTH:
          $row = $this->_meta->fetch_array();
          break;
        case Zend_Db::FETCH_OBJ:
          $row = $this->_meta->fetch_object();
          break;
        case Zend_Db::FETCH_BOUND:
          // Basically the same as fetch_both for now 
          $row = $this->_meta->fetch_array();
          break;
        default:
          /**
           * @see Zend_Db_Statement_Mysqli_Exception
           */
          // require_once 'Zend/Db/Statement/Mysqli/Exception.php';
          throw new Zend_Db_Statement_Mysqli_Exception("Invalid fetch mode '$style' specified");
          break;
      }

      // End of data (or error)
      if( !$row ) {
        return $row;
      }

      // Special case for FETCH_BOUND
      if( $style==Zend_Db::FETCH_BOUND ) {
          return $this->_fetchBound($row);
      }
      
      return $row;
    }
}
?>
