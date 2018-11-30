<?

/**
 *
 * @category Application_Core
 * @package Authorization
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 */
return [
    // Package -------------------------------------------------------------------
    'package' => [
        'type' => 'module',
        'name' => 'authorization',
        'version' => '4.2.3',
        'revision' => '$Revision: 9747 $',
        'path' => 'application/modules/Authorization',
        'title' => 'Authorization',
        'description' => 'Authorization',
        'author' => 'Vlad Smith',
        'dependencies' => [
            [
                'type' => 'module',
                'name' => 'core',
                'minVersion' => '4.2.0',
            ],
        ],
        'directories' => [
            'application/modules/Authorization',
        ],
        'files' => [
            'application/languages/en/authorization.csv',
        ],
    ],
    // Hooks ---------------------------------------------------------------------
    'hooks' => [
        [
            'event' => 'onItemDeleteBefore',
            'resource' => 'Authorization_Plugin_Core',
        ],
    ],
    // Items ---------------------------------------------------------------------
    'items' => [
        'authorization_level'
    ],
    // Routes --------------------------------------------------------------------
];