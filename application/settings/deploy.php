<?
defined('_ENGINE') or die('Deploy config can not be included.');

#
##
###
####
#   Paths of an application for comfortable access.
########################################################################################################################
if (!defined('ENGINE_DEPLOY__PATH_CONSTANTS')) {
    define('ENGINE_DEPLOY__PATH_CONSTANTS',             TRUE); # |>--- Self-stub

    defined('_ENGINE_DEPLOY_SQL')               ||       # |>--- Solid SQL file to execute to deploy
    define('_ENGINE_DEPLOY_SQL',                        APPLICATION_PATH_PRV . DS . 'deploy' . DS . 'sql' . DS . 'solid.sql');


}