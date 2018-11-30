<?

/**
 *
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 */
class Authorization_Bootstrap extends Engine_Application_Bootstrap_Abstract
{
    public function __construct($application)
    {
        parent::__construct($application);
        $this->initActionHelperPath();
    }
}
