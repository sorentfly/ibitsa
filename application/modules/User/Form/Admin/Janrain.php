<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Twitter.php 9382 2011-10-14 00:41:45Z john $
 * @author     John Boehr <john@socialengine.com>
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Admin_Janrain extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Janrain Integration')
      ->setDescription('USER_ADMIN_SETTINGS_JANRAIN_DESCRIPTION')
      ->setAttrib('enctype', 'multipart/form-data')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ->setMethod("POST");
      ;

    $description = $this->getTranslator()->translate('USER_ADMIN_SETTINGS_JANRAIN_DESCRIPTION');
    $description = vsprintf($description, array(
      'http://www.janrain.com/products/engage&campaign=socialengine?utm_source=socialengine&utm_medium=partner&utm_campaign=socialenginereferral',
      $this->getView()->url(array('action' => 'janrain-import')),
      'http://support.socialengine.com/questions/213/Admin-Panel-Settings-Janrain-Integration',
    ));
    $this->setDescription($description);

    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);
    
    $this->addElement('Text', 'domain', array(
      'label' => 'Janrain Application Domain',
      'description' => 'In the form username.rpxnow.com',
      'filters' => array(
        'StringTrim',
      ),
    ));
    
    $this->addElement('Text', 'id', array(
      'label' => 'Janrain Application ID',
      'description' => '',
      'filters' => array(
        'StringTrim',
      ),
    ));
    
    $this->addElement('Text', 'key', array(
      'label' => 'Janrain API Key',
      'description' => '',
      'filters' => array(
        'StringTrim',
      ),
    ));
    
    $this->addElement('Radio', 'enable', array(
      'label' => 'Integrate Features',
      'description' => 'What features would you like to integrate?',
      'multiOptions' => array(
        'none'  => 'None',
        'login' => 'Login only',
        'publish' => 'Publish',
      ),
      'value' => 'none'
    ));
    
    $this->addElement('Radio', 'type', array(
      'label' => 'Account Type',
      'description' => 'What type of Janrain account do you have? The ' . 
          'integration will take advantage of the extra features if available.',
      'multiOptions' => array(
        'basic'  => 'Basic',
        'plus' => 'Plus',
        'pro' => 'Pro/Enterprise',
      ),
      'value' => 'basic'
    ));
    
//    $this->addElement('MultiCheckbox', 'providers', array(
//      'label' => 'Selected Providers',
//      'description' => 'If you are using the "basic" or "plus" account type, '
//          . 'please choose no more than six (6) of the providers that you '
//          . 'have configured in '
//          . 'Janrain\'s control panel. This is required to display the '
//          . 'correct icons.',
//      'multiOptions' => array(
//        'facebook' => 'Facebook',
//        'google' => 'Google',
//        'twitter' => 'Twitter',
//        'paypal' => 'PayPal',
//        'yahoo' => 'Yahoo!',
//        'linkedin' => 'LinkedIn',
//        'live_id' => 'Windows Live',
//        'salesforce' => 'Salesforce',
//        'foursquare' => 'Foursquare',
//        'orkut' => 'Orkut',
//        'aol' => 'AOL',
//        'blogger' => 'Blogger',
//        'flickr' => 'Flickr',
//        'hyves' => 'Hyves',
//        'livejournal' => 'LiveJournal',
//        'mixi' => 'Mixi',
//        'myopenid' => 'MyOpenID',
//        'myspace' => 'Myspace',
//        'netlog' => 'Netlog',
//        'openid' => 'OpenID',
//        'verisign' => 'Verisign',
//        'vzn' => 'VZ-Netzwerke',
//        'wordpress' => 'Wordpress',
//      ),
//      'value' => array(
//        'google',
//        'yahoo',
//        'aol',
//        'openid',
//      ),
//    ));


    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));

  }
}
