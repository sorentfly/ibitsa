<?

/**
 *
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 */
class Core_Api_Mail extends Core_Api_Abstract
{
    protected $_enabled;

    protected $_queueing;

    protected $_transport;

    protected $_log;

    public function __construct()
    {
        /*Test of mail send to user 314503
         *
         * Zend_Registry::set('smtp_debug_data_request', true);
        $user = Engine_Api::_()->user()->getUser(314503);
        Engine_Api::_()->getApi('mail', 'core')->sendSystemRaw($user, 'core_lostpassword', array(
            'host' => $_SERVER['HTTP_HOST'],
            'email' => $user->email,
            'date' => time(),
            'recipient_title' => $user->getTitle(),
            'recipient_link' => $user->getHref(),
            'recipient_photo' => $user->getPhotoUrl('thumb.icon'),
            'object_link' => 'http://abitu.net/some',
            'queue' => false,
        ));
        die();*/


        $this->_enabled = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('core.mail.enabled', true);
        $this->_queueing = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('core.mail.queueing', true);
    }

    // Options

    public function getTransport()
    {
        if( null === $this->_transport ) {

            // Get config
            $mailConfig = array();
            $mailConfigFile = APPLICATION_PATH . '/application/settings/mail.php';
            if( file_exists($mailConfigFile) ) {
                $mailConfig = include $mailConfigFile;
            } else {
                $mailConfig = array(
                    'class' => 'Zend_Mail_Transport_Sendmail',
                    'args' => array(),
                );
            }

            // Get transport
            try {
                $args = ( !empty($mailConfig['args']) ? $mailConfig['args'] : array() );
                $r = new ReflectionClass($mailConfig['class']);
                $transport = $r->newInstanceArgs($args);
                if( !($transport instanceof Zend_Mail_Transport_Abstract) ) {
                    $this->_transport = false;
                } else {
                    $this->_transport = $transport;
                }
            } catch( Exception $e ) {
                $this->_transport = false;
                throw $e;
            }
        }

        if( !($this->_transport instanceof Zend_Mail_Transport_Abstract) )
        {
            return null;
        }

        return $this->_transport;
    }

    public function getCharset()
    {
        return 'utf-8';
    }

    /**
     * @return Zend_Log
     */
    public function getLog()
    {
        if( null === $this->_log ) {
            $log = new Zend_Log();
            $log->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/temporary/log/mail.log', 'a'));
            if( 'development' == APPLICATION_ENV ) {
                $log->addWriter(new Zend_Log_Writer_Firebug());
            }
            $this->_log = $log;
        }
        return $this->_log;
    }


    // Doing things

    public function create()
    {
        return new Zend_Mail($this->getCharset());
    }

    public function send(Zend_Mail $mail)
    {
        if( $this->_enabled ) {

            if( $this->_queueing ) {

                // Single
                if( count($mail->getRecipients()) <= 1 ) {
                    $mailTable = Engine_Api::_()->getDbtable('mail', 'core');
                    $mailTable->insert(array(
                        'type' => 'zend',
                        'body' => serialize($mail),
                        'recipient_count' => 1,
                        'creation_time'   => date("Y-m-d H:i:s"),
                    ));
                }

                // Multi
                else {
                    $recipients = $mail->getRecipients();
                    $mailClone = clone $mail;
                    $mailClone->clearRecipients();

                    // Insert main
                    $mailTable = Engine_Api::_()->getDbtable('mail', 'core');
                    $mail_id = $mailTable->insert(array(
                        'type' => 'zend',
                        'body' => serialize($mailClone),
                        'recipient_count' => count($recipients),
                        'recipient_total' => count($recipients),
                        'creation_time'   => date("Y-m-d H:i:s"),
                    ));

                    // Insert recipients
                    $mailRecipientsTable = Engine_Api::_()->getDbtable('mailRecipients', 'core');
                    foreach( $recipients as $oneRecipient ) {
                        if( $oneRecipient instanceof Core_Model_Item_Abstract ) {
                            $mailRecipientsTable->insert(array(
                                'mail_id' => $mail_id,
                                'user_id' => $oneRecipient->user_id,
                            ));
                        } else if( is_string($oneRecipient) ) {
                            $mailRecipientsTable->insert(array(
                                'mail_id' => $mail_id,
                                'email' => $oneRecipient,
                            ));
                        }
                    }
                }

            } else {

                $mailClone = clone $mail;
                $mailClone->clearRecipients();
                $mailClone->addTo( $mailClone->getFrom() );
                foreach( $mail->getRecipients() as $oneRecipient ) {
                    if( $oneRecipient instanceof Core_Model_Item_Abstract && !empty($oneRecipient->email) ) {
                        $mailClone->addBcc($oneRecipient->email);
                    } else if( is_string($oneRecipient) ) {
                        $mailClone->addBcc($oneRecipient);
                    }
                    // send in batches of 30
                    if (30 <= count($mailClone->getRecipients())) {
                        $this->sendRaw($mailClone);
                        $mailClone->clearRecipients();
                        $mailClone->addTo( $mailClone->getFrom() );
                    }
                }
                if( 1 <= count($mailClone->getRecipients()) ) {
                    $this->sendRaw($mailClone);

                    // Logging in dev mode
                    if( 'development' == APPLICATION_ENV ) {
                        $this->getLog()->log(sprintf('[%s] %s <- %s', 'Zend', join(', ', $mailClone->getRecipients()), $mailClone->getFrom()), Zend_Log::DEBUG);
                    }
                }
            }
        }

        return $this;
    }

