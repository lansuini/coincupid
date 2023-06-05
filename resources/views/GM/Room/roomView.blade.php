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
                                <input type="text" class="form-control" data-field="room_id" placeholder="{{ __('ts.RoomID') }}" />
                                <button type="button" class="btn btn-default" id="btnSearch">
                                    <i class="fas fa-search"></i>{{ __('ts.Search') }}
                                </button>

                                <button type="button" class="btn btn-primary" id="btnCreate">
                                    <i class="fas fa-plus"></i>{{ __('ts.Create') }}
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

                    <div class="form-group">
                        <label class="">{{ __('ts.Room ID') }}</label>
                        <input type="text" class="form-control" data-field="room_id" readonly/>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.Channel ID') }}</label>
                        <input type="text" class="form-control" data-field="channel_id" readonly/>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.Status') }}</label>
                        <select class="form-control status" data-field="status"></select>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.UID Mantissa') }}</label>
                        <input type="text" class="form-control" data-field="uid_tails" />
                        <span class="help-block">Multiple are separated by the English symbol ",".</span>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.Room Maximum Allowed Entry') }}</label>
                        <input type="text" class="form-control" data-field="max_num" />
                        <span class="help-block">0 or empty means no limit</span>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.Description') }}</label>
                        <textarea class="form-control" data-field="desc"></textarea>
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
                        <label class="">{{ __('ts.Room ID') }}</label>
                        <input type="text" class="form-control" data-field="room_id" />
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.Channel ID') }}</label>
                        <input type="text" class="form-control" data-field="channel_id" />
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.Status') }}</label>
                        <select class="form-control status" data-field="status"></select>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.UID Mantissa') }}</label>
                        <input type="text" class="form-control" data-field="uid_tails" />
                        <span class="help-block">Multiple are separated by the English symbol ",".</span>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.Room Maximum Allowed Entry') }}</label>
                        <input type="text" class="form-control" data-field="max_num" />
                        <span class="help-block">0 or empty means no limit</span>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.Description') }}</label>
                        <textarea class="form-control" data-field="desc"></textarea>
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


<div id="enabledToggle" class="hide" style="display: none">
    <form>
        <input type="text" class="form-control" data-field="status">
    </form>
</div>

</div>
@append

@section('content')
<!-- edit form -->

@stop

@section('script')

<script id="processInput" type="text/html">
    <div class="form-group row">
        <div class="col-sm-8">

            <input type="text" class="form-control" data-field="roomIDs" val />
        </div>
        <div class="col-md-4">
            <button class="btn btn-xs btn-danger" onclick="removeProcessItem(this)">
                remove
            </button>
        </div>
    </div>
</script>

