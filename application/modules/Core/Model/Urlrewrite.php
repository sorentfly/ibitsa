<?

/**
 * Model of rules for url rewrite
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 */
class Core_Model_Urlrewrite extends Core_Model_Item_Abstract
{
    protected $_searchTriggers = false;

    public function apply($domainFrom, $pathFrom)
    {
        $domainsOriginals = array_flip( Core_Model_DbTable_Urlrewrites::getDomainsPseudos() );
        $domainTo = isset($domainsOriginals[$this->url_to_domain]) ? $domainsOriginals[$this->url_to_domain] :  $this->url_to_domain;
        $isRedirect = $domainTo && $domainFrom != $domainTo || $this->is_redirect;
        if ($this->subject_type && $this->subject_id && ( $subject = Engine_Api::_()->getItem($this->subject_type, $this->subject_id) )){
            $urlTo = $subject->getHref(array('_nourlrewrite'=>true)); /*processing in Engine_Controller_Router_Rewrite */
        }else{
            $urlTo =  $this->getTargetUrl();
        }
        if ($urlTo){
            //reformating $pathFrom url - cleaning of double / or spaces
            $serialPath = Core_Model_DbTable_Urlrewrites::buildURLSerial($pathFrom);
            $cleanedFromPath = end($serialPath);
            //replacing
            $targetURL = $urlTo . mb_substr($cleanedFromPath, mb_strlen($this->url_from));
        }else{
            $targetURL = $pathFrom;
        }

        if ( $isRedirect && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' ){
            /*IGNORE redirect rewrite on ajax*/
            return $pathFrom;
        }
        if ($isRedirect){
            $domainTo = $domainTo ? '//'.$domainTo : '';
            header('Location: ' . $domainTo . $targetURL);
            die();
        }
        return $targetURL;
    }
    protected $_targetUrl = null;
    public function getTargetUrl()
    {
        return $this->_targetUrl ? $this->_targetUrl : $this->url_to;
    }
    public function setTargetUrl($targetUrl)
    {
        $this->_targetUrl = $targetUrl;
    }
}