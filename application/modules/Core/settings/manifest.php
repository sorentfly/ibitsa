<?
return [
    // Package -------------------------------------------------------------------
    'package' => [
        'type'          => 'module',
        'name'          => 'core',
        'version'       => '4.8.7',
        'revision'      => '$Revision: 10271 $',
        'path'          => 'application/modules/Core',
        'repository'    => BITSA_SITE,
        'title'         => 'Core',
        'description'   => 'Core',
        'author'        => 'Vlad Smith',
        'dependencies' => [
            [
                'type' => 'library',
                'name' => 'engine',
                'required' => true,
                'minVersion' => '4.1.8',
            ],
        ],
        'directories' => [
            'application/modules/Core',
        ],
        'files' => [
            'application/languages/en/core.csv',
        ],
    ],
    // Composer -------------------------------------------------------------------
    'composer' => [
        'link' => [
            'script' => ['_composeLink.tpl', 'core'],
            'plugin' => 'Core_Plugin_Composer',
            'auth' => ['core_link', 'create'],
        ],
        'tag' => [
            'script' => ['_composeTag.tpl', 'core'],
            'plugin' => 'Core_Plugin_Composer',
        ],
    ],
    // Hooks ---------------------------------------------------------------------
    'hooks' => [
        [
            'event' => 'onItemDeleteBefore',
            'resource' => 'Core_Plugin_Core',
        ],
        [
            'event' => 'onRenderLayoutDefault',
            'resource' => 'Core_Plugin_Core',
        ],
        [
            'event' => 'onRenderLayoutDefaultSimple',
            'resource' => 'Core_Plugin_Core',
        ],
        [
            'event' => 'onRenderLayoutMobileDefault',
            'resource' => 'Core_Plugin_Core',
        ],
        [
            'event' => 'onRenderLayoutMobileDefaultSimple',
            'resource' => 'Core_Plugin_Core',
        ],
        [
            'event' => 'onItemCreateAfter',
            'resource' => 'Core_Plugin_Core',
        ],

    ],
    // Items ---------------------------------------------------------------------
    'items' => [
        'core_ad',
        'core_adcampaign',
        'core_adphoto',
        'core_comment',
        'core_geotag',
        'core_link',
        'core_like',
        'core_list',
        'core_list_item',
        'core_page',
        'core_report',
        'core_mail_template',
        'core_tag',
        'core_tag_map',
        'core_custom_form',
        'core_custom_form_response',
        'tab',
        'division',
        'urlrewrite',
        'branch',
        'ticket'
    ],
    // Routes --------------------------------------------------------------------
    'routes' => [
        'home' => [
            'route'     => '/',
            'defaults'  => [
                'module' => 'core',
                'controller' => 'index',
                'action' => 'index'
            ]
        ],
        'core_admin_settings' => [
            'route'     => "admin/core/settings/:action/*",
            'defaults'  => [
                'module' => 'core',
                'controller' => 'admin-settings',
                'action' => 'index'
            ],
            'reqs' => [
                'action' => '\D+',
            ]
        ],
    ]
];
