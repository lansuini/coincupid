<?php
use App\Models\Manager\Role;
use App\Models\NodeEntrance;
return [
    'testItems' => [
        'Y' => 'ts.Yes',
        'N' => 'ts.No',
    ],
    'lockType' => [
        '0' => 'ts.Normal',
        '1' => 'ts.Locked',
    ],
    'bindGoogleCodeType' => [
        '0' => 'ts.Unbind',
        '1' => 'ts.Bound',
    ],
    'roleType' => function() {
        return Role::pluck('name', 'id')->toArray();
    },
    'playType' => function() {
        return NodeEntrance::pluck( 'method_name', 'game_id')->toArray();
    },
    'successType' => [
        '0' => 'ts.Fail',
        '1' => 'ts.Succ',
    ],
    'experienceType' => [
        ['key' => '0', 'value' => 'ts.Not Exp Room', 'txt-class' => 'text-danger'],
        ['key' => '1', 'value' => 'ts.Exp Room', 'txt-class' => 'text-success'],
    ],
    'maintenanceType' => [
        ['key' => '0', 'value' => 'ts.Open API', 'txt-class' => 'text-success'],
        ['key' => '1', 'value' => 'ts.Close API', 'txt-class' => 'text-danger'],
    ],
    'enabledType' => [
        ['key' => '0', 'value' => 'ts.Stop', 'txt-class' => 'text-danger'],
        ['key' => '1', 'value' => 'ts.Enable', 'txt-class' => 'text-success'],
    ],
    'serverStatusType' => [
        ['key' => '1', 'value' => 'ts.Stop', 'txt-class' => 'text-danger'],
        ['key' => '0', 'value' => 'ts.Enable', 'txt-class' => 'text-success'],
    ],
    'processControlGameType' => [ // 0约战1普通2百人3私人房
        ['key' => '0', 'value' => 'ts.Dating', 'txt-class' => 'text-danger'],
        ['key' => '1', 'value' => 'ts.Normal', 'txt-class' => 'text-success'],
        ['key' => '2', 'value' => 'ts.Hundreds of people', 'txt-class' => 'text-info'],
        ['key' => '3', 'value' => 'ts.private room', 'txt-class' => 'text-dark'],
    ],
    
    'gameAliasType' => function () {
        $data = config('gm.game_alias');
        $new = [];
        foreach ($data as $k => $v) {
            $new[] = ['key' => $k, 'value' => $v['name']];
        }
        return $new;
    },

    'nginx1Type' => [
        'GM' => 'GM',
        'Merchant' => 'Merchant',
        'Analysis' => 'Analysis',
        'Server' => 'Server',
        'H5' => 'H5',
    ],
    
    'nginx2Type' => [
        'ALL' => 'ALL',
        'struct1' => 'Struct1',
        'struct2' => 'Struct2',
        'struct3' => 'Struct3',
    ],

    'nginx3Type' => [
        // 'Hide' => 'Hide',
        'Show' => 'Show',
    ],

    'actionType' => [
        'GM_MANAGER_ADMIN_EDIT_PASSWORD' => 'ts.update admin password',
        'GM_MANAGER_ADMIN_EDIT_GOOGLECODE' => 'ts.update admin googleCode',

        'GM_MANAGER_ADMIN_CREATE' => 'ts.create admin',

        'GM_MANAGER_ADMIN_CREATE' => 'ts.create admin',
        'GM_MANAGER_ADMIN_EDIT' => 'ts.update admin',
        'GM_MANAGER_ADMIN_DELETE' => 'ts.remove admin',

        'GM_MANAGER_ROLE_CREATE' => 'ts.create role',
        'GM_MANAGER_ROLE_EDIT' => 'ts.update role',
        'GM_MANAGER_ROLE_DELETE' => 'ts.remove role',

        'SERVER_ROOM_PUSH_CONFIG' => 'ts.room push config',
        'SERVER_ROOM_DELETE' => 'ts.remove room',
        'SERVER_ROOM_CREATE' => 'ts.create room',
        'SERVER_ROOM_EDIT' => 'ts.update room',
        'SERVER_ROOM_PROCESS_EDIT' => 'ts.update room process',
        'SERVER_ROOM_INVENTORY_EDIT' => 'ts.update room inventory',
        'SERVER_ROOM_ENABLED_EDIT' => 'ts.update room enabled',
        'SERVER_ROOM_JSON_EDIT' => 'ts.update room json',

        'ROOM_DELETE' => 'ts.remove server room',
        'ROOM_CREATE' => 'ts.create server room',
        'ROOM_EDIT' => 'ts.update server room',
        'ROOM_ENABLED_EDIT' => 'ts.update server room enabled',

        'SERVER_PLAY_DELETE' => 'ts.remove play',
        'SERVER_PLAY_CREATE' => 'ts.create play',
        'SERVER_PLAY_EDIT' => 'ts.update play',

        'SERVER_PROCESS_CONTROL_DELETE' => 'ts.remove process control',
        'SERVER_PROCESS_CONTROL_CREATE' => 'ts.create process control',
        'SERVER_PROCESS_CONTROL_EDIT' => 'ts.update process control',

        'SERVER_MAINTENANCE_EDIT' => 'ts.update maintenance',
        'DEVTOOLS_CONFIG_EDIT' => 'ts. update devtools config',
    ],
];