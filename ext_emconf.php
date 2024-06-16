<?php


$EM_CONF['jve_upgradewizard'] = [
    'title' => 'Update helper for TYPO3 LTS- 11 / 12',
    'description' => 'Fixes the ways Typoscript and TSconfig included files: in database and in template extensions',
    'category' => 'plugin',
    'author' => 'Joerg Velletti',
    'author_email' => 'typo3@velletti.de',
    'state' => 'beta',
    'version' => '12.4.18',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.1-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
