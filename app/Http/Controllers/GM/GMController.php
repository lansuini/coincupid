<?php

namespace App\Http\Controllers\GM;
use App\Http\Controllers\Controller;
class GMController extends Controller {

    protected $proj = 'GM';

    protected $classMaps = [
        'Role' => '\App\Models\Manager\Role',
        'Admin' => '\App\Models\Manager\Admin',
        'LoginLog' => '\App\Models\Manager\LoginLog',
        'ActionLog' => '\App\Models\Manager\ActionLog',
    ];

    protected $admin;

    protected $role;

    protected $actionLog;

    protected $baseDataPath = 'gm.selectItems';
    
    protected $apiPath = '/';
    
    public function __construct() {
        $this->admin = new $this->classMaps['Admin'];
        $this->role = new $this->classMaps['Role'];
        $this->actionLog = new $this->classMaps['ActionLog'];
    }
}