<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminSettingsController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_AdminSettingsController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    return $this->_helper->redirector->gotoRoute(array(
      'route' => 'admin_default',
      'module' => 'authorization',
      'controller' => 'level',
      'action' => 'edit'
    ));
  }

  public function generalAction()
  {

  }

  public function friendsAction()
  {
    $form = new User_Form_Admin_Settings_Friends();
    $form->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));
    $form->setMethod("POST");
    $this->view->form = $form;
    
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) ) {
      $form->saveValues();
      $form->addNotice('Your changes have been saved.');
    }
  }

  public function facebookAction()
  {
    $form = $this->view->form = new User_Form_Admin_Facebook();
    $form->populate((array) Engine_Api::_()->getApi('settings', 'core')->core_facebook);

    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }
    
    $values = $form->getValues();
    if( empty($values['appid']) || empty($values['secret']) ) {
      $values['appid'] = '';
      $values['secret'] = '';
      $values['enable'] = 'none';
    }

    Engine_Api::_()->getApi('settings', 'core')->core_facebook = $values;
    $form->addNotice('Your changes have been saved.');
    $form->populate($values);
  }

  public function twitterAction()
  {
    // Get form
    $form = $this->view->form = new User_Form_Admin_Twitter();
    $form->populate((array) Engine_Api::_()->getApi('settings', 'core')->core_twitter);

    // Get classes
    include_once 'Services/Twitter.php';
    include_once 'HTTP/OAuth/Consumer.php';

    if( !class_exists('Services_Twitter', false) ||
        !class_exists('HTTP_OAuth_Consumer', false) ) {
      return $form->addError('Unable to load twitter API classes');
    }

    // Check data
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    $values = $form->getValues();

    if( empty($values['key']) || empty($values['secret']) ) {
      $values['key'] = '';
      $values['secret'] = '';
      $values['enable'] = 'none';
    } else {

      // Try to check credentials
      try {
        $twitter = new Services_Twitter();
        $oauth = new HTTP_OAuth_Consumer($values['key'], $values['secret']);
        //$twitter->setOAuth($oauth);
        $oauth->getRequestToken('http://twitter.com/oauth/request_token');
        $oauth->getAuthorizeUrl('http://twitter.com/oauth/authorize');
        
      } catch( Exception $e ) {
        return $form->addError($e->getMessage());
      }
    }

    // Okay
    Engine_Api::_()->getApi('settings', 'core')->core_twitter = $form->getValues();
    $form->addNotice('Your changes have been saved.');
    $form->populate($values);
  }

  public function janrainAction()
  {
    $form = $this->view->form = new User_Form_Admin_Janrain();
    
    $values = (array) Engine_Api::_()->getApi('settings', 'core')->core_janrain;
    //$values['providers'] = @explode(',', @$values['providers']);
    $form->populate($values);
    if( _ENGINE_ADMIN_NEUTER ) {
      $form->populate(array(
        'id' => '******',
        'key' => '******',
      ));
    }

    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }
    
    $values = $form->getValues();
    
    if( !empty($values['domain']) ) {
      $host = $values['domain'];
      if( false !== strpos($values['domain'], 'http') ) {
        $parts = parse_url($values['domain']);
        $host = $parts['host'];
      }
      $values['domain'] = $host;
      if( empty($values['domain']) ) {
        return $form->addError('Please make sure the domain is in the format: username.rpxnow.com');
      }
      if( preg_match('~^(.+?)\.rpxnow\.com~', $values['domain'], $matches) ) {
        $values['username'] = $matches[1];
      } else {
        $values['username'] = $values['domain'];
      }
    } else {
      $values['username'] = '';
    }
    
    if( empty($values['domain']) && 
        empty($values['id']) && 
        empty($values['key']) ) {
      $values['enable'] = 'none';
    } else if( empty($values['domain']) ) {
      return $form->addError('Please fill in the "Janrain Application Domain" field.');
    } else if( empty($values['id']) ) {
      return $form->addError('Please fill in the "Janrain Application ID" field.');
    } else if( empty($values['key']) ) {
      return $form->addError('Please fill in the "Janrain API Key" field.');
    }
    
