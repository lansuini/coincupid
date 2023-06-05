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
                                <input type="text" class="form-control" data-field="uid" placeholder="{{ __('ts.UID') }}" />
                                <input type="text" class="form-control" data-field="room_name" placeholder="{{ __('ts.RoomName') }}" />
                                <select class="form-control" data-field="client_id" id="client_id"></select>
                                <button type="button" class="btn btn-default" id="btnSearch">
                                    <i class="fas fa-search"></i>{{ __('ts.Search') }}
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
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
    function getColumns() {
        return [ {
            field: "uid",
            title: "{{ __('ts.UID') }}",
            align: "center"
        }, {
            field: "client_id",
            title: "{{ __('ts.Client') }}",
            align: "center",
            formatter: function(b, c, a) {
                return cform.getValue(typeData['customerType'], b)
            },
        }, {
            field: "gameid",
            title: "{{ __('ts.GameName') }}",
            align: "center",
            formatter: function(b, c, a) {
                return cform.getValue(typeData['gameAliasType'], b)
            }
        }, {
            field: "room_name",
            title: "{{ __('ts.RoomName') }}",
            align: "center",
        }, 
        // {
        //     field: "total_gold",
        //     title: "{{ __('ts.TotalGold') }}",
        //     align: "center"
        // }, 
        {
            field: "all_result",
            title: "{{ __('ts.RoomWinLose') }}",
            align: "center",
            sortable: true,
            formatter: function(b, c, a) {
                return common.ya(b)
            },
        }, {
            field: "today_result",
            title: "{{ __('ts.TheDayRoomWinLose') }}",
            align: "center",
            sortable: true,
            formatter: function(b, c, a) {
                return common.ya(b)
            },
        }, {
            field: "history_result",
            title: "{{ __('ts.HistoryRoomWinLose') }}",
            align: "center",
            sortable: true,
            formatter: function(b, c, a) {
                return common.ya(b)
            },
        }
        ]
    }

    $(function() {

        common.getAjax(apiPath + "getbasedata?requireItems=gameAliasType,customerType", function(a) {
            typeData = a.result
            $("#client_id").initSelect(a.result.customerType, "key", "value", "{{ __('ts.Client') }}")
            $('#client_id').select2()
            $("#btnSearch").initSearch(apiPath + "player/online", getColumns(), {
                // sortName: "id",
                // sortOrder: 'desc'
            })
            $("#btnSubmit").click()
        })

        common.initSection(true)

    })
</script>
@stop