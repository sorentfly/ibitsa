<?
class Application_Api_Core extends Core_Api_Abstract
{
    protected $_privilegeConfig = null;
    protected $_mapToMCA = [];

    /**
     * @param $privileges
     * @param $MCA
     * @return bool
     */
    public function getPrivilegeLevelFor($privileges, $MCA)
    {
        if (!is_array($privileges)){
            $privileges = explode(',', $privileges);
        }
        if (!$this->_privilegeConfig){
            # TODO: create file with privileges
            $this->_privilegeConfig = include APPLICATION_PATH_SET . '/application-privileges.php';
        }

        $findCompare = function($privilege, $step = 0) use ($MCA, &$findCompare){
            $compare = !$step ? $MCA['module'] : ($step==1 ? $MCA['controller'] : $MCA['action']);
            if ( in_array('*', $privilege) || in_array($compare, $privilege)) return 1;
            if ( ($hasStar = !empty($privilege['*'])) || !empty($privilege[$compare]) ){
                if ($hasStar){
                    return $privilege['*'];
                }else if ($step==2){
                    return $privilege[$compare];
                }

                return $findCompare($privilege[$compare], ++$step);
            }
            return false;
        };

        foreach($privileges as $privilege){
            if (!isset($this->_privilegeConfig[$privilege])) continue;
            if ($compare = $findCompare($this->_privilegeConfig[$privilege]) ){

                //match!
                return $compare;
            }
        }
        return false;
    }
}