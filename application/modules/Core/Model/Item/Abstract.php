<?

/**
 *
 * * @property string creation_date
 * * @property string modified_date
 *
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 */
abstract class Core_Model_Item_Abstract extends Engine_Db_Table_Row implements Core_Model_Item_Interface
{
    /**
     * @var string The module name of this model (say that 12 times fast)
     */
    protected $_moduleName;

    /**
     * The unique identifier of this instance
     *
     * @var integer|mixed
     */
    protected $_identity;

    /**
     * The resource type, i.e. user, group, etc
     *
     * @var string
     */
    protected $_type;

    /**
     * The short resource type (last class suffix)
     *
     * @var string
     */
    protected $_shortType;

    /**
     * For mixin objects to have local storage
     *
     * @var stdClass
     */
    protected $_store;

    /**
     * List of columns that, when changed, will cause the search indexer to update
     *
     * @var array
     */
    protected $_searchTriggers = array('search', 'title', 'description', 'body');

    /**
     * List of columns that, when changed, will cause the modified_date column to
     * be updated
     *
     * @var array
     */
    protected $_modifiedTriggers = array('title', 'description', 'body',
        'photo_id', 'file_id', 'category_id');

    /**
     * Disable internal hooks?
     * @var boolean
     */
    protected $_disableHooks = false;

    /**
     * Abstract constructor
     *
     * @param array $config
     * @throws Core_Model_Item_Exception
     * @internal param mixed $identity
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        // Get identity
        $primary = $this->getTable()->info(Zend_Db_Table_Abstract::PRIMARY);
        if( count($primary) !== 1 ) {
            throw new Core_Model_Item_Exception(sprintf('Item tables must have only a single primary column, given: %s', join(', ', $primary)));
        }
        $prop = array_shift($primary);
        if( !isset($this->$prop) ) {
            //throw new Core_Model_Item_Exception(sprintf('Primary column "%s" not defined', $prop));
            $thisarr = $this->toArray();
            $this->_identity = reset($thisarr);
        } else if( isset($this->$prop) ) {
            $this->_identity = $this->$prop;
        }

        // Get store
        $this->_store = new stdClass();

        // Backwards compatibility
        if( isset($this->_searchColumns) && is_array($this->_searchColumns) ) {
            $this->_searchTriggers = $this->_searchColumns;
            unset($this->_searchColumns);
        }
    }

    /**
     * Magic caller
     *
     * @param string $method
     * @param array $args
     * @return void|Zend_Db_Table_Row_Abstract|Zend_Db_Table_Rowset_Abstract
     * @throws Core_Model_Item_Exception
     * @internal param array $arguments
     */
    public function __call($method, array $args)
    {
        throw new Core_Model_Item_Exception(sprintf('Unknown method %s in class %s', $method, get_class($this)));
    }

    /**
     * Get the module this model belongs to
     *
     * @return string The module name of this model
     */
    public function getModuleName()
    {
        if( empty($this->_moduleName) )
        {
            $class = get_class($this);
            if (preg_match('/^([a-z][a-z0-9]*)_/i', $class, $matches)) {
                $prefix = $matches[1];
            } else {
                $prefix = $class;
            }
            $this->_moduleName = $prefix;
        }
        return $this->_moduleName;
    }

    /**
     * Gets the resource type of the current object.
     * User_Model_User -> user
     * Album_Model_Photo -> album_photo
     *
     * @param bool $inflect
     * @return string The type identifier (i.e. user, group, etc)
     */
    public function getType($inflect = false)
    {
        if( null === $this->_type )
        {
            $this->_type = Engine_Api::classToType(get_class($this), $this->getModuleName());
        }

        if( $inflect )
        {
            return str_replace(' ', '', ucwords(str_replace('_', ' ', $this->_type)));
        }

        return $this->_type;
    }

    /**
     * Get a short type (used for id column prefixes)
     * User_Model_User -> user
     * Album_Model_Photo -> photo
     *
     * @param boolean $inflect
     * @return string
     */
    public function getShortType($inflect = false)
    {
        if( null === $this->_shortType )
        {
            $this->_shortType = ltrim(strrchr(strtolower(get_class($this)), '_'), '_');
        }

        if( $inflect )
        {
            return str_replace(' ', '', ucwords(str_replace('_', ' ', $this->_shortType)));
        }

        return $this->_shortType;
    }

