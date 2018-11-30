<?php

$domains = [
    'bitsa' => [
        'domain'        => BITSA_SITE /* !domain constants must to defined in configs in root! For good local/sandbox project deploying. */,
        'title'         => 'iBitsa',                                                # site_name
        'description'   => 'Социальная сеть для всех людей на планете.',
        'logo'          => __BROAD_IMAGE_FOLDER . 'admin' . DS . 'logo.png',        # image path
        'og:logo'       => '//' . BITSA_SITE . '/public/images/admin/logo.png',     # og:image

        'layouts'       => [
            'default' => 'default'
        ],

        # 'activity_enable' => false,                                               # Мощное логгирование действий

        'contact_email' => 'faq' . '@' . BITSA_SITE,
        'language'      => 'ru',                                                    # en/ru
        'default_locale' => 'ru_RU',                                                # ru_RU/en_EN
    ]
];

return $domains;