<script>
    var typeData = []

    function showEditModal(data) {
        cform.get('editModal', apiPath + 'server/room/' + data['id'])
    }

    function enabledToggle(data) {
        $('#enabledToggle form').find("[data-field='status']").val(data['status'] == 1 ? 0 : 1)
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
                cform.patch("enabledToggle", apiPath + 'server/room/enabled/' + data['id'])
            }
        })
    }

    function showCreateModal() {
        $('#createModal').modal()
    }

    function delRow(id) {
        myConfirm.show({
            title: "{{ __('ts.confirm deletion ?') }}",
            sure_callback: function() {
                cform.del(apiPath + "server/room/" + id)
            }
        })
    }

    function getColumns() {
        return [{
                field: "id",
                title: "{{ __('ts.ID') }}",
                align: "center",
                sortable: true,
            }, {
                field: "room_id",
                title: "{{ __('ts.RoomID') }}",
                align: "center",
                sortable: true
            }, {
                field: "channel_id",
                title: "{{ __('ts.ChannelID') }}",
                align: "center",
                sortable: true,
            }, {
                field: "uid_tails",
                title: "{{ __('ts.UID Mantissa') }}",
                align: "center",
            }, {
                field: "max_num",
                title: "{{ __('ts.Room Maximum Allowed Entry') }}",
                align: "center",
            }, {
                field: "status",
                title: "{{ __('ts.Status') }}",
                align: "center",
                formatter: function(b, c, a) {
                    return cform.getValue(typeData['serverStatusType'], b)
                }
            }, {
                field: "desc",
                title: "{{ __('ts.Desc') }}",
                align: "center",
            },
            {
                field: "c_adminid",
                title: "{{ __('ts.Founder') }}",
                align: "center",
            }, 
            {
                field: "c_time",
                title: "{{ __('ts.Created') }}",
                align: "center",
            }, 
            {
                field: "last_m_adminid",
                title: "{{ __('ts.Modified by') }}",
                align: "center",
            }, 
            {

                field: "m_time",
                title: "{{ __('ts.Last') }}",
                align: "center",
            }, 
            {
                field: "-",
                title: "{{ __('ts.Action') }}",
                align: "center",
                formatter: function(b, c, a) {
                    @if($role->isPermission($request, 'room_list_enable'))
                    if (c['status'] == 0) {
                        var enBtn = "<a class=\"btn btn-xs btn-success\" onclick='enabledToggle(" + JSON.stringify(c) + ")'>{{ __('ts.enable') }}</a>"
                    } else {
                        var enBtn = "<a class=\"btn btn-xs btn-danger\" onclick='enabledToggle(" + JSON.stringify(c) + ")'>{{ __('ts.stop') }}</a>"
                    }
                    @else
                    var enBtn = ''
                    @endif
                    return enBtn +
                        "<a class=\"btn btn-xs btn-primary\" onclick='showEditModal(" + JSON.stringify(c) + ")'>{{ __('ts.Edit') }}</a>" +
                        "<a class=\"btn btn-xs btn-danger\" onclick='delRow(\"" + c.id + "\")'>{{ __('ts.Del') }}</a>"
                }
            }
        ]
    }

    function removeProcessItem(s) {
        $(s).parent().parent().remove()
    }

    $(function() {

        common.getAjax(apiPath + "getbasedata?requireItems=serverStatusType", function(a) {
            typeData = a.result
            $("#btnSearch").initSearch(apiPath + "server/room", getColumns())
            $("#btnSubmit").click()

            $(".status").initSelect(a.result.serverStatusType, "key", "value", "{{ __('ts.status') }}")
        });

        $('#updateBtnSubmit').click(function() {
            var id = $('#editModal form').find("[data-field='id']").val()
            console.log(1)
            cform.patch('editModal', apiPath + 'server/room/' + id, function(d) {
                myAlert.success(d.result)
                $('#editModal').modal('hide');
                $('#btnSearch').click()
            })
        })

        $('#createBtnSubmit').click(function() {
            cform.post('createModal', apiPath + 'server/room')
        })

        $('#btnCreate').click(function() {
            showCreateModal()
        })

        $('#addProcessItem').click(function() {
            $('#processItems').append($('#processInput').html())
        })

        $('#processBtnSubmit').click(function() {
            var id = $('#processModal form').find("[data-field='id']").val()
            cform.patch('processModal', apiPath + 'server/room/process/' + id)
        })

        $('#inventoryBtnSubmit').click(function() {
            var id = $('#inventoryModal').find("[data-field='id']").val()
            cform.patch('inventory', apiPath + 'server/room/inventory/' + id)
        })

        $('#JSONBtnSubmit').click(function() {
            var id = $('#JSONModal form').find("[data-field='id']").val()
            // console.log(common.getFields('JSONModal'))
            var data = {}
            var value = $('#JSONModal form').serializeArray()
            $.each(value, function (index, item) {
                var keys = item.name.replace(/]/g, '').split('[')

                for (var i = 0; i < keys.length;i++) {
                    var t = Number(keys[i])
                    if (!isNaN(t)) {
                        // console.log('', t)
                        keys[i] = parseInt(keys[i])
                    }
                }

                if (keys.length == 2) {
                    if (data[keys[0]] == undefined) {
                        data[keys[0]] = typeof(keys[1]) == 'number' ? [] : {}
                    }

                    data[keys[0]][keys[1]] = item.value
         
                } else if (keys.length == 3) {
                    if (data[keys[0]] == undefined) {
                        data[keys[0]] = typeof(keys[1]) == 'number' ? [] : {}
                    }

                    if (data[keys[0]][keys[1]] == undefined) {
                        data[keys[0]][keys[1]] = typeof(keys[2]) == 'number' ? [] : {}
                    }

                    data[keys[0]][keys[1]][keys[2]] = item.value

                } else if (keys.length == 4) {
                    if (data[keys[0]] == undefined) {
                        data[keys[0]] = typeof(keys[1]) == 'number' ? [] : {}
                    }

                    if (data[keys[0]][keys[1]] == undefined) {
                        data[keys[0]][keys[1]] = typeof(keys[2]) == 'number' ? [] : {}
                    }

                    if (data[keys[0]][keys[1]][keys[2]] == undefined) {
                        data[keys[0]][keys[1]][keys[2]] = typeof(keys[3]) == 'number' ? [] : {}
                    }

                    data[keys[0]][keys[1]][keys[2]][keys[3]] = item.value
                } else {
                    data[keys[0]] = item.value
                }
            })

            cform.patch2('JSONModal', apiPath + 'server/room/json/' + id, function(d) {
                if (d == 'success') {
                    myAlert.success(d)
                    $('#JSONModal').modal('hide')
                } else {
                    $('#JSONItems').html(d)

                    $('.actionButton').click(function() {
                        var act = $(this).attr('act');
                        var field = $(this).attr('field');
                        $('input[data-field="actionButton"]').val(act)
                        $('input[data-field="actionField"]').val(field)

                        console.log(act, field);
                        $('#JSONBtnSubmit').click()
                    })
                }
            }, JSON.stringify(data))
        })
    })
</script>
@stop