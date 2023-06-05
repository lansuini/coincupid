@extends('/GM/Layout')

@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
@include('/GM/navigator')

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">

                    <div classs="card">

                        <div class="card-header">
                            <div class="btn-group v-search-bar" id="divSearch">
                                <select class="form-control" data-field="game_id" id="game_id"></select>
                                <input type="text" class="form-control" data-field="created" style="width:200px;" placeholder="{{ __('ts.created') }}" id="reservation" />
                                <button type="button" class="btn btn-default" id="btnSearch">
                                    <i class="fas fa-search"></i>{{ __('ts.Search') }}
                                </button>
                            </div>

                            <div id="toolbar" class="select">
                            </div>
                        </div>

                        <div class="card-body">
                            <table id="tabMain"></table>
                            <div class="font-weight-light">TIPs: The (VaildUser/BetAmount/PayoutAmount/RTP) data field is updated hourly</div>
                        </div>

                    </div>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
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
                field: "game_id",
                title: "{{ __('ts.Name') }}",
                align: "center",
                formatter: function(b, c, a) {
                    return cform.getValue(typeData['gameAliasType'], b)
                },
                sortable: true,
            },
            {
                field: "sum_gold",
                title: "{{ __('ts.TheDayWinLose') }}",
                align: "center",
                sortable: true,
                formatter: function(b, c, a) {
                    return common.ya(b)
                },
            },
            {
                field: "sum_tax",
                title: "{{ __('ts.TheDayTax') }}",
                align: "center",
                sortable: true,
                formatter: function(b, c, a) {
                    return common.ya(b)
                },
            },
            {
                field: "sum_history_gold",
                title: "{{ __('ts.HistoryWinLose') }}",
                align: "center",
                sortable: true,
                formatter: function(b, c, a) {
                    return common.ya(b)
                },
            },
            {
                field: "sum_history_tax",
                title: "{{ __('ts.HistoryTax') }}",
                align: "center",
                sortable: true,
                formatter: function(b, c, a) {
                    return common.ya(b)
                },
            },
            {
                field: "valid_user",
                title: "{{ __('ts.VaildUser') }}",
                align: "center",
                sortable: true,
                formatter: function(b, c, a) {
                    return b
                },
            },
            {
                field: "bet_amount",
                title: "{{ __('ts.bet_amount') }}",
                align: "center",
                sortable: true,
                formatter: function(b, c, a) {
                    return common.ya(b)
                },
            },
            {
                field: "payout_amount",
                title: "{{ __('ts.payout_amount') }}",
                align: "center",
                sortable: true,
                formatter: function(b, c, a) {
                    return common.ya(b)
                },
            },
            {
                field: "RTP",
                title: "{{ __('ts.RTP') }}",
                align: "center",
                sortable: true,
                formatter: function(b, c, a) {
                    return common.ya(b) + '%'
                },
            },
            {
                field: "RTPET",
                title: "{{ __('ts.RTP(Excluding Tax)') }}",
                align: "center",
                sortable: true,
                formatter: function(b, c, a) {
                    return common.ya(b) + '%'
                },
            }
        ]
    }

    $(function() {

        $('#reservation').daterangepicker({
            "startDate": moment().subtract(7, 'days'),
            "endDate": moment()
        })

        common.getAjax(apiPath + "getbasedata?requireItems=gameAliasType", function(a) {
            typeData = a.result
            $("#game_id").initSelect(a.result.gameAliasType, "key", "value", "{{ __('ts.Games') }}")
            $("#btnSearch").initSearch(apiPath + "player/roomwinlose", getColumns(), {
                // sortName: "id",
                // sortOrder: 'desc',
                // showColumns: true,
                // toolbar: '#toolbar',
                // showExport: true,
                // exportTypes: ['csv'],
                // exportDataType: "all"

            })
            $("#btnSubmit").click()
        })

        common.initSection(true)

    })
</script>
@stop