<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Account.php 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Admin_Signup_Account extends Engine_Form
{
  public function init()
  {
    // Get step and step number
    $stepTable = Engine_Api::_()->getDbtable('signup', 'user');
    $stepSelect = $stepTable->select()->where('class = ?', str_replace('_Form_Admin_', '_Plugin_', get_class($this)));
    $step = $stepTable->fetchRow($stepSelect);
    $stepNumber = 1 + $stepTable->select()
      ->from($stepTable, new Zend_Db_Expr('COUNT(signup_id)'))
      ->where('`order` < ?', $step->order)
      ->query()
      ->fetchColumn()
      ;
    $stepString = $this->getView()->translate('Step %1$s', $stepNumber);
    $this->setDisableTranslator(true);


    // Custom
    $this->setTitle($this->getView()->translate('%1$s: Create Account', $stepString));
    
    $settings = Engine_Api::_()->getApi('settings', 'core');


    // Element: username
    $this->addElement('Radio', 'username', array(
      'label' => 'Enable Profile Address?',
      'description' => 'USER_FORM_ADMIN_SIGNUP_USERNAME_DESCRIPTION',
      'multiOptions' => array(
        1 => 'Yes, allow members to choose a profile address.',
        0 => 'No, do not allow profile addresses.'
      ),
      'value' => 1,
    ));

    
    // Element: approve
    $this->addElement('Radio', 'approve', array(
      'label' => 'Auto-approve Members',
      'description' => 'USER_FORM_ADMIN_SIGNUP_APPROVE_DESCRIPTION',
      'multiOptions' => array(
        1 => 'Yes, enable members upon signup.',
        0 => 'No, do not enable members upon signup.'
      ),
      'value' => 1,
    ));

    // Element: terms
    $this->addElement('Radio', 'terms', array(
      'label' => 'Terms of Service',
      'description' => 'USER_FORM_ADMIN_SIGNUP_TERMS_DESCRIPTION',
      'multiOptions' => array(
        1 => 'Yes, make members agree to your terms of service on signup.',
        0 => 'No, members will not be shown a terms of service checkbox on signup.',
      ),
      'value' => 1,
    ));

    // Element: random
    $this->addElement('Radio', 'random', array(
      'label' => 'Generate Random Passwords?',
      'description' => 'USER_FORM_ADMIN_SIGNUP_RANDOM_DESCRIPTION',
      'multiOptions' => array(
        1 => 'Yes, generate random passwords and email to new members.',
        0 => 'No, let members choose their own passwords.',
      ),
      'value' => 0,
    ));

    // Element: verifyemail
    $this->addElement('Radio', 'verifyemail', array(
      'label' => 'Verify Email Address?',
      'description' => 'USER_FORM_ADMIN_SIGNUP_VERIFYEMAIL_DESCRIPTION',
      'multiOptions' => array(
        2 => 'Yes, verify email addresses.',
        1 => 'No, just send members a welcome email',
        0 => 'No, do not email new members.'
      ),
      'value' => 0,
    ));
	
	// Element: Admin Email Notification
    $this->addElement('Radio', 'adminemail', array(
      'label' => 'Notify Admin by email when user signs up?',
      'description' => 'USER_FORM_ADMIN_SIGNUP_NOTIFYEMAIL_DESCRIPTION',
      'multiOptions' => array(
        1 => 'Yes, notify admin by email.',
        0 => 'No, do not notify admin by email.',
      ),
      'value' => 0,
    ));

    // Element: inviteonly
    $this->addElement('Radio', 'inviteonly', array(
      'label' => 'Invite Only?',
      'description' => 'USER_FORM_ADMIN_SIGNUP_INVITEONLY_DESCRIPTION',
      'multiOptions' => array(
        2 => 'Yes, admins and members must invite new members before they can signup.',
        1 => 'Yes, admins must invite new members before they can signup.',
        0 => 'No, disable the invite only feature.',
      ),
      'value' => 0,
    ));

    // Element: checkemail
    $this->addElement('Radio', 'checkemail', array(
      'label' => 'Check Invite Email?',
      'description' => 'USER_FORM_ADMIN_SIGNUP_CHECKEMAIL_DESCRIPTION',
      'multiOptions' => array(
        1 => "Yes, check that a member's email address was invited.",
        0 => "No, anyone with an invite code can signup.",
      ),
      'value' => 1,
    ));

    $this->getElement('inviteonly')->getDecorator('HtmlTag')
        ->setOption('style', 'max-width: 450px;');
    /*
    $this->getElement('terms')->getDecorator('HtmlTag2')->setOption('style', 'border-top:none;clear: right;padding-top:0px;padding-bottom:0px;');


    $check_email->getDecorator('HtmlTag2')->setOption('style', 'border-top:none; clear:right; float:right;');
    
  //        $invite_count->getDecorator('HtmlTag2')->setOption('style', 'border-top:none; clear:right; float:right;');
    $invite_only->getDecorator('HtmlTag2')->setOption('class', 'form-wrapper signup-invite-wrapper');
    $check_email->getDecorator('HtmlTag2')->setOption('class', 'form-wrapper signup-check-wrapper');
    
    $terms->removeDecorator('label');
    $invite_only->removeDecorator('label');

    $check_email->getDecorator('label')->setOption('tagOptions', array('style'=>'padding-right:0px;visibility:hidden;', 'class'=>'form-label'));

    
    $this->addDisplayGroup(array('terms'), 'term_group');
    $this->addDisplayGroup(array('inviteonly', 'checkemail'), 'invite_group');

    $term_group = $this->getDisplayGroup('term_group');
    $invite_group = $this->getDisplayGroup('invite_group');

    $term_group->setLegend("Terms of Service");
    $invite_group->setLegend("Invite Only?");
     *
     */
    
    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));
    $this->populate($settings->getSetting('user_signup'));

  }

}