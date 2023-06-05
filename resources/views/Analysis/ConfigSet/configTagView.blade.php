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
                                <select class="form-control" data-field="attr_id" id="attr_id">
{{--                                    <option value="0">全部</option>--}}
                                </select>
                                <input type="text" class="form-control" data-field="tag_name" placeholder="{{ __('bk.tagName') }}" />

                                <button type="button" class="btn btn-default" id="btnSearch">
                                    <i class="fas fa-search"></i>{{ __('bk.Search') }}
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
                            <label class="col-form-label">{{ __('bk.tagId') }}</label>
                            <input type="text" class="form-control" data-field="tag_id" />
                        </div>

                        <div class="modal-body">
                            <form class="">

                                <div class="form-group">
                                    <label class="col-form-label">{{ __('bk.tagName') }}</label>
                                    <input type="text" class="form-control" data-field="tag_name" />
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">{{ __('bk.tagName') }}</label>
                                    <input type="text" class="form-control" data-field="tag_name_en" />
                                </div>

                                <div class="form-group">
                                    <label class="" for="attr_id">{{ __('bk.AttrNames') }}</label>
                                    <select class="form-control" id="attr_id1" data-field="attr_id"></select>
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
                            <label class="" for="attr_id">{{ __('bk.AttrNames') }}</label>
                            <select class="form-control" id="attr_id0" data-field="attr_id"></select>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">{{ __('bk.tagName') }}</label>
                            <input type="text" class="form-control" data-field="tag_name" />
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">{{ __('bk.tagName') }}</label>
                            <input type="text" class="form-control" data-field="tag_name_en" />
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
        cform.get('editModal', apiPath + 'configset/tagDetail/' + data['tag_id'], function(res) {


        })
    }

    function showCreateModal() {
        $('#createModal').modal()
    }

    function delAccount(tag_id) {
        myConfirm.show({
            title: "{{ __('bk.confirm deletion ?') }}",
            sure_callback: function() {
                cform.del(apiPath + "configset/tagDel/" + tag_id, function(d) {
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
            field: "tag_id",
            title: "{{ __('bk.id') }}",
            align: "center",
            formatter: function(b, c, a) {
                return b
            },
            sortable: true,
        }, {
            field: "tag_name",
            title: "{{ __('bk.tagName') }}",
            align: "center"
        }, {
            field: "tag_name_en",
            title: "{{ __('bk.tagName') }}",
            align: "center"
        },{
            field: "attr_id",
            title: "{{ __('bk.AttrNames') }}",
            align: "center",
            formatter: function(b, c, a) {
                return cform.getValue(typeData['attrTag'], b)
            },
            sortable: true,
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
                return "<a class=\"btn btn-xs btn-primary\" onclick='showEditModal(" + JSON.stringify(c) + ")'>{{ __('bk.Edit') }}</a>" +
                    "<a class=\"btn btn-xs btn-danger\" onclick='delAccount(\"" + c.tag_id + "\")'>{{ __('bk.Del') }}</a>"
            }
        }]
    }

    $(function() {

        common.getAjax(apiPath + "getbasedata?requireItems=testItems,attrTag", function(a) {
            typeData = a.result
            $("#attr_id").initSelect(a.result.attrTag, "key", "value", "{{ __('bk.AttrNames') }}")
            $("#attr_id0").initSelect(a.result.attrTag, "key", "value", "{{ __('bk.AttrNames') }}")
            $("#attr_id1").initSelect(a.result.attrTag, "key", "value", "{{ __('bk.AttrNames') }}")
            $("#btnSearch").initSearch(apiPath + "configset/configTag", getColumns(), {
                sortName: "tag_id",
                sortOrder: 'desc'
            })

            $("#btnSubmit").click()
        })


        $('#updateBtnSubmit').click(function() {
            var id = $('#editModal form').find("[data-field='tag_id']").val()
            cform.patch('editModal', apiPath + 'configset/tagEdit/' + id, function(d) {
                myAlert.success(d.result)
                $('#editModal').modal('hide');
                $('#btnSearch').click()
            })
        })

        $('#createBtnSubmit').click(function() {
            cform.post('createModal', apiPath + 'configset/tagAdd')
        })

        $('#btnCreate').click(function() {
            showCreateModal()
        })
    })
</script>
@stop
