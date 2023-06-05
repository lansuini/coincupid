<?php
$menu = [
    [
        'is_menu' => 2,
        'sort' => 0,
        'name' => 'ts.Dashboard',
        'key' => 'Dashboard',
        'url' => 'dashboard/view',
        'icon' => 'mif-meter',
        'routes' => [
            [['GET'], 'dashboard/view'],
            [['GET'], 'dashboard'],
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
        'name' => 'ts.User',
        'key' => 'User',
        'url' => '',
        'icon' => '',
        'sub_menu_list' => [
            ['is_menu' => 1, 'sort' => 1, 'name' => 'ts.Account', 'key' => 'user_account', 'url' => 'user/account/view', 'routes' => [
                [['GET'], 'user/account/view'],
                [['GET'], 'user/accountdetail'],
                [['GET'], 'user/getOccupationTree'],
                [['POST','PATCH'], 'user/accountEdit/{id}'],
                [['POST','PATCH'], 'user/accountStatusEdit/{id}'],
            ]],
            ['is_menu' => 1, 'sort' => 2, 'name' => 'ts.LoginLog', 'key' => 'user_login_log', 'url' => 'user/loginlog/view', 'routes' => [
                [['GET'], 'user/loginlog/view'],
                [['GET'], 'user/loginlog'],
            ]],
            ['is_menu' => 1, 'sort' => 3, 'name' => 'ts.PlayLog', 'key' => 'User_play_log', 'url' => 'user/playlog/view', 'routes' => [
                [['GET'], 'user/playlog/view'],
                [['GET'], 'user/playlog'],
                [['GET'], 'user/playlogdetail'],
            ]],
            ['is_menu' => 1, 'sort' => 4, 'name' => 'ts.GoldLog', 'key' => 'user_gold_log', 'url' => 'user/goldlog/view', 'routes' => [
                [['GET'], 'user/playgold/view'],
                [['GET'], 'user/goldlog'],
            ]],
            ['is_menu' => 1, 'sort' => 5, 'name' => 'ts.Real-OnlinePlay', 'key' => 'user_real_online_play', 'url' => 'user/realonlineplay/view', 'routes' => [
                [['GET'], 'user/realonlineplay/view'],
                [['GET'], 'user/realonlineplay'],
            ]],
            ['is_menu' => 1, 'sort' => 6, 'name' => 'ts.Live Match', 'key' => 'User_live_match', 'url' => 'user/livematch/view', 'routes' => [
                [['GET'], 'user/livematch/view'],
                [['GET'], 'user/livematch'],
            ]],
            ['is_menu' => 1, 'sort' => 7, 'name' => 'ts.Online', 'key' => 'User_online', 'url' => 'user/online/view', 'routes' => [
                [['GET'], 'user/online/view'],
                [['GET'], 'user/online'],
            ]],
            ['is_menu' => 1, 'sort' => 8, 'name' => 'ts.RoomWinLose', 'key' => 'User_room_win_lose', 'url' => 'user/roomwinlose/view', 'routes' => [
                [['GET'], 'user/roomwinorlose/view'],
                [['GET'], 'user/roomwinlose'],
            ]],
            ['is_menu' => 1, 'sort' => 9, 'name' => 'ts.DataReport', 'key' => 'data_report', 'url' => 'user/datareport/view', 'routes' => [
                [['GET'], 'user/datareport/view'],
                [['GET'], 'user/datareport'],
            ]],
        ]
    ],
    [
        'is_menu' => 1,
        'sort' => 2,
        'name' => 'ts.Customer',
        'key' => 'customer',
        'url' => '',
        'icon' => '',
        'sub_menu_list' => [
            ['is_menu' => 1, 'sort' => 1, 'name' => 'ts.Client', 'key' => 'customer_Client', 'url' => 'customer/client/view', 'routes' => [
                [['GET'], 'customer/client/view'],
                [['GET', 'POST'], 'customer/client'],
                [['GET', 'PATCH', 'DELETE'], 'customer/client/{id}']
            ]],
            ['is_menu' => 1, 'sort' => 2, 'name' => 'ts.Merchant', 'key' => 'merchant_manage_admin', 'url' => 'merchant/manager/account/view', 'routes' => [
                [['GET'], 'merchant/manager/account/view'],
                [['GET', 'POST'], 'merchant/manager/account'],
                [['GET', 'PATCH', 'DELETE'], 'merchant/manager/account/{id}']
            ]],
            ['is_menu' => 1, 'sort' => 3, 'name' => 'ts.MerchantRole', 'key' => 'merchant_manage_role', 'url' => 'merchant/manager/role/view', 'routes' => [
                [['GET'], 'merchant/manager/role/view'],
                [['GET', 'POST'], 'merchant/manager/role'],
                [['GET', 'PATCH', 'DELETE'], 'merchant/manager/role/{id}']
            ]],
            ['is_menu' => 1, 'sort' => 4, 'name' => 'ts.MerchantLoginLog', 'key' => 'merchant_manage_login_log', 'url' => 'merchant/manager/loginlog/view', 'routes' => [
                [['GET'], 'merchant/manager/loginlog/view'],
                [['GET'], 'merchant/manager/loginlog'],
            ]],
            ['is_menu' => 1, 'sort' => 5, 'name' => 'ts.MerchantActionLog', 'key' => 'merchant_manage_action_log', 'url' => 'merchant/manager/actionlog/view', 'routes' => [
                [['GET'], 'merchant/manager/actionlog/view'],
                [['GET'], 'merchant/manager/actionlog'],
                [['GET'], 'merchant/manager/actionlog/{id}']
            ]],
            ['is_menu' => 1, 'sort' => 6, 'name' => 'ts.ServerRequestLog', 'key' => 'customer_server_request_log', 'url' => 'customer/serverrequestlog/view', 'routes' => [
                [['GET'], 'customer/serverrequestlog/view'],
                [['GET'], 'customer/serverrequestlog'],
                [['POST', 'GET'], 'customer/serverrequestlog/{clientId}/{id}'],
            ]],
            ['is_menu' => 1, 'sort' => 7, 'name' => 'ts.ServerPostLog', 'key' => 'customer_server_post_log', 'url' => 'customer/serverpostlog/view', 'routes' => [
                [['GET'], 'customer/serverpostlog/view'],
                [['GET'], 'customer/serverpostlog'],
                // [['POST'], 'customer/serverpostlog/{id}'],
            ]],
        ]
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
            ['is_menu' => 1, 'sort' => 5, 'name' => 'ts.Currency', 'key' => 'manage_currency', 'url' => 'manager/currency/view', 'routes' => [
                [['GET'], 'manager/currency/view'],
                [['GET', 'POST'], 'manager/currency'],
                [['GET', 'PATCH', 'DELETE'], 'manager/currency/{id}']
            ]],
            ['is_menu' => 1, 'sort' => 6, 'name' => 'ts.ConfigTag', 'key' => 'configSet_tag', 'url' => 'configset/configtag/view', 'routes' => [
                [['GET'], 'configset/configtag/view'],
                [['GET'], 'configset/configTag'],
                [['GET', 'POST'], 'configset/tagAdd'],
                [['GET'], 'configset/tagDetail/{id}'],
                [['GET', 'POST'], 'configset/tagEdit/{id}'],
                [['GET', 'PATCH', 'DELETE'], 'configset/tagDel/{id}'],
            ]],
            ['is_menu' => 1, 'sort' => 7, 'name' => 'ts.ConfigAttrTag', 'key' => 'configSet_AttrTag', 'url' => 'configset/configAttrTag/view', 'routes' => [
                [['GET'], 'configset/configAttrTag/view'],
                [['GET'], 'configset/configAttrTag'],
                [['GET', 'POST'], 'configset/attrTagAdd'],
                [['GET'], 'configset/attrTagDetail/{id}'],
                [['GET', 'POST'], 'configset/attrTagEdit/{id}'],
                [['GET', 'PATCH', 'DELETE'], 'configset/attrTagDel/{id}'],
            ]],
            ['is_menu' => 1, 'sort' => 8, 'name' => 'ts.ConfigIntention', 'key' => 'configSet_intention', 'url' => 'configset/configIntention/view', 'routes' => [
                [['GET'], 'configset/configIntention/view'],
                [['GET'], 'configset/configIntention'],
                [['GET', 'POST'], 'configset/intentionAdd'],
                [['GET'], 'configset/intentionDetail/{id}'],
                [['GET', 'POST'], 'configset/intentionEdit/{id}'],
            ]]
        ],
    ],
];

return $menu;
