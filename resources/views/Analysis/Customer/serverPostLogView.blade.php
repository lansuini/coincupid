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
                            <div id="divSearch">
                                <div class="btn-group v-search-bar">
                                    <input type="text" class="form-control" placeholder="{{ __('ts.trace id') }}" data-field="trace_id" id="trace_id" />
                                    <input type="text" class="form-control" placeholder="{{ __('ts.transfer reference') }}" data-field="transfer_reference" id="transfer_reference" />
                                    <input type="text" class="form-control" placeholder="{{ __('ts.UID') }}" data-field="uid" />
                                    <select class="form-control" data-field="client_id" id="client_id"></select>

                                </div>

                                <div class="btn-group v-search-bar">
                                    <select class="form-control" data-field="type" id="type"></select>
                                    <select class="form-control" data-field="is_success" id="is_success"></select>
                                    <select class="form-control" data-field="cost_time" id="cost_time"></select>
                                    <input type="text" class="form-control" style="width:190px;" data-field="created" placeholder="{{ __('ts.created') }}" id="reservation" />
                                    <button type="button" class="btn btn-default" id="btnSearch">
                                        <i class="fas fa-search"></i>{{ __('ts.Search') }}
                                    </button>
                                </div>

                                <div id="toolbar" class="select"></div>
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
    var typeData = []

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
            field: "trace_id",
            title: "{{ __('ts.TraceID') }}",
            align: "center",
            formatter: function(b, c, a) {
                return b
            },
        }, {
            field: "client_id",
            title: "{{ __('ts.Client') }}",
            align: "center",
            formatter: function(b, c, a) {
                return cform.getValue(typeData['customerType'], b)
            },
        }, {
            field: "transfer_reference",
            title: "{{ __('ts.TransferReference') }}",
            align: "center",
            formatter: function(b, c, a) {
                return b
            },
        }, {
            field: "uid",
            title: "{{ __('ts.UID') }}",
            align: "center"
        }, {
            field: "type",
            title: "{{ __('ts.Type') }}",
            align: "center",
            formatter: function(b, c, a) {
                return cform.getValue(typeData['serverPostType'], b)
            },
        }, {
            field: "error_code",
            title: "{{ __('ts.ErrorCode') }}",
            align: "center",
            visible: true,
        }, {
            field: "error_text",
            title: "{{ __('ts.ErrorText') }}",
            align: "center",
            visible: false,
        }, {
            field: "arg",
            title: "{{ __('ts.Args') }}",
            align: "center",
            visible: false,
        }, {
            field: "return",
            title: "{{ __('ts.Response') }}",
            align: "center",
            visible: false,
        }, {
            field: "ip",
            title: "{{ __('ts.IP') }}",
            align: "center",
            visible: false,
        }, {
            field: "cost_time",
            title: "{{ __('ts.CostTime(ms)') }}",
            align: "center",
            formatter: function(b, c, a) {
                return b
            },
            sortable: true,
        }, {
            field: "created",
            title: "{{ __('ts.Created') }}",
            align: "center",
        }]
    }

    $(function() {
        $('#reservation').daterangepicker({
            "startDate": moment().subtract(3, 'days'),
            "endDate": moment()
        }, function(start, end, label) {
            console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
        })

        common.getAjax(apiPath + "getbasedata?requireItems=customerType,customerAPIType2,successType,serverPostType,costTimeType2", function(a) {
            typeData = a.result
            $("#client_id").initSelect(a.result.customerAPIType2, "key", "value", "{{ __('ts.Client') }}", 0 in a.result.customerAPIType2 ? a.result.customerAPIType2[0]['key'] : null)
            $("#type").initSelect(a.result.serverPostType, "key", "value", "{{ __('ts.ServerPostType') }}")
            $("#is_success").initSelect(a.result.successType, "key", "value", "{{ __('ts.successed status') }}")
            $("#cost_time").initSelect(a.result.costTimeType2, "key", "value", "{{ __('ts.cost time status') }}")
            $('#client_id').select2()
            $("#btnSearch").initSearch(apiPath + "customer/serverpostlog", getColumns(), {
                sortName: "id",
                sortOrder: 'desc',
                showColumns: true,
                toolbar: '#toolbar',
            })

            $("#btnSubmit").click()
            common.initSection(true)
        })
    })
</script>
@stop