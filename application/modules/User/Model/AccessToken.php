<?
class User_Model_AccessToken extends Application_Model_Token
{
    public function __toString()
    {
        return $this->token;
    }
}
