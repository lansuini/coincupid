<?php

namespace App\Http\Controllers\Analysis;
use App\Http\Controllers\GM\GMController;

class AnalysisController extends GMController {
    protected $proj = 'Analysis';

    protected $classMaps = [
        'Role' => '\App\Models\Manager\Analysis\Role',
        'Admin' => '\App\Models\Manager\Analysis\Admin',
        'LoginLog' => '\App\Models\Manager\Analysis\LoginLog',
        'ActionLog' => '\App\Models\Manager\Analysis\ActionLog',
    ];

    protected $baseDataPath = 'analysis.selectItems';
}