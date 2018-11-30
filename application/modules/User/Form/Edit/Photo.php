<?
class User_Form_Edit_Photo extends Engine_Form
{

    public function init()
    {
        $this->setAttrib('enctype', 'multipart/form-data')
                ->setAttrib('name', 'EditPhoto');

        $this->addElement('Image', 'current', array(
            'label' => 'Current Photo',
            'ignore' => true,
            'decorators' => array(array('ViewScript', array(
                        'viewScript' => '_formEditImage.tpl',
                        'class' => 'form element',
                        'testing' => 'testing'
                    )))
        ));
        Engine_Form::addDefaultDecorators($this->current);

        $this->addElement('File', 'Filedata', array(
            'label' => 'Choose New Photo',
            'destination' => APPLICATION_PATH . '/public/temporary/',
            'multiFile' => 1,
            'validators' => array(
                array('Count', false, 1),
                //array('Size', false, 15728640),
                array('Extension', false, 'jpg,jpeg,png,gif'),
            ),
            'onchange' => 'changeFilePhoto(event);'
        ));

        $this->addElement('Hidden', 'coordinates', array(
            'filters' => array('HtmlEntities')
        ));

        $this->addElement('Button', 'done', array(
            'label' => 'Save Photo',
            'type' => 'submit',
            'decorators' => array('ViewHelper'),
        ));
        
        $this->addElement('Cancel', 'remove', array(
            'label' => 'remove photo',
            'prependText' => ' or ',            
            'onclick' => 'RemoveUserPhoto(event)',
            'decorators' => array('ViewHelper'),
        ));        

        $this->addDisplayGroup(array('done', 'remove'), 'buttons');
    }
}
