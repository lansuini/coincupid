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
                                <input type="text" class="form-control" data-field="gameid" placeholder="{{ __('ts.GAMEID') }}" />
                                <input type="text" class="form-control" data-field="name" placeholder="{{ __('ts.name') }}" value=""/>
                                <button type="button" class="btn btn-default" id="btnSearch">
                                    <i class="fas fa-search"></i>{{ __('ts.Search') }}
                                </button>

                                <button type="button" class="btn btn-info" id="btnPushConfig">
                                    <i class="fa-solid fa-arrows-rotate"></i> PushConfig
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
                        <label class="">{{ __('ts.Name') }}</label>
                        <input type="text" class="form-control" data-field="name" />
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.Game') }}</label>
                        <select class="form-control gameid" data-field="gameid"></select>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.Enabled') }}</label>
                        <select class="form-control enabled" data-field="enabled" readonly></select>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.Bottom') }}</label>
                        <input type="text" class="form-control" data-field="bottom" />
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.MinGold') }}</label>
                        <input type="text" class="form-control" data-field="mingold" />
                        <span class="help-block">zero means no limit</span>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.MaxGold') }}</label>
                        <input type="text" class="form-control" data-field="maxgold" />
                        <span class="help-block">zero means infinite</span>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.No.') }}</label>
                        <input type="text" class="form-control" data-field="sortid" />
                        <span class="help-block">Range 1-999. Unique. Order from smallest to largest</span>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.HotRegister') }}</label>
                        <input type="text" class="form-control" data-field="hot_register" />
                        <span class="help-block">number</span>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.PictureName') }}</label>
                        <input type="text" class="form-control" data-field="pic_name" />
                        <span class="help-block"></span>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.JSON Config') }}</label>
                        <textarea class="form-control"readonly data-field="xmlgame" rows="12" wrap="hard" cols="78"></textarea>
                        <span class="help-block"></span>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.isExperience') }}</label>
                        <select class="form-control tiyan" data-field="tiyan" /></select>
                        <span class="help-block"></span>
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
                        <label class="">{{ __('ts.Name') }}</label>
                        <input type="text" class="form-control" data-field="name" />
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.GameID') }}</label>
                        <select class="form-control gameid" data-field="gameid"></select>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.Enabled') }}</label>
                        <select class="form-control enabled" data-field="enabled"></select>
                    </div>



                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.Bottom') }}</label>
                        <input type="text" class="form-control" data-field="bottom" />
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.MinGold') }}</label>
                        <input type="text" class="form-control" data-field="mingold" />
                        <span class="help-block">zero means no limit</span>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.MaxGold') }}</label>
                        <input type="text" class="form-control" data-field="maxgold" />
                        <span class="help-block">zero means infinite</span>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.No.') }}</label>
                        <input type="text" class="form-control" data-field="sortid" />
                        <span class="help-block">Range 1-999. Unique. Order from smallest to largest</span>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.HotRegister') }}</label>
                        <input type="text" class="form-control" data-field="hot_register" />
                        <span class="help-block">number</span>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.PictureName') }}</label>
                        <input type="text" class="form-control" data-field="pic_name" />
                        <span class="help-block"></span>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.JSON Config') }}</label>
                        <textarea class="form-control" data-field="xmlgame" rows="12" wrap="hard" cols="78"></textarea>
                        <span class="help-block"></span>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.isExperience') }}</label>
                        <select class="form-control tiyan" data-field="tiyan" /></select>
                        <span class="help-block"></span>
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

