<?

/**
 * Table model to rewrite an initial REQUEST_URI
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 */
class Core_Model_DbTable_Urlrewrites extends Core_Model_Item_DbTable_Abstract
{
    protected $_rowClass = 'Core_Model_Urlrewrite';

    public static function getDomainsPseudos()
    {
        $domains = Engine_Api::_()->core()->getDomainsSettings();
        $pseudos = array();
        foreach($domains as $pseudo=>$domain){
            $pseudos[$domain['domain']] = $pseudo;
        }
        /* NOTE - в таблице можно использовать домены не по спевдониму. НО чтобы на локальном сервере перенаправления проходили успешно на локальный доп-сайт, имея ту-же БД, что на production-сервере - желательно использовать именно псевдонимы. */
        return $pseudos;
    }

    public static function buildURLSerial($URL)
    {
        $urlChunks = explode('/',$URL);
        $urlChunks = array_filter($urlChunks, function($item) { return !empty($item) && trim($item); });//remove empty chunks
        //get list of URL-s which search in DB
        $serial = array();
        foreach($urlChunks as $chunk){
            $prefix = array();
            foreach ($urlChunks as $chunkPrefix){
                if ($chunk===$chunkPrefix) break;
                $prefix[] = '/'.$chunkPrefix;
            }
            $serial[] = implode('', $prefix).'/'.$chunk;
        }
        return count($serial) ? $serial : array('/');
    }

    /* @see \Core_Bootstrap::run */
    /*Парсинг URL-а в том случае, если роут не найден*/
    public function matchRewrite($domain, $requestURI)
    {
        $requestPath = explode('?',$requestURI);
        $requestPath = count($requestPath) ? $requestPath[0] : '';
        $targetSearchUrls = self::buildURLSerial($requestPath);
        $db = $this->getAdapter();
        if (($guid = $db->fetchRow(
                $db->select()->from('engine4_core_urlnamed', ['subject_type', 'subject_id', 'name'])
                    ->where('name IN (?)', array_map(function($v){return ltrim($v, "/");}, $targetSearchUrls))
            )) && ($subject = Engine_Api::_()->getItem($guid['subject_type'], $guid['subject_id']))
        ){
            /* @var Event_Model_Event|Course_Model_Course|User_Model_User|Group_Model_Group $subject */
            return substr_replace($requestURI, $subject->getHref(['_nourlrewrite' => true]), 1, mb_strlen($guid['name']));
        }
        //
        $domainsPseudos = self::getDomainsPseudos();

        $domains = array('', $domain);
        if (isset($domainsPseudos[$domain])){
            $domains[] = $domainsPseudos[$domain];
        }
        //fetch rewrite rule with the biggest url_from, domain-priorited
        $rewriteSelect = $this->select()
            ->where('url_from in (?)', $targetSearchUrls)
            ->where('url_from_domain in (?)', $domains)
            ->where('is_fully_replace_only = 0 OR url_from = ?', end($targetSearchUrls))
            ->order( array(new Zend_Db_Expr('IF(is_fully_replace_only = 1 , 1 ,0 ) DESC'), new Zend_Db_Expr('LENGTH(url_from) DESC'), new Zend_Db_Expr('IF(url_from_domain != \'\' , 1 ,0 ) DESC') ) )
            ->limit(1);
        /* @var Core_Model_Urlrewrite $urlRewrite */
        $urlRewrite = $this->fetchRow($rewriteSelect);
        if ($urlRewrite && end($targetSearchUrls) != $urlRewrite->url_from && $urlRewrite->partial_repalced_only_url_to){
            $urlRewrite->setTargetUrl($urlRewrite->partial_repalced_only_url_to);
        }
        return $urlRewrite;
    }

    protected $_itemHrefRuntimeCache = array();

    /* @see \Engine_Controller_Router_Rewrite::assemble */
    /* Сборка URL объекта */
    public function itemHref($type, $id)
    {
        if (isset($this->_itemHrefRuntimeCache[$type.'_'.$id])){
            return $this->_itemHrefRuntimeCache[$type.'_'.$id];
        }
        $db = $this->getAdapter();
        if ($name = $db->fetchOne(
            $db->select()->from('engine4_core_urlnamed', ['name'])
                ->where('subject_type = ?', $type)->where('subject_id = ?', $id)
        )){
            return $this->_itemHrefRuntimeCache[$type.'_'.$id] = '/' . $name;
        }
        $domain = $_SERVER['HTTP_HOST'];
        $domainsPseudos = self::getDomainsPseudos();
        $domains = array('', $domain);
        if (isset($domainsPseudos[$domain])){
            $domains[] = $domainsPseudos[$domain];
        }

        $hrefSelect = $this->select()
            ->where('subject_type = ?', $type)
            ->where('subject_id = ?', $id)
            ->where('url_from_domain in (?)', $domains)
            ->order(new Zend_Db_Expr('LENGTH(url_from) ASC'))//when trying to get item href - get shortest URL
            ->limit(1);
        $rewrite = $this->fetchRow($hrefSelect);
        $rewrite = $rewrite ? $rewrite->url_from : null;

        return $this->_itemHrefRuntimeCache[$type.'_'.$id] = $rewrite;
    }
}