    public function sendRaw(Zend_Mail $mail)
    {
        if( $this->_enabled ) {
            try {

                $mail->send($this->getTransport());
            } catch( Exception $e ) {
                if (Zend_Registry::isRegistered('smtp_debug_data_request')){
                    echo "\nSEND MAIL ERROR:\nHEADERS: \n".var_export($mail->getHeaders(), true). "\n" ."\n".$e->getMessage()."\n".$e->getTraceAsString()."\n";
                }
                Engine_Api::_()->core()->logException($e);
            }

            // Logging in dev mode
            if( 'development' == APPLICATION_ENV ) {
                $this->getLog()->log(sprintf('[%s] %s <- %s', 'Zend', join(', ', $mail->getRecipients()), $mail->getFrom()), Zend_Log::DEBUG);
            }

            // Track emails
            Engine_Api::_()->getDbtable('statistics', 'core')->increment('core.emails');
        }

        return $this;
    }


    // System

    public function sendSystem($recipient, $type, array $params = array())
    {
        // Just send
        if( !$this->_queueing || (isset($params['queue']) && $params['queue'] === false) ) {
            $this->sendSystemRaw($recipient, $type, $params);
        }

        // Queue
        else {

            if( !is_array($recipient) && !($recipient instanceof Zend_Db_Table_Rowset_Abstract) ) {
                $recipient = array($recipient);
            }
            $recipients = array();
            // Pre-process recpients

            foreach( $recipient as $oneRecipient ) {
                if( !$this->_validateRecipient($oneRecipient) ) {
                    throw new Exception(get_class($this).'::sendSystem() requires an item, an array of items with an email, or a string email address.');
                }
                if (is_numeric($oneRecipient) && $oneRecipient == (int)$oneRecipient){
                    $oneRecipient = Engine_Api::_()->user()->getUser($oneRecipient);
                }
                if ($oneRecipient instanceof User_Model_User && !$oneRecipient->getIdentity()){
                    continue;
                }
                if ($oneRecipient instanceof User_Model_User && $oneRecipient->spam){
                    continue;
                }
                /* @var Activity_Model_DbTable_NotificationSettings $notificationSettingsTable */
                $notificationSettingsTable = Engine_Api::_()->getDbtable('notificationSettings', 'activity');
                if ($oneRecipient instanceof User_Model_User
                    && strpos($type, 'notify_')===0
                    && !$notificationSettingsTable->checkEnabledNotification($oneRecipient, substr($type, strlen('notify_')))
                ){
                    continue;
                }
                $recipients[] = $oneRecipient;
            }
            if (!$recipients) return $this;

            // Insert main row
            $mailTable = Engine_Api::_()->getDbtable('mail', 'core');
            $mailRecipientsTable = Engine_Api::_()->getDbtable('mailRecipients', 'core');
            $mail_id = $mailTable->insert(array(
                'type' => 'system',
                'body' => serialize(array(
                    'type' => $type,
                    'params' => $params,
                )),
                'recipient_count' => count($recipients),
                'creation_time'   => date('Y-m-d H:i:s'),
            ));

            // Insert recipients
            foreach( $recipients as $oneRecipient ) {
                if( $oneRecipient instanceof Core_Model_Item_Abstract ) {
                    $mailRecipientsTable->insert(array(
                        'mail_id' => $mail_id,
                        'user_id' => $oneRecipient->user_id,
                    ));
                } else if( is_string($oneRecipient) || is_numeric($oneRecipient) ) {
                    $mailRecipientsTable->insert(array(
                        'mail_id' => $mail_id,
                        'email' => $oneRecipient,
                    ));
                }
            }
        }

        return $this;
    }

