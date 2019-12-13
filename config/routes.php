<?php
return [
    'index' =>
    [
        'path' => '/',
        'controller' => 'MainController',
        'action' => 'index'
    ],
    'editor_new' =>
        [
            'path' => '/new/',
            'controller' => 'EditorController',
            'action' => 'index'
        ],
    'editor_edit' =>
    [
        'path' => '/edit/:filename/',
        'controller' => 'EditorController',
        'action' => 'index'
    ],
];