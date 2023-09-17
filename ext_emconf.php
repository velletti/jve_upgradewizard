<?php


$EM_CONF['jve_upgradewizard'] = [
    'title' => 'Update helper for lts12',
    'description' => 'Fixes the ways included files have been added in database.',
    'category' => 'plugin',
    'author' => 'Joerg Velletti',
    'author_email' => 'typo3@velletti.de',
    'state' => 'beta',
    'version' => '12.4.6',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.1-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