    protected $_currentMailParams = null;

    public function sendSystemRaw($recipient, $type, array $params = array())
    {
        // Verify recipient(s)
        if( !is_array($recipient) && !($recipient instanceof Zend_Db_Table_Rowset_Abstract) ) {
            $recipient = array($recipient);
        }
        $recipients = array();
        foreach( $recipient as $oneRecipient ) {
            if( !$this->_validateRecipient($oneRecipient) ) {
                throw new Engine_Exception(get_class($this).'::sendSystem() requires an item, an array of items with an email, or a string email address.');
            }
            $recipients[] = $oneRecipient;
        }

        // Send

        // Get admin info
        $fromAddress = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.mail.from', 'admin@' . $_SERVER['HTTP_HOST']);
        $fromName = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.mail.name', 'Site Admin');

        $params['admin_email'] = $fromAddress;
        $params['admin_title'] = $fromName;
        if (!empty($params['from_adress']))
        {
            $fromAddress = $params['from_adress'];
        }
        if (!empty($params['from_name']))
        {
            $fromName = $params['from_name'];
        }
        // Build subject/body
        $translate = Zend_Registry::get('Zend_Translate');

        $subjectKey =  strtoupper('_EMAIL_' . $type . '_SUBJECT');
        $bodyTextKey = strtoupper('_EMAIL_' . $type . '_BODY');
        $bodyHtmlKey = strtoupper('_EMAIL_' . $type . '_BODYHTML');


        // Send to each recipient
        foreach( $recipients as $recipient ) {

            // Copy params
            $rParams = $params;

            // See if they're actually a member
            if( is_string($recipient) ) {
                $user = Engine_Api::_()->getItemTable('user')->fetchRow(array('email LIKE ?' => $recipient));
                if( null !== $user ) {
                    $recipient = $user;
                }
            }

            if (is_numeric($recipient) && (int)$recipient > 0){
                $user = Engine_Api::_()->user()->getUser((int)$recipient);
                if ($user->getIdentity()){
                    $recipient = $user;
                }
            }

            // Check recipient
            if( $recipient instanceof Core_Model_Item_Abstract ) {
                $isMember = true;

                // Detect email and name
                $recipientEmail = $recipient->email;
                $recipientName = $recipient->getTitle();

                // Detect language
                if( !empty($rParams['language']) ) {
                    $recipientLanguage = $rParams['language'];
                } else if( !empty($recipient->language) ) {
                    $recipientLanguage = $recipient->language;
                } else {
                    $recipientLanguage = $translate->getLocale();
                }
                if( !Zend_Locale::isLocale($recipientLanguage) ||
                    $recipientLanguage == 'auto' ||
                    !in_array($recipientLanguage, $translate->getList()) ) {
                    $recipientLanguage = $translate->getLocale();
                }

                // add automatic params
                $rParams['email'] = $recipientEmail;
                $rParams['language'] = $recipientLanguage;
                $rParams['recipient_email'] = $recipientEmail;
                $rParams['recipient_title'] = $recipientName;
                $rParams['recipient_link'] = $recipient->getHref();
                $rParams['recipient_photo'] = $recipient->getPhotoUrl('thumb.normal');
                $rParams['unsubscribe'] = '/unsubscribe?h='.
                    md5($recipient->email.$recipient->salt).
                    '&u='.$recipient->getIdentity();

            } else if( is_string($recipient) ) {
                $isMember = false;

                // Detect email and name
                if( strpos($recipient, ' ') !== false ) {
                    $parts = explode(' ', $recipient, 2);
                    $recipientEmail = $parts[0];
                    $recipientName = trim($parts[1], ' <>');
                } else {
                    $recipientEmail = $recipient;
                    $recipientName = '';
                }

                // Detect language
                if( !empty($rParams['language']) ) {
                    $recipientLanguage = $rParams['language'];
                    //} else if( !empty($recipient->language) ) {
                    //  $recipientLanguage = $recipient->language;
                } else {
                    $recipientLanguage = $translate->getLocale();
                }
                if( !Zend_Locale::isLocale($recipientLanguage) ||
                    $recipientLanguage == 'auto' ||
                    !in_array($recipientLanguage, $translate->getList()) ) {
                    $recipientLanguage = $translate->getLocale();
                }

                // add automatic params
                $rParams['email'] = $recipientEmail;
                $rParams['recipient_email'] = $recipientEmail;
                $rParams['recipient_title'] = $recipientName;
                $rParams['recipient_link'] = '';
                $rParams['recipient_photo'] = '';

            } else {
                continue;
            }

            $this->_currentMailParams = $rParams;

            // Get subject and body
            $subjectTemplate  = !empty($params['subject_text']) ? $params['subject_text'] : (string) $this->_translate($subjectKey,  $recipientLanguage);
            $bodyTextTemplate = (string) $this->_translate($bodyTextKey, $recipientLanguage);
            $bodyHtmlTemplate = (string) $this->_translate($bodyHtmlKey, $recipientLanguage);

            if( !($subjectTemplate) ) {
                if (Zend_Registry::isRegistered('smtp_debug_data_request')) echo ' NO_TRANSLATED_SUBJ '.$type;
                else throw new Engine_Exception(sprintf('No subject translation available for system email "%s"', $type));
                return $this;
            }
            if( !$bodyHtmlTemplate && !$bodyTextTemplate ) {
                if (Zend_Registry::isRegistered('smtp_debug_data_request')) echo ' NO_TRANSLATED_BODY '.$type;
                else throw new Engine_Exception(sprintf('No body translation available for system email "%s"', $type));
                return $this;
            }

            // Get headers and footers
            $headerPrefix = '_EMAIL_HEADER_' . ( $isMember ? 'MEMBER_' : '' );
            $footerPrefix = '_EMAIL_FOOTER_' . ( $isMember ? 'MEMBER_' : '' );

            $subjectHeader  = (string) $this->_translate($headerPrefix . 'SUBJECT',   $recipientLanguage);
            $subjectFooter  = (string) $this->_translate($footerPrefix . 'SUBJECT',   $recipientLanguage);
            $bodyTextHeader = (string) $this->_translate($headerPrefix . 'BODY',      $recipientLanguage);
            $bodyTextFooter = (string) $this->_translate($footerPrefix . 'BODY',      $recipientLanguage);
            $bodyHtmlHeader = (string) $this->_translate($headerPrefix . 'BODYHTML',  $recipientLanguage, $bodyTextHeader);
            $bodyHtmlFooter = (string) $this->_translate($footerPrefix . 'BODYHTML',  $recipientLanguage, $bodyTextFooter);

            // Do replacements
            foreach( $rParams as $var => $val ) {
                $raw = trim($var, '[]');
                $var = '[' . $var . ']';
                //if( !$val ) {
                //  $val = $var;
                //}
                // Fix nbsp
                $val = str_replace('&amp;nbsp;', ' ', $val);
                $val = str_replace('&nbsp;', ' ', $val);
                // Replace
                $subjectTemplate  = str_replace($var, $val, $subjectTemplate);
                $bodyTextTemplate = str_replace($var, $val, $bodyTextTemplate);
                $bodyHtmlTemplate = str_replace($var, $val, $bodyHtmlTemplate);
                $subjectHeader    = str_replace($var, $val, $subjectHeader);
                $subjectFooter    = str_replace($var, $val, $subjectFooter);
                $bodyTextHeader   = str_replace($var, $val, $bodyTextHeader);
                $bodyTextFooter   = str_replace($var, $val, $bodyTextFooter);
                $bodyHtmlHeader   = str_replace($var, $val, $bodyHtmlHeader);
                $bodyHtmlFooter   = str_replace($var, $val, $bodyHtmlFooter);
            }

            // Do header/footer replacements
            $subjectTemplate  = str_replace('[header]', $subjectHeader, $subjectTemplate);
            $subjectTemplate  = str_replace('[footer]', $subjectFooter, $subjectTemplate);
            $bodyTextTemplate = str_replace('[header]', $bodyTextHeader, $bodyTextTemplate);
            $bodyTextTemplate = str_replace('[footer]', $bodyTextFooter, $bodyTextTemplate);
            $bodyHtmlTemplate = str_replace('[header]', $bodyHtmlHeader, $bodyHtmlTemplate);
            $bodyHtmlTemplate = str_replace('[footer]', $bodyHtmlFooter, $bodyHtmlTemplate);

            // Check for missing text or html
            if( !$bodyHtmlTemplate ) {
                $bodyHtmlTemplate = nl2br($bodyTextTemplate);
            }
            if( !$bodyTextTemplate ) {
                $bodyTextTemplate = $bodyHtmlTemplate;
            }
            $bodyTextTemplate = strip_tags($bodyTextTemplate);

            // Send
            if ($subjectKey == '_EMAIL_NOTIFY_ADMIN_USER_SIGNUP_SUBJECT') {
                // the signup notification is emailed to the first SuperAdmin by default
                $users_table = Engine_Api::_()->getDbtable('users', 'user');
                $users_select = $users_table->select()
                    ->where('level_id = ?', 1)
                    ->where('enabled >= ?', 1);
                $super_admin = $users_table->fetchRow($users_select);
                $recipientEmail = $super_admin->email;
            }
            $mail = $this->create()
                ->addTo($recipientEmail, $recipientName)
                ->setFrom($fromAddress, $fromName)
                ->setSubject($subjectTemplate)
                ->setBodyHtml($bodyHtmlTemplate)
                ->setBodyText($bodyTextTemplate)
                ->addHeader('List-Unsubscribe', 'https://'. (isset($rParams['host']) ? $rParams['host'] : $_SERVER['HTTP_HOST']) .'/members/settings/notifications');

            if (!empty($params['reply_to_address'])){
                $mail->setReplyTo(
                    $params['reply_to_address'] === true ? $fromAddress : $params['reply_to_address'],
                    !empty($params['reply_to_name']) ? $params['reply_to_name'] : $fromName
                );
            }
            $this->sendRaw($mail);

            if ( !empty($params['send_hook_item']) && ($item = Engine_Api::_()->getItemByGuid($params['send_hook_item'])) && method_exists($item, 'sendEmailHook') ){
                $item->sendEmailHook($recipient, $mail, $rParams);
            }
            // Logging in dev mode
            if( 'development' == APPLICATION_ENV ) {
                $this->getLog()->log(sprintf('[%s] (%s) %s <- %s', 'System', $type, join(', ', $mail->getRecipients()), $mail->getFrom()), Zend_Log::DEBUG);
            }
        }

        return $this;
    }

