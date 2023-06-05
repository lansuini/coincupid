<?php

namespace App\Http\Controllers\GM;

use Illuminate\Http\Request;
use App\Models\Manager\Role;
use App\Models\Manager\Admin;
use App\Models\ConfigAttribute;
use App\Models\Manager\ActionLog;
use App\Models\WebLogAnalysis;
use App\Models\NodeRoom;
use App\Models\ConfigGame;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Library\DynamicJsonForm;
use App\Http\Library\Server;
use App\Models\NodeEntrance;
use App\Rules\XML;
use Illuminate\Support\Facades\Redis;

class DevToolsController extends GMController
{
    public function webLogAnalysisView(Request $request)
    {
        return view('GM/DevTools/webLogAnalysisView', [
            'pageTitle' => $this->role->getCurrentPageTitle($request),
            'role' => new Role, 'request' => $request
        ]);
    }

    public function webLogAnalysisList(Request $request)
    {
        $countDate = $request->query->get('count_date');
        $countDate = empty($countDate) ? date('m/d/Y') : urldecode($countDate);
        $d  = \DateTime::createFromFormat('m/d/Y', $countDate)->format('Y-m-d');
        $model = new WebLogAnalysis();
        $model = $model->select(
            'id',
            'host',
            'content',
            'created',
            'updated',
        );
        $model->where('count_date', $d);
        $res = $model->first();
        return [
            'result' => !empty($res) ? json_decode($res->content, true) : null,
            'updated' => !empty($res) ? ($res->updated ?? $res->created) : null,
            'isToday' => $d == date('Y-m-d') ? true : false,
            'H' => date('H'),
        ];
    }

    public function configView(Request $request)
    {
        return view('GM/DevTools/configView', [
            'pageTitle' => $this->role->getCurrentPageTitle($request),
        ]);
    }

    public function configDetail(Request $request)
    {
        $redis = Redis::connection('cache');
        $maintenance = (int) $redis->get('stop_api_service');
        return ['success' => 1, 'data' => ['maintenance' => $maintenance]];
    }

    public function configEdit(Request $request)
    {
        $data = $request->only(
            'maintenance',
        );

        $redis = Redis::connection('cache');
        $maintenance = (int) $redis->get('stop_api_service');
        if ($maintenance != $data['maintenance']) {
            $redis->setex('stop_api_service', 86400, $data['maintenance']);
            ActionLog::create([
                'admin_id' => $this->admin->getLoginID($request),
                'admin_username' => $this->admin->getLoginUsername($request),
                'browser' => $request->header('User-Agent'),
                'key' => 'DEVTOOLS_CONFIG_EDIT',
                'is_success' => 1,
                'url' => $request->url(),
                'ip' => $this->ip($request),
                'desc' => 'config',
                'target_id' => 0,
                'before' => json_encode(['maintenance' => $maintenance]),
                'after' =>  json_encode(['maintenance' =>  $data['maintenance']]),
                'params' => json_encode($request->all()),
                'method' => $request->method()
            ]);
        }
        return ['success' => 1, 'result' => __('ts.update success')];
    }
}
