<?
class Abitu_Form_Decorator_FormDate extends Zend_Form_Decorator_Abstract
{
    protected $_placement = null;
    public function render($content)
    {
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
                '<div class="form-element" data-datepicker="1">' . $content . '<i class="fa fa-calendar formIconCalendar" onclick="document.getElementById(\''.$elementName.'\').focus();" title="' . $translate->_('Select a date') . '"></i>';
       
        $wrapper .= '<div class="field_correct" id="' . $elementName . '_correct"></div>';
        
        if ($description != null)
        {
            $wrapper .= '<p class="description">' . $description . '</p>';        
        }
        
       
        $wrapper .= '</div>';
        $wrapper .= '</div>';
        
        return $wrapper;
    }
}