<?

/**
 *
 * @category Application_Core
 * @package Authorization
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 */
class Authorization_Plugin_Core
{
    /**
     * @param Engine_Hooks_Event $event
     */
    public function onItemDeleteBefore(Engine_Hooks_Event $event)
    {
        $payload = $event->getPayload();
        if( $payload instanceof Core_Model_Item_Abstract )
        {
            $table = Engine_Api::_()->getDbtable('allow', 'authorization');
            $table->delete(array(
                'resource_type = ?' => $payload->getType(),
                'resource_id = ?' => $payload->getIdentity(),
            ));
        }
    }
}