    /**
     * Gets the numeric unique identifier for this object
     *
     * @return integer|mixed
     */
    public function getIdentity()
    {
        return (int) $this->_identity;
    }

    /**
     * Gets a globally unique identitfier
     *
     * @param bool $asArray Return guid as an array of length two
     * @return string|array The guid
     */
    public function getGuid($asArray = false)
    {
        if( $asArray )
        {
            return array($this->getType(), $this->getIdentity());
        }

        else
        {
            return sprintf('%s_%d', $this->getType(), $this->getIdentity());
        }
    }

    /**
     * Gets an absolute URL to this resource
     *
     * @return string The URL
     */
    public function getHref()
    {
        return null;
        //throw new Core_Model_Item_Exception('getHref must be defined in child classes');
    }

    /**
     * Gets the title of the item. This would be a name for users
     *
     * @return string The title
     */
    public function getTitle()
    {
        if( isset($this->title) )
        {
            return $this->title;
        }
        return null;
    }

    /**
     * Gets a url slug for this item, based on it's title
     *
     * @param null $str
     * @param int $maxStrLen
     * @return string The slug
     */
    public function getSlug($str = null, $maxStrLen = 64)
    {
        if( null === $str ) {
            $str = $this->getTitle();
        }
        if( strlen($str) > $maxStrLen ) {
            $str = Engine_String::substr($str, 0, $maxStrLen);
        }

        $search  = array('À','Á','Â','Ã','Ä','Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ð','Ñ','Ò','Ó','Ô','Õ','Ö','Ø','Ù','Ú','Û','Ü','Ý','ß','à','á','â','ã','ä','å','æ','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ø','ù','ú','û','ü','ý','ÿ','Ā','ā','Ă','ă','Ą','ą','Ć','ć','Ĉ','ĉ','Ċ','ċ','Č','č','Ď','ď','Đ','đ','Ē','ē','Ĕ','ĕ','Ė','ė','Ę','ę','Ě','ě','Ĝ','ĝ','Ğ','ğ','Ġ','ġ','Ģ','ģ','Ĥ','ĥ','Ħ','ħ','Ĩ','ĩ','Ī','ī','Ĭ','ĭ','Į','į','İ','ı','Ĳ','ĳ','Ĵ','ĵ','Ķ','ķ','Ĺ','ĺ','Ļ','ļ','Ľ','ľ','Ŀ','ŀ','Ł','ł','Ń','ń','Ņ','ņ','Ň','ň','ŉ','Ō','ō','Ŏ','ŏ','Ő','ő','Œ','œ','Ŕ','ŕ','Ŗ','ŗ','Ř','ř','Ś','ś','Ŝ','ŝ','Ş','ş','Š','š','Ţ','ţ','Ť','ť','Ŧ','ŧ','Ũ','ũ','Ū','ū','Ŭ','ŭ','Ů','ů','Ű','ű','Ų','ų','Ŵ','ŵ','Ŷ','ŷ','Ÿ','Ź','ź','Ż','ż','Ž','ž','ſ','ƒ','Ơ','ơ','Ư','ư','Ǎ','ǎ','Ǐ','ǐ','Ǒ','ǒ','Ǔ','ǔ','Ǖ','ǖ','Ǘ','ǘ','Ǚ','ǚ','Ǜ','ǜ','Ǻ','ǻ','Ǽ','ǽ','Ǿ','ǿ');
        $replace = array('A','A','A','A','A','A','AE','C','E','E','E','E','I','I','I','I','D','N','O','O','O','O','O','O','U','U','U','U','Y','s','a','a','a','a','a','a','ae','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','o','u','u','u','u','y','y','A','a','A','a','A','a','C','c','C','c','C','c','C','c','D','d','D','d','E','e','E','e','E','e','E','e','E','e','G','g','G','g','G','g','G','g','H','h','H','h','I','i','I','i','I','i','I','i','I','i','IJ','ij','J','j','K','k','L','l','L','l','L','l','L','l','l','l','N','n','N','n','N','n','n','O','o','O','o','O','o','OE','oe','R','r','R','r','R','r','S','s','S','s','S','s','S','s','T','t','T','t','T','t','U','u','U','u','U','u','U','u','U','u','U','u','W','w','Y','y','Y','Z','z','Z','z','Z','z','s','f','O','o','U','u','A','a','I','i','O','o','U','u','U','u','U','u','U','u','U','u','A','a','AE','ae','O','o');
        $str = str_replace($search, $replace, $str);

        $str = preg_replace('/([a-z])([A-Z])/', '$1 $2', $str);
        $str = strtolower($str);
        $str = preg_replace('/[^a-z0-9-]+/i', '-', $str);
        $str = preg_replace('/-+/', '-', $str);
        $str = trim($str, '-');
        if( !$str ) {
            $str = '-';
        }
        return $str;
    }

