<?
class Abitu_Form_Decorator_FormPassword extends Zend_Form_Decorator_Abstract
{
    protected $_placement = null;
    public function render($content)
    {
        if (!isset($_SESSION['mobile']['mobile'])) $_SESSION['mobile']['mobile'] = false;
        if($this->getElement()->getAttrib('class') == 'hidden' && $_SESSION['mobile']['mobile'] !== true)
        {
            $style = ' style="display:none;"';
        }else{
            $style = '';
        }
        $translate = Zend_Registry::get('Zend_Translate'); 
        $elementName = $this->getElement()->getName();
        
        $label = $this->getElement()->getLabel();
        if($this->getElement()->isRequired())
        {
           $label .= '<b>*</b>';
        }
        
        $description = $this->getElement()->getDescription();
         
         $wrapper = '<div class="form-wrapper"' . $style . '>' .
                    '<div class="form-label"><label for="' . $elementName . '">' . $label . '</label></div>' .
                    '<div class="form-element">' . $content ;
        if ($description != null)
        {
            $wrapper .= '<p class="description">' . $description . '</p>';
        }
        
        if ($elementName === 'password' && $_SESSION['mobile']['mobile'] !== true)
        {            
            $wrapper .= '<div id="password_line" title="' . $translate->_('Password difficulty') . '">' .
                    '<div id="red_line" style="display: none;"></div>' .
                    '<div id="yellow_line" style="display: none;"></div>' .
                    '<div id="green_line" style="display: none;"></div>' .
                    '</div>'
                    . '<div id="password_difficulty"></div>'
                    . '<div class="field_correct" id="password_correct"></div>'.
                 '</div>'.
                    '<div class="password-options"><p><span title="' . $translate->_('Recommendations for choosing a password') . '" id="password_choose">' . $translate->_('How to create a secure password') . '</span></p>' .
                '<p id="password_choose_info" style="display: none;">' . $translate->_('Your password must contain between 6 and 32 characters. It can contain both letters and numbers plus any of the following symbols: ! # $ % ^ & * ( ) _ - + : ; . , @') . '</p>
                <p><span title="' . $translate->_('Show values of passwords') . '" id="password_show_hide" onclick="passwordShowHide(this);">' . $translate->_('Show passwords') . '</span></p></div>' .
                    '<div id="password_difficulty_block" style="display: none;">' .
                        '<span id="password_difficulty_content"></span><div id="password_difficulty_close" onclick="passwordDifficultyBlockHide()" title="' . $translate->_('Close (Esc)') . '">×</div>' .
                        '</div>';
        }
        else
        {
            $wrapper .= '<div class="field_correct" id="' . $elementName . '_correct"></div>'
                    . '</div><div id="' . $elementName . '_status"></div>';
        }
        
        $wrapper .= '</div>';
                        
        return $wrapper;
    }
}