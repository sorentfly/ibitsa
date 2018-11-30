<?

/**
 *
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 */
class Core_Api_String extends Core_Api_Abstract
{
    public $max_announce_length = 400;

    /**
     * @param $string
     * @param $len
     * @param bool $no_close_tags
     * @param string $symbols
     * @return string
     */
    public function crop_with_splitters($string, $len, $no_close_tags = false, $symbols = ' .:-+"\'!?%()/=#»«'){
        if (mb_strlen($string)<=$len) return $string;
        $tagStarted = false;
        $realSymbolCounter = 0;
        $fullReturn = true;
        for($i =0;$i<mb_strlen($string);$i++){
            $symbol = mb_substr($string, $i, 1);
            if ($symbol == '<'){$tagStarted = true;}
            else if ($symbol == '>'){$tagStarted = false;}
            if (!$tagStarted){
                $realSymbolCounter++;
                if ($realSymbolCounter >= $len){
                    $len = $i;
                    $fullReturn = false;
                    break;
                }
            }
        }
        if ($fullReturn) return $string;
        //bacward to first crop symbol
        $string = mb_substr($string, 0, $len);
        $max = 0;
        for($i=0;$i<mb_strlen($symbols);$i++){
            $pos = mb_strrpos($string,$symbols{$i});
            if ($pos>$max) $max =  $pos;
        }
        $string = trim(mb_substr($string, 0, $max) . ' ...',"\r\n");
        return $no_close_tags? $string : $this->close_tags($string);
    }

    /**
     * @param $string
     * @param $minimum_len
     * @return string
     */
    public function crop_to_paragraph($string, $minimum_len){
        $tags_we_search = array('<\/\s*[a-z]+\s*>');
        $max_cropped_pos = 0;
        $max_cropped_pos_splitter = '';
        foreach($tags_we_search as $regex_part){
            if (preg_match_all('/.*('.$regex_part.').*/i', $string, $matches, PREG_OFFSET_CAPTURE)){
                foreach($matches[1] as $match){
                    $cropped_pos = mb_strlen(substr($string, 0, $match[1]));
                    if ($cropped_pos > $minimum_len && $cropped_pos>$max_cropped_pos){
                        $max_cropped_pos = $cropped_pos;
                        $max_cropped_pos_splitter = $match[0];
                    }
                }
            }
        }
        if (!$max_cropped_pos) return $string;
        else   return mb_substr ($string, 0, $max_cropped_pos) . $max_cropped_pos_splitter;
    }

    /**
     * @param $string
     * @return bool|string
     */
    public function crop_tags_halfs($string){
        $opened_counter = 0;
        $right_position = 0;
        for($i =0;$i<strlen($string);$i++){
            if ($string{$i} == '<'){$opened_counter++;}
            else if ($string{$i} == '>'){$opened_counter--;}
            if ($opened_counter == 0){
                $right_position = $i;
            }
        }
        if ($opened_counter != 0){
            return substr($string, 0, $right_position+1);
        }
        return $string;
    }

    /**
     * @param $string
     * @param null $start
     * @param null $end
     * @return string
     */
    public function textBetween($string, $start = null, $end = null){
        if (!$string){
            return $string;
        }
        $string = ' ' . $string;
        if ($start){
            $ini = mb_strpos($string, $start);
            if ($ini == 0) return mb_substr($string, 1);
            $ini += mb_strlen($start);
        }else{
            $ini = 0;
        }
        $len = ($end ? mb_strpos($string, $end, $ini) : mb_strlen($string)) - $ini;
        return mb_substr($string, $ini, $len);
    }

    /**
     * @param $data
     * @return array
     */
    public function filterLongVarList($data)
    {
        if (!is_array($data)) return $data;
        foreach($data as $k=>$one){
            if (is_string($one)){
                $isJson = in_array(mb_substr($one,0,1), ['{', '[']) && in_array(mb_substr($one,-1,1), ['}', ']']);
                if ($isJson){
                    try{
                        $data[$k] = json_decode($one, true);
                        if ($data[$k]) $one = $data[$k];
                    }catch (Exception $e){}
                }
                if (is_string($one) && mb_strlen($one) > 50 ){
                    $data[$k] = mb_substr(strip_tags($one), 0, 47). '...';
                }
            }
            if (is_array($one)){
                $data[$k] = $this->filterLongVarList($one);
            }
        }
        return $data;
    }

