<?php

namespace App\Models\Manager;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Role extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'name',
        'role_keys',
        'created'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [];

    protected $table = 'gm_role';

    
    public $timestamps = false;

    public const SUPER = 1;

    protected $connection = 'Master';

    protected $menuPath = 'gm.gm.menu';

    protected $admin = '\App\Models\Manager\Admin';
    
    public function getMenu($roleKeys, $roleID = -1)
    {
        $menu = config($this->menuPath);
        $menu = collect($menu)->sortBy('sort')->toArray();
        
        foreach ($menu as $mk => $mv) {
            if (self::SUPER == $roleID || in_array($mv['key'], $roleKeys)) {
 
                if (isset($mv['sub_menu_list'])) {
                    $mv['sub_menu_list'] = collect($mv['sub_menu_list'])->sortBy('sort')->toArray();
                    foreach ($mv['sub_menu_list'] ?? [] as $k => $v) {
                        $mv['sub_menu_list'][$k] = $v;
                        if ((self::SUPER == $roleID || in_array($v['key'], $roleKeys)) && !empty($v['url']) && $v['is_menu'] == 1) {
                            
                        } else {
                            unset($mv['sub_menu_list'][$k]);
                        }
                    }
    
                    $menu[$mk]['sub_menu_list'] = $mv['sub_menu_list'] ?? [];
                }
            } else {
                // dd($mv['key'], $roleKeys);
                unset($menu[$mk]);
            }
        }
        // dd($roleKeys, $menu);
        return $menu;
    }

    public function getCurrentUserMenu(Request $request)
    {
        $roleKeys = (array) $request->session()->get($this->getTag() . 'role_keys');
        $roleID = (int) $request->session()->get($this->getTag() . 'role_id');
        return $this->getMenu($roleKeys, $roleID);
    }

    public function getCurrentPageTitle(Request $request)
    {
        $pageTitles = [];
        $menu = config($this->menuPath);
        $uri = $request->route()->uri;
        foreach ($menu as $mv) {
            if (isset($mv['url']) && $mv['url'] == $uri) {
                $pageTitles[] = $mv['name'];
                break;
            }

            foreach ($mv['sub_menu_list'] ?? [] as $v) {
                if (isset($v['url']) && $v['url'] == $uri) {
                    $pageTitles[] = $mv['name'];
                    $pageTitles[] = $v['name'];
                    break;
                }
            }

            if (!empty($pageTitles)) {
                break;
            }
        }
        return $pageTitles;
    }

    public function getCurrentKey(Request $request)
    {
        $menu = config($this->menuPath);
        $uri = $request->route()->uri;
        $method = $request->method();
        foreach ($menu as $mv) {

            if (isset($mv['routes'])) {
                foreach ($mv['routes'] as $route) {
                    if (in_array($method, $route[0]) && $uri == $route[1]) {
                        return $mv['key'];
                    }
                }
            }

            foreach ($mv['sub_menu_list'] ?? [] as $v) {
                foreach ($v['routes'] as $route) {
                    if (in_array($method, $route[0]) && $uri == $route[1]) {
                        return $v['key'];
                    }
                }
            }
        }
        return false;
    }

    public function isPermission(Request $request, $key) {
        $roleKeys = (array) $request->session()->get($this->getTag() . 'role_keys');
        $roleID = (int) $request->session()->get($this->getTag() . 'role_id');
        if ($roleID == self::SUPER) {
            return true;
        }
        return in_array($key, $roleKeys);
    }

    public function getTag() {
        return (new Admin)->tag;
    }

    public function getMenuPath() {
        return $this->menuPath;
    }
}
