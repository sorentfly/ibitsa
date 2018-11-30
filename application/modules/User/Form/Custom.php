<?

/**
 * Class User_Form_Custom
 *
 * @author      Vlad Smith (stuf.developer@gmail.com)
 * @description
 *  Builds a custom form, created in Core_CustomFormController by high-level access users.
 */
class User_Form_Custom extends Engine_Form
{
    private $form_id = null;    // ID формы
    private $editing = null;    // Процесс редактирования данных
    private $sender  = null;    // Кто работает с формой (user|school)
    /* @var Core_Model_CustomForm $subject */
    private $subject = null;    // Субъект формы
    private $viewer  = null;    // Пользователь
    private $data    = null;    //
    private $school_list = [];  // Список сущностей "школа" текущего пользователя, от имени которых можно отправить форму

    private $name_iterator = 0;


    public function __construct($options = null)
    {
        if (isset($options['form_id'])) {
            $this->form_id = (int)$options['form_id'] > 0 ? (int)$options['form_id'] : null;
            unset($options['form_id']);
        } else
            throw new Core_Model_Item_Exception('Parameter "form_id" is required for class User_Form_Custom');

        if (isset($options['editing'])) {
            $this->editing = $options['editing'] ? (bool)$options['editing'] : null;
            unset($options['editing']);
        }

        if (isset($options['schools'])) {
            $this->school_list = $options['schools'] ? $options['schools'] : null;
            unset($options['schools']);
        }

        parent::__construct($options);

        $this->setAttrib('class', 'global_form custom_form');
    }

    public function buildForm()
    {
        $this->constructDependencies();
        $this->constructForm();
        return $this;
    }

    public function constructDependencies()
    {
        $this->subject = Engine_Api::_()->core()->getSubject();
        $this->viewer  = Engine_Api::_()->user()->getViewer();
        $this->sender = $this->subject->filling_object;
        $this->data = $this->subject->getData();
    }

    private function constructForm()
    {
        if (empty($this->data))
            throw new Zend_Form_Exception("Form data can't be empty");

        $this->setTitle($this->data['title']);


        if ( $this->sender == 'school' ) { // Форма отправляется от имени школы
            if (count($this->school_list) > 1) {
                $this->addElement('Select', 'school_id', [
                    'label' => "School",
                    'onchange' => 'javascript:fetchSchoolResponseData(this.value);',
                    'multiOptions' => $this->school_list
                ]);
            } else {
                $this->addElement('Hidden', 'school_id', [
                    'label' => "School",
                    'onchange' => 'javascript:fetchSchoolResponseData(this.value);',
                    'value' => array_keys($this->school_list)[0]
                ]);
            }
        }

        // construct fields
        foreach ($this->data['fields'] as $i => $element_data) {
            $this->constructElement($element_data);
        }

        if ($this->editing && $this->subject->multi_post != 1) {
            $this->addElement('Label', 'label', [
                'tag' => 'p',
                'content' => Zend_Registry::get('Zend_Translate')->_('__FORM_HAS_RESPONSE_' . strtoupper($this->sender)),
                'decorators' => array(
                    'ViewHelper'
                )
            ]);
            // Element: cancel
            $this->addElement('Cancel', 'cancel', array(
                'link' => true,
                'label' => 'вернуться',
                'onclick' => 'parent.Smoothbox.close(); return false;',
                'decorators' => array(
                    'ViewHelper'
                )
            ));

            // DisplayGroup: buttons
            $this->addDisplayGroup(array('label', 'cancel'), 'alertButton');
        } else {
            // Element: buttons
            $this->addElement('Button', 'submit', array(
                'label' => $this->data['submit_btn'],
                'type' => 'submit',
                'decorators' => array(
                    'ViewHelper',
                ),
            ));

            // Element: cancel
            $this->addElement('Cancel', 'cancel', array(
                'prependText' => ' or ',
                'link' => true,
                'label' => 'вернуться',
                'onclick' => 'parent.Smoothbox.close(); return false;',
                'decorators' => array(
                    'ViewHelper'
                )
            ));

            if ($this->sender == 'school' && $this->subject->canBotherViewer($this->viewer)) {
                // Element: cancel forever
                $this->addElement('Cancel', 'cancel_forever', array(
                    'prependText' => ' or ',
                    'link' => true,
                    'label' => 'отказаться',
                    'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(
                        [
                            'controller'    => 'customForms',
                            'action'        => 'cancel',
                            'form_id'       => $this->form_id
                        ], 'user_extended'),
                    'decorators' => array(
                        'ViewHelper'
                    )
                ));
            }
            // DisplayGroup: buttons
            $this->addDisplayGroup(array('submit', 'cancel', 'cancel_forever'), 'buttons');
        }
    }

    private function constructElement($data)
    {
        $name = isset($data['name']) && $data['name']['value'] != '' ?
            $data['name']['value'] :
            'unnecessary_'.$this->name_iterator++;
        $is_disabled = $this->editing && $this->subject->multi_post == 0;

        $settings = [];
        if ($data['type']['value'] == 'Label') {
            $settings['tag'] = $data['subtype']['value'];
            $settings['content'] = @$data['label'];
        }
        if (isset($data['label']))
            $settings['label'] = $data['label'];
        if (isset($data['tip']))
            $settings['placeholder'] = $data['tip'];
        if (isset($data['description']))
            $settings['description'] = $data['description'];
        if (isset($data['required']))
            $settings['required'] = $data['required'];
        if (isset($data['values']))
            $settings['multiOptions'] = $data['values'];
        if (isset($data['title']))
            $settings['content'] = $data['title'];
        if ($is_disabled) {
            if (in_array($data['type']['value'], ['MultiCheckbox', 'Radio'])) {
                $settings['disabled'] = true;
            } else {
                $settings['readonly'] = 'readonly';
            }
        }

        $el = $this->createElement($data['type']['value'], $name, $settings);
        if (!isset($settings['label']))
            $el->removeDecorator('Label');
        $this->addElement($el);
    }
}