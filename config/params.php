<?php
return [
    'controllers' =>
    [
        'namespace' => 'Multitext\Controller\\',
        'path' => 'src/controllers/'
    ],
    'services' =>
    [
        'websocket' =>
        [
            'url' => 'wss://{%%host%%}:443/websocket/'
        ]
    ],
    'view' =>
    [
        'scripts' =>
        [
            'default' => ['js/jquery-3.4.1.js'],
            'editor/editor.php' => ['js/state.js', 'js/socket.js']
        ]
    ]
];