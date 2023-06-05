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
                                <!-- <input type="text" class="form-control" data-field="created" placeholder="{{ __('ts.created') }}" id="reservation" /> -->
                                <div class=" date" id="reservationdate" data-target-input="nearest">
                                    <input type="text" class="form-control datetimepicker-input" data-toggle="datetimepicker" data-target="#reservationdate" data-field="created" placeholder="{{ __('ts.created') }}" />
                                    <!-- <div class="input-group-append" data-target="#reservationdate" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div> -->
                                </div>

                                <button type="button" class="btn btn-default" id="btnSearch">
                                    <i class="fas fa-search"></i>{{ __('ts.Search') }}
                                </button>
                            </div>

                            <div id="toolbar" class="select">
                            </div>
                        </div>

                        <div class="card-body">
                            <div id="chart1" style="width:100%;height:400px;"></div>
                            <div id="chart2" style="width:100%;height:400px;"></div>
                            <table id="tabMain"></table>
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
    $(function() {

        function getColumns() {
            return [{
                field: "r1",
                title: "{{ __('ts.-') }}",
                align: "center",
            }, {
                field: "r2",
                title: "{{ __('ts.Online') }}",
                align: "center",
            }, {
                field: "r3",
                title: "{{ __('ts.Play') }}",
                align: "center",
            }]
        }
        // $('#reservation').daterangepicker({
        //     "startDate": moment().subtract(3, 'days'),
        //     "endDate": moment(),
        //     timePicker: true,
        //     timePickerIncrement: 30,
        //     locale: {
        //         format: 'MM/DD/YYYY hh:mm A'
        //     }
        // })

        $('#reservationdate').datetimepicker({
            format: 'L',
            defaultDate: moment()
        })

        common.getAjax(apiPath + "getbasedata?requireItems=testItems,gameAliasType", function(a) {
            typeData = a.result
            // $("#game_id").initSelect(a.result.reasonType, "key", "value", "{{ __('ts.Types') }}")
            // $('#game_id').select2()
            $("#game_id").initSelect(a.result.gameAliasType, "key", "value", "{{ __('ts.Games') }}")
            $("#btnSearch").initSearch(apiPath + "player/realonlineplay", getColumns(), {
                // sortName: "id",
                // sortOrder: 'desc',
                // showColumns: true,
                // toolbar: '#toolbar',
                // showExport: true,
                // exportTypes: ['csv'],
                // exportDataType: "all"
                pagination: false,
                paginationParts: [],
                success_callback: function (h) {
                    // console.log(h)
                    echarts.init($('#chart1')[0]).setOption(h.charts.onlineOpt)
                    echarts.init($('#chart2')[0]).setOption(h.charts.playOpt)
                }
            })
            $("#btnSubmit").click()
        })

        // common.initSection(true)
        // var chartDom = $('#chart1')[0];
        // // var chartDom = document.getElementById('chart1');
        // var myChart = echarts.init(chartDom);
        // var option;

        // option = {
        //     xAxis: {
        //         type: 'category',
        //         data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
        //     },
        //     yAxis: {
        //         type: 'value'
        //     },
        //     series: [{
        //         data: [150, 230, 224, 218, 135, 147, 260],
        //         type: 'line'
        //     }]
        // };

        // option && myChart.setOption(option);

        // echarts.init($('#chart2')[0]).setOption(option);

    })
</script>
@stop