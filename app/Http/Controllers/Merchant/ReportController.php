<?php

namespace App\Http\Controllers\Merchant;

use Illuminate\Http\Request;
use App\Models\Manager\Role;
use App\Models\Manager\Admin;
use App\Models\ConfigAttribute;
use App\Models\Manager\ActionLog;
use App\Models\DataReport;
use App\Models\AccountsToday;
use App\Models\UserEnterOut;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Http\Library\DynamicJsonForm;
use App\Http\Library\Server;
use App\Models\NodeEntrance;
use App\Rules\XML;

class ReportController extends MerchantController
{
    public function totalView(Request $request)
    {
        return view('Merchant/Report/totalView', [
            'pageTitle' => $this->role->getCurrentPageTitle($request),
            'role' => new Role, 'request' => $request
        ]);
    }

    public function totalList(Request $request)
    {
        $limit = $request->query->get('limit', 20);
        $offset = $request->query->get('offset', 0);
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'desc');
        $gameID = $request->query->get('gameid');

        $created = $request->query->get('created');
        $created = urldecode($created);
        $created = explode(' - ', $created);

        $s  = \DateTime::createFromFormat('m/d/Y', $created[0])->format('Y-m-d');
        $e  = \DateTime::createFromFormat('m/d/Y', $created[1])->format('Y-m-d');

        $clientId = $this->admin->getCurrent($request)->client_id;

        $model = new UserEnterOut();
        !empty($sort) && $model = $model->orderBy($sort, $order);
        $gameID && $model = $model->where('node.gameid', $gameID);
        $model = $model->select(
            'node.gameid',
            $model->raw('sum(change_gold) as win_lose')
        );
        $model = $model->leftjoin('account', 'account.uid', '=', 'user_enter_out.uid');
        $model = $model->leftjoin('node', 'node.id', '=', 'user_enter_out.nodeid');
        $model = $model->where('user_enter_out.post_time', '>=', $s);
        $model = $model->where('user_enter_out.post_time', '<=', $e);
        $model = $model->where('account.client_id', $clientId);
        $model = $model->where('user_enter_out.client_id', $clientId);
        $model = $model->where('user_enter_out.type', 3);
        $model = $model->where('user_enter_out.result', '!=', 0);
        $model = $model->groupBy('node.gameid');
        $total = $model->count();
        $rows = $model->offset($offset)->limit($limit)->get()->toArray();
        return [
            'result' => [],
            'rows' => $rows,
            'success' => 1,
            'total' => $total,
        ];
    }

    public function dayView(Request $request)
    {
        return view('Merchant/Report/dayView', [
            'pageTitle' => $this->role->getCurrentPageTitle($request),
            'role' => new Role, 'request' => $request
        ]);
    }

    public function dayList(Request $request)
    {
        $limit = $request->query->get('limit', 20);
        $offset = $request->query->get('offset', 0);
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'desc');
        $gameID = $request->query->get('gameid');

        $created = $request->query->get('created');
        $created = urldecode($created);
        $created = explode(' - ', $created);

        $s  = \DateTime::createFromFormat('m/d/Y', $created[0])->format('Y-m-d');
        $e  = \DateTime::createFromFormat('m/d/Y', $created[1])->format('Y-m-d');

        $clientId = $this->admin->getCurrent($request)->client_id;

        $model = new UserEnterOut();
        !empty($sort) && $model = $model->orderBy($sort, $order);
        $gameID && $model = $model->where('node.gameid', $gameID);
        $model = $model->select(
            'node.gameid',
            'user_enter_out.post_time',
            $model->raw('sum(change_gold) as win_lose')
        );
        $model = $model->leftjoin('account', 'account.uid', '=', 'user_enter_out.uid');
        $model = $model->leftjoin('node', 'node.id', '=', 'user_enter_out.nodeid');
        $model = $model->where('user_enter_out.post_time', '>=', $s);
        $model = $model->where('user_enter_out.post_time', '<=', $e);
        $model = $model->where('account.client_id', $clientId);
        $model = $model->where('user_enter_out.client_id', $clientId);
        $model = $model->where('user_enter_out.type', 3);
        $model = $model->where('user_enter_out.result', '!=', 0);
        $model = $model->groupBy('node.gameid', 'user_enter_out.post_time');
        $total = $model->count();
        $rows = $model->offset($offset)->limit($limit)->get()->toArray();
        return [
            'result' => [],
            'rows' => $rows,
            'success' => 1,
            'total' => $total,
        ];
    }

    public function dataReportView(Request $request)
    {
        return view('Merchant/Report/dataReportView', [
            'pageTitle' => $this->role->getCurrentPageTitle($request),
            'request' => $request
        ]);
    }

    public function dataReportList(Request $request)
    {
        $limit = (int) $request->query->get('limit', 20);
        $offset = (int) $request->query->get('offset', 0);
        $sort = $request->query->get('sort', 'game_id');
        $order = $request->query->get('order', 'desc');
        $gameId = (int) $request->query->get('game_id');
        // $clientId = (int) $request->query->get('client_id');
        $created = $request->query->get('created');
        $created = urldecode($created);
        $created = explode(' - ', $created);
        $s  = \DateTime::createFromFormat('m/d/Y', $created[0])->format('Y-m-d');
        $e  = \DateTime::createFromFormat('m/d/Y', $created[1])->format('Y-m-d');


        $model = new DataReport();
        !empty($sort) && $model = $model->orderBy($sort, $order);
        $clientId = $this->admin->getCurrent($request)->client_id;
        $model = $model->where('data_report.client_id', $clientId);
        $gameId && $model = $model->where('data_report.game_id', $gameId);
        $model = $model->whereBetween('data_report.count_date', [$s, $e]);
        $model = $model->select(
            'data_report.id',
            'data_report.game_id',
            'data_report.count_date',
            'data_report.updated_time',
            'data_report.transfer_amount',
            'data_report.bet_count',
            'data_report.bet_amount',
            'data_report.client_id'
        );

        $total = $model->count();
        $rows = $model->offset($offset)->limit($limit)->get()->toArray();

        $model = new DataReport();
        $clientId && $model = $model->where('data_report.client_id', $clientId);
        $gameId && $model = $model->where('data_report.game_id', $gameId);
        $model = $model->whereBetween('data_report.count_date', [$s, $e]);
        $model = $model->select(
            $model->raw('sum(bet_count) as bet_count'),
            $model->raw('sum(bet_amount) as bet_amount'),
            $model->raw('sum(transfer_amount) as transfer_amount'),
        );
        $row = $model->first();
        $rows[] = [
            'game_id' => 'TOTAL',
            'bet_amount' => $row->bet_amount,
            'bet_count' => $row->bet_count,
            'transfer_amount' => $row->transfer_amount,
        ];
        return [
            'result' => [],
            'rows' => $rows,
            'success' => 1,
            'total' => $total,
        ];
    }
}
