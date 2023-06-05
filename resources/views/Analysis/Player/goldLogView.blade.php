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
                                <select class="form-control" data-field="type_id" id="type_id"></select>
                                <input type="text" class="form-control" data-field="uid" placeholder="{{ __('ts.UID') }}" />
                                <input type="text" class="form-control" data-field="pid" placeholder="{{ __('ts.NO.') }}" />
                                <input type="text" class="form-control" data-field="created" style="width:320px;" placeholder="{{ __('ts.created') }}" id="reservation" />
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

</div>
@append

@section('content')
<!-- edit form -->

@stop

@section('script')
<script>
    function getColumns() {
        return [{
                field: "game_id",
                title: "{{ __('ts.Name') }}",
                align: "center",
                formatter: function(b, c, a) {
                    return cform.getValue(typeData['gameAliasType'], b)
                },
                sortable: true,
            },
            {
                field: "uid",
                title: "{{ __('ts.UID') }}",
                align: "center"
            }, {
                field: "quantity1",
                title: "{{ __('ts.Before') }}",
                align: "center",
                formatter: function(b, c, a) {
                    return common.ya(b)
                },
                sortable: true,
            }, {
                field: "quantity2",
                title: "{{ __('ts.After') }}",
                align: "center",
                formatter: function(b, c, a) {
                    return common.ya(b)
                },
                sortable: true,
            },
            {
                field: "quantity",
                title: "{{ __('ts.Count') }}",
                align: "center",
                formatter: function(b, c, a) {
                    return common.ya(b)
                },
                sortable: true,
            },
            {
                field: "type_id",
                title: "{{ __('ts.Type') }}",
                align: "center",
                formatter: function(b, c, a) {
                    return cform.getValue(typeData['reasonType'], b)
                },
            },
            {
                field: "post_time",
                title: "{{ __('ts.Created') }}",
                align: "center",
                sortable: true
            }
        ]
    }

    $(function() {

        $('#reservation').daterangepicker({
            "startDate": moment().subtract(1, 'days'),
            "endDate": moment(),
            timePicker: true,
            timePickerIncrement: 30,
            locale: {
                format: 'MM/DD/YYYY hh:mm A'
            }
        })

        common.getAjax(apiPath + "getbasedata?requireItems=reasonType,gameAliasType", function(a) {
            typeData = a.result
            $("#game_id").initSelect(a.result.gameAliasType, "key", "value", "{{ __('ts.Games') }}")
            $("#type_id").initSelect(a.result.reasonType, "key", "value", "{{ __('ts.Types') }}")
            $('#type_id').select2()
            $("#btnSearch").initSearch(apiPath + "player/goldlog", getColumns(), {
                sortName: "id",
                sortOrder: 'desc',
                showColumns: true,
                toolbar: '#toolbar',
                // showExport: true,
                // exportTypes: ['csv'],
                // exportDataType: "all"

            })
            $("#btnSubmit").click()
        })

        common.initSection(true)

    })
</script>
@stop