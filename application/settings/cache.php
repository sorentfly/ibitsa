<?
defined('_ENGINE') or die(__BROAD_ERROR__ENGINE);

$temporary = [
    'File'      => [
        'default_backend'   => _ENGINE_CACHE_MODE_FILE,
        'frontend' => [
            'core' => [
                'automatic_serialization'   => TRUE,
                'cache_id_prefix'           => 'Bitsa_Engine_',
                'lifetime'                  => '7200',
                'caching'                   => TRUE,
                'gzip'                      => TRUE,
            ],
        ],
        'backend' => [
            'File' => [
                'cache_dir' => APPLICATION_PATH_CACHE
            ]
        ],
        'default_file_path' => APPLICATION_PATH_CACHE,
    ],

    'Memcached' => [
        'default_backend'   => _ENGINE_CACHE_MODE_MEMCACHED,
        'frontend' => [
            'core' => [
                'automatic_serialization'   => TRUE,
                'cache_id_prefix'           => 'Engine4_',
                'lifetime'                  => '7200',
                'caching'                   => TRUE,
                'gzip'                      => TRUE,
            ],
        ],
        'backend' => [
            'Memcached' => [
                'servers' => [
                    0 => [
                        'host' => '127.0.0.1',
                        'port' => 11211,
                    ],
                ],
                'compression' => FALSE,
            ],
        ],
        'default_file_path' => APPLICATION_PATH_CACHE,
    ]
];



if (
    isset($temporary[_DEVELOPER__CACHE])
    && !empty($temporary[_DEVELOPER__CACHE])
) {
    defined('_ENGINE_CACHE_CORE_CONF')   ||
    define('_ENGINE_CACHE_CORE_CONF',       $temporary[_DEVELOPER__CACHE]);

    unset($temporary);

    return _ENGINE_CACHE_CORE_CONF;
}