<?
class Bitsa_Form_Decorator_FormRecaptcha extends Zend_Form_Decorator_Abstract
{
    protected $_placement = null;
    public function render($content)
    {
        $translate = Zend_Registry::get('Zend_Translate');
        $elementId = $this->getElement()->getName();
        
        $label = $this->getElement()->getLabel();
        if($this->getElement()->isRequired())
        {
           $label .= '<b>*</b>';
        }
        
        $description = $this->getElement()->getDescription();        
        
        $wrapper =  '<div class="form-wrapper">' . 
                        '<div class="form-label"></div>' .
                        '<div class="form-element">' . 
                            '<div id="recaptcha_widget">' . 
                                '<div id="recaptcha_image" onclick="document.getElementById("recaptcha_response_field").focus();" title="' . $translate->_('Enter the symbols from this picture') . '"></div>' . 
                            '</div>' .
                            '<div class="recaptcha_reload">' .
                                '<span id="recaptcha_reload" onclick="recaptchaReload();"><img alt="' . $translate->_('Show a different image') . '" height="16" id="recaptcha_reload_pic" src="/application/modules/User/externals/images/user/next1.gif" title="' . $translate->_('Show a different image').'" width="16"/> <span title="'.$translate->_('Show a different image').'">'.$translate->_('Show a different image').'</span></span>' .
                                '<img alt="' . $translate->_('Help').'" class="help" height="16" id="help_captcha" onclick="elementShow(document.getElementById(\'help_captcha_block\'));" title="' . $translate->_('Help') . '" src="/application/modules/User/externals/images/user/help.png" width="16"/>' .
                            '</div>' .
                            '<div id="help_captcha_block" style="display: none;"><i></i>'.$translate->_('To sign up you must enter the text from the image ').
                            $translate->_('Automated software cannot enter the symbols from the picture, but you can!').' ' . $translate->_("For more information you can read in <a href='//wikipedia.org/wiki/ReCAPTCHA' target='_blank' title='ReCAPTCHA'>wikipedia</a> and in the <a href='javascript: Recaptcha.showhelp()'> help section</a>").
                                '<div id="help_captcha_close" onclick="elementHide(document.getElementById(\'help_captcha_block\'));" title="'.$translate->_('Close (Esc)').'">×</div>' .
                            '</div>' .
                        '</div>' .
                    '</div>' .
                    '<div class="form-wrapper">' . 
                        '<div class="form-label"><label for="' . $elementId . '">' . $label . '</label></div>' .
                        '<div class="form-element">' . $content . 
                            '<div class="field_correct" id="' . $elementId . '_correct"></div>' . 
                            '<script type="text/javascript" src="//www.google.com/recaptcha/api/challenge?k='.(Engine_Api::_()->getApi('settings', 'core')->core_spam_recaptchapublic).'"></script>'.
                        '</div>' .                
                    '</div>';
        if ($description != null)
        {
            $wrapper .= '<div class="form-wrapper">' .
                            '<div class="form-label"></div>' .
                            '<div class="form-element">' . $description . '</div>' .
                        '</div>';
        }
        
        return $wrapper;
    }
}