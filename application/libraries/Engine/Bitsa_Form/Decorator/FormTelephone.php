<?
class Engine_Form_Decorator_FormTelephone extends Zend_Form_Decorator_Abstract
{
    protected $_placement = null;
    private $translate;
    public function render($content)
    {
        $this->translate = Zend_Registry::get('Zend_Translate');
        if($this->getElement()->getAttrib('class') == 'hidden' && $_SESSION['mobile']['mobile'] !== true)
        {
            $style = ' style="display:none;"';
        }else{
            $style = '';
        }
        
        $elementName = $this->getElement()->getName();
        $elementId = $this->getElement()->getId();
        $value = $this->getElement()->getValue();
        $avalibleCountries = array(
            '+7' => array('shortName'=>'ru', 'label'=>$this->translate->_('Russia')),
            '+380' => array('shortName'=>'ua', 'label'=>$this->translate->_('Ukraine')),
            '+375' => array('shortName'=>'by', 'label'=>$this->translate->_('Belarus')),
            '+7 ' => array('shortName'=>'kz', 'label'=>$this->translate->_('Kazakhstan')),
            '+994' => array('shortName'=>'az', 'label'=>$this->translate->_('Azerbaijan')),
            '+374' => array('shortName'=>'am', 'label'=>$this->translate->_('Armenia')),
            '+995' => array('shortName'=>'ge', 'label'=>$this->translate->_('Georgia')),
            '+972' => array('shortName'=>'il', 'label'=>$this->translate->_('Israel')),

            '+91' => array('shortName'=>'in', 'label'=>$this->translate->_('India')),
            '+86' => array('shortName'=>'cn', 'label'=>$this->translate->_('China')),

            '+1' => array('shortName'=>'us', 'label'=>$this->translate->_('USA')),
            '+49' => array('shortName'=>'de', 'label'=>$this->translate->_('Germany')),
            '+996' => array('shortName'=>'kg', 'label'=>$this->translate->_('Kyrgyzstan')),
            '+371' => array('shortName'=>'lv', 'label'=>$this->translate->_('Latvia')),
            '+370' => array('shortName'=>'lt', 'label'=>$this->translate->_('Lithuania')),
            '+372' => array('shortName'=>'ee', 'label'=>$this->translate->_('Estonia')),
            '+373' => array('shortName'=>'md', 'label'=>$this->translate->_('Moldova')),
            '+992' => array('shortName'=>'tj', 'label'=>$this->translate->_('Tajikistan')),
            '+998' => array('shortName'=>'uz', 'label'=>$this->translate->_('Uzbekistan')),
            '+___' => array('shortName'=>'other', 'label'=>$this->translate->_('Other')),
        );
        $countryOptions = array();
        $activeCountry = null;
        foreach($avalibleCountries as $code=>$country){
            if (!$activeCountry && mb_strpos($value, $code)===0){
                $activeCountry = $code;
            }
            $countryOptions[] = '<li onmousedown="selectCountryCode(\'' . $elementId . '\', this);" onmouseover="backlightItem(this);"><span class="'.$country['shortName'].'"></span><span class="phoneCountryPopupLabel">' . $country['label'] . '</span> <i>'.$code.'</i></li>';
        }
        if (!$activeCountry){
            $activeCountry = '+7';
        }
        $label = $this->getElement()->getLabel();
        if($this->getElement()->isRequired())
        {
           $label .= ' <b class="asterisk">*</b>';
        }
        
        $description = $this->getElement()->getDescription();
        //
        $countryHidden = '<input type="hidden" name="'.$elementName.'_country" id="'.$elementName.'_country" value="'.$avalibleCountries[$activeCountry]['label'].'" short="'.$avalibleCountries[$activeCountry]['shortName'].'">';
        $wrapper = '<div class="form-wrapper telephoneWrapper" ' . $style . '>' . 
                '<div class="form-label"><label for="' . $elementName . '">' . $label . '</label></div>' .
                '<div class="form-element"><div class="telephone"><div class="telephone-flag '.$avalibleCountries[$activeCountry]['shortName'].'" onclick="telephoneCodeListToggle(this);"></div>' . $content . $countryHidden .'</div>'
                . '<div class="telephone-code-list" style="display: none;"><ul>' .
                implode("\n", $countryOptions).
                '</ul></div>';        
        
        
        $wrapper .= '<div class="field_correct" id="' . $elementId . '_correct"></div>';
        
        if ($description != null)
        {
            $wrapper .= '<p class="description">' . $description . '</p>';        
        }
        
       
        $wrapper .= '</div>';
        $wrapper .= '</div>';

        return $wrapper;
    }
}