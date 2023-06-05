<?php

namespace App\Http\Controllers\Analysis;

use App\Http\Controllers\GM\HomeController as Controller;
use Illuminate\Http\Request;
use App\Models\Gold;
use App\Models\AccountsToday;
use App\Models\AccountExt;
use App\Models\Versus;
use App\Models\RoomGoldDayStatistics;
class HomeController extends Controller
{
    protected $proj = 'Analysis';

    protected $classMaps = [
        'Role' => '\App\Models\Manager\Analysis\Role',
        'Admin' => '\App\Models\Manager\Analysis\Admin',
        'LoginLog' => '\App\Models\Manager\Analysis\LoginLog',
        'ActionLog' => '\App\Models\Manager\Analysis\ActionLog',
    ];

    protected $baseDataPath = 'analysis.selectItems';

    public function dashboardView(Request $request)
    {
        return view('Analysis/DashboardView', [
            'pageTitle' => $this->role->getCurrentPageTitle($request),
            'request' => $request
        ]);
    }

    public function dashboardList(Request $request)
    {
        $limit = $request->query->get('limit', 20);
        $offset = $request->query->get('offset', 0);
        $gameId = $request->query->get('game_id', 0);
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'desc');
        $date = $request->query->get('date');
        $date = empty($date) ? date('m/d/Y') : urldecode($date);
        $s  = \DateTime::createFromFormat('m/d/Y', $date)->format('Y-m-d 00:00:00');
        $e  = \DateTime::createFromFormat('m/d/Y', $date)->format('Y-m-d 23:59:59');
        $d  = \DateTime::createFromFormat('m/d/Y', $date)->format('Y-m-d');

        $gold = new Gold;
        $gameId && $gold = $gold->where('game_id', $gameId);
        $playerWinRateB = $gold->where('post_time', '>=', $s)
        ->where('post_time', '<=', $e)
        ->where('type_id', 54)
        ->select($gold->raw('count(quantity) as count'))
        ->first()->count;

        $gold = new Gold;
        $gameId && $gold = $gold->where('game_id', $gameId);
        $playerWinRateA = $gold->where('post_time', '>=', $s)
        ->where('post_time', '<=', $e)
        ->where('type_id', 54)
        ->where('quantity', '>', 0)
        ->select($gold->raw('count(quantity) as count'))
        ->first()->count;

        $accountsToday = new AccountsToday;
        $gameId && $accountsToday = $accountsToday->where('accounts_today.gameid', $gameId);
        $todayConversionRateA = $accountsToday->where('accounts_today.update_time', $d)
        ->where('accounts_today.history_result', '!=', null)
        ->where('account_ext.register_time', '>=', $s)
        ->where('account_ext.register_time', '<=', $e)
        ->leftjoin('account_ext', 'account_ext.uid', '=', 'accounts_today.uid')
        ->select($accountsToday->raw('count(accounts_today.uid) as count'))->first()->count;

        $accountExt = new AccountExt;
        $gameId && $accountExt = $accountExt->where('game_id', $gameId);
        $todayConversionRateB = $accountExt->where('register_time', '>=', $s)
        ->where('register_time', '<=', $e)
        ->select($accountExt->raw('count(uid) as count'))->first()->count;

        $accountsToday = new AccountsToday;
        $gameId && $accountsToday = $accountsToday->where('gameid', $gameId);
        $activePeopleTodayA = $accountsToday->where('update_time', $d)->count();

        $accountsToday = new AccountsToday;
        $gameId && $accountsToday = $accountsToday->where('accounts_today.gameid', $gameId);
        $registerWinLose = $accountsToday->where('accounts_today.update_time', $d)
        ->where('account_ext.register_time', '>=', $s)
        ->where('account_ext.register_time', '<=', $e)
        ->leftjoin('account_ext', 'account_ext.uid', '=', 'accounts_today.uid')
        ->select('accounts_today.uid', 'accounts_today.today_result', 'accounts_today.history_result')
        ->orderBy($sort, $order)
        ->offset($offset)->limit($limit)->get()->toArray();

        $accountsToday = new AccountsToday;
        $gameId && $accountsToday = $accountsToday->where('accounts_today.gameid', $gameId);
        $registerWinLoseCnt = $accountsToday->where('accounts_today.update_time', $d)
        ->where('account_ext.register_time', '>=', $s)
        ->where('account_ext.register_time', '<=', $e)
        ->leftjoin('account_ext', 'account_ext.uid', '=', 'accounts_today.uid')
        ->count();

        $validUserA = $accountsToday->where('update_time', $d)->where('today_result', '>', 0)->count();

        $versus = new Versus;
        $gameId && $versus = $versus->where('game_id', $gameId);
        $bettingOddsA = $versus->where('post_time', '>=', $s)->where('post_time', '<=', $e)->count();

        $roomGoldDayStatistics = new RoomGoldDayStatistics;
        $gameId && $roomGoldDayStatistics = $roomGoldDayStatistics->where('game_id', $gameId);
        $betAmountA = $roomGoldDayStatistics->where('create_date', $d)->sum('bet_tax');

        $roomGoldDayStatistics = new RoomGoldDayStatistics;
        $gameId && $roomGoldDayStatistics = $roomGoldDayStatistics->where('game_id', $gameId);
        $payoutAmountA = (int) $roomGoldDayStatistics->where('create_date', $d)->sum($roomGoldDayStatistics->raw('gold - tax'));
        $data = [
            'player_win_rate' => [
                'a' => $playerWinRateA,
                'b' => $playerWinRateB,
                'c' => $playerWinRateB > 0 ? round(($playerWinRateA / $playerWinRateB) * 100, 2) : 0,
            ],

            'today_conversion_rate' => [
                'a' => $todayConversionRateA,
                'b' => $todayConversionRateB,
                'c' => $todayConversionRateB > 0 ? round(($todayConversionRateA / $todayConversionRateB)* 100, 2) : 0,
            ],

            'active_people_today' => [
                'a' => $activePeopleTodayA,
            ],

            'valid_user' => [
                'a' => $validUserA,
            ],

            'bet_amount' => [
                'a' => $betAmountA,
            ],
            
            'payout_amount' => [
                'a' => $payoutAmountA,
            ],

            'betting_odds' => [
                'a' => $bettingOddsA,
            ],

            // 'register_win_lose' => $registerWinLose,
        ];

        return [
            'result' => $data,
            'success' => 1,

            // 'result' => [],
            'rows' => $registerWinLose,
            'success' => 1,
            'total' => $registerWinLoseCnt,
        ];
    }
}
