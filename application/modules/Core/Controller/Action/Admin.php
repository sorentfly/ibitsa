<?

/**
 *
 *
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 *
 */
abstract class Core_Controller_Action_Admin extends Core_Controller_Action_User
{
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        // Neuter
        if( defined('_ENGINE_ADMIN_NEUTER') && _ENGINE_ADMIN_NEUTER ) {
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_POST = array();
            $_FILES = array();
        }

        // Parent
        /*
        parent::__construct($request, $response, $invokeArgs);
         */
        $this->setRequest($request)
            ->setResponse($response)
            ->_setInvokeArgs($invokeArgs);
        $this->_helper = new Zend_Controller_Action_HelperBroker($this);
        $this->init();

        // Normal
        $this->_helper->contextSwitch->setLayout('smoothbox', 'admin-simple');
        if( !$this->_helper->requireAdmin->checkRequire() ) {
            if( !$this->_helper->requireUser()->isValid() ) {
                return;
            }
            $this->_helper->requireAdmin();
            return;
        }
        //$this->_helper->requireAdmin();

        // Reauthentication
        if( !Engine_Api::_()->authorization()->isReAuthenticated() ){
            $_SESSION['ADMIN_REQ_URL'] = $_SERVER['REQUEST_URI'];
            return $this->_helper->redirector->gotoRoute(array('controller' => 'auth', 'action' => 'login'), 'admin_default', true);
        }




        // Neuter
        if( defined('_ENGINE_ADMIN_NEUTER') && _ENGINE_ADMIN_NEUTER ) {
            $this->view->headScript()->appendScript("
window.addEvent('load', function() {
  $$('form[method=post] button[type=submit]')
    /*.set('disabled', true)*/
    .setStyles({
      'background-color' : '#868686',
      'border' : '1px solid #777777',
    })
    .addEvent('click', function(event) {
      event.stop();
      alert('disabled');
    });
});
");
        }
    }
    public function postDispatch()
    {
        $layoutHelper = $this->_helper->layout;
        if( $layoutHelper->isEnabled() && !$layoutHelper->getLayout() )
        {
            $layoutHelper->setLayout('admin');
        }
    }

    public function csv_folder_to_array($folder_path, $locale_code) {
        // Gather Folder's CSV Files
        $csv_file_array = array();
        foreach ( glob($folder_path . DIRECTORY_SEPARATOR . '*.csv') as $csv_filename) {
            $csv_file_array[] = $csv_filename;
        }

        // Loop through each CSV and Digest The CSV Contents
        $csv_file_count = count( $csv_file_array );
        $big_array = array();
        for( $i = 0; $i < $csv_file_count; $i++) {
            // Check for "custom.csv", do this last.
            if( $csv_file_array[$i] != $folder_path . 'custom.csv' ) {
                $file_array = $this->_csv_to_array( $csv_file_array[$i] );
                $big_array = array_merge($big_array, $file_array);
            }
        }

        // Create Array from "custom.csv" and merge with Big array
        if( file_exists( $folder_path . 'custom.csv' ) ) {
            $custom_array = $this->_csv_to_array($folder_path . 'custom.csv' );
            $big_array = array_merge($big_array, $custom_array);
        }

        $file_contents = '<?php' . "\n" . 'return ';
        $file_contents .= var_export($big_array, true);
        $file_contents .= ";\n?>";

        // Write $big_array to appropriate file
        $php_array_file = $folder_path . '/' . $locale_code . '.php';
        touch($php_array_file);
        chmod($php_array_file, 0777);
        $fp = fopen($php_array_file, 'w');
        fwrite($fp, $file_contents);
        fclose($fp);

        if (function_exists('exec')) {
            exec('php -l ' . escapeshellarg($php_array_file), $output, $ret);
            return $ret == 0;
        }

        return true;
    }

    protected function _csv_to_array( $csv_file ) {
        $file_array = array();
        if( ( $data = fopen($csv_file, 'r')) !== FALSE) {
            // ignore first characters of file until double quotes are found (")
            while( ( $phrase = @fgetcsv( $data, 0, ';', '"', '\#' ) ) !== FALSE ) {

                // Make Sure Incoming phrase starts with Double Quotes (")
                $start = strpos( $phrase[0], '"');
                if( $start != 0 ) {
                    $phrase[0] =  substr( $phrase[0], $start );
                }

                // If First and Last charachters are " double quotes, remove them
                if( strpos( $phrase[0], '"') == 0 && strrpos($phrase[0] , '"') == (strlen( $phrase[0] ) - 1 ) ) {
                    $phrase[0] = substr($phrase[0], 1,  -1);
                }

                $phrase_count = count( $phrase );
                if( $phrase_count == 2 ) {
                    // Add Singular Phrases
                    $file_array[ $phrase[0] ] = $phrase[1];
                }
                elseif( $phrase_count > 2 ) {
                    // Add Pluralized Phrases
                    $plural_array = array();
                    for($c = 1; $c < $phrase_count; $c++ ) {
                        $plural_array[] = $phrase[$c];
                    }
                    $file_array[ $phrase[0] ] = $plural_array;
                }

                else{
                    // Do nothing for Blank Phrases
                }

            }

        }

        return $file_array;
    }

    protected function _check_phrase( $phrase ) {
        // Check for Place Holders (pregreplace $ with \$)
        $phrase = preg_replace('/\$/', '\\\$', $phrase);

        // Check for Double Quotes
        $double_quote_check = strpos( $phrase, '"');

        // Check for Single Quotes
        $single_quote_check = strpos( $phrase, "'" );

        // If both exist, Add Double quotes as First and Last Charaacters, Then Escape Double Quotes
        if( $double_quote_check !== FALSE && $single_quote_check !== FALSE) {
            $phrase = '"' . preg_replace('/\"/', '\\\"', $phrase) . '"';
        }

        // If Double Quotes exist, Suround with Single Quotes
        elseif( $double_quote_check !== FALSE ) {
            $phrase = "'" . $phrase . "'";
        }
        // If Single Quotes Exist Surround with Double Quotes
        else{ $phrase = '"' . $phrase . '"'; }

        return $phrase;
    }
}