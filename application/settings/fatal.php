<?

function bitsa_fatal_handler() {
    $error = error_get_last();

    if (empty($error) || $error['type']!=1){
        return;
    }

    $error_code = 'FATAL';

    # |- Do whatever you want with this error, for example:
    $output = PHP_EOL . 'Error Code: ' . $error_code . PHP_EOL;

    if (!empty($_SERVER['REQUEST_URI'])){
        $output .= 'URL:' . (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . PHP_EOL;
    }

    if ( Engine_Api::_()->hasModuleBootstrap('user') ){
        if ( ($viewer = Engine_Api::_()->user()->getViewer()) && $viewer->getIdentity()){
            $output .= 'VIEWER: ' . $viewer->getIdentity() . PHP_EOL;
        }else{
            $output .= 'VIEWER: no' . PHP_EOL;
        }
    }

    $output .= var_export($error, true) . PHP_EOL;
    if ( ($log = Zend_Registry::get('Zend_Log')) instanceof Zend_Log ) {
        $log->log($output, Zend_Log::CRIT);
    }

    echo("Fatal occured.");
}


register_shutdown_function('bitsa_fatal_handler');