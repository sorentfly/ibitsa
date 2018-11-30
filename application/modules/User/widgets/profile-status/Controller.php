<?
require_once('application/libraries/NameCaseLib/Library/NCL.NameCase.ru.php');

class User_Widget_ProfileStatusController extends Engine_Content_Widget_Abstract
{

    private $db;
    private $tb_prefix;

    public function indexAction()
    {
        $this->db = Engine_Db_Table::getDefaultAdapter();
        $this->tb_prefix = Engine_Db_Table::getTablePrefix();
                
        $this->view->user = $user = Engine_Api::_()->core()->getSubject('user');
        $viewer = Engine_Api::_()->user()->getViewer('user');

        $wasOnlineTs = strtotime($user->online_date) + (int)date('Z');

        if (!$viewer->getIdentity()){
            //Task #463 - http://nat.dc.phystech.edu:3022/redmine/issues/463
            return $this->setNoRender();
        }else{
            date_default_timezone_set($viewer->timezone);
        }

        $date_difference = time() - $wasOnlineTs;
        $isEn = Zend_Registry::get('Zend_Translate')->getLocale() == 'en';

        if($user->gender == 1)
        {
            $last_activity = $isEn ? 'Was online ' : 'Заходила ';
        }
        else
        {
            $last_activity = $isEn ? 'Was online ' : 'Заходил ';
        }
        if($date_difference < 300) //Совсем Онлайн
        {
            $last_activity = $isEn ? 'Online' : 'Онлайн';
        }
        else
        {
            $monthsRu = array('января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
            if((int) date('Y') === (int) date('Y', $wasOnlineTs) && (int) date('z') === (int) date('z', $wasOnlineTs)) //Юзер заходил сегодня
            {
                if($date_difference < 300) //Онлайн (Меньше 5 минут назад)
                {
                    $last_activity = $isEn ? 'Online' : 'Онлайн';
                }
                else if($date_difference < 3600)
                {
                    $minutes = floor($date_difference / 60);

                    $last_activity .= $minutes . ' ' . $isEn
                        ? ($this->declension($minutes, 'minute', 'minutes', 'minutes') . ' ago' )
                        : ( $this->declension($minutes, 'минут', 'минуты', 'минута') . ' назад');
                }
                else
                {
                    $last_activity .= ($isEn ? 'today at ' :'сегодня в ') . date('G:i', $wasOnlineTs);
                }
            }
            else if((int) date('Y') === (int) date('Y', $wasOnlineTs) && (int) date('z') === ((int) date('z', $wasOnlineTs) + 1)) //Заходил вчера
            {
                $last_activity .= ($isEn ? 'yesterday at ' :'вчера в ') . date('G:i', $wasOnlineTs);
            }
            else if((int) date('Y') === (int) date('Y', $wasOnlineTs)) //В этом году
            {
                $last_activity .= date('j', $wasOnlineTs) . ' ';

                $last_activity .= ($isEn ? date('M', $wasOnlineTs) : $monthsRu[(int) date('n', $wasOnlineTs) - 1]) . ($isEn ? ' at ' : ' в ');
                $last_activity .= date('G:i', $wasOnlineTs);
            }
            else if((int) date('Y', $wasOnlineTs) < 2012)//Более года назад
            {
                $last_activity = '';
            }
            else
            {
                $last_activity .= date('j', $wasOnlineTs) . ' ';
                $last_activity .= ($isEn ? date('M', $wasOnlineTs) : $monthsRu[(int) date('n', $wasOnlineTs) - 1]) . ' ';
                $last_activity .= date('Y', $wasOnlineTs) . ($isEn ? ' year' : ' года');
            }
        }

        $this->view->last_activity = $last_activity;
        $this->view->auth = $user->authorization()->isAllowed($viewer, 'view');
        $this->view->checked = $user->checked;
        
        $name_case_lib = new NCLNameCaseRu();
        
        if(Zend_Registry::get('Locale')->__toString() === 'en')
        {
            $this->view->first_name_genetive = $user->first_name . "'s";
        }
        else
        {
            switch ($user->gender)
            {
                case 1: $gender = NCL::$WOMAN;
                    break;
                case 2: $gender = NCL::$MAN;
                    break;
                default: $gender = null;
            }

            $this->view->first_name_genetive = $name_case_lib->q($user->first_name, NCL::$GENETIVE, $gender);
            $this->view->user = $user;
        }
        $this->view->friends_count = $user->membership()->getMemberCount();
    }

    private function declension($number, $case_1, $case_2, $case_3)
    {
        $number = (String) $number;

        if((int) $number[strlen($number) - 1] > 4 || (int) $number[strlen($number) - 1] === 0 || (strlen($number) > 1 && (int) ($number[strlen($number) - 2] . $number[strlen($number) - 1]) > 10 && (int) ($number[strlen($number) - 2] . $number[strlen($number) - 1]) < 15))
        {
            return $case_1;
        }
        else if((int) $number[strlen($number) - 1] === 2 || (int) $number[strlen($number) - 1] === 3 || (int) $number[strlen($number) - 1] === 4)
        {
            return $case_2;
        }
        else if((int) $number[strlen($number) - 1] === 1)
        {
            return $case_3;
        }
        else
        {
            return $case_1;
        }
    }
}