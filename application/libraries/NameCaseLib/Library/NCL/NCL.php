<?php
class NCL
{
    /**
     * Мужской пол
     * @static integer
     */
    static $MAN = 1;

    /**
     * Женский пол
     * @static integer 
     */
    static $WOMAN = 2;

    /**
     * Именительный падеж
     * @static integer 
     */
    static $IMENITLN = 0;
    static $NOMINATIVE = 0;
    
    /**
     * Родительный падеж
     * @static integer 
     */
    static $RODITLN = 1;
    static $GENETIVE = 1;
    
    /**
     * Дательный падеж
     * @static integer 
     */
    static $DATELN = 2;
    static $DATIVE = 2;
    
    /**
     * Винительный падеж
     * @static integer 
     */
    static $VINITELN = 3;
    static $ACCUSATIVE = 3;
    
    /**
     * Творительный падеж
     * @static integer 
     */
    static $TVORITELN = 4;
    static $INSTRUMENTAL = 4;
    /**
     * Предложный падеж
     * @static integer 
     */
    static $PREDLOGN = 5;
    static $ABLATIVE = 5;
    
    /**
     * Назвиний відмінок
     * @static integer 
     */
    static $UaNazyvnyi = 0;
    
    /**
     * Родовий відмінок
     * @static integer 
     */
    static $UaRodovyi = 1;
    
    /**
     * Давальний відмінок
     * @static integer 
     */
    static $UaDavalnyi = 2;
    
    /**
     * Знахідний відмінок
     * @static integer 
     */
    static $UaZnahidnyi = 3;
    
    /**
     * Орудний відмінок
     * @static integer 
     */
    static $UaOrudnyi = 4;
    
    /**
     * Місцевий відмінок
     * @static integer 
     */
    static $UaMiszevyi = 5;
    
    /**
     * Кличний відмінок
     * @static integer 
     */
    static $UaKlychnyi = 6;

}