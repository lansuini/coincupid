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
                                <input type="text" class="form-control" placeholder="{{ __('ts.ID') }}" data-field="id" id="id" />
                                <input type="text" class="form-control" placeholder="{{ __('ts.UID') }}" data-field="uid" />
                                <select class="form-control" data-field="client_id" id="client_id"></select>
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

    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('ts.Detail') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                <div class="modal-body">
                    <form class="row g-3">

                        <div class="col-md-4">
                            <label class="col-form-label">{{ __('ts.ID') }}</label>
                            <input type="text" class="form-control" data-field="id" />
                        </div>

                        <div class="col-md-4">
                            <label class="col-form-label">{{ __('ts.PID') }}</label>
                            <input type="text" class="form-control" data-field="pid" />
                        </div>

                        <div class="col-md-4">
                            <label class="col-form-label">{{ __('ts.Client') }}</label>
                            <select class="form-control client_id" data-field="client_id"></select>
                        </div>

                        <div class="col-md-4">
                            <label class="col-form-label">{{ __('ts.UID') }}</label>
                            <input type="text" class="form-control" data-field="uid" />
                        </div>

                        <div class="col-md-4">
                            <label class="col-form-label">{{ __('ts.TYPE') }}</label>
                            <select class="form-control type" data-field="type"></select>
                        </div>

                        <div class="col-md-4">
                            <label class="col-form-label">{{ __('ts.CostTime(ms)') }}</label>
                            <input type="text" class="form-control" data-field="cost_time" />
                        </div>

                        <div class="col-md-12">
                            <label class="col-form-label">{{ __('ts.URL') }}</label>
                            <input type="text" class="form-control" data-field="url" />
                        </div>

                        <div class="col-md-4">
                            <label class="col-form-label">{{ __('ts.Method') }}</label>
                            <input type="text" class="form-control" data-field="method" />
                        </div>

                        <div class="col-md-2">
                            <label class="col-form-label">{{ __('ts.ErrorCode') }}</label>
                            <input type="text" class="form-control" data-field="error_code" />
                        </div>

                        <div class="col-md-6">
                            <label class="col-form-label">{{ __('ts.ErrorText') }}</label>
                            <input type="text" class="form-control" data-field="error_text" />
                        </div>

                        <div class="col-md-4">
                            <label class="col-form-label">{{ __('ts.Code') }}</label>
                            <input type="text" class="form-control" data-field="code" />
                        </div>

                        <div class="col-md-4">
                            <label class="col-form-label">{{ __('ts.Created') }}</label>
                            <input type="text" class="form-control" data-field="created" />
                        </div>

                        <div class="col-md-4">
                            <label class="col-form-label">{{ __('ts.isSuccess') }}</label>
                            <select class="form-control is_success" data-field="is_success"></select>
                        </div>

                        <div class="col-md-6">
                            <label class="col-form-label">{{ __('ts.Args') }}</label>
                            <textarea class="form-control" data-field="args" rows="15"></textarea>
                        </div>


                        <div class="col-md-6">
                            <label class="col-form-label">{{ __('ts.Params') }}</label>
                            <textarea class="form-control" data-field="params" rows="15"></textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="col-form-label">{{ __('ts.Response') }}</label>
                            <textarea class="form-control" data-field="response" rows="25"></textarea>
                        </div>






                    </form>
                </div>

                <div class="modal-footer">
                    <input type="button" class="btn btn-default" value="{{ __('ts.Close') }}" data-dismiss="modal" />
                </div>
            </div>
        </div>
    </div>

</div>
@append

@section('content')
<!-- edit form -->

@stop

@section('script')
<script>
    var typeData = []

    function retry(clientId, id) {
        myConfirm.show({
            title: "{{ __('ts.confirm retry this request ?') }}",
            sure_callback: function() {
                cform.post('T001', apiPath + 'customer/serverrequestlog/' + clientId + '/' + id, function(d) {
                    $('#id').val(d.pid)
                    $('#btnSearch').click()
                })
            }
        })
    }

    function subList(id) {
        $('#id').val(id)
        $('#btnSearch').click()
    }

    function showDetailModal(clientId, id) {
        // data = decodeURI(data)
        cform.get('detailModal', apiPath + 'customer/serverrequestlog/' + clientId + '/' + id)
    }

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
            field: "pid",
            title: "{{ __('ts.PID') }}",
            align: "center",
            formatter: function(b, c, a) {
                return b
            },
            sortable: true,
        }, {
            field: "client_id",
            title: "{{ __('ts.Client') }}",
            align: "center",
            formatter: function(b, c, a) {
                return cform.getValue(typeData['customerType'], b)
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
                return cform.getValue(typeData['serverRequestType'], b)
            },
        }, {
            field: "url",
            title: "{{ __('ts.URL') }}",
            align: "center"
        }, {
            field: "code",
            title: "{{ __('ts.HttpCode') }}",
            align: "center",
        }, {
            field: "error_code",
            title: "{{ __('ts.ErrorCode') }}",
            align: "center"
        }, {
            field: "is_success",
            title: "{{ __('ts.IsSuccess') }}",
            align: "center",
            formatter: function(b, c, a) {
                return cform.getValue(typeData['successType'], b)
            },
        }, {
            field: "args",
            title: "{{ __('ts.Args') }}",
            align: "center",
            visible: false,
        }, {
            field: "method",
            title: "{{ __('ts.Method') }}",
            align: "center",
            visible: false,
        }, {
            field: "params",
            title: "{{ __('ts.Params') }}",
            align: "center",
            visible: false,
        }, {
            field: "response",
            title: "{{ __('ts.Response') }}",
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
        }, {
            field: "-",
            title: "{{ __('ts.Action') }}",
            align: "center",
            formatter: function(b, c, a) {
                return "<a class=\"btn btn-xs btn-primary\" onclick='retry(" + c.client_id + "," + c.id + ")'>{{ __('ts.Retry') }}</a>" +
                    "<a class=\"btn btn-xs btn-info\" onclick='subList(" + c.id + ")'>{{ __('ts.SubList') }}</a>" +
                    "<a class=\"btn btn-xs btn-info\" onclick='showDetailModal(" + c.client_id + "," + c.id + ")'>{{ __('ts.Detail') }}</a>"
            }
        }]
    }

    $(function() {
        $('#reservation').daterangepicker({
            "startDate": moment().subtract(3, 'days'),
            "endDate": moment()
        }, function(start, end, label) {
            console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
        })

        common.getAjax(apiPath + "getbasedata?requireItems=customerType,customerAPIType1,successType,serverRequestType,costTimeType1", function(a) {
            typeData = a.result
            $("#client_id").initSelect(a.result.customerAPIType1, "key", "value", "{{ __('ts.Client') }}", 0 in a.result.customerAPIType1 ? a.result.customerAPIType1[0]['key'] : null)
            $("#type").initSelect(a.result.serverRequestType, "key", "value", "{{ __('ts.ServerRequestType') }}")
            $("#is_success").initSelect(a.result.successType, "key", "value", "{{ __('ts.successed status') }}")
            $("#cost_time").initSelect(a.result.costTimeType1, "key", "value", "{{ __('ts.cost time status') }}")
            $(".client_id").initSelect(a.result.customerAPIType1, "key", "value", "{{ __('ts.Client') }}")
            $(".type").initSelect(a.result.serverRequestType, "key", "value", "{{ __('ts.ServerRequestType') }}")
            $(".is_success").initSelect(a.result.successType, "key", "value", "{{ __('ts.successed status') }}")

            $('#client_id').select2()
            $("#btnSearch").initSearch(apiPath + "customer/serverrequestlog", getColumns(), {
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