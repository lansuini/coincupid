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

                                    <select class="form-control" id="t" data-field="t"></select>
                                    <input type="text" class="form-control" data-field="v" placeholder="" />
 

                                </div>

                                <div class="btn-group v-search-bar">
                                <select class="form-control" id="t2" data-field="t2"></select>
                                    <input type="text" class="form-control" style="width:190px;" data-field="created" placeholder="{{ __('ts.created') }}" id="reservation" />
                                    <button type="button" class="btn btn-default" id="btnSearch">
                                        <i class="fas fa-search"></i>{{ __('ts.Search') }}
                                    </button>
                                </div>

                                <div id="toolbar" class="select">
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

    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('ts.Detail') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                <div class="modal-body">
                    <form class="row g-3">

                        <div class="col-md-4">
                            <label class="col-form-label">{{ __('ts.Key') }}</label>
                            <input type="text" class="form-control" data-field="key" />
                        </div>
                        <div class="col-md-4">
                            <label class="col-form-label">{{ __('ts.ID') }}</label>
                            <input type="text" class="form-control" data-field="id" />
                        </div>

                        <div class="col-md-4">
                            <label class="col-form-label">{{ __('ts.AdminID') }}</label>
                            <input type="text" class="form-control" data-field="admin_id" />
                        </div>

                        <div class="col-md-12">
                            <label class="col-form-label">{{ __('ts.Browser') }}</label>
                            <input type="text" class="form-control" data-field="browser" />
                        </div>



                        <div class="col-md-6">
                            <label class="col-form-label">{{ __('ts.Before') }}</label>
                            <textarea rows="15" class="form-control" data-field="before"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="col-form-label">{{ __('ts.After') }}</label>
                            <textarea rows="15" class="form-control" data-field="after"></textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="col-form-label">{{ __('ts.URL') }}</label>
                            <input type="text" class="form-control" data-field="url" />
                        </div>

                        <div class="col-md-4">
                            <label class="col-form-label">{{ __('ts.TargetID') }}</label>
                            <input type="text" class="form-control" data-field="target_id" />

                        </div>

                        <div class="col-md-4">
                            <label class="col-form-label">{{ __('ts.IP') }}</label>
                            <input type="text" class="form-control" data-field="ip" />
                        </div>

                        <div class="col-md-4">
                            <label class="col-form-label">{{ __('ts.Method') }}</label>
                            <input type="text" class="form-control" data-field="method" />
                        </div>

                        <div class="col-md-6">
                            <label class="col-form-label">{{ __('ts.Params') }}</label>
                            <textarea rows="15" class="form-control" data-field="params"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="col-form-label">{{ __('ts.Desc') }}</label>
                            <input type="text" class="form-control" data-field="desc" />
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

    function getColumns() {
        return [{
            field: "uid",
            title: "{{ __('ts.UID') }}",
            align: "center",
            sortable: true,
        }, {
            field: "player_name",
            title: "{{ __('ts.UserName') }}",
            align: "center"
        }, {
            field: "nickname",
            title: "{{ __('ts.Nickname') }}",
            align: "center"
        }, {
            field: "balance",
            title: "{{ __('ts.Balance') }}",
            align: "center",
            formatter: function(b, c, a) {
                return common.ya(b)
            }
        }, {
            field: "last_logon_time",
            title: "{{ __('ts.LastLogonTime') }}",
            align: "center",
            sortable: true,
        }, {
            field: "created",
            title: "{{ __('ts.Created') }}",
            align: "center",
            sortable: true,
        }]
    }

    $(function() {
        $('#reservation').daterangepicker({
            "startDate": moment().subtract(3, 'days'),
            "endDate": moment()
        }, function(start, end, label) {
            console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
        })

        common.getAjax(apiPath + "getbasedata?requireItems=accountSearchType,accountSearchTimeType", function(a) {
            typeData = a.result
            $("#t").initSelect(a.result.accountSearchType, "key", "value", "")
            $("#t2").initSelect(a.result.accountSearchTimeType, "key", "value", "")

            $("#btnSearch").initSearch(apiPath + "user/account", getColumns(), {
                sortName: "uid",
                sortOrder: 'desc',
            })
            $("#btnSubmit").click()


        })

    })
</script>
@stop