    protected function _validateRecipient($recipient)
    {
        if( $recipient instanceof Core_Model_Item_Abstract && !empty($recipient->email) ) {
            return true;
        } else if( is_string($recipient) && strpos($recipient, '@') >= 1 ) {
            return true;
        } else if( is_numeric($recipient) && $recipient == (int)$recipient ) {
            return true;
        }
        return false;
    }

    protected function _translate($key, $locale, $noDefault = false)
    {
        $translate = Zend_Registry::get('Zend_Translate');
        $value = $translate->translate($key, $locale);
        if (is_array($value)){
            $value = reset($value);
        }
        $trimmedValue = is_string($value) ? trim($value) : $value;

        if( $value == $key || '' == trim($trimmedValue) ) {
            if (strpos($key, '_EMAIL_NOTIFY_')===0 && strrpos($key, '_BODY')!==false && $this->_currentMailParams &&
                $this->_currentMailParams && isset($this->_currentMailParams['notification_id']) &&
                ($notification = Engine_Api::_()->getItem('activity_notification', $this->_currentMailParams['notification_id']))
            ){
                /* @see SELECT engine4_activity_notificationtypes.*, engine4_core_mailtemplates.type AS mtype FROM engine4_activity_notificationtypes left join engine4_core_mailtemplates on  engine4_core_mailtemplates.type = CONCAT('notify_', engine4_activity_notificationtypes.type) where engine4_core_mailtemplates.type is null; */
                /* @var Activity_Model_Notification $notification */
                $baseUrl = null;
                if ( ($object = $notification->getObject()) && !empty($object->domain)){
                    $DS = Engine_Api::_()->core()->getDomainsSettings();
                    if (!empty($DS[$object->domain])){
                        $baseUrl = 'https://'.$DS[$object->domain]['domain'];
                    }
                }
                if (!$baseUrl) $baseUrl = 'https://'.ABITU_SITE_ORIGINAL;
                Zend_Controller_Front::getInstance()->setBaseUrl($baseUrl);
                return '[header]<br/> '. $notification->getContent() . ' <br/>[footer]';
            }
            if (strpos($key, '_EMAIL_NOTIFY_')===0 && strrpos($key, '_SUBJECT')!==false){
                $notificationType = strtolower(substr(explode('_SUBJECT', $key)[0], strlen('_EMAIL_NOTIFY_')));
                return '[object_title]: ' . ($locale == 'en' ? 'Notification ' : 'Оповещение ').$notificationType;
            }
            if (is_string($noDefault)){
                return $noDefault;
            }
            if( $noDefault ) {
                return false;
            } else {
                $value = $translate->translate($key);
                if( $value == $key || '' == trim($trimmedValue) ) {
                    $value = $translate->translate($key, 'en');
                    if( $value == $key || '' == trim($trimmedValue) ) {
                        return false;
                    }
                }
            }
        }
        return $value;
    }
}