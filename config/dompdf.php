<?php

return [

    /* -------------------------------------------------------------
     | GENERAL SETTINGS
     |--------------------------------------------------------------
     | كامل ملف الإعدادات الافتراضي مضاف إليه تسجيل خط Cairo
     */

    'show_warnings' => false,
    'public_path'   => null,

    /* ------------------------- FONT DIR / CACHE ----------------- */
    'fontDir'   => storage_path('fonts/'),
    'fontCache' => storage_path('fonts/'),

    /* ------------------------- EMBEDDED FONTS ------------------- */
    'font_data' => [
        // Cairo Arabic/Latin
        'cairo' => [
            'R'          => 'Cairo-Regular.ttf',
            'B'          => 'Cairo-Bold.ttf',
            'useOTL'     => 0xFF,
            'useKashida' => 75,
        ],
        // الخط الافتراضي القديم يبقى احتياطيًا
        'dejavusans' => [
            'R' => 'DejaVuSans.ttf',
            'B' => 'DejaVuSans-Bold.ttf',
            'I' => 'DejaVuSans-Oblique.ttf',
            'BI'=> 'DejaVuSans-BoldOblique.ttf',
        ],
    ],
    'default_font' => 'cairo',
    'isFontSubsettingEnabled' => true,

    /* -------------------------------------------------------------
     | CONVERSION & RENDER OPTIONS
     |-------------------------------------------------------------- */
    'convert_entities' => true,

    'options' => [
        'font_dir'    => storage_path('fonts'),
        'font_cache'  => storage_path('fonts'),
        'temp_dir'    => sys_get_temp_dir(),

        'chroot'      => realpath(base_path()),

        'allowed_protocols' => [
            'data://'  => ['rules' => []],
            'file://'  => ['rules' => []],
            'http://'  => ['rules' => []],
            'https://' => ['rules' => []],
        ],

        'pdf_backend'            => 'CPDF',
        'default_media_type'     => 'screen',
        'default_paper_size'     => 'a4',
        'default_paper_orientation' => 'portrait',

        'dpi'                    => 96,
        'enable_php'             => false,
        'enable_javascript'      => true,
        'enable_remote'          => true,
        'allowed_remote_hosts'   => null,

        'font_height_ratio'      => 1.1,
        'enable_html5_parser'    => true,
    ],

];
