<?

/**
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 *
 */
class Core_IndexController extends Core_Controller_Action_Standard
{
    /**
     * @var
     */
    private $settings;

    /**
     *
     */
    public function init()
    {
        die('init');
        #$this->settings = Engine_Api::_()->getApi('settings', 'core');
    }

    /**
     *
     */
    public function indexAction()
    {
        #$this->_helper->content->setNoRender()->setEnabled();
        die('loil');
    }
}