<?
class Abitu_Form_Decorator_FormProfileButton extends Zend_Form_Decorator_Abstract
{
    protected $_placement = null;
    public function render($content)
    {
        if($this->getElement()->getAttrib('class') == 'hidden')
        {
            $class = ' hidden';
        }else{
            $class = '';
        }
        
        $data_type = $this->getElement()->getAttrib('data-type');
        
        if(isset($_SESSION['first_visit']) && $_SESSION['first_visit'])
        {
            $translate = Zend_Registry::get('Zend_Translate'); 
            
            $wrapper = '<hr/><div class="form-wrapper' . $class . '">';
            
            if($data_type !== '0')
            {
                $wrapper .= '<div class="form-label"><button class="form-previous" title="' . $translate->_('Previous step') . '">' . $translate->_('Back') . '</button></div>';
            }
            else
            {
                $wrapper .= '<div class="form-label"></div>';
            }
            
            $wrapper .= '<div class="form-element">' . $content . '<button class="form-next" title="' . $translate->_('Next step') . '">' . $translate->_('Next') . '</button></div>' .
                    '</div>';
        }
        else
        {
            $wrapper = '<hr/><div class="form-wrapper' . $class . '">' .
                    '<div class="form-label"></div>' .
                    '<div class="form-element">' . $content . '</div>' .
                    '</div>';
        }
        
        return $wrapper;
    }
}