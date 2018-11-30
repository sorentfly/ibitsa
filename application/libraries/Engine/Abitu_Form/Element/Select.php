<?
class Abitu_Form_Element_Select extends Zend_Form_Element_Select
{
    private $translate;
    public function init()
    {
        $this->translate = Zend_Registry::get('Zend_Translate');

        if($this->getId() === 'status')
        {
            if(Zend_Registry::isRegistered('gender') && (Zend_Registry::get('gender') == 3 || Zend_Registry::get('gender') == 1))
            {
                foreach ($this->getMultiOptions() as $key => $value)
                {
                    if($this->getValue() == $key)
                    {
                        $this->setValue($value);
                    }
                        
                    switch ($value)
                    {
                        case 'Schoolboy': $status_options[$key] = 'Школьница';
                            break;
                        case 'Applicant': $status_options[$key] = 'Абитуриентка';
                            break;
                        case 'Student': $status_options[$key] = 'Студентка';
                            break;
                        case 'Teacher': $status_options[$key] = 'Преподаватель';
                            break;
                        case 'Representative of the institution': $status_options[$key] = 'Представительница учебного заведения';
                            break;
                    }                    
                }
            }
            else
            {
                foreach ($this->getMultiOptions() as $key => $value)
                {
                    if($this->getValue() == $key)
                    {
                        $this->setValue($value);
                    }
                        
                    switch ($value)
                    {
                        case 'Schoolboy': $status_options[$key] = 'Школьник';
                            break;
                        case 'Applicant': $status_options[$key] = 'Абитуриент';
                            break;
                        case 'Student': $status_options[$key] = 'Студент';
                            break;
                        case 'Teacher': $status_options[$key] = 'Преподаватель';
                            break;
                        case 'Representative of the institution': $status_options[$key] = 'Представитель учебного заведения';
                            break;
                    }
                    
                }
            }
            $this->setMultiOptions($status_options);
        }
        
        if($this->getId() === 'university_current_status' && Zend_Registry::isRegistered('gender'))
        {
            if(Zend_Registry::get('gender') == 3 || Zend_Registry::get('gender') == 1)
            {
                foreach ($this->getMultiOptions() as $key => $value)
                {
                    if($this->getValue() == $key)
                    {
                        $this->setValue($value);
                    }
                    
                    switch ($value)
                    {
                        case "Applicant": $status_options[$key] = 'Абитуриентка';
                            break;
                        case "Student (Bachelor's)": $status_options[$key] = 'Студентка (бакалавр)';
                            break;
                        case "Student (Specialist)": $status_options[$key] = 'Студентка (специалист)';
                            break;
                        case "Student (Master's)": $status_options[$key] = 'Студентка (магистр)';
                            break;                        
                        case "Alumnus (Specialist)": $status_options[$key] = 'Выпускница (специалист)';
                            break;
                        case "Alumnus (Bachelor's)": $status_options[$key] = 'Выпускница (бакалавр)';
                            break;
                        case "Alumnus (Master's)": $status_options[$key] = 'Выпускница (магистр)';
                            break;
                        case "Postgraduate student": $status_options[$key] = 'Аспирантка';
                            break;
                        case "Candidate of Sciences": $status_options[$key] = 'Кандидат наук';
                            break;
                        case "PhD": $status_options[$key] = 'Доктор наук';
                            break;
                        
                        
                    }
                }
            }
            else
            {
                foreach ($this->getMultiOptions() as $key => $value)
                {
                    if($this->getValue() == $key)
                    {
                        $this->setValue($value);
                    }
                    
                    switch ($value)
                    {
                        case "Applicant": $status_options[$key] = 'Абитуриент';
                            break;
                        case "Student (Bachelor's)": $status_options[$key] = 'Студент (бакалавр)';
                            break;
                        case "Student (Specialist)": $status_options[$key] = 'Студент (специалист)';
                            break;
                        case "Student (Master's)": $status_options[$key] = 'Студент (магистр)';
                            break;                        
                        case "Alumnus (Specialist)": $status_options[$key] = 'Выпускник (специалист)';
                            break;
                        case "Alumnus (Bachelor's)": $status_options[$key] = 'Выпускник (бакалавр)';
                            break;
                        case "Alumnus (Master's)": $status_options[$key] = 'Выпускник (магистр)';
                            break;
                        case "Postgraduate student": $status_options[$key] = 'Аспирант';
                            break;
                        case "Candidate of Sciences": $status_options[$key] = 'Кандидат наук';
                            break;
                        case "PhD": $status_options[$key] = 'Доктор наук';
                            break;
                    }
                }
            }
            $this->setMultiOptions($status_options);
        }

    }
    
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled())
        {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators))
        {
            $this->addDecorator('ViewHelper');
            if($this->getId() === 'university_current_status' || $this->getId() === 'university_mode_study')
            {
                $this->addDecorator('FormAdvancedSelect');                
            }
            else
            {
                $this->addDecorator('FormSelect');
            }
        }
    }
}