    /**
     * @param $text_html
     * @return string
     */
    public function close_tags($text_html){
        if (!trim($text_html)) return '';
        $doc = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $doc->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' .$text_html);/*$doc->encoding = 'UTF-8';*/
        $doc->encoding = 'utf-8';
        libxml_clear_errors();
        $text_html = $this->textBetween(trim($doc->saveHTML()," \r\n" ), '<body>', '</body>');
        return $text_html;
    }

    /**
     * @param $string
     * @param bool $ghost
     * @return string
     */
    public function transliterate($string, $ghost = false)
    {
        if($ghost){
            $replace = array("А"=>"A","а"=>"a","Б"=>"B","б"=>"b","В"=>"V","в"=>"v","Г"=>"G","г"=>"g","Д"=>"D","д"=>"d",
                "Е"=>"E","е"=>"e","Ё"=>"E","ё"=>"e","Ж"=>"Zh","ж"=>"zh","З"=>"Z","з"=>"z","И"=>"I","и"=>"i",
                "Й"=>"I","й"=>"i","К"=>"K","к"=>"k","Л"=>"L","л"=>"l","М"=>"M","м"=>"m","Н"=>"N","н"=>"n","О"=>"O","о"=>"o",
                "П"=>"P","п"=>"p","Р"=>"R","р"=>"r","С"=>"S","с"=>"s","Т"=>"T","т"=>"t","У"=>"U","у"=>"u","Ф"=>"F","ф"=>"f",
                "Х"=>"Kh","х"=>"kh","Ц"=>"Tc","ц"=>"tc","Ч"=>"Ch","ч"=>"ch","Ш"=>"Sh","ш"=>"sh","Щ"=>"Shch","щ"=>"shch",
                "Ы"=>"Y","ы"=>"y","Э"=>"E","э"=>"e","Ю"=>"Iu","ю"=>"iu","Я"=>"Ia","я"=>"ia","ъ"=>"","ь"=>"");
        } else {
            $arStrES = array("ае","уе","ое","ые","ие","эе","яе","юе","ёе","ее","ье","ъе","ый","ий");
            $arStrOS = array("аё","уё","оё","ыё","иё","эё","яё","юё","ёё","её","ьё","ъё","ый","ий");
            $arStrRS = array("а$","у$","о$","ы$","и$","э$","я$","ю$","ё$","е$","ь$","ъ$","@","@");

            $replace = array("А"=>"A","а"=>"a","Б"=>"B","б"=>"b","В"=>"V","в"=>"v","Г"=>"G","г"=>"g","Д"=>"D","д"=>"d",
                "Е"=>"Ye","е"=>"e","Ё"=>"Ye","ё"=>"e","Ж"=>"Zh","ж"=>"zh","З"=>"Z","з"=>"z","И"=>"I","и"=>"i",
                "Й"=>"Y","й"=>"y","К"=>"K","к"=>"k","Л"=>"L","л"=>"l","М"=>"M","м"=>"m","Н"=>"N","н"=>"n",
                "О"=>"O","о"=>"o","П"=>"P","п"=>"p","Р"=>"R","р"=>"r","С"=>"S","с"=>"s","Т"=>"T","т"=>"t",
                "У"=>"U","у"=>"u","Ф"=>"F","ф"=>"f","Х"=>"Kh","х"=>"kh","Ц"=>"Ts","ц"=>"ts","Ч"=>"Ch","ч"=>"ch",
                "Ш"=>"Sh","ш"=>"sh","Щ"=>"Shch","щ"=>"shch","Ъ"=>"","ъ"=>"","Ы"=>"Y","ы"=>"y","Ь"=>"","ь"=>"",
                "Э"=>"E","э"=>"e","Ю"=>"Yu","ю"=>"yu","Я"=>"Ya","я"=>"ya","@"=>"y","$"=>"ye");

            $string = str_replace($arStrES, $arStrRS, $string);
            $string = str_replace($arStrOS, $arStrRS, $string);
        }

        return iconv("UTF-8","UTF-8//IGNORE",strtr($string,$replace));
    }

