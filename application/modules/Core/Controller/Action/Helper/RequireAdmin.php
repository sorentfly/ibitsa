<?

/**
 *
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 */
class Core_Controller_Action_Helper_RequireAdmin extends Core_Controller_Action_Helper_RequireAbstract
{
    /**
     * @var array
     */
    protected $_errorAction = array('requireadmin', 'error', 'core');

    /**
     * @return bool
     */
    public function checkRequire()
    {
        try
        {
            $viewer = Engine_Api::_()->user()->getViewer();
        }
        catch( Exception $e )
        {
            $viewer = null;
        }

        if ($viewer && $viewer->academyStatus() == 'admin'){
            $MCA = $this->getRequest()->getModuleName() . '_' .
                $this->getRequest()->getControllerName() . '_' .
                $this->getRequest()->getActionName();
            foreach(Engine_Api::_()->zftsh()->getAdminAllowedActions() as $adminAllowedAction){
                if (mb_strpos($MCA, $adminAllowedAction)===0){
                    return true;
                }
            }
        }

        $ret = false;

        $ret = Engine_Api::_()->getApi('core', 'authorization')->isAllowed('admin', null, 'view');

        if( !$ret && APPLICATION_ENV == 'development' && Zend_Registry::isRegistered('Zend_Log') && ($log = Zend_Registry::get('Zend_Log')) instanceof Zend_Log )
        {
            $target = $this->getRequest()->getModuleName() . '.' .
                $this->getRequest()->getControllerName() . '.' .
                $this->getRequest()->getActionName();
            $log->log('Require class ' . get_class($this) . ' failed check for: ' . $target, Zend_Log::DEBUG);
        }

        return $ret;
    }
}