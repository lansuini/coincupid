<?php
$menu = [
    [
        'is_menu' => 0,
        'sort' => 0,
        'name' => 'ts.Dashboard',
        'key' => 'Dashboard',
        'url' => 'home',
        'icon' => 'mif-meter',
        'routes' => [
            [['GET'], '/'],
        ],
        'sub_menu_list' => [
            ['is_menu' => 0, 'sort' => 1, 'name' => 'ts.GetBaseData', 'key' => 'basedata', 'routes' => [
                [['GET'], 'getbasedata'],
            ]],
            ['is_menu' => 0, 'sort' => 1, 'name' => 'ts.Lang', 'key' => 'lang', 'routes' => [
                [['POST'], 'admin/lang'],
            ]],
            ['is_menu' => 0, 'sort' => 1, 'name' => 'ts.UpdatePassword', 'key' => 'account_password_edit', 'routes' => [
                [['GET'], 'server/game/maintenance/view'],
                [['POST'], 'server/game/maintenance'],
            ]],
            ['is_menu' => 0, 'sort' => 2, 'name' => 'ts.UpdateGoogleCode', 'key' => 'account_google_code_edit', 'routes' => [
                [['GET'], 'manager/account/googlecode/view'],
                [['POST'], 'manager/account/googlecode'],
            ]],
        ]
    ],
    [
        'is_menu' => 1,
        'sort' => 2,
        'name' => 'ts.Game User',
        'key' => 'game_user',
        'url' => '',
        'icon' => '',
        'sub_menu_list' => [
            ['is_menu' => 1, 'sort' => 1, 'name' => 'ts.Account', 'key' => 'manage_admin', 'url' => 'user/account/view', 'routes' => [
                [['GET'], 'user/account/view'],
                [['GET', 'POST'], 'user/account'],
                [['GET', 'PATCH', 'DELETE'], 'user/account/{id}']
            ]],
            ['is_menu' => 1, 'sort' => 2, 'name' => 'ts.UserEnterExitRoomWinLose', 'key' => 'user_enter_exit_room_win_lose', 'url' => 'user/enterexitroomwinlose/view', 'routes' => [
                [['GET'], 'user/enterexitroomwinlose/view'],
                [['GET'], 'user/enterexitroomwinlose'],
            ]],
        ],
    ],
    [
        'is_menu' => 1,
        'sort' => 3,
        'name' => 'ts.Game Report',
        'key' => 'game_report',
        'url' => '',
        'icon' => '',
        'sub_menu_list' => [
            ['is_menu' => 1, 'sort' => 1, 'name' => 'ts.Total Report', 'key' => 'total_report', 'url' => 'report/total/view', 'routes' => [
                [['GET'], 'report/total/view'],
                [['GET'], 'report/total'],
            ]],
            ['is_menu' => 1, 'sort' => 2, 'name' => 'ts.Day Report', 'key' => 'day_report', 'url' => 'report/day/view', 'routes' => [
                [['GET'], 'report/day/view'],
                [['GET'], 'report/day'],
            ]],

            ['is_menu' => 1, 'sort' => 3, 'name' => 'ts.Data Report', 'key' => 'data_report', 'url' => 'report/datareport/view', 'routes' => [
                [['GET'], 'report/datareport/view'],
                [['GET'], 'report/datareport'],
            ]],
        ],
    ],

    [
        'is_menu' => 1,
        'sort' => 3,
        'name' => 'ts.Support',
        'key' => 'support',
        'url' => '',
        'icon' => '',
        'sub_menu_list' => [
            ['is_menu' => 1, 'sort' => 1, 'name' => 'ts.API Document', 'key' => 'api_document', 'url' => 'support/apidocument/view', 'routes' => [
                [['GET'], 'support/apidocument/view'],
                [['GET'], 'support/apidocument'],
            ]],
        ],
    ],
];

return $menu;
