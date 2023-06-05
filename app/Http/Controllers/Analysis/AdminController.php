<?php

namespace App\Http\Controllers\Analysis;

use App\Http\Controllers\GM\AdminController as Controller;
use App\Models\Currencys;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $proj = 'Analysis';

    protected $classMaps = [
        'Role' => '\App\Models\Manager\Analysis\Role',
        'Admin' => '\App\Models\Manager\Analysis\Admin',
        'LoginLog' => '\App\Models\Manager\Analysis\LoginLog',
        'ActionLog' => '\App\Models\Manager\Analysis\ActionLog',
    ];

    protected $baseDataPath = 'analysis.selectItems';

    public function currencyView(Request $request)
    {
        return view('Analysis/Manager/currencysView', ['pageTitle' => $this->role->getCurrentPageTitle($request)]);
    }

    public function currencyList(Request $request)
    {
        $limit = $request->query->get('limit', 20);
        $offset = $request->query->get('offset', 0);
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'desc');
        $name = $request->query->get('name');
        $countMonth = $request->query->get('count_month');

        $model = new Currencys();
        !empty($sort) && $model = $model->orderBy($sort, $order);
        $name && $model = $model->where('name', $name);
        $countMonth && $model = $model->where('count_month', $countMonth);

        $model = $model->select(
            'id',
            'name',
            'exchange_rate',
            'count_month',
            'created',
            'updated'
        );
        $total = $model->count();
        $rows = $model->offset($offset)->limit($limit)->get()->toArray();
        return [
            'result' => [],
            'rows' => $rows,
            'success' => 1,
            'total' => $total,
        ];
    }

    public function currencyDetail(Request $request, $id)
    {
        $data = Currencys::select(
            'id',
            'name',
            'exchange_rate',
            'count_month',
            'created',
            'updated'
        )
            ->where('id', $id)->first();
        return ['success' => 1, 'data' => $data];
    }

    public function currencyAdd(Request $request)
    {
        $name = $request->input('name');
        $countMonth = $request->input('count_month');
        $validator = Validator::make($request->all(), [
            'name' => [
                'required', 'string', 'max:50',
                Rule::unique('App\Models\Currencys')->where(function ($query) use ($name, $countMonth) {
                    return $query->where('name', $name)
                        ->where('count_month', $countMonth);
                }),
            ],
            'exchange_rate' => ['required', 'numeric'],
            'count_month' => ['required', 'integer', 'between:202210,203210'],
        ]);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }

        $data = $request->only(
            'name',
            'exchange_rate',
            'count_month',
        );
        $after = Currencys::create($data);
        $this->actionLog->create([
            'admin_id' => $this->admin->getLoginID($request),
            'admin_username' => $this->admin->getLoginUsername($request),
            'browser' => $request->header('User-Agent'),
            'key' => 'MANAGER_CURRENCY_CREATE',
            'is_success' => 1,
            'url' => $request->url(),
            'ip' => $this->ip($request),
            'desc' => $after->username,
            'target_id' => $after->id,
            'after' =>  $after->toJson(),
            'params' => json_encode($request->only(
                'name',
                'exchange_rate',
                'count_month',
            )),
            'method' => $request->method()
        ]);
        return ['success' => 1, 'result' => __('ts.create success')];
    }

    public function currencyEdit(Request $request, $id)
    {
        $isInputSecretKey = !empty($request->input('secret_key'));

        $validator = Validator::make($request->all(), [
            'exchange_rate' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }

        $data = $request->only(
            'exchange_rate',
        );
        $before = Currencys::select(
            'name',
            'exchange_rate',
            'count_month',
        )->where('id', $id)->first();
        Currencys::where('id', $id)->update($data);
        $after = Currencys::select(
            'name',
            'exchange_rate',
            'count_month',
        )->where('id', $id)->first();
        $collection = collect($before);
        $diffItems = $collection->diff($after);
        if (!$diffItems->isEmpty()) {
            $this->actionLog->create([
                'admin_id' => $this->admin->getLoginID($request),
                'admin_username' => $this->admin->getLoginUsername($request),
                'browser' => $request->header('User-Agent'),
                'key' => 'MANAGER_CURRENCY_EDIT',
                'is_success' => 1,
                'url' => $request->url(),
                'ip' => $this->ip($request),
                'desc' => $before->company_name,
                'target_id' => $before->id,
                'before' => $before->toJson(),
                'after' =>  $after->toJson(),
                'params' => json_encode($request->only(
                    'exchange_rate',
                )),
                'method' => $request->method()
            ]);
        }
        return ['success' => 1, 'result' => __('ts.update success')];
    }

    public function currencyDel(Request $request, $id)
    {
        $before = Currencys::select(
            'name',
            'exchange_rate',
            'count_month',
            'created',
            'updated',
        )->where('id', $id)->first();
        if (empty($before)) {
            return ['success' => 0, 'result' => __('ts.id error')];
        }
        Currencys::where('id', $id)->delete();
        $this->actionLog->create([
            'admin_id' => $this->admin->getLoginID($request),
            'admin_username' => $this->admin->getLoginUsername($request),
            'browser' => $request->header('User-Agent'),
            'key' => 'MANAGER_CURRENCY_DELETE',
            'is_success' => 1,
            'url' => $request->url(),
            'ip' => $this->ip($request),
            'desc' => $before->company_name,
            'target_id' => $before->id,
            'before' => $before->toJson(),
            'params' => json_encode($request->all()),
            'method' => $request->method()
        ]);
        return ['success' => 1, 'result' => __('ts.delete success')];
    }
}
