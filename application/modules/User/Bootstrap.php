<?

/**
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Bootstrap extends Engine_Application_Bootstrap_Abstract
{
    public function __construct($application)
    {
        parent::__construct($application);

        // Add view helper and action helper paths
        $this->initViewHelperPath();
        $this->initActionHelperPath();

        // Get viewer
        $viewer = Engine_Api::_()->user()->getViewer();

        // Check if they were disabled
        if( $viewer->getIdentity() && !$viewer->enabled ) {
            Engine_Api::_()->user()->getAuth()->clearIdentity();
            Engine_Api::_()->user()->setViewer(null);
        }

        if (!$viewer->getIdentity()){
            Zend_Registry::get('Zend_View')->headTranslate([
                'Registration', 'Signup complete!'
            ]);
        }

        // Check user online state
        $table = Engine_Api::_()->getDbtable('online', 'user');
        $table->check($viewer);
    }
}
