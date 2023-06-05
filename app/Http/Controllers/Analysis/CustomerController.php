<?php

namespace App\Http\Controllers\Analysis;

use Illuminate\Http\Request;

use App\Models\ServerRequestLog;
use App\Models\ServerPostLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Customer;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Http\Library\MerchantCB;
use App\Http\Library\MerchantCF;
use App\Models\TransferInOut;
use GuzzleHttp\Client;

use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Schema;

class CustomerController extends AnalysisController
{
    public function clientView(Request $request)
    {
        return view('Analysis/Customer/clientView', ['pageTitle' => $this->role->getCurrentPageTitle($request)]);
    }

    public function clientList(Request $request)
    {
        $limit = $request->query->get('limit', 20);
        $offset = $request->query->get('offset', 0);
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'desc');
        $companyName = $request->query->get('company_name');
        $operatorToken = $request->query->get('operator_token');
        $isLock = $request->query->get('is_lock');

        $model = new Customer();
        !empty($sort) && $model = $model->orderBy($sort, $order);
        $companyName && $model = $model->where('company_name', 'like', '%' . $companyName . '%');
        $operatorToken && $model = $model->where('operator_token', $operatorToken);
        $isLock && $model = $model->where('is_lock', $isLock);

        $model = $model->select(
            'id',
            'company_name',
            'operator_token',
            'merchant_addr',
            'is_lock',
            'api_ip_white',
            'created',
            'api_mode',
            'game_domain',
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

    public function clientDetail(Request $request, $id)
    {
        $data = Customer::select(
            'id',
            'company_name',
            'operator_token',
            'merchant_addr',
            'is_lock',
            'api_ip_white',
            'api_mode',
            'created',
            'game_domain',
            'game_oc'
        )
            ->where('id', $id)->first();
        return ['success' => 1, 'data' => $data];
    }

    public function clientAdd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => ['required', 'string', 'max:256', 'unique:\App\Models\Customer,company_name'],
            'operator_token' => ['required', 'string', 'min:32', 'max:32', 'unique:\App\Models\Customer,operator_token', 'alpha_dash'],
            'secret_key' => ['required', 'string', 'min:32', 'max:32', 'alpha_dash'],
            'merchant_addr' => ['required', 'string', 'max:512'],
            'is_lock' => ['required', 'integer', Rule::in([0, 1])],
        ]);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }

        $data = $request->only(
            'company_name',
            'operator_token',
            'secret_key',
            'merchant_addr',
            'is_lock',
            'api_ip_white',
            'api_mode',
            'game_domain',
            'game_oc',
        );
        $data['secret_key'] = encrypt($data['secret_key']);
        $after = Customer::create($data);
        $transferInOut = new TransferInOut;
        $transferInOut->createTable($after->id);
        $this->actionLog->create([
            'admin_id' => $this->admin->getLoginID($request),
            'admin_username' => $this->admin->getLoginUsername($request),
            'browser' => $request->header('User-Agent'),
            'key' => 'CUSTOMER_CLIENT_CREATE',
            'is_success' => 1,
            'url' => $request->url(),
            'ip' => $this->ip($request),
            'desc' => $after->username,
            'target_id' => $after->id,
            'after' =>  $after->toJson(),
            'params' => json_encode($request->only(
                'company_name',
                'operator_token',
                'secret_key',
                'merchant_addr',
                'is_lock',
                'api_ip_white',
                'api_mode',
                'game_domain',
                'game_oc',
            )),
            'method' => $request->method()
        ]);
        return ['success' => 1, 'result' => __('ts.create success')];
    }

    public function clientEdit(Request $request, $id)
    {
        $isInputSecretKey = !empty($request->input('secret_key'));

        $validator = Validator::make($request->all(), [
            'company_name' => ['required', 'string', 'max:256'],
            // 'operator_token' => ['required', 'string', 'min:32', 'max:32', 'unique:\App\Models\Customer,operator_token', 'alpha_dash'],
            // 'secret_key' => ['required', 'string', 'min:32', 'max:32', 'alpha_dash'],
            'merchant_addr' => ['required', 'string', 'max:512'],
            'is_lock' => ['required', 'integer', Rule::in([0, 1])],
        ]);

        if ($validator->fails()) {
            return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
        }

        $data = $request->only(
            'company_name',
            // 'operator_token',
            'secret_key',
            'merchant_addr',
            'is_lock',
            'api_ip_white',
            'api_mode',
            'game_domain',
            'game_oc',
        );

        $data['game_domain'] = $data['game_domain'] ?? '';
        $data['api_ip_white'] = $data['api_ip_white'] ?? '';
        $data['game_oc'] = $data['game_oc'] ?? '';
        if ($isInputSecretKey) {
            $validator = Validator::make($request->all(), [
                'secret_key' => ['required', 'string', 'min:32', 'max:32', 'alpha_dash'],
            ]);

            if ($validator->fails()) {
                return ['success' => 0, 'result' => $validator->errors()->first(), 'validator' => $validator->errors()];
            }

            $data['secret_key'] = encrypt($data['secret_key']);
        }

        $before = Customer::select(
            'company_name',
            'operator_token',
            'secret_key',
            'merchant_addr',
            'is_lock',
            'api_ip_white',
            'api_mode',
            'game_domain',
            'game_oc',
            'id',
        )->where('id', $id)->first();
        Customer::where('id', $id)->update($data);
        $after = Customer::select(
            'company_name',
            'operator_token',
            'secret_key',
            'merchant_addr',
            'is_lock',
            'api_ip_white',
            'api_mode',
            'game_domain',
            'game_oc',
            'id',
        )->where('id', $id)->first();
        $collection = collect($before);
        $diffItems = $collection->diff($after);
        if (!$diffItems->isEmpty()) {
            $this->actionLog->create([
                'admin_id' => $this->admin->getLoginID($request),
                'admin_username' => $this->admin->getLoginUsername($request),
                'browser' => $request->header('User-Agent'),
                'key' => 'CUSTOMER_CLIENT_EDIT',
                'is_success' => 1,
                'url' => $request->url(),
                'ip' => $this->ip($request),
                'desc' => $before->company_name,
                'target_id' => $before->id,
                'before' => $before->toJson(),
                'after' =>  $after->toJson(),
                'params' => json_encode($request->only(
                    'company_name',
                    'operator_token',
                    'merchant_addr',
                    'is_lock',
                    'api_ip_white',
                    'api_mode',
                    'game_domain',
                    'game_oc',
                )),
                'method' => $request->method()
            ]);
        }

        if ($isInputSecretKey) {
            $this->actionLog->create([
                'admin_id' => $this->admin->getLoginID($request),
                'admin_username' => $this->admin->getLoginUsername($request),
                'browser' => $request->header('User-Agent'),
                'key' => 'CUSTOMER_CLIENT_EDIT_SECRET_KEY',
                'is_success' => 1,
                'url' => $request->url(),
                'ip' => $this->ip($request),
                'desc' => $before->company_name,
                'target_id' => $before->id,
                'method' => $request->method()
            ]);
        }

        Customer::refreshCustomerByOperatorToken($before->operator_token);
        return ['success' => 1, 'result' => __('ts.update success')];
    }

    public function clientDel(Request $request, $id)
    {
        $before = Customer::select(
            'company_name',
            'operator_token',
            'secret_key',
            'merchant_addr',
            'is_lock',
            'api_ip_white',
            'created',
            'api_mode',
            'game_domain',
            'game_oc',
        )->where('id', $id)->first();
        if (empty($before)) {
            return ['success' => 0, 'result' => __('ts.id error')];
        }
        Customer::where('id', $id)->delete();
        $this->actionLog->create([
            'admin_id' => $this->admin->getLoginID($request),
            'admin_username' => $this->admin->getLoginUsername($request),
            'browser' => $request->header('User-Agent'),
            'key' => 'CUSTOMER_CLIENT_DELETE',
            'is_success' => 1,
            'url' => $request->url(),
            'ip' => $this->ip($request),
            'desc' => $before->company_name,
            'target_id' => $before->id,
            'before' => $before->toJson(),
            'params' => json_encode($request->all()),
            'method' => $request->method()
        ]);

        Customer::refreshCustomerByOperatorToken($before->operator_token);
        return ['success' => 1, 'result' => __('ts.delete success')];
    }

    public function serverRequestLogView(Request $request)
    {
        return view('Analysis/Customer/serverRequestLogView', ['pageTitle' => $this->role->getCurrentPageTitle($request)]);
    }

    public function serverRequestLogDetail(Request $request, $clientId, $id)
    {
        $serverRequestLogTableName = 'server_request_log_' . $clientId;
        $model = new ServerRequestLog();
        $model = $model->setTable($serverRequestLogTableName);
        $data = $model->select(
            'id',
            'pid',
            'client_id',
            'uid',
            'type',
            'url',
            'cost_time',
            'response',
            'error_code',
            'error_text',
            'params',
            'method',
            'code',
            'args',
            'created',
            'is_success',
        )
            ->where('id', $id)->first();
        if (empty($data->error_text)) {
            $mcb = new MerchantCB;
            $errors = $mcb->getErrors();
            $data->error_text = $errors[$data->error_code] ?? '';
        }
        return ['success' => 1, 'data' => $data];
    }

    public function serverRequestLogList(Request $request)
    {
        $limit = $request->query->get('limit', 20);
        $offset = $request->query->get('offset', 0);
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'desc');
        $id = $request->query->get('id', 0);
        $clientId = $request->query->get('client_id');
        $uid = $request->query->get('uid');
        $type = $request->query->get('type', -1);
        $costTime = $request->query->get('cost_time', -1);
        $isSuccess = $request->query->get('is_success', -1);
        $created = $request->query->get('created');
        $created = urldecode($created);
        $created = explode(' - ', $created);
        $s  = \DateTime::createFromFormat('m/d/Y', $created[0])->format('Y-m-d 00:00:00');
        $e  = \DateTime::createFromFormat('m/d/Y', $created[1])->format('Y-m-d 23:59:59');

        $serverRequestLogTableName = 'server_request_log_' . $clientId;
        if (Schema::connection('Master')->hasTable($serverRequestLogTableName)) {
            $model = new ServerRequestLog();
            $model = $model->setTable($serverRequestLogTableName);
            !empty($sort) && $model = $model->orderBy($sort, $order);
            $type != -1 && $model = $model->where('type', $type);
            $clientId && $model = $model->where('client_id', $clientId);
            $uid && $model = $model->where('uid', $uid);
            $isSuccess != -1 && $model = $model->where('is_success', $isSuccess);
            $costTime == 0 && $model = $model->where('cost_time', '<', 500);
            $costTime == 1 && $model = $model->where('cost_time', '>=', 500);
            $costTime == 2 && $model = $model->where('cost_time', '<=', 200);

            // $isSuccess == 0 && $model = $model->where('error_code', '!=', 0);

            if ($id == 0) {
                $model = $model->where('pid', 0);
            } else {
                $id = is_numeric($id) ? $id : $model->getTraceId($id);
                $model = $model->where('id', $id)->orWhere('pid', $id);
            }

            $model = $model->where('created', '>=', $s);
            $model = $model->where('created', '<=', $e);

            $model = $model->select(
                'id',
                'pid',
                'client_id',
                'type',
                'url',
                'cost_time',
                'response',
                'error_code',
                'error_text',
                'params',
                'method',
                'code',
                'created',
                'uid',
                'is_success',
                'admin_id'
            );
            $total = $model->count();
            $rows = $model->offset($offset)->limit($limit)->get()->toArray();
        } else {
            $rows = [];
            $total = 0;
        }
        return [
            'result' => [],
            'rows' => $rows,
            'success' => 1,
            'total' => $total,
        ];
    }

    public function serverRequestLogAdd(Request $request, $clientId, $id)
    {
        $serverRequestLogTableName = 'server_request_log_' . $clientId;
        $model = new ServerRequestLog();
        $model = $model->setTable($serverRequestLogTableName);
        $data = $model->where('id', $id)->first();
        $pid = $data->pid == 0 ? $data->id : $data->pid;
       
        $args = json_decode($data->args, true);
        if ($data->type == 1) {
            $mcb = new MerchantCB();
            $res = $mcb->getVerifySession($args[0], $pid);
        } else if ($data->type == 2) {
            $mcb = new MerchantCB();
            $res = $mcb->getCashGet($args[0], $args[1], $pid);
        } else if ($data->type == 3) {
            $mcb = new MerchantCB();
            $res = $mcb->getCashTransferInOut($args[0], $pid);
        } else if ($data->type == 4) {
            $mcf = new MerchantCF();
            $res = $mcf->verifySession($args[0], $pid);
        } else {
            return ['success' => 0, 'result' => __('ts.method not found')];
        }

        $this->actionLog->create([
            'admin_id' => $this->admin->getLoginID($request),
            'admin_username' => $this->admin->getLoginUsername($request),
            'browser' => $request->header('User-Agent'),
            'key' => 'SERVER_REQUEST_RETRY',
            'is_success' => $res['error'] == null ? 1 : 0,
            'url' => $request->url(),
            'ip' => $this->ip($request),
            'desc' => 'retry',
            'target_id' => $pid,
            'method' => $request->method()
        ]);

        return ['success' => 1, 'result' => 'success', 'data' => $res, 'pid' => $pid];
    }

    public function serverPostLogView(Request $request)
    {
        return view('Analysis/Customer/serverPostLogView', ['pageTitle' => $this->role->getCurrentPageTitle($request)]);
    }

    public function serverPostLogList(Request $request)
    {
        $limit = $request->query->get('limit', 20);
        $offset = $request->query->get('offset', 0);
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'desc');
        $transferReference = $request->query->get('transfer_reference');
        $clientId = $request->query->get('client_id');
        $traceId = $request->query->get('trace_id');
        $uid = $request->query->get('uid');
        $type = $request->query->get('type', -1);
        $isSuccess = $request->query->get('is_success', -1);
        $costTime = $request->query->get('cost_time', -1);
        $created = $request->query->get('created');
        $created = urldecode($created);
        $created = explode(' - ', $created);
        $s  = \DateTime::createFromFormat('m/d/Y', $created[0])->format('Y-m-d 00:00:00');
        $e  = \DateTime::createFromFormat('m/d/Y', $created[1])->format('Y-m-d 23:59:59');

        $tableName = 'server_post_log_' . $clientId;
        $tableSubName = 'server_post_sub_log_' . $clientId;

        if (Schema::connection('Master')->hasTable($tableName)) {
            $model = new ServerPostLog();
            $model = $model->setTable($tableName);
            !empty($sort) && $model = $model->orderBy($sort, $order);
            $type != -1 && $model = $model->where($tableName . '.type', $type);
            $clientId && $model = $model->where($tableName . '.client_id', $clientId);
            $uid && $model = $model->where($tableName . '.uid', $uid);
            $traceId && $model = $model->where($tableName . '.trace_id', $traceId);
            if ($isSuccess == 1) {
                $model = $model->whereNull($tableName . '.error_code');
            } else if ($isSuccess == 0) {
                $model = $model->whereNotNull($tableName . '.error_code');
            }

            $costTime == 0 && $model = $model->where('cost_time', '<', 200);
            $costTime == 1 && $model = $model->where('cost_time', '>=', 200);
            $costTime == 2 && $model = $model->where('cost_time', '<=', 100);

            $transferReference && $model = $model->where($tableSubName . '.transfer_reference', $transferReference);
            $model = $model->where($tableName . '.created', '>=', $s);
            $model = $model->where($tableName . '.created', '<=', $e);

            $model = $model->select(
                $tableName . '.id',
                $tableName . '.trace_id',
                $tableName . '.uid',
                $tableName . '.client_id',
                $tableName . '.type',
                $tableName . '.arg',
                $tableName . '.return',
                $tableName . '.ip',
                $tableName . '.error_code',
                $tableName . '.error_text',
                $tableName . '.cost_time',
                $tableSubName . '.transfer_reference',
                $tableName . '.created',
            );
            $model = $model->leftjoin($tableSubName, $tableName . '.id', '=', $tableSubName . '.pid');
            $total = $model->count();
            $rows = $model->offset($offset)->limit($limit)->get()->toArray();
        } else {
            $rows = [];
            $total = 0;
        }
        return [
            'result' => [],
            'rows' => $rows,
            'success' => 1,
            'total' => $total,
            't' => $tableName,
        ];
    }
}
