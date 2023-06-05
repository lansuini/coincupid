<?php

namespace App\Models\Manager\Analysis;

use App\Models\Manager\Role as Model;

class Role extends Model
{
    protected $table = 'analysis_role';

    protected $connection = 'Master';

    protected $menuPath = 'analysis.analysis.menu';

    public function getTag() {
        return (new Admin)->tag;
    }
}
