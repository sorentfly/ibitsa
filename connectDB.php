<?

defined('_ENGINE_GENERAL')
||
die('Unable to connect to database.');

return new mysqli(
    CONFIG__DB_HOST,
    CONFIG__DB_USER_NAME,
    CONFIG__DB_USER_PASS,
    CONFIG__DB_SCHEME
);