//    if( $values['type'] != 'pro' && $values['enable'] != 'none' ) {
//      if( !is_array($values['providers']) ||
//          count($values['providers']) < 1 ) {
//        return $form->addError('Please make sure that at least one provider is selected.');
//      }
//    }
//    if( count($values['providers']) > 6 ) {
//      return $form->addError('Please make sure that no more than six providers are selected.');
//    }
    
    //$values['providers'] = join(',', $values['providers']);
    
    // Pull the providers
    $domainUrl = rtrim((false === strpos($values['domain'], 'http') ? 'https://' : '' ) . $values['domain'], '/');
    if( !empty($values['domain']) ) {
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_URL, $domainUrl . '/api/v2/providers');
      curl_setopt($curl, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($curl, CURLOPT_FAILONERROR, true);
      $result = curl_exec($curl);
      if( !empty($result) ) {
        $result = Zend_Json::decode($result);
      }
      if( is_array($result) && !empty($result['signin']) ) {
        $preferredOrder = array(
          'facebook' => 'Facebook',
          'twitter' => 'Twitter',
          'google' => 'Google',
          'live_id' => 'Windows Live',
          'linkedin' => 'LinkedIn',
          'yahoo' => 'Yahoo!',
          'aol' => 'AOL',
          
          'paypal' => 'PayPal',
          'salesforce' => 'Salesforce',
          'foursquare' => 'Foursquare',
          'orkut' => 'Orkut',
          'blogger' => 'Blogger',
          'flickr' => 'Flickr',
          'hyves' => 'Hyves',
          'livejournal' => 'LiveJournal',
          'mixi' => 'Mixi',
          'myopenid' => 'MyOpenID',
          'myspace' => 'Myspace',
          'netlog' => 'Netlog',
          'openid' => 'OpenID',
          'verisign' => 'Verisign',
          'vzn' => 'VZ-Netzwerke',
          'wordpress' => 'Wordpress',
        );
        $result['signin'] = array_intersect(array_keys($preferredOrder), $result['signin']);
        $values['providers'] = join(',', $result['signin']);
      } else {
        $values['providers'] = 'google,yahoo,aol,openid';
      }
    }

    Engine_Api::_()->getApi('settings', 'core')->core_janrain = $values;
    $form->addNotice('Your changes have been saved.');
    
    
    // Populate again
    //$values['providers'] = explode(',', $values['providers']);
    $form->populate($values);
  }
  
  public function janrainImportAction()
  {
    $this->view->form = $form = new Core_Form_Confirm(array(
      'title' => 'Janrain Import',
      'description' => 'We will now import the member account associations ' .
          'into the Janrain integration. This will allow you to disable ' .
          'the original Facebook/Twitter integrations without your ' .
          'members losing access to their accounts.',
    ));
    
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }
    
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    
    $db = Engine_Db_Table::getDefaultAdapter();
    
    // Get original row count
    $prevCount = current($db->fetchCol('SELECT COUNT(user_id) FROM `engine4_user_janrain`'));
    
    // Import facebook
    $sql = "
      INSERT IGNORE INTO `engine4_user_janrain`
      (SELECT 
        user_id, 
        CONCAT('http://www.facebook.com/profile.php?id=', facebook_uid) as identity, 
        'Facebook' as provider, 
        NULL as token 
      FROM `engine4_user_facebook`)
    ";
    $db->query($sql);
    
    // Import twitter
    $sql = "
      INSERT IGNORE INTO `engine4_user_janrain`
      (SELECT 
        user_id, 
        CONCAT('http://twitter.com/account/profile?user_id=', twitter_uid) as identity, 
        'Twitter' as provider, 
        NULL as token 
      FROM `engine4_user_twitter`)
    ";
    $db->query($sql);
    
    // Get new row count
    $newCount = current($db->fetchCol('SELECT COUNT(user_id) FROM `engine4_user_janrain`'));
    
    
    $this->view->notice = $this->view->translate('%1$d records were ' . 
        'successfully imported. Click ' .
        '<a href="%2$s">here</a> to return.', 
        $newCount - $prevCount,
        $this->view->url(array('action' => 'janrain')));
  }

  public function levelAction()
  {
    return $this->_helper->redirector->gotoRoute(array(
      'module' => 'authorization',
      'controller' => 'level',
      'action' => 'edit'
    ));
  }
}
