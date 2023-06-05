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
                                <input type="text" class="form-control" data-field="name" placeholder="{{ __('ts.name') }}" />
                                <input type="text" class="form-control" data-field="count_month" placeholder="{{ __('ts.CountMonth') }}" />

                                <button type="button" class="btn btn-default" id="btnSearch">
                                    <i class="fas fa-search"></i>{{ __('ts.Search') }}
                                </button>

                                <button type="button" class="btn btn-primary" id="btnCreate">
                                    <i class="fas fa-plus"></i>{{ __('ts.Create') }}
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <table id="tabMain" style="word-wrap: break-work;word-break: break-all;"></table>

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
                    <h4 class="modal-title">{{ __('ts.Edit') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                <div class="modal-body">
                    <form class="">
                        <div class="form-group sr-only">
                            <label class="col-form-label">{{ __('ts.id') }}</label>
                            <input type="text" class="form-control" data-field="id" />
                        </div>

                        <div class="modal-body">
                            <form class="">
                                <div class="form-group">
                                    <label class="col-form-label">{{ __('ts.Name') }}</label>
                                    <input type="text" class="form-control" data-field="name" readonly/>
                                </div>

                                <div class="form-group">
                                    <label class="" for="txtRole">{{ __('ts.Count Month') }}</label>
                                    <input type="text" class="form-control" data-field="count_month" readonly/>
                                </div>

                                <div class="form-group">
                                    <label class="" for="txtRole">{{ __('ts.Exchange Rate') }}</label>
                                    <input type="text" class="form-control" data-field="exchange_rate" />
                                </div>
                            </form>
                        </div>

                    </form>
                </div>

                <div class="modal-footer">
                    <input type="button" class="btn btn-default" value="{{ __('ts.Close') }}" data-dismiss="modal" />
                    <input type="button" class="btn btn-primary" value="{{ __('ts.Submit') }}" id="updateBtnSubmit" />
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('ts.Create') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                <div class="modal-body">
                    <form class="">
                        <div class="form-group">
                            <label class="col-form-label">{{ __('ts.Name') }}</label>
                            <input type="text" class="form-control" data-field="name" />
                        </div>

                        <div class="form-group">
                            <label class="" for="txtRole">{{ __('ts.Count Month') }}</label>
                            <input type="text" class="form-control" data-field="count_month" />
                        </div>

                        <div class="form-group">
                            <label class="" for="txtRole">{{ __('ts.Exchange Rate') }}</label>
                            <input type="text" class="form-control" data-field="exchange_rate" />
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <input type="button" class="btn btn-default" value="{{ __('ts.Close') }}" data-dismiss="modal" />
                    <input type="button" class="btn btn-primary" value="{{ __('ts.Submit') }}" id="createBtnSubmit" />
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

    function showEditModal(data) {
        cform.get('editModal', apiPath + 'manager/currency/' + data['id'], function(res) {
            var gameOCVal = res.data.game_oc
            if (gameOCVal != null) {
                gameOCVal = gameOCVal.split(',')
                $('.game_oc_edit').find('input:checkbox').each(function() {
                    var val = $(this).val()
                    if (gameOCVal.indexOf(val) != -1) {
                        $(this).prop('checked', true)
                    } else {
                        $(this).prop('checked', false)
                    }
                })
            }

        })
    }

    function showCreateModal() {
        $('#createModal').modal()
    }

    function delAccount(id) {
        myConfirm.show({
            title: "{{ __('ts.confirm deletion ?') }}",
            sure_callback: function() {
                cform.del(apiPath + "manager/currency/" + id, function(d) {
                    location.href = location.href
                })
            }
        })
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
            field: "name",
            title: "{{ __('ts.Name') }}",
            align: "center"
        }, {
            field: "exchange_rate",
            title: "{{ __('ts.ExchangeRate') }}",
            align: "center"
        }, {
            field: "count_month",
            title: "{{ __('ts.CountMonth') }}",
            align: "center",
            width: "180",
            widthUnit: "px",
        }, {
            field: "created",
            title: "{{ __('ts.Created') }}",
            align: "center",
        }, {
            field: "updated",
            title: "{{ __('ts.Updated') }}",
            align: "center",
        }, {
            field: "-",
            title: "{{ __('ts.Action') }}",
            align: "center",
            formatter: function(b, c, a) {
                return "<a class=\"btn btn-xs btn-primary\" onclick='showEditModal(" + JSON.stringify(c) + ")'>{{ __('ts.Edit') }}</a>" +
                    "<a class=\"btn btn-xs btn-danger\" onclick='delAccount(\"" + c.id + "\")'>{{ __('ts.Del') }}</a>"
            }
        }]
    }

    $(function() {

        common.getAjax(apiPath + "getbasedata?requireItems=testItems", function(a) {
            typeData = a.result
            $("#btnSearch").initSearch(apiPath + "manager/currency", getColumns(), {
                sortName: "id",
                sortOrder: 'desc'
            })

            $("#btnSubmit").click()
        })


        $('#updateBtnSubmit').click(function() {
            var id = $('#editModal form').find("[data-field='id']").val()
            cform.patch('editModal', apiPath + 'manager/currency/' + id, function(d) {
                myAlert.success(d.result)
                $('#editModal').modal('hide');
                $('#btnSearch').click()
            })
        })

        $('#createBtnSubmit').click(function() {
            cform.post('createModal', apiPath + 'manager/currency')
        })

        $('#btnCreate').click(function() {
            showCreateModal()
        })
    })
</script>
@stop