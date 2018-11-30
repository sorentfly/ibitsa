<?php
/**
 * Abitu
 *
 * @author     Goncharov ( sunnydayd@mail.ru )
 * @description роутер, наследующийся от реврайченого - позволяет получать по роутам профиля короткие ссылки. 
 *              Для изучающих SE - надо заметить, что Engine_Controller_Router_Rewrite->assemble используется при построении всех ссылок ( $item->getHref , $view->url )
 */
class Engine_Controller_Router_Rewrite extends Zend_Controller_Router_Rewrite
{
    /* @var Core_Model_DbTable_Urlrewrites */
    protected $_urlRewriteTable = null;
    public function getItemHrefRewrite()
    {
        if (!$this->_urlRewriteTable){
            $this->_urlRewriteTable = Engine_Api::_()->getItemTable('urlrewrite');
        }
        return array(
            'user_profile' => array('itemType'=>'user', 'itemIdParam'=>'id'),
            'event_profile' => array('itemType'=>'event', 'itemIdParam'=>'id'),
            'group_profile' => array('itemType'=>'group', 'itemIdParam'=>'id'),
            'folder_profile' => array('itemType'=>'folder', 'itemIdParam'=>'folder_id'),
            'folder_attachment_profile' => array('itemType'=>'folder_attachment', 'itemIdParam'=>'attachment_id'),
            'video_view' => array('itemType'=>'video', 'itemIdParam'=>'video_id'),
            'album_specific' => array('itemType'=>'album', 'itemIdParam'=>'album_id'),
            'article_entry_view' => array('itemType'=>'article', 'itemIdParam'=>'article_id'),
            'olympic_specific' => array('itemType'=>'event', 'itemIdParam'=>'olympic_id'),
            'course_profile' => array('itemType'=>'course', 'itemIdParam'=>'id'),
        );
    }
    public function assemble($userParams, $name = null, $reset = false, $encode = true) {
        if (isset($userParams['_nourlrewrite'])){
            $tryRewrite = false;
            unset($userParams['_nourlrewrite']);
        }else{
            $tryRewrite = true;
        }
        $rewriteRoutes = $this->getItemHrefRewrite();
        
        $href = parent::assemble($userParams, $name, $reset, $encode);
        if ($tryRewrite && isset($rewriteRoutes[$name]) && ($itemId = $userParams[$rewriteRoutes[$name]['itemIdParam']])){
            $rewrite =  $this->_urlRewriteTable->itemHref($rewriteRoutes[$name]['itemType'], $itemId);
            if ($rewrite){
                /*NOTE - $rewrite , это адрес-псевдоним без параметров, соответственно необходимо поддержать параметризацию таким вот хаком  */
                if (count($userParams) > 1){
                    $originalHref = parent::assemble(array($rewriteRoutes[$name]['itemIdParam']=>$itemId), $name, $reset, $encode);
                    $href = $rewrite . mb_substr($href, mb_strlen($originalHref));
                }else{
                    $href = $rewrite;
                }
            }
        }
        return $href;
    }
}