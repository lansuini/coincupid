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
                            <div id="divSearch" style="display: none;">
                                <div class="btn-group">
                                </div>

                                <div class="btn-group ">

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
    var typeData = []

    function getColumns() {
        return [{
            field: "file_name",
            title: "{{ __('ts.FileName') }}",
            align: "center",
            sortable: true,
        }, {
            field: "updated",
            title: "{{ __('ts.Updated') }}",
            align: "center"
        }, {
            field: "-",
            title: "{{ __('ts.Action') }}",
            align: "center",
            formatter: function(b, c, a) {
                return "<a class=\"btn btn-xs btn-primary\" target='_blank' href=" + c['download'] + ">{{ __('ts.Download') }}</a>"
            }
        }]
    }

    $(function() {


        common.getAjax(apiPath + "getbasedata?requireItems=testItems", function(a) {
            typeData = a.result
            $("#btnSearch").initSearch(apiPath + "support/apidocument", getColumns())
            $("#btnSubmit").click()


        })

    })
</script>
@stop