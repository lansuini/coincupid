<?php

/**
 * 权限、菜单总配置，数组格式
 * sort 排序仅对同级有效
 * 数组的key: 权限标识，用作权限判断，绝不能重复！！！！！
 * 数组的key: 权限标识，用作权限判断，绝不能重复！！！！！
 * 数组的key: 权限标识，用作权限判断，绝不能重复！！！！！
 */

$menu = [
    [
        'is_menu' => 2,
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
        'name' => 'ts.Game Server',
        'key' => 'server_config',
        'url' => '',
        'icon' => '',
        'sub_menu_list' => [
            ['is_menu' => 1, 'sort' => 1, 'name' => 'ts.GameRoom', 'key' => 'game_room_list', 'url' => 'server/game/room/view', 'routes' => [
                [['GET'], 'server/game/room/view'],
                [['GET', 'POST'], 'server/game/room'],
                [['GET', 'PATCH', 'DELETE'], 'server/game/room/{id}'],
                [['PATCH'], 'server/game/room/enabled/{id}'],
                [['GET', 'PATCH'], 'server/game/room/json/{id}'],
                [['POST'], 'server/game/room/pushconfig'],
                [['GET', 'PATCH'], 'server/game/room/json/{id}'],
                [['GET', 'PATCH'], 'server/game/room/process/{id}'],
                [['GET', 'PATCH'], 'server/game/room/inventory/{id}'],
            ]],
            ['is_menu' => 0, 'sort' => 1, 'name' => 'ts.GameRoomEnableOC', 'key' => 'game_room_list_enable', 'routes' => [
                [['PATCH'], 'server/game/room/enabled/{id}'],
            ]],
            ['is_menu' => 1, 'sort' => 2, 'name' => 'ts.GamePlay', 'key' => 'game_play_list', 'url' => 'server/game/play/view', 'routes' => [
                [['GET'], 'server/game/play/view'],
                [['GET', 'POST'], 'server/game/play'],
                [['GET', 'PATCH', 'DELETE'], 'server/game/play/{id}']
            ]],
            ['is_menu' => 1, 'sort' => 3, 'name' => 'ts.ProcessControl', 'key' => 'game_process_control', 'url' => 'server/game/processcontrol/view',  'routes' => [
                [['GET'], 'server/game/processcontrol/view'],
                [['GET', 'POST'], 'server/game/processcontrol'],
                [['GET', 'PATCH', 'DELETE'], 'server/game/processcontrol/{id}']
            ]],
            ['is_menu' => 1, 'sort' => 4, 'name' => 'ts.Maintenance', 'key' => 'game_maintenance', 'url' => 'server/game/maintenance/view', 'routes' => [
                [['GET'], 'server/game/maintenance/view'],
                [['GET', 'PATCH'], 'server/game/maintenance'],
            ]],
            ['is_menu' => 1, 'sort' => 5, 'name' => 'ts.ServerRoom', 'key' => 'room_list', 'url' => 'server/room/view', 'routes' => [
                [['GET'], 'server/room/view'],
                [['GET', 'POST'], 'server/room'],
                [['GET', 'PATCH', 'DELETE'], 'server/room/{id}'],
                [['PATCH'], 'server/room/enabled/{id}'],
            ]],
        ],
    ],
    [
        'is_menu' => 1,
        'sort' => 3,
        'name' => 'ts.DevTools',
        'key' => 'dev_tools',
        'url' => '',
        'icon' => '',
        'sub_menu_list' => [
            ['is_menu' => 1, 'sort' => 1, 'name' => 'ts.WebLogAnalysis', 'key' => 'devtools_webloganalysis', 'url' => 'devtools/webloganalysis/view', 'routes' => [
                [['GET'], 'devtools/webloganalysis/view'],
                [['GET'], 'devtools/webloganalysis'],
            ]],
            ['is_menu' => 1, 'sort' => 2, 'name' => 'ts.Config', 'key' => 'devtools_config', 'url' => 'devtools/config/view', 'routes' => [
                [['GET'], 'devtools/config/view'],
                [['GET', 'PATCH', 'POST'], 'devtools/config'],
            ]],
        ],
    ],
    [
        'is_menu' => 1,
        'sort' => 999,
        'name' => 'ts.AdminManage',
        'key' => 'account_manage',
        'url' => '',
        'icon' => '',
        'sub_menu_list' => [
            ['is_menu' => 1, 'sort' => 1, 'name' => 'ts.Account', 'key' => 'manage_admin', 'url' => 'manager/account/view', 'routes' => [
                [['GET'], 'manager/account/view'],
                [['GET', 'POST'], 'manager/account'],
                [['GET', 'PATCH', 'DELETE'], 'manager/account/{id}']
            ]],
            ['is_menu' => 1, 'sort' => 2, 'name' => 'ts.Role', 'key' => 'manage_role', 'url' => 'manager/role/view', 'routes' => [
                [['GET'], 'manager/role/view'],
                [['GET', 'POST'], 'manager/role'],
                [['GET', 'PATCH', 'DELETE'], 'manager/role/{id}']
            ]],
            ['is_menu' => 1, 'sort' => 3, 'name' => 'ts.LoginLog', 'key' => 'manage_login_log', 'url' => 'manager/loginlog/view', 'routes' => [
                [['GET'], 'manager/loginlog/view'],
                [['GET'], 'manager/loginlog'],
            ]],
            ['is_menu' => 1, 'sort' => 4, 'name' => 'ts.ActionLog', 'key' => 'manage_action_log', 'url' => 'manager/actionlog/view', 'routes' => [
                [['GET'], 'manager/actionlog/view'],
                [['GET'], 'manager/actionlog'],
                [['GET'], 'manager/actionlog/{id}']
            ]],
        ],
    ],
];

return $menu;