<div class="modal fade" id="processModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('ts.Process Edit') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>

            <div class="modal-body">
                <form class="">
                    <div class="form-group sr-only">
                        <label class="col-form-label">{{ __('ts.id') }}</label>
                        <input type="text" class="form-control" data-field="id" />
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-8">
                            <label class="">{{ __('ts.ProcessID') }}</label>
                        </div>

                    </div>
                    <div id="processItems"></div>

                    <div class="form-group">
                        <button type="button" class="" id="addProcessItem">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>

                </form>
            </div>

            <div class="modal-footer">
                <input type="button" class="btn btn-default" value="{{ __('ts.Close') }}" data-dismiss="modal" />
                <input type="button" class="btn btn-primary" value="{{ __('ts.Submit') }}" id="processBtnSubmit" />
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="inventoryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('ts.Inventory') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>

            <div class="modal-body">
                <form class="">

                    <div class="form-group sr-only">
                        <label class="col-form-label">{{ __('ts.id') }}</label>
                        <input type="text" class="form-control" data-field="id" />
                    </div>

                    <div class="form-group">
                        <label class="">{{ __('ts.Award-sending probability (10,000)') }}</label>
                        <input type="text" class="form-control" data-field="tax" />
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.Kill alert line') }}</label>
                        <input type="text" class="form-control" data-field="actual_tax" />
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.Tempool extraction rate (10,000)') }}</label>
                        <input type="text" class="form-control" data-field="pool_extract" />
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.Real-time prize pool') }}</label>
                        <input type="text" class="form-control" data-field="actual_pool" />
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.Player win list rate (10,000)') }}</label>
                        <input type="text" class="form-control" data-field="pool_rate" />
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.Minimum trigger value') }}</label>
                        <input type="text" class="form-control" data-field="min" />
                        <span class="help-block"></span>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.Award pool factor k') }}</label>
                        <input type="text" class="form-control" data-field="actual_num" />
                        <span class="help-block"></span>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.Real-time stock') }}</label>
                        <input type="text" class="form-control" data-field="actual_stock" />
                        <span class="help-block"></span>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.Inventory lower limit') }}</label>
                        <input type="text" class="form-control" data-field="stock_min" />
                        <span class="help-block"></span>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.Inventory cap') }}</label>
                        <input type="text" class="form-control" data-field="stock_max" />
                        <span class="help-block"></span>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.Inventory conversion rate (%)') }}</label>
                        <input type="text" class="form-control" data-field="stock_switch" />
                        <span class="help-block"></span>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">{{ __('ts.The color pool trigger maximum (10,000)') }}</label>
                        <input type="text" class="form-control" data-field="pool_max_lose" />
                        <span class="help-block"></span>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <input type="button" class="btn btn-default" value="{{ __('ts.Close') }}" data-dismiss="modal" />
                <input type="button" class="btn btn-primary" value="{{ __('ts.Submit') }}" id="inventoryBtnSubmit" />
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="JSONModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('ts.JSON Edit') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>

            <div class="modal-body">
                <form class="">
                    <div class="form-group sr-only">
                        <label class="col-form-label">{{ __('ts.id') }}</label>
                        <input type="text" class="form-control" data-field="id" />
                    </div>
                    <div id="JSONItems"></div>
                </form>
            </div>

            <div class="modal-footer">
                <input type="button" class="btn btn-default" value="{{ __('ts.Close') }}" data-dismiss="modal" />
                <input type="button" class="btn btn-primary" value="{{ __('ts.Submit') }}" id="JSONBtnSubmit" />
            </div>
        </div>
    </div>
</div>

