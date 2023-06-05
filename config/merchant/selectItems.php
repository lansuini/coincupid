<?php

use App\Models\Manager\Merchant\Role;
use App\Models\NodeEntrance;
use App\Models\Customer;
use App\Models\Node;
return [
    'testItems' => [
        'Y' => 'ts.Yes',
        'N' => 'ts.No',
    ],
    // 'accountType' => [
    //     ['key' => '2', 'value' => 'ts.Normal', 'txt-class' => 'text-info'],
    //     ['key' => '1', 'value' => 'ts.Guest', 'txt-class' => 'text-warning'],
    //     ['key' => '100', 'value' => 'ts.Robot', 'txt-class' => 'text-danger'],
    // ],
    // 'riskUserType' => [
    //     ['key' => '0', 'value' => 'ts.Normal', 'txt-class' => 'text-info'],
    //     ['key' => '1', 'value' => 'ts.Control', 'txt-class' => 'text-danger'],
    // ],
    'customerType' => function () {
        return Customer::pluck('company_name', 'id')->toArray();
    },
    'bannedType' => [
        ['key' => '0', 'value' => 'ts.-', 'txt-class' => ''],
        ['key' => '1', 'value' => 'ts.Normal', 'txt-class' => 'text-info'],
        ['key' => '3', 'value' => 'ts.Control', 'txt-class' => 'text-success'],
    ],
    // 'accountSearchType' => [
    //     'uid' => 'ts.UID',
    //     'player_name' => 'ts.PlayerName',
    //     'nickname' => 'ts.Nickname',
    // ],
    'OSType' => [
        ['key' => '2', 'value' => 'ts.Android', 'txt-class' => 'text-info'],
        ['key' => '3', 'value' => 'ts.IOS', 'txt-class' => 'text-success'],
    ],
    'lockType' => [
        '0' => 'ts.Normal',
        '1' => 'ts.Locked',
    ],
    'bindGoogleCodeType' => [
        '0' => 'ts.Unbind',
        '1' => 'ts.Bound',
    ],
    'roleType' => function () {
        return Role::pluck('name', 'id')->toArray();
    },
    'nodeType' => function () {
        return Node::pluck('name', 'id')->toArray();
    },
    'accountSearchType' => [
        'player_name' => 'ts.PlayerName',
        'nickname' => 'ts.Nickname',
        'uid' => 'ts.UID',
    ],
    'accountSearchTimeType' => [
        'created' => 'ts.Created',
        'last_logon_time' => 'ts.LastLogonTime',
    ],
    'successType' => [
        '0' => 'ts.Fail',
        '1' => 'ts.Succ',
    ],
    // 'experienceType' => [
    //     ['key' => '0', 'value' => 'ts.Not Exp Room', 'txt-class' => 'text-danger'],
    //     ['key' => '1', 'value' => 'ts.Exp Room', 'txt-class' => 'text-success'],
    // ],
    // 'enabledType' => [
    //     ['key' => '0', 'value' => 'ts.Stop', 'txt-class' => 'text-danger'],
    //     ['key' => '1', 'value' => 'ts.Enable', 'txt-class' => 'text-success'],
    // ],

    // 'processControlGameType' => [ // 0约战1普通2百人3私人房
    //     ['key' => '0', 'value' => 'ts.Dating', 'txt-class' => 'text-danger'],
    //     ['key' => '1', 'value' => 'ts.Normal', 'txt-class' => 'text-success'],
    //     ['key' => '2', 'value' => 'ts.Hundreds of people', 'txt-class' => 'text-info'],
    //     ['key' => '3', 'value' => 'ts.private room', 'txt-class' => 'text-dark'],
    // ],

    'gameAliasType' => function () {
        $data = config('gm.game_alias');
        $new = [];
        foreach ($data as $k => $v) {
            $new[] = ['key' => $k, 'value' => $v['name']];
        }
        return $new;
    },
    // 'customerType' => function () {
    //     return Customer::pluck('company_name', 'id')->toArray();
    // },
    'actionType' => [
        'MERCHANT_MANAGER_ADMIN_EDIT_PASSWORD' => 'ts.update admin password',
        'MERCHANT_MANAGER_ADMIN_EDIT_GOOGLECODE' => 'ts.update admin googleCode',

        'MERCHANT_MANAGER_ADMIN_CREATE' => 'ts.create admin',

        'MERCHANT_MANAGER_ADMIN_CREATE' => 'ts.create admin',
        'MERCHANT_MANAGER_ADMIN_EDIT' => 'ts.update admin',
        'MERCHANT_MANAGER_ADMIN_DELETE' => 'ts.remove admin',

        'MERCHANT_MANAGER_ROLE_CREATE' => 'ts.create role',
        'MERCHANT_MANAGER_ROLE_EDIT' => 'ts.update role',
        'MERCHANT_MANAGER_ROLE_DELETE' => 'ts.remove role',

    ],
];
