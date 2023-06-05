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
                                <input type="text" class="form-control" data-field="name" placeholder="{{ __('bk.UserIntention') }}" />

                                <button type="button" class="btn btn-default" id="btnSearch">
                                    <i class="fas fa-search"></i>{{ __('ts.Search') }}
                                </button>

                                <button type="button" class="btn btn-primary" id="btnCreate">
                                    <i class="fas fa-plus"></i>{{ __('bk.Create') }}
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
                    <h4 class="modal-title">{{ __('bk.Edit') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                <div class="modal-body">
                    <form class="">
                        <div class="form-group sr-only">
                            <label class="col-form-label">{{ __('ts.ID') }}</label>
                            <input type="text" class="form-control" data-field="id" />
                        </div>

                        <div class="modal-body">
                            <form class="">

                                <div class="form-group">
                                    <label class="col-form-label">{{ __('bk.UserIntention') }}</label>
                                    <input type="text" class="form-control" data-field="name" />
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">{{ __('bk.UserIntentionEn') }}</label>
                                    <input type="text" class="form-control" data-field="name_en" />
                                </div>

                                <div class="form-group">
                                    <label class="" for="display">{{ __('bk.Status') }}</label>
                                    <select class="form-control enabled" data-field="display"></select>
                                </div>

                            </form>
                        </div>

                    </form>
                </div>

                <div class="modal-footer">
                    <input type="button" class="btn btn-default" value="{{ __('bk.Close') }}" data-dismiss="modal" />
                    <input type="button" class="btn btn-primary" value="{{ __('bk.Submit') }}" id="updateBtnSubmit" />
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('bk.Create') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                <div class="modal-body">
                    <form class="">

                        <div class="form-group">
                            <label class="col-form-label">{{ __('bk.UserIntention') }}</label>
                            <input type="text" class="form-control" data-field="name" />
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">{{ __('bk.UserIntentionEn') }}</label>
                            <input type="text" class="form-control" data-field="name_en" />
                        </div>
                        <div class="form-group">
                            <label class="" for="display">{{ __('bk.Status') }}</label>
                            <select class="form-control enabled" data-field="display"></select>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <input type="button" class="btn btn-default" value="{{ __('bk.Close') }}" data-dismiss="modal" />
                    <input type="button" class="btn btn-primary" value="{{ __('bk.Submit') }}" id="createBtnSubmit" />
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
        cform.get('editModal', apiPath + 'configset/intentionDetail/' + data['id'], function(res) {


        })
    }

    function showCreateModal() {
        $('#createModal').modal()
    }

    function delAccount(id) {
        myConfirm.show({
            title: "{{ __('ts.confirm deletion ?') }}",
            sure_callback: function() {
                cform.del(apiPath + "configset/intentionDel/" + id, function(d) {
                    location.href = location.href
                })
            }
        })
    }

    function getColumns() {
        return [{
            templet:"#checked",
            title: "<input type='checkbox' name='siam_all' title='' lay-skin='primary' lay-filter='siam_all'>",
            width:60
        },{
            field: "id",
            title: "{{ __('bk.id') }}",
            align: "center",
            formatter: function(b, c, a) {
                return b
            },
            sortable: true,
        }, {
            field: "name",
            title: "{{ __('bk.UserIntention') }}",
            align: "center"
        }, {
            field: "name_en",
            title: "{{ __('bk.UserIntentionEn') }}",
            align: "center"
        },{
            field: "display",
            title: "{{ __('ts.Status') }}",
            align: "center",
            formatter: function(b, c, a) {
                return cform.getValue(typeData['enabledType'], b)
            },
        }, {
            field: "created_at",
            title: "{{ __('bk.Created') }}",
            align: "center",
        }, {
            field: "updated_at",
            title: "{{ __('bk.Updated') }}",
            align: "center",
        }, {
            field: "-",
            title: "{{ __('bk.Action') }}",
            align: "center",
            formatter: function(b, c, a) {
                return "<a class=\"btn btn-xs btn-primary\" onclick='showEditModal(" + JSON.stringify(c) + ")'>{{ __('bk.Edit') }}</a>"
            }
        }]
    }

    $(function() {

        common.getAjax(apiPath + "getbasedata?requireItems=enabledType", function(a) {
            typeData = a.result
            $("#btnSearch").initSearch(apiPath + "configset/configIntention", getColumns(), {
                sortName: "id",
                sortOrder: 'desc'
            })
            $(".enabled").initSelect(a.result.enabledType, "key", "value", "{{ __('ts.enabled') }}")
            $("#btnSubmit").click()
        })


        $('#updateBtnSubmit').click(function() {
            var id = $('#editModal form').find("[data-field='id']").val()
            cform.patch('editModal', apiPath + 'configset/intentionEdit/' + id, function(d) {
                myAlert.success(d.result)
                $('#editModal').modal('hide');
                $('#btnSearch').click()
            })
        })

        $('#createBtnSubmit').click(function() {
            cform.post('createModal', apiPath + 'configset/intentionAdd')
        })

        $('#btnCreate').click(function() {
            showCreateModal()
        })
    })
</script>
@stop
