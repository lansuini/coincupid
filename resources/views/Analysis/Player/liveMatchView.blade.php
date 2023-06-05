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
                                <div class=" date" id="reservationdate" data-target-input="nearest">
                                    <input type="text" class="form-control datetimepicker-input" data-toggle="datetimepicker" data-target="#reservationdate" data-field="created" placeholder="{{ __('ts.created') }}" />
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
                            <!-- <div id="chart2" style="width:100%;height:400px;"></div> -->
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
                field: "name",
                title: "{{ __('ts.Game') }}",
                align: "center",
            }, {
                field: "num",
                title: "{{ __('ts.Count') }}",
                align: "center",
            }]
        }

        $('#reservationdate').datetimepicker({
            format: 'L',
            defaultDate: moment()
        })

        common.getAjax(apiPath + "getbasedata?requireItems=testItems", function(a) {
            typeData = a.result
            $("#btnSearch").initSearch(apiPath + "player/livematch", getColumns(), {
                pagination: false,
                paginationParts: [],
                success_callback: function (h) {
                    echarts.init($('#chart1')[0]).setOption(h.charts.gameOpt)
                }
            })
            $("#btnSubmit").click()
        })



    })
</script>
@stop