<div id="enabledToggle" class="hide" style="display: none">
    <form>
        <input type="text" class="form-control" data-field="enabled">
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
        cform.get('editModal', apiPath + 'server/game/room/' + data['id'])
    }

    function showProcessModal(data) {
        $('#processModal form').find("[data-field='id']").val(data['id'])
        common.getAjax(apiPath + "server/game/room/process/" + data['id'], function(a) {

            var html = ''
            for (var i = 0; i < a.result.length; i++) {
                var v1 = $('#processInput').clone()
                v1.find('input').val(a.result[i]['roomid'])

                html += v1.html().replace(/val/g, "value='" + a.result[i]['roomid'] + "'")
            }
            $('#processItems').html(html)
            $('#processModal').modal()
        })
    }

    function showInventoryModal(data) {
        $('#inventoryModal').find("[data-field='id']").val(data['id'])
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
                // cform.post("I000", apiPath + 'server/game/room/pushconfig')
                common.getAjax(apiPath + "server/game/room/inventory/" + data['id'], function(a) {
                    $('#inventoryModal').modal()
                    Swal.close()
                })
            }
        })
    }

    function enabledToggle(data) {
        $('#enabledToggle form').find("[data-field='enabled']").val(data['enabled'] == 1 ? 0 : 1)
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
                cform.patch("enabledToggle", apiPath + 'server/game/room/enabled/' + data['id'])
            }
        })
    }

    function showCreateModal() {
        $('#createModal').modal()
    }

    function showJSONModal(data) {
        $('#JSONModal form').find("[data-field='id']").val(data['id'])
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
                // cform.patch("enabledToggle", apiPath + 'server/game/room/enabled/' + data['id'])
                $.ajax({
                    url: apiPath + 'server/game/room/json/' + data['id'],
                    type: "get",
                    success: function(d) {
                        $('#JSONItems').html(d)

                        $('.actionButton').click(function() {
                            var act = $(this).attr('act');
                            var field = $(this).attr('field');
                            $('input[data-field="actionButton"]').val(act)
                            $('input[data-field="actionField"]').val(field)

                            console.log(act, field);
                            $('#JSONBtnSubmit').click()
                        })

                        Swal.close()
                        $('#JSONModal').modal()
                    }
                })
            }
        })
    }

    function delRow(id) {
        myConfirm.show({
            title: "{{ __('ts.confirm deletion ?') }}",
            sure_callback: function() {
                cform.del(apiPath + "server/game/room/" + id, function(d) {
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
                sortable: true,
            }, {
                field: "gameid",
                title: "{{ __('ts.GAME') }}",
                align: "center",
                sortable: true,
                formatter: function(b, c, a) {
                    return cform.getValue(typeData['playType'], b) + '(' + b + ')'
                }
            }, {
                field: "name",
                title: "{{ __('ts.Name') }}",
                align: "center",
                sortable: true,
            }, {
                field: "sortid",
                title: "{{ __('ts.No.') }}",
                align: "center",
            }, {
                field: "enabled",
                title: "{{ __('ts.Enabled') }}",
                align: "center",
                sortable: true,
                formatter: function(b, c, a) {
                    return cform.getValue(typeData['enabledType'], b)
                }
            },
            {
                field: "bottom",
                title: "{{ __('ts.Bottom') }}",
                align: "center",
            }, 
            {
                field: "mingold",
                title: "{{ __('ts.MinGold') }}",
                align: "center",
            }, 
            {

                field: "maxgold",
                title: "{{ __('ts.MaxGold') }}",
                align: "center",
            }, 
            // {
            //     field: "hot_register",
            //     title: "{{ __('ts.PopularityRanking') }}",
            //     align: "center",
            // }, 
            // {
            //     field: "pic_name",
            //     title: "{{ __('ts.PictureName') }}",
            //     align: "center",
            // }, 
            {
                field: "tiyan",
                title: "{{ __('ts.isExperience') }}",
                align: "center",
                sortable: true,
                formatter: function(b, c, a) {
                    return cform.getValue(typeData['experienceType'], b)
                }
            },
            {
                field: "-",
                title: "{{ __('ts.Action') }}",
                align: "center",
                formatter: function(b, c, a) {
                    @if($role->isPermission($request, 'game_room_list_enable'))
                    if (c['enabled'] == 1) {
                        var enBtn = "<a class=\"btn btn-xs btn-success\" onclick='enabledToggle(" + JSON.stringify(c) + ")'>{{ __('ts.enable') }}</a>"
                    } else {
                        var enBtn = "<a class=\"btn btn-xs btn-danger\" onclick='enabledToggle(" + JSON.stringify(c) + ")'>{{ __('ts.stop') }}</a>"
                    }
                    @else
                    var enBtn = ''
                    @endif
                    return "<a class=\"btn btn-xs btn-warning\" onclick='showProcessModal(" + JSON.stringify(c) + ")'>{{ __('ts.Process') }}</a>" +
                        "<a class=\"btn btn-xs btn-info\" onclick='showInventoryModal(" + JSON.stringify(c) + ")'>{{ __('ts.Inventory') }}</a>" +
                        enBtn +
                        "<a class=\"btn btn-xs btn-primary\" onclick='showEditModal(" + JSON.stringify(c) + ")'>{{ __('ts.Edit') }}</a>" +
                        "<a class=\"btn btn-xs btn-info\" onclick='showJSONModal(" + JSON.stringify(c) + ")'>{{ __('ts.JSON') }}</a>" +
                        "<a class=\"btn btn-xs btn-danger\" onclick='delRow(\"" + c.id + "\")'>{{ __('ts.Del') }}</a>"
                }
            }
        ]
    }

    function removeProcessItem(s) {
        $(s).parent().parent().remove()
    }

    $(function() {

        common.getAjax(apiPath + "getbasedata?requireItems=enabledType,experienceType,playType", function(a) {
            typeData = a.result
            $("#btnSearch").initSearch(apiPath + "server/game/room", getColumns())
            $("#btnSubmit").click()

            $(".enabled").initSelect(a.result.enabledType, "key", "value", "{{ __('ts.enabled') }}")
            $(".tiyan").initSelect(a.result.experienceType, "key", "value", "{{ __('ts.experience') }}")

            $(".gameid").initSelect(a.result.playType, "key", "value", "{{ __('ts.play') }}")

            common.initSection(true)
        });

        $('#updateBtnSubmit').click(function() {
            var id = $('#editModal form').find("[data-field='id']").val()
            console.log(1)
            cform.patch('editModal', apiPath + 'server/game/room/' + id, function(d) {
                myAlert.success(d.result)
                $('#editModal').modal('hide');
                $('#btnSearch').click()
            })
        })

        $('#createBtnSubmit').click(function() {
            cform.post('createModal', apiPath + 'server/game/room')
        })

        $('#btnPushConfig').click(function() {
            Swal.fire({
                title: 'Loading...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                    cform.post("I000", apiPath + 'server/game/room/pushconfig')
                }
            })
        })

        $('#btnCreate').click(function() {
            showCreateModal()
        })

        $('#addProcessItem').click(function() {
            $('#processItems').append($('#processInput').html())
        })

        $('#processBtnSubmit').click(function() {
            var id = $('#processModal form').find("[data-field='id']").val()
            cform.patch('processModal', apiPath + 'server/game/room/process/' + id)
        })

        $('#inventoryBtnSubmit').click(function() {
            var id = $('#inventoryModal').find("[data-field='id']").val()
            cform.patch('inventory', apiPath + 'server/game/room/inventory/' + id)
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

            cform.patch2('JSONModal', apiPath + 'server/game/room/json/' + id, function(d) {
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