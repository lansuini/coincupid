<?php

namespace App\Models\Manager\Merchant;

use App\Models\Manager\Role as Model;

class Role extends Model
{
    protected $table = 'merchant_role';

    protected $connection = 'Master';

    protected $menuPath = 'merchant.merchant.menu';

    public function getTag() {
        return (new Admin)->tag;
    }
}
