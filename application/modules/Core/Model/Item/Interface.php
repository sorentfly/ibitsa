<?

/**
 *
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 */
interface Core_Model_Item_Interface
{
    public function getType();

    public function getIdentity();

    public function getTitle();

    public function getDescription();

    public function getHref();
}