    /**
     * Gets the description of the item. This might be about me for users
     *
     * @return string The description
     */
    public function getDescription()
    {
        if( isset($this->description) )
        {
            return $this->description;
        }
        return '';
    }

    /**
     * Gets keywords for this item, should be overridden
     *
     * @return string
     */
    public function getKeywords()
    {
        if( isset($this->keywords) )
        {
            return $this->keywords;
        }
        return '';
    }

    /**
     * Gets rich HTML content (i.e. video object src) for feed and other things
     *
     * @return string|null
     */
    public function getRichContent()
    {
        return null;
    }

    /**
     * Get the date this item was created
     *
     * @return integer The creation date
     */
    public function getCreationDate()
    {
        return $this->creation_date;
    }

    /**
     * Get the date this item was last modifier
     *
     * @return integer The last modified date
     */
    public function getModificationDate()
    {
        return $this->modified_date;
    }

    /**
     * Gets an item that defines the authorization permissions, usually the item
     * itself
     *
     * @return Core_Model_Item_Abstract
     */
    public function getAuthorizationItem()
    {
        return $this;
    }

    /**
     * Возвращает статус разрешения - разрешено ли взаимодействовать пользователю/контексту $role с текущим объектом по действию $action.
     * При возврате Authorization_Api_Core::LEVEL_IGNORE - выполняется стандартная логика проверки прав из Authorization_Api_Core->isAllowed.
     * Метод позволяет сделать мануальную логику обработки прав - при его переопределении с возвратом чего-то отличного от Authorization_Api_Core::LEVEL_IGNORE,
     *    вся логика работы прав (вызовы Authorization_Api_Core->isAllowed ) будут проксироваться на этот метод.
     *
     * @param $role string|User_Model_User
     * @param $action string (view, edit, comment, etc)
     *
     * @return Authorization_Api_Core::LEVEL_* constant
     */
    public function isAllowed($role, $action = 'view')
    {
        return Authorization_Api_Core::LEVEL_IGNORE;
    }

    public function getPhotoFileId()
    {
        if( empty($this->photo_id) ) {
            return null;
        }
        return $this->photo_id;
    }
    /**
     * Gets a url to the current photo representing this item. Return null if none
     * set
     *
     * @param string The photo type (null -> main, thumb, icon, etc);
     * @return string The photo url
     */
    public function getPhotoUrl($type = null)
    {
        $photoId = $this->getPhotoFileId();
        if( empty($photoId) ) {
            return null;
        }

        $file = Engine_Api::_()->getItemTable('storage_file')->getFile($photoId, $type);
        if( !$file ) {
            return null;
        }

        return $file->map();
    }

    public function getAutosizedBackground($type = null, $nophotoImage = DEFAULT_NOPHOTO, $noScale = false)
    {
        $photoId = $this->getPhotoFileId();
        $photoURL = $nophotoImage;
        $isNoPhoto = true;
        $scale = 'h';
        $file = null;
        if( !empty($photoId) ) {
            $file = Engine_Api::_()->getItemTable('storage_file')->getFile($photoId, $type);
            if( $file ) {
                $photoURL = $file->map();
                $isNoPhoto = false;
                if ($file->height > $file->width){
                    $scale = 'v';
                }
            }
        }



        $bgstyle = "background-image: url(". $photoURL .");background-position:center;background-repeat:no-repeat;". ($isNoPhoto && $this->getType() != 'user' ? 'opacity:0.4;' : '' );
        $scalestyle = '';
        if (!$noScale){
            $scalestyle = $scale=='h' ? "background-size: auto 100%;" : "background-size: 100% auto;";
            if ($file && !$file->height && !$file->width){
                $scalestyle .= "background-position:center;";
            }
        }
        return $bgstyle.$scalestyle;
    }

    public function getBannerImage()
    {
        $bannerUrl = '';
        if (!empty($this->banner_id)){
            $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->banner_id);
            $bannerUrl = $file ? $file->map() : '';
        }
        if (!$bannerUrl && isset($this->parent_id) && ($parent = $this->getParent()) && !$parent->isSelf($this)){
            return $parent->getBannerImage();
        }
        return $bannerUrl;
    }
    /**
     * Checks if this item is searchable
     *
     * @return bool
     */
    public function isSearchable()
    {
        return !empty($this->_searchTriggers) && is_array($this->_searchTriggers);
    }

    /** Virtual function to enable swap search regulator from $this is_blocked and display flags will get from IT
     * @return Core_Model_Item_Abstract
     */
    public function getSearchRegulator()
    {
        return $this;
    }


    /**
     * Get data to be indexed for search, but not displayed to the user
     *
     * @return string
     */
    public function getHiddenSearchData()
    {
        return '';
    }

    /**
     * Get a generic media type. Values:
     * audio, image, video, news, blog
     *
     * @return string
     */
    public function getMediaType()
    {
        $type = $this->getType();
        if( strpos($type, 'photo') !== false ) {
            return 'image';
        } else if( strpos($type, 'video') !== false ) {
            return 'video';
        } else if( strpos($type, 'blog') !== false ) {
            return 'blog';
        } else if( strpos($type, 'link') !== false ) {
            return 'link';
        } else if( strpos($type, 'poll') !== false ) {
            return 'poll';
        } else if( strpos($type, 'event') !== false ) {
            return 'event';
        } else if( strpos($type, 'group') !== false ) {
            return 'group';
        } else if( strpos($type, 'forum') !== false ) {
            return 'forum';
        } else if( strpos($type, 'activity_action') !== false ) {
            return 'post';
        } else {
            return 'item';
        }
    }

    /**
     * Can this item own various types of content?
     * Examples:
     *   user
     *   event
     *   group
     *
     * @return boolean
     */
    public function isContentParent()
    {
        return !empty($this->_isContentParent);
    }



    // Meta

    /**
     * Gets the primary table model associated with this class.
     *
     * @return Zend_Db_Table_Abstract
     */
    public function getTable()
    {
        return $this->_getTable();
    }

    /**
     *
     * @return Zend_Db_Table_Abstract
     */
    protected function _getTable()
    {
        if( null === $this->_table )
        {
            $this->_table = Engine_Api::_()->getItemTable($this->getType());
        }
        return $this->_table;
    }

    /**
     * Sets the primary table model associated with this class
     *
     * @param Zend_Db_Table_Abstract $table
     * @return Core_Model_Item_Abstract
     */
    public function setTable(Zend_Db_Table_Abstract $table = null)
    {
        $this->_table = $table;
        return $this;
    }




    // Internal hooks

    /**
     * Disable hooks. Sometimes required to prevent infinite loops in hooks.
     *
     * @param bool $flag
     * @return self
     */
    public function disableHooks($flag = true)
    {
        $this->_disableHooks = (bool) $flag;
        return $this;
    }

    /**
     * Pre-insert hook. If overridden, should be called at end of function.
     *
     * @return void
     */
    protected function _insert()
    {
        if( $this->_disableHooks ) return;

        parent::_insert();

        if( isset($this->creation_date) ) {
            $this->creation_date = date('Y-m-d H:i:s');
        }

        // Should updated be initialized on creation or be left null?
        if( isset($this->modified_date) ) {
            $this->modified_date = date('Y-m-d H:i:s');
        }

        Engine_Hooks_Dispatcher::getInstance()
            ->callEvent('on'.$this->getType(true).'CreateBefore', $this);
        Engine_Hooks_Dispatcher::getInstance()
            ->callEvent('onItemCreateBefore', $this);
    }

    /**
     * Post-insert hook. If overridden, should be called at end of function.
     * @return void
     * @throws Core_Model_Item_Exception
     */
    protected function _postInsert()
    {
        if( $this->_disableHooks ) return;

        parent::_postInsert();

        // Get identity
        $primary = $this->getTable()->info(Zend_Db_Table_Abstract::PRIMARY);
        if( count($primary) !== 1 ) {
            throw new Core_Model_Item_Exception(sprintf('Item tables must have only a single primary column, given: %s', join(', ', $primary)));
        }
        $prop = array_shift($primary);
        if( !isset($this->$prop) ) {
            throw new Core_Model_Item_Exception(sprintf('Primary column "%s" not defined', $prop));
        } else if( isset($this->$prop) ) {
            $this->_identity = $this->$prop;
        }

        //Go hooks
        Engine_Hooks_Dispatcher::getInstance()
            ->callEvent('on'.$this->getType(true).'CreateAfter', $this);

        Engine_Hooks_Dispatcher::getInstance()
            ->callEvent('onItemCreateAfter', $this);

        // Search indexer
        if( $this->isSearchable() &&
            is_array($this->_searchTriggers)  ) {
            // Index
            Engine_Api::_()->getApi('search', 'core')->index($this);
        }
    }

    /**
     * Pre-update hook. If overridden, should be called at end of function.
     *
     * @return void
     */
    protected function _update()
    {
        if( $this->_disableHooks ) return;

        Engine_Hooks_Dispatcher::getInstance()
            ->callEvent('on'.$this->getType(true).'UpdateBefore', $this);
        Engine_Hooks_Dispatcher::getInstance()
            ->callEvent('onItemUpdateBefore', $this);

        // Update modified
        if( is_array($this->_modifiedTriggers) &&
            isset($this->modified_date) &&
            empty($this->_modifiedFields['modified_date']) && // Prevents modified_date from being overwritten here
            count(array_intersect_key((array)@$this->_modifiedFields, array_flip($this->_modifiedTriggers))) > 0 ) {
            $this->modified_date = date('Y-m-d H:i:s'); //new Zend_Db_Expr('NOW()');
        }

        parent::_update();
    }

    /**
     * Post-insert hook. If overridden, should be called at end of function.
     *
     * @return void
     */
    protected function _postUpdate()
    {
        if( $this->_disableHooks ) return;

        parent::_postUpdate();

        Engine_Hooks_Dispatcher::getInstance()
            ->callEvent('on'.$this->getType(true).'UpdateAfter', $this);
        Engine_Hooks_Dispatcher::getInstance()
            ->callEvent('onItemUpdateAfter', $this);

        // Search indexer
        if( !$this->isSearchable() ) {
            // De-index
            Engine_Api::_()->getApi('search', 'core')->unindex($this);
        } else if( is_array($this->_searchTriggers) &&
            count(array_intersect_key((array)@$this->_modifiedFields, array_flip($this->_searchTriggers))) > 0 ) {
            // Re-index
            Engine_Api::_()->getApi('search', 'core')->index($this);
        }
    }

    /**
     * Pre-delete hook. If overridden, should be called at end of function.
     *
     * @return void
     */
    protected function _delete()
    {
        if( $this->_disableHooks ) return;

        parent::_delete();

        Engine_Hooks_Dispatcher::getInstance()
            ->callEvent('on'.$this->getType(true).'DeleteBefore', $this);
        Engine_Hooks_Dispatcher::getInstance()
            ->callEvent('onItemDeleteBefore', $this);

        // Unindex from search
        Engine_Api::_()->getApi('search', 'core')->unindex($this);
    }

    /**
     * Post-insert hook. If overridden, should be called at end of function.
     *
     * @return void
     */
    protected function _postDelete()
    {
        if( $this->_disableHooks ) return;

        parent::_postDelete();

        Engine_Hooks_Dispatcher::getInstance()
            ->callEvent('on'.$this->getType(true).'DeleteAfter', array(
                'type' => $this->getType(),
                'identity' => $this->getIdentity()
            ));
        Engine_Hooks_Dispatcher::getInstance()
            ->callEvent('onItemDeleteAfter', array(
                'type' => $this->getType(),
                'identity' => $this->getIdentity()
            ));
    }




    // Misc

    /**
     * Returns the local storage object
     *
     * @param null $spec
     * @return stdClass
     */
    public function store($spec = null)
    {
        return $this->_store;
    }


    // Ownership/Auth

    public function authorization($adapter = null)
    {
        $object = Engine_Api::_()->getApi('core', 'authorization');
        if( null !== $adapter && $object->$adapter ) {
            $object = $object->$adapter;
        }
        return new Engine_ProxyObject($this, $object);
    }

    /**
     * Checks if the passed item has the same guid as the object
     *
     * @param Core_Model_Item_Abstract $item
     * @return bool
     */
    public function isSelf(Core_Model_Item_Abstract $item)
    {
        return ( $item->getGuid() === $this->getGuid() );
    }

    /**
     * Get the parent of this item. The parent is an item this belongs to.
     *
     * @param string (OPTIONAL) $itemType
     * @return Core_Model_Item_Abstract
     * @throws Core_Model_Item_Exception
     */
    public function getParent($recurseType = null)
    {
        if( empty($recurseType) ) $recurseType = null;

        // Parent and owner are same
        if( !empty($this->_parent_is_owner) ) {
            return $this->getOwner($recurseType);
        }

        // Just return self for users
        if( $this->getType() === 'user' ) {
            if( null === $recurseType || $recurseType === 'user' ) {
                return $this;
            } else {
                throw new Core_Model_Item_Exception('Cannot request parent of user of type other than user');
            }
        }

        // Get parent type
        $type = null;
        if( !empty($this->_parent_type) ) { // Local definition
            $type = $this->_parent_type;
        } else if( !empty($this->parent_type) ) { // Db definition
            $type = $this->parent_type;
        } else if( !empty($this->resource_type) ) {
            $type = $this->resource_type;
        }

        if( null === $type || !Engine_Api::_()->hasItemType($type) ) {
            throw new Core_Model_Item_Exception('Unable to determine parent type or parent type doesn\'t exist');
        }

        // Get parent id
        $id = null;
        if( !empty($this->parent_id) ) {
            $id = $this->parent_id;
        } else if( !empty($this->resource_id) ) {
            $id = $this->resource_id;
        } else {
            $short_type = Engine_Api::typeToShort($type, Engine_Api::_()->getItemModule($type));
            $prop = $short_type . '_id';
            if( !empty($this->$prop) ) {
                $id = $this->$prop;
            }
        }

        if( null === $id || !(($parent = Engine_Api::_()->getItem($type, $id)) instanceof Core_Model_Item_Abstract) ||
            !$parent->getIdentity() ) {
            return null;
        }

        if( null !== $recurseType && $parent->getType() != $recurseType ) {
            $newParent = $parent->getParent($recurseType);
            if( $newParent->isSelf($parent) ) {
                throw new Core_Model_Item_Exception('Infinite recursion detected in getOwner()');
            }
            return $newParent;
        }

        return $parent;
    }

    public function getOwner($recurseType = null)
    {
        if( empty($recurseType) ) $recurseType = null;

        // Just return self for users
        if( $this->getType() === 'user' ) {
            if( null === $recurseType || $recurseType === 'user' ) {
                return $this;
            } else {
                throw new Core_Model_Item_Exception('Cannot request owner of user of type other than user');
            }
        }

        // Get owner type
        $type = null;
        if( !empty($this->_owner_type) ) { // Local definition
            $type = $this->_owner_type;
        } else if( !empty($this->owner_type) ) { // Db definition
            $type = $this->owner_type;
        } else {
            $type = 'user';
        }

        if( null === $type ) {
            throw new Core_Model_Item_Exception('No owner type defined and not overriden');
        }
        if( !Engine_Api::_()->hasItemType($type) ) {
            throw new Core_Model_Item_Exception('Unknown owner type: '.$type);
        }

        // Get parent id
        $id = null;
        if( !empty($this->owner_id) ) {
            $id = $this->owner_id;
        } else {
            $short_type = Engine_Api::typeToShort($type, Engine_Api::_()->getItemModule($type));
            $prop = $short_type . '_id';
            if( !empty($this->$prop) ) {
                $id = $this->$prop;
            }
        }

        if( null === $id ) {
            throw new Core_Model_Item_Exception('No owner id defined');
        }
        if( !(($owner = Engine_Api::_()->getItem($type, $id)) instanceof Core_Model_Item_Abstract) ||
            !$owner->getIdentity() ) {
            //throw new Core_Model_Item_Exception('Owner missing');

            //instead of throwing exception return and empty user object, the user model should handle it gracefully
            return Engine_Api::_()->getItem($type, $id);
        }

        if( null !== $recurseType && $owner->getType() != $recurseType ) {
            $newOwner = $owner->getOwner($recurseType);
            if( $newOwner->isSelf($owner) ) {
                throw new Core_Model_Item_Exception('Infinite recursion detected in getOwner()');
            }
            return $newOwner;
        }

        return $owner;
    }

    /**
     * Checks if passed object is the owner. All items default to owning themselves
     *
     * @param Core_Model_Item_Abstract $owner The object to check for ownership
     * @return bool
     */
    public function isOwner(Core_Model_Item_Abstract $owner)
    {
        if( $this->isSelf($owner) ) {
            return true;
        }

        $tmp = $this->getOwner() ;
        return $tmp instanceof Core_Model_Item_Abstract
            ? $tmp->isSelf($owner)
            : false;
    }

    public function getChildren($type, $params = array())
    {
        if( !Engine_Api::_()->hasItemType($type) || empty($this->_children_types) || !in_array($type, $this->_children_types) ) {
            throw new Core_Model_Item_Exception(sprintf('Specified child type doesn\'t exist or not registered as a child type of this item: %s', $type));
        }

        $childTable = Engine_Api::_()->getItemTable($type);
        $childSelect = $this->getChildrenSelect($type, $params);
        return $childTable->fetchAll($childSelect);
    }

    public function getChildrenSelect($type, $params = array())
    {
        if( !Engine_Api::_()->hasItemType($type) || empty($this->_children_types) || !in_array($type, $this->_children_types) ) {
            throw new Core_Model_Item_Exception(sprintf('Specified child type doesn\'t exist or not registered as a child type of this item: %s', $type));
        }

        $childTable = Engine_Api::_()->getItemTable($type);
        $method = 'getChildrenSelectOf'.$this->getType(true);

        if( !method_exists($childTable, $method) ) {
            throw new Core_Model_Item_Exception('Child table doesn\'t support retrieval by parent');
        }

        $childSelect = $childTable->$method($this, $params);;
        if( !($childSelect instanceof Zend_Db_Select) ) {
            throw new Core_Model_Item_Exception('Child table did not return a select object');
        }

        // Throw in some automatic stuff
        if( !empty($params['order']) ) {
            $childSelect->order($params['order']);
        }
        if( !empty($params['limit']) || !empty($params['offset']) ) {
            $childSelect->limit(@$params['limit'], @$params['offset']);
        }

        return $childSelect;
    }



    // Data type convertors

    /**
     * Experimetnal string accessor. Returns an html string representation of the
     * object
     *
     * @param array $attribs
     * @return string
     */
    public function toString($attribs = array())
    {
        $href = $this->getHref();
        $title = $this->getTitle();
        $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
        if (array_key_exists('title', $attribs) && $attribs['title'] === null){
            unset($attribs['title']);
        }
        if( !$href ) {
            return $title;
        } else if( !$view ) {
            return '<a href="'.$href.'">'.$title.'</a>';
        } else {
            return $view->htmlLink($href, $title, $attribs);
        }
    }

    /**
     * Magic Method for {self::toString()}
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->toString();
        } catch( Exception $e ) {
            Zend_Registry::get('Zend_Log')->log($e, Zend_Log::ERR);
            return '';
        }
    }
    public function isEditableBy(User_Model_User $user)
    {
        return $user && $user->getIdentity() && ( $user->level_id == 1 || $user->level_id == 2 || $this->getOwner()->isSelf($user) || Engine_Api::_()->authorization()->isAllowed($this, $user, 'edit') );
    }

    public function isViewableBy(User_Model_User $user)
    {
        if ($user->getIdentity() && $user->level_id <= 2 || $user->isSelf($this) ){
            return true;
        }
        return Engine_Api::_()->authorization()->isAllowed($this, $user, 'view');
    }
    /**
     * Gets an array of data about the object that is safe for sending to untrusted
     * sources (i.e. doesn't contain any passwords, keys, or private settings or
     * information)
     *
     * @return array
     */
    public function toRemoteArray()
    {
        $arr = array(
            'identity' => $this->getIdentity(),
            'type' => $this->getType(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'keywords' => $this->getKeywords(),
            'href' => $this->getHref(),
            'photo' => $this->getPhotoUrl(),
        );
        if( isset($this->creation_date) ) {
            $arr['creation_date'] = $this->creation_date;
        }
        if( isset($this->modified_date) ) {
            $arr['modified_date'] = $this->modified_date;
        }
        return $arr;
    }

    /*Bitsa*/
    public function getTimeView($locale_instance, $style = 'vertical'){
        if (!isset($this->starttime) || !isset($this->endtime)){
            return '';
        }
        $time_start = $locale_instance->toTime($this->starttime,array('size'=>'short'));
        $time_end = $locale_instance->toTime($this->endtime,array('size'=>'short'));
        $endtimevalid = strtotime($this->endtime) > 86400;
        if ($locale_instance->toDate($this->starttime)!=$locale_instance->toDate($this->endtime) && $endtimevalid){
            $startDateString = str_replace('г.', '', $locale_instance->toDate($this->starttime,array('size'=>'long')));
            $endDateString = str_replace('г.', '', $locale_instance->toDate($this->endtime,array('size'=>'long')));
            //
            if ($style == 'vertical'){
                $tmstring = 'C ' . $startDateString . '<BR>по ' . $endDateString;
            }else if ($style == 'horizontal'){
                $tmstring = $startDateString  . ' - ' . $endDateString;
            }
            return $tmstring;
        }else{
            $timeadd = '';
            if ($endtimevalid && (int)str_replace(':','',$time_end) <= (int)str_replace(':','',$time_start) ){
                $timeadd = ' c ' . $time_start;
            }else if ($endtimevalid && ($time_start!='0:00' || $time_end!='0:00'&&$time_end!='23:59')){
                $timeadd = ' ' . $time_start . ' - ' .$time_end;
            }
            return 'Начало ' . $locale_instance->toDate($this->starttime,array('size'=>'long')) . $timeadd;
        }
    }


    public function getAnnotations(User_Model_User $annotationsFor = null)
    {
        $annotationTable = Engine_Api::_()->getDbTable('annotations', 'core');
        $select = $annotationTable->select()
            ->where('resource_type = ?', $this->getType())
            ->where('resource_id = ?', $this->getIdentity());
        if ($annotationsFor){
            $select->where('user_id = ?', $annotationsFor->getIdentity());
        }

        $anotationRows = $select->query()->fetchAll();
        $annotations = [];
        foreach ( $anotationRows as $row ) {
            if ( empty($row['annotation_json']) ) continue;
            $annotation = array('id' => $row['comment_id']);
            $annotation = $annotation + json_decode($row['annotation_json'], true);
            $annotations[] = $annotation;
        }
        return $annotations;
    }

    /**
     *  | Метод позволяет управлять мета-данными столбцов таблицы
     *  . [ таблица форм
     *  .        user_cancelled_ids (ID пользователей, попросивших не беспокоить формой) |
     *  .        school_answered_ids (ID школ, прошедших опрос проекта)
     *  . ]
     *  . [ таблица пользователей
     *  .        school_moderator_ids (ID школ, в которых пользователь числится модератором)
     *  . ]
     *
     * @param $column
     *  | Название столбца для выполнения действия
     * @param $action
     *  | Действие со столбцом
     *  . . добавить новый ID к списку
     *  . . искать по списку
     *  . . удалить из списка
     * @param null $data
     *  | Данные, необходимые для действия
     * @return bool|array
     *  | Результат
     * @throws Exception
     */
    public function manageMeta($column, $action, $data = null)
    {
        $ids = empty($this->$column) ?
            [] :
            array_filter(explode(',', $this->$column), function($elem) {
                return !empty($elem);
            });
        if ($action == 'find' && $data) {
            return $ids == [] ?
                false :
                array_search($data, $ids) !== false;
        } elseif ($action == 'add' && $data) {
            if ($this->manageMeta($column, 'find', $data)) return true;
            $ids[] = $data;
        } elseif ($action == 'remove' && $data) {
            $ids = array_filter($ids, function($v) use ($data) { return $v != $data; });
        } elseif ($action == 'list') {
            return $ids;
        } else {
            throw new Exception('Unknown action or empty $data.');
        }

        $this->$column = trim(implode(',', $ids), ',');
        $this->save();
        return true;
    }
}
