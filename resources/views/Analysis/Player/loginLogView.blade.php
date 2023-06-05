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
                                <input type="text" class="form-control" data-field="uid" placeholder="{{ __('ts.uid') }}" />
                                <input type="text" class="form-control" data-field="ip" placeholder="{{ __('ts.ip') }}" />
                                <select class="form-control" data-field="client_id" id="client_id"></select>
                                <input type="text" class="form-control" style="width:190px;" data-field="created" placeholder="{{ __('ts.created') }}" id="reservation"/>
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
        return [{
            field: "id",
            title: "{{ __('ts.ID') }}",
            align: "center",
            formatter: function(b, c, a) {
                return b
            },
            sortable: true,
        }, {
            field: "uid",
            title: "{{ __('ts.UID') }}",
            align: "center"
        }, {
            field: "game_id",
            title: "{{ __('ts.Name') }}",
            align: "center",
            formatter: function(b, c, a) {
                return cform.getValue(typeData['gameAliasType'], b)
            },
            sortable: true,
        }, {
            field: "client_id",
            title: "{{ __('ts.clientId') }}",
            align: "center",
            formatter: function(b, c, a) {
                return cform.getValue(typeData['customerType'], b)
            },
        }, {
            field: "version",
            title: "{{ __('ts.Ver') }}",
            align: "center"
        }, {
            field: "os",
            title: "{{ __('ts.OS') }}",
            align: "center",
            formatter: function(b, c, a) {
                return cform.getValue(typeData['OSType'], b)
            },
            visible: false,
        }, {
            field: "os_version",
            title: "{{ __('ts.OS Ver') }}",
            align: "center"
        }, {
            field: "ip",
            title: "{{ __('ts.IP') }}",
            align: "center"
        }, {
            field: "brand",
            title: "{{ __('ts.Brand') }}",
            align: "center",
            visible: false,
        }, {
            field: "model",
            title: "{{ __('ts.Model') }}",
            align: "center",
            visible: false,
        }, {
            field: "post_time",
            title: "{{ __('ts.Created') }}",
            align: "center",
            formatter: function(b, c, a) {
                return moment.unix(b).format("MM/DD/YYYY HH:mm:ss");
            }
        }]
    }

    $(function() {

        $('#reservation').daterangepicker({
            "startDate": moment().subtract(3, 'days'),
            "endDate": moment()
        })

        common.getAjax(apiPath + "getbasedata?requireItems=OSType,customerType,gameAliasType", function(a) {
            typeData = a.result
            $("#game_id").initSelect(a.result.gameAliasType, "key", "value", "{{ __('ts.Games') }}")
            $("#client_id").initSelect(a.result.customerType, "key", "value", "{{ __('ts.Client') }}")
            $('#client_id').select2()
            $("#btnSearch").initSearch(apiPath + "player/loginlog", getColumns(), {
                sortName: "id",
                sortOrder: 'desc'
            })
            $("#btnSubmit").click()
        })

        common.initSection(true)

    })
</script>
@stop