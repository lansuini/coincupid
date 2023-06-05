@extends('/GM/Layout')

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">{{ __('ts.Home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('ts.Dashboard') }}</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">

                    <div classs="card">

                        <div class="card-header">
                            <div class="btn-group v-search-bar" id="divSearch">
                                <select class="form-control" data-field="game_id" id="game_id"></select>
                                <div class=" date" id="reservationdate" data-target-input="nearest" style="display: none;">
                                    <input type="text" class="form-control datetimepicker-input" data-toggle="datetimepicker" data-target="#reservationdate" data-field="date" placeholder="{{ __('ts.date') }}" />
                                </div>
                                <button type="button" class="btn btn-default" id="btnSearch">
                                    <i class="fas fa-search"></i>{{ __('ts.Search') }}
                                </button>
                            </div>


                        </div>



                    </div>
                </div>
                <!-- /.col -->
            </div>
            <!-- Small boxes (Stat box) -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="betting_odds">0</h3>

                            <p>{{ __('ts.Betting Odds') }}</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-bag"></i>
                        </div>
                        <a href="#" class="small-box-footer" style="display: none;">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 id="bet_amount">0</h3>

                            <p>{{ __('ts.Bet Amount') }}</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars"></i>
                        </div>
                        <a href="#" class="small-box-footer" style="display: none;">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="payout_amount">0</h3>

                            <p>{{ __('ts.Payout Amount') }}</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-person-add"></i>
                        </div>
                        <a href="#" class="small-box-footer" style="display: none;">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="valid_user">0</h3>

                            <p>{{ __('ts.Valid User') }}</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-pie-graph"></i>
                        </div>
                        <a href="#" class="small-box-footer" style="display: none;">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
            </div>
            <!-- /.row -->

            <!-- Small boxes (Stat box) -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-olive">
                        <div class="inner">
                            <h3 id="player_win_rate">0</h3>

                            <p>{{ __('ts.Player Win Rate') }}</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-bag"></i>
                        </div>
                        <a href="#" class="small-box-footer" style="display: none;">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-navy">
                        <div class="inner">
                            <h3 id="today_conversion_rate">0</h3>

                            <p>{{ __('ts.Todays Conversion Rate') }}</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars"></i>
                        </div>
                        <a href="#" class="small-box-footer" style="display: none;">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-dark">
                        <div class="inner">
                            <h3 id="active_people_today">0</h3>

                            <p>{{ __('ts.Active People Today') }}</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-person-add"></i>
                        </div>
                        <a href="#" class="small-box-footer" style="display: none;">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6" style="display: none;">
                    <!-- small box -->
                    <div class="small-box bg-lightblue">
                        <div class="inner">
                            <h3>0</h3>

                            <p>{{ __('ts.Valid User') }}</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-pie-graph"></i>
                        </div>
                        <a href="#" class="small-box-footer" style="display: none;">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
            </div>
            <!-- /.row -->


            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="card-title">{{ __('ts.Register Account WinLose') }}</h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table id="tabMain" class="table table-striped table-valign-middle"></table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>

@append

@section('content')
<!-- edit form -->

@stop

@section('script')
<script>
    function getColumns() {
        return [{
            field: "uid",
            title: "{{ __('ts.UID') }}",
            align: "center",
            formatter: function(b, c, a) {
                return b
            }
        }, {
            field: "today_result",
            title: "{{ __('ts.WinLose') }}",
            align: "center",
            formatter: function(b, c, a) {
                return common.ya(b)
            },
        }, {
            field: "history_result",
            title: "{{ __('ts.HistoryWinLose') }}",
            align: "center",
            formatter: function(b, c, a) {
                return common.ya(b)
            },
        }]
    }

    $(function() {
        common.getAjax(apiPath + "getbasedata?requireItems=gameAliasType", function(a) {
            typeData = a.result
            $("#game_id").initSelect(a.result.gameAliasType, "key", "value", "{{ __('ts.Games') }}")
            $("#btnSearch").initSearch(apiPath + "dashboard", getColumns(), {
                sortName: "today_result",
                sortOrder: 'desc',
                success_callback: function(d) {
                    // console.log(d)
                    $('#player_win_rate').html(d.result.player_win_rate.c + '% (' + d.result.player_win_rate.a + '/' + d.result.player_win_rate.b + ')')
                    $('#today_conversion_rate').html(d.result.today_conversion_rate.c + '% (' + d.result.today_conversion_rate.a + '/' + d.result.today_conversion_rate.b + ')')
                    $('#active_people_today').html(d.result.active_people_today.a)
                    $('#valid_user').html(d.result.valid_user.a)
                    $('#bet_amount').html(common.ya(d.result.bet_amount.a))
                    $('#payout_amount').html(common.ya(d.result.payout_amount.a))
                    $('#betting_odds').html(d.result.betting_odds.a)
                }
            })

            $('#reservationdate').datetimepicker({
                format: 'L',
                defaultDate: moment()
            })

            $("#btnSubmit").click()
        })

    })
</script>
@stop