    /**
     * @param $value
     * @return mixed
     */
    function escape($value) {
        $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
        $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");

        return str_replace($search, $replace, $value);
    }


    /**
     * NOTE: без этой проверки в $select->order(..) можно провести инъекцию http://framework.zend.com/security/advisory/ZF2014-04
     *
     * @param $order
     * @param bool $noDirection
     * @return array|null|string
     */
    public function filterOrderExpr($order, $noDirection = false)
    {
        if (is_array($order)){
            $orderFiltered = array();
            foreach ($order as $orderOne)
            {
                $orderFiltered[] = $this->filterOrderExpr($orderOne);
            }
            return $orderFiltered;
        }
        $orderExp = explode(' ', trim($order) );
        if (!$orderExp[0] || preg_match('@[^а-яёa-z0-9\._]+@ui', $orderExp[0]) ){
            return null;
        }
        if ($noDirection){
            return $orderExp[0];
        }
        $dir = 'asc';
        if (count($orderExp)>1){
            $dir = mb_strtolower(end($orderExp));
            if ($dir!='asc'&$dir!='desc'){
                $dir = 'asc';
            }
        }
        return $orderExp[0].' '.$dir;
    }

    /**
     * NOTE: функция для валидации SQL селекта.
     * !ВАЖНО! она не спасает от sql инъекций, а используется лишь для SQL полей,
     * к которым имеют доступ суперадмины - для того, чтобы админ
     * случайно (или намеренно) не впаял DELETE, DROP, ...
     *
     * @param $sql
     * @return int
     */
    public function validateSQLSelect($sql)
    {
        return preg_match('/^(\s*?)select\s*?.*?\s*?from([\s]|[^;]|([\'"].*;.*[\'"]))*?(;\s*)?$/ui', $sql);
    }

    /**
     * @param $input
     * @return mixed
     */
    public function sslReplace($input){
        /* КОСТЫЛЬик для подгрузки изображений вставленных через tinymce на локалке*/
        if (defined('ENABLE_OLDFILE_ABSOLUTE_PATH_TO_ABITU') && defined('OLDFILE_LIMIT_DATE') && ENABLE_OLDFILE_ABSOLUTE_PATH_TO_ABITU){
            $input = preg_replace(['@(src\s*=\s*["\'])(/public/jbimages_images)@i', '@(url\s*\(\s*["\']?)(/public/jbimages_images)@i'], '$1https:'.ENABLE_OLDFILE_ABSOLUTE_PATH_TO_ABITU.'/public/jbimages_images', $input);
        }
        /*Замер показал 1/10000 секунды - всё ок, можно не париться по поводу скорости.*/
        return preg_replace(['@(src\s*=\s*["\'])(http:)@i', '@(url\s*\(\s*["\']?)(http:)@i'], '$1https:', $input);
    }

    const EXECUTABLE_EXTENSIONS = 'php,exe,js,tpl,sh,sql,bat,pl,cgi,jar,dll,so,py,pyc,html,htm';

    /**
     * @param $date
     * @return mixed
     */
    public function dateRussifyMonth($date){
        $months = ['Jan' => 'янв','Feb' => 'фев','Mar' => 'мар','Apr' => 'апр','May' => 'мая','Jun' => 'июн','Jul' => 'июл','Aug' => 'авг','Sep' => 'сен','Oct' => 'окт','Nov' => 'ноя','Dec' => 'дек'];
        foreach($months as $eng => $rus){
            $date = str_replace($eng, mb_ucfirst($rus), $date);
        }
        return $date;
    }

    /**
     * @param $phone
     * @param bool $softValidity
     * @return mixed
     */
    public function replacePhoneToNumbers($phone, $softValidity = true)
    {
        $phone = str_replace('-', '', str_replace(' ', '', $phone));
        $phone = ltrim($phone, '+');
        if ( in_array(mb_substr($phone, 0, 1), ['7', '8']) ) $phone = mb_substr($phone, 1);
        return $softValidity ? preg_replace('@\D+@u', '', $phone) : str_replace('(', '', str_replace(')', '', $phone));
    }

}