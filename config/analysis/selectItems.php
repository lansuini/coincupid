<?php

use App\Models\Manager\Analysis\OccupationCate;
use App\Models\Manager\Analysis\Role;
use App\Models\Manager\Analysis\UserIntention;
use App\Models\Manager\Analysis\UserTagAttr;
use App\Models\NodeEntrance;
use App\Models\Customer;

return [
    'testItems' => [
        'Y' => 'ts.Yes',
        'N' => 'ts.No',
    ],
    'accountType' => [
        ['key' => '2', 'value' => 'ts.Normal', 'txt-class' => 'text-info'],
        ['key' => '1', 'value' => 'ts.Guest', 'txt-class' => 'text-warning'],
        ['key' => '100', 'value' => 'ts.Robot', 'txt-class' => 'text-danger'],
    ],
    'accountSex' => [
        ['key' => '1', 'value' => 'bk.Male', 'txt-class' => 'text-info'],
        ['key' => '2', 'value' => 'bk.Female', 'txt-class' => 'text-warning'],
        ['key' => '3', 'value' => 'bk.Other', 'txt-class' => 'text-danger'],
    ],
    'riskUserType' => [
        ['key' => '0', 'value' => 'ts.Normal', 'txt-class' => 'text-info'],
        ['key' => '1', 'value' => 'ts.Control', 'txt-class' => 'text-danger'],
    ],
    'bannedType' => [
        ['key' => '0', 'value' => 'ts.-', 'txt-class' => ''],
        ['key' => '1', 'value' => 'ts.Normal', 'txt-class' => 'text-info'],
        ['key' => '3', 'value' => 'ts.Control', 'txt-class' => 'text-success'],
    ],
    'accountSearchType' => [
        'username' => 'ts.Username',
        'nickname' => 'ts.Nickname',
        'uid' => 'ts.UID',
    ],
    'accountSearchTimeType' => [
        'created' => 'ts.Created',
        'last_logon_time' => 'ts.LastLogonTime',
    ],
    'OSType' => [
        ['key' => '2', 'value' => 'ts.Android', 'txt-class' => 'text-info'],
        ['key' => '3', 'value' => 'ts.IOS', 'txt-class' => 'text-success'],
    ],
    'lockType' => [
        ['key' => '0', 'value' => 'ts.Normal', 'txt-class' => 'text-info'],
        ['key' => '1', 'value' => 'ts.Locked', 'txt-class' => 'text-danger'],
    ],
    'apiModeType' => [
        '0' => 'ts.Single',
        '1' => 'ts.Transfer',
    ],
    'bindGoogleCodeType' => [
        '0' => 'ts.Unbind',
        '1' => 'ts.Bound',
    ],
    'roleType' => function () {
        return Role::pluck('name', 'id')->toArray();
    },
    'playType' => function () {
        return NodeEntrance::pluck('method_name', 'game_id')->toArray();
    },
    'serverRequestType' => [
        1 => 'ts.VerifySession',
        2 => 'ts.CashGet',
        3 => 'ts.CashTransferInOut',
        4 => 'ts.VerifySession(Transfer)',
    ],
    'serverPostType' => [
        0 => 'ts.loginGame',
        1 => 'ts.getPlayerWallet',
        2 => 'ts.transferIn',
        3 => 'ts.transferOut',
        4 => 'ts.redirect',
        5 => 'ts.redirect(single)',
    ],
    'costTimeType1' => [
        0 => 'ts.Normal[<500ms]',
        1 => 'ts.Slow[>=500ms]',
        2 => 'ts.Fast[<=200ms]',
    ],
    'costTimeType2' => [
        0 => 'ts.Normal[<200ms]',
        1 => 'ts.Slow[>=200ms]',
        2 => 'ts.Fast[<=100ms]',
    ],
//    'customerType' => function () {
//        return Customer::pluck('company_name', 'id')->toArray();
//    },
    'customerAPIType1' => function () {
        $c = Customer::orderBy('api_mode', 'asc')->get();
        $r = [];
        foreach ($c as $v) {
            $txt = $v->api_mode == 1 ? 'Transfer' : 'Single';
            $r[] = ['key' => $v->id, 'value' => $v->company_name . '[' . $txt . ']'];
        }
        return $r;
    },
    'customerAPIType2' => function () {
        $c = Customer::orderBy('api_mode', 'desc')->get();
        $r = [];
        foreach ($c as $v) {
            $txt = $v->api_mode == 1 ? 'Transfer' : 'Single';
            $r[] = ['key' => $v->id, 'value' => $v->company_name . '[' . $txt . ']'];
        }
        return $r;
    },
    'successType' => [
        ['key' => '0', 'value' => 'ts.Fail', 'txt-class' => 'text-danger'],
        ['key' => '1', 'value' => 'ts.Succ', 'txt-class' => 'text-success'],
    ],
    'experienceType' => [
        ['key' => '0', 'value' => 'ts.Not Exp Room', 'txt-class' => 'text-danger'],
        ['key' => '1', 'value' => 'ts.Exp Room', 'txt-class' => 'text-success'],
    ],
    'enabledType' => [
        ['key' => '0', 'value' => 'ts.Stop', 'txt-class' => 'text-danger'],
        ['key' => '1', 'value' => 'ts.Enable', 'txt-class' => 'text-success'],
    ],

    'processControlGameType' => [ // 0约战1普通2百人3私人房
        ['key' => '0', 'value' => 'ts.Dating', 'txt-class' => 'text-danger'],
        ['key' => '1', 'value' => 'ts.Normal', 'txt-class' => 'text-success'],
        ['key' => '2', 'value' => 'ts.Hundreds of people', 'txt-class' => 'text-info'],
        ['key' => '3', 'value' => 'ts.private room', 'txt-class' => 'text-dark'],
    ],

    'resultType' => [
        ['key' => '1', 'value' => 'ts.LOSE', 'txt-class' => 'text-danger'],
        ['key' => '2', 'value' => 'ts.WIN', 'txt-class' => 'text-success'],
        ['key' => '3', 'value' => 'ts.DRAW', 'txt-class' => 'text-dark'],
    ],

    'gameAliasType' => function () {
        $data = config('gm.game_alias');
        $new = [];
        foreach ($data as $k => $v) {
            $new[] = ['key' => $k, 'value' => $v['name']];
        }
        return $new;
    },

    'gameAliasType2' => function () {
        $data = config('gm.game_alias');
        $new = [];
        $new[] = ['key' => 0, 'value' => 'Web-Lobby'];
        foreach ($data as $k => $v) {
            $new[] = ['key' => $k, 'value' => $v['name']];
        }
        return $new;
    },

    'actionType' => [
        'ANALYSIS_MANAGER_ADMIN_EDIT_PASSWORD' => 'ts.update admin password',
        'ANALYSIS_MANAGER_ADMIN_EDIT_GOOGLECODE' => 'ts.update admin googleCode',
        'ANALYSIS_MANAGER_ADMIN_CREATE' => 'ts.create admin',
        'ANALYSIS_MANAGER_ADMIN_EDIT' => 'ts.update admin',
        'ANALYSIS_MANAGER_ADMIN_DELETE' => 'ts.remove admin',
        'ANALYSIS_MANAGER_ROLE_CREATE' => 'ts.create role',
        'ANALYSIS_MANAGER_ROLE_EDIT' => 'ts.update role',
        'ANALYSIS_MANAGER_ROLE_DELETE' => 'ts.remove role',

        'MERCHANT_MANAGER_ADMIN_EDIT_PASSWORD' => 'ts.update merchant admin password',
        'MERCHANT_MANAGER_ADMIN_EDIT_GOOGLECODE' => 'ts.update merchant admin googleCode',
        'MERCHANT_MANAGER_ADMIN_CREATE' => 'ts.create merchant admin',
        'MERCHANT_MANAGER_ADMIN_EDIT' => 'ts.update merchant admin',
        'MERCHANT_MANAGER_ADMIN_DELETE' => 'ts.remove merchant admin',
        'MERCHANT_MANAGER_ROLE_CREATE' => 'ts.create merchant role',
        'MERCHANT_MANAGER_ROLE_EDIT' => 'ts.update merchant role',
        'MERCHANT_MANAGER_ROLE_DELETE' => 'ts.remove merchant role',

        'SERVER_REQUEST_RETRY' => 'ts.server request retry',

        'MANAGER_CURRENCY_CREATE' => 'ts.create currency role',
        'MANAGER_CURRENCY_EDIT' => 'ts.update currency role',
        'MANAGER_CURRENCY_DELETE' => 'ts.remove currency role',
    ],

    'reasonType' => [

        54 => 'ts.Game Cost',
        56 => 'ts.On-stage fee',
        62 => 'ts.Handred Bet',
        63 => 'ts.Player wins tax money',
        70 => 'ts.Prize Pool',
        64 => 'ts.Bet Return',
        8001 => 'ts.transfer In',
        8002 => 'ts.transfer Out',

    ],
    'attrTag' => function () {
        $c = UserTagAttr::orderBy('attr_id', 'desc')->get();
        $r = [];
        foreach ($c as $v) {
            $r[] = ['key' => $v->attr_id, 'value' => $v->attr_name];
        }
        return $r;
    },
    'userIntention' => function () {
        $c=UserIntention::get();
        $r = [];
        foreach ($c as $v) {
            $r[] = ['key' => $v->id, 'value' => $v->name];
        }
        return $r;
    },'educational' => [
        1 => 'bk.High School',
        2 => 'bk.Some college',
        3 => 'bk.Associate degree',
        4 => 'bk.Bachelor degreey',
        5 => 'bk.Graduate degree',
        6 => 'bk.PhD/Post Doctoral',
    ],
    'income' => [
        1 => 'bk.bellow $100K',
        2 => 'bk.$100K~$200K',
        3 => 'bk.$200K~$500K',
        4 => 'bk.$500K~$1000K',
        5 => 'bk.$1M~$10M',
        6 => 'bk.$above 10M',
    ],
    'isLock' => [
        0 => 'bk.Unlock',
        1 => 'bk.Locked',
        2 => 'bk.Unknow',
    ]
];

