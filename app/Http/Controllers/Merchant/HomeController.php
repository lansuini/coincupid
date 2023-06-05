<?php
namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\GM\HomeController as Controller;

class HomeController extends Controller
{
    protected $proj = 'Merchant';

    protected $classMaps = [
        'Role' => '\App\Models\Manager\Merchant\Role',
        'Admin' => '\App\Models\Manager\Merchant\Admin',
        'LoginLog' => '\App\Models\Manager\Merchant\LoginLog',
        'ActionLog' => '\App\Models\Manager\Merchant\ActionLog',
    ];

    protected $baseDataPath = 'merchant.selectItems';
}
