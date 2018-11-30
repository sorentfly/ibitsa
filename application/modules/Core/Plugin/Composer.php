<?

/**
 *
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 */
class Core_Plugin_Composer extends Core_Plugin_Abstract
{
    public function onAttachLink($data)
    {
        try {
            $viewer = Engine_Api::_()->user()->getViewer();
            if( Engine_Api::_()->core()->hasSubject() ) {
                $subject = Engine_Api::_()->core()->getSubject();
                if( $subject->getType() != 'user' ) {
                    $data['parent_type'] = $subject->getType();
                    $data['parent_id'] = $subject->getIdentity();
                }
            }

            // Filter HTML
            $filter = new Zend_Filter();
            $filter->addFilter(new Engine_Filter_Censor());
            $filter->addFilter(new Engine_Filter_HtmlSpecialChars());
            if( !empty($data['title']) ) {
                $data['title'] = $filter->filter($data['title']);
            }
            if( !empty($data['description']) ) {
                $data['description'] = $filter->filter($data['description']);
            }

            $link = Engine_Api::_()->getApi('links', 'core')->createLink($viewer, $data);
        } catch( Exception $e ) {
            throw $e;
            return;
        }
        return $link;
    }
}