<?
return array(
    // Package -------------------------------------------------------------------
    'package' => array(
        'type' => 'module',
        'name' => 'user',
        'version' => '4.2.8',
        'revision' => '$Revision: 9808 $',
        'path' => 'application/modules/User',
        'repository' => 'socialengine.com',
        'title' => 'Members',
        'description' => 'Members',
        'author' => 'Webligo Developments',
        'changeLog' => 'settings/changelog.php',
        'dependencies' => array(
            array(
                'type' => 'module',
                'name' => 'core',
                'minVersion' => '4.2.0',
            ),
        ),
        'actions' => array(
            'install',
            'upgrade',
            'refresh',
        //'enable',
        //'disable',
        ),
        'callback' => array(
            'path' => 'application/modules/User/settings/install.php',
            'class' => 'User_Installer',
            'priority' => 3000,
        ),
        'directories' => array(
            'application/modules/User',
        ),
        'files' => array(
            'application/languages/en/user.csv',
        ),
    ),
    // Compose -------------------------------------------------------------------
    'compose' => array(
        array('_composeFacebook.tpl', 'user'),
        array('_composeTwitter.tpl', 'user'),
    ),
    'composer' => array(
        'facebook' => array(
            'script' => array('_composeFacebook.tpl', 'user'),
        ),
        'twitter' => array(
            'script' => array('_composeTwitter.tpl', 'user'),
        ),
    ),
    // Hooks ---------------------------------------------------------------------
    'hooks' => array(
        array(
            'event' => 'onUserEnable',
            'resource' => 'User_Plugin_Core',
        ),
        array(
            'event' => 'onUserDeleteBefore',
            'resource' => 'User_Plugin_Core',
        ),
        array(
            'event' => 'onUserCreateAfter',
            'resource' => 'User_Plugin_Core',
        )
    ),
    // Items ---------------------------------------------------------------------
    'items' => array(
        'user',
        'user_list',
        'user_list_item',
    ),
    // Routes --------------------------------------------------------------------
    'routes' => array(
        // User - General
        'user_extended' => array(
            'route' => 'members/:controller/:action/*',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'index',
                'action' => 'index'
            ),
            'reqs' => array(
                'controller' => '\D+',
                'action' => '\D+',
            )
        ),
        'user_profile_edit_adv' => array(
            'route' => 'members/edit/profile/:tabname',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'edit',
                'action' => 'profile'
            ),
            'reqs' => array(
                'tabname' => '\w*',
            )
        ),
        'user_profile_edit_adv_by_side' => array(
            'route' => 'members/edit/profile/id/:user_id/:tabname/*',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'edit',
                'action' => 'profile'
            ),
            'reqs' => array(
                'user_id' => '\d+',
                'tabname' => '\w*'
            )
        ),
        'user_general' => array(
            'route' => 'members/:action/*',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'index',
                'action' => 'browse'
            ),
            'reqs' => array(
                'action' => '(home|browse|apicadastre)',
            )
        ),
        // User - Specific
        'profile_redirect' => array(
            'route' => 'profile/',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'index',
                'action' => 'home'
            )
        ),
        'user_profile' => array(
            'route' => 'profile/:id/',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'profile',
                'action' => 'index'
            )
        ),
        'user_profile_with_tabs' => array(
            'route' => 'profile/:id/:tabname/*',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'profile',
                'action' => 'index'
            )
        ),
        'user_profile_with_tabs_and_page' => array(
            'route' => 'profile/:id/:tabname/:page/*',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'profile',
                'action' => 'index'
            ),
            'reqs' => array(
                'page' => '\d+'
            )
        ),


        'user_foreign_login' => array(
            'route' => '/foreign_login/*',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'auth',
                'action' => 'foreign-login'
            ),
            'reqs' => array(
                'foreign_host' => '([а-яa-z0-9\-\.]*)\.(([а-яa-z]{2,4})',
                'redirect_uri' => '\w*'
            )
        ),
        'user_login' => array(
            //'type' => 'Zend_Controller_Router_Route_Static',
            'route' => '/login/*',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'auth',
                'action' => 'login'
            )
        ),
        'user_login_school' => array(
            'route' => '/login_school/*',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'auth',
                'action' => 'login',
                'is_school' => true
            )
        ),
        'user_logout' => array(
            'type' => 'Zend_Controller_Router_Route_Static',
            'route' => '/logout',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'auth',
                'action' => 'logout'
            )
        ),
        'user_regorenter' => array(
            'route' => '/loginorregister/*',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'signup',
                'action' => 'loginorregister'
            )
        ),
        'user_signup' => array(
            'route' => '/signup/*',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'signup',
                'action' => 'index'
            )
        ),
        'user_unsubscribe' => array(
            'route' => '/unsubscribe/*',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'subscribe',
                'action' => 'unsubscribe'
            )
        ),
        //#Referral code### 
        'user_signup_referral' => array(
            'route' => '/signup/:friend/*',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'signup',
                'action' => 'index'
            ),
            'reqs' => array(
                'friend' => '\d*',
            )
        ),
        'user_referral_list' => array(
            'route' => '/referrals',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'referral',
                'action' => 'index'
            )
        ),
        'user_referral_admin' => array(
            'route' => '/referrals/admin',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'referral',
                'action' => 'admin'
            )
        ),
        'user_signup_check' => array(
            'route' => '/signup/:action/',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'signup'
            ),
            'reqs' => array(
                'action' => 'check|mailcheck|usercheck|resend|taken|confirm'
            )
        ),
        'user_signup_resend' => array(
            'route' => '/signup/resend/:user_id',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'signup',
                'action' => 'resend'
            ),
            'reqs' => array(
                'user_id' => '.*'
            )
        ),
        'license_agreement' => array(
            'route' => '/help/terms/:option/',
            'defaults' => array(
                'module' => 'core',
                'controller' => 'help',
                'action' => 'terms',
                'option' => 'full'
            ),
            'reqs' => array(
                'option' => '(text|full)',
            )
        ),
        'user_signup_verify' => array(
            'route' => '/signup/verify/:id/:code/',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'signup',
                'action' => 'verify'
            ),
            'reqs' => array(
                'id' => '\d+',
                'code' => '\w+'
            )
        ),
        'user_profile_edit' => array(
            'route' => '/user_edit/:form_name/:user_id/',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'Edit',
                'action' => 'edit'
            ),
            'reqs' => array(
                'form_name' => '\w*',
                'user_id' => '\d+'
            ),
        ),
        'user_search' => array(
            'route' => 'user_search/',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'search',
                'action' => 'index'
            )
        ),
        'profile_finish' => array(
            'route' => 'profile_finish/',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'edit',
                'action' => 'end'
            )
        ),
        'back_to_olympic' => array(
            'route' => 'to_olympic/',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'edit',
                'action' => 'back'
            )
        ),
        'statistics' => array(
            'route' => 'statistics/',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'statistics',
                'action' => 'index'
            )
        ),
        'custom_forms' => array(
            'route' => 'user/special-forms/form/key/:form_id/*',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'customForms',
                'action' => 'custom'
            ),
            'reqs' => array(
                'form_id' => '\d+'
            ),
        ),
        'api_user_data' => array(
            'route' => 'api/user/get/*',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'api',
                'action' => 'get'
            ),
        ),
    // END CODE!!!
    )
);
