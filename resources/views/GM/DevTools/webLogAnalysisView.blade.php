@extends('/GM/Layout')

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    @include('/GM/navigator')
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">

                    <div classs="card">

                        <div class="card-header">
                            <div class="btn-group v-search-bar" id="divSearch">
                                <select class="form-control" data-field="nginx" id="nginx"></select>
                                <select class="form-control" data-field="nginx2" id="nginx2"></select>
                                <select class="form-control" data-field="nginxerror" id="nginxerror"></select>
                                <select class="form-control" data-field="php" id="php"></select>
                                <select class="form-control" data-field="laravel" id="laravel"></select>
                                <div class=" date" id="reservationdate" data-target-input="nearest">
                                    <input type="text" class="form-control datetimepicker-input" data-toggle="datetimepicker" data-target="#reservationdate" data-field="date" placeholder="{{ __('ts.date') }}" />
                                </div>
                                <button type="button" class="btn btn-default" id="btnSearch">
                                    <i class="fas fa-search"></i>{{ __('ts.Search') }}
                                </button>
                            </div>


                        </div>

                    </div>
                </div>
                <!-- /.col -->
            </div>
            <!-- Small boxes (Stat box) -->
            <div class="row texttips">
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="nginx_access_exception_status">0</h3>

                            <p>{{ __('ts.Nginx Access Exception Status') }} H5/Server/GM/Merchant/Analysis</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-bag"></i>
                        </div>
                        <a href="#" class="small-box-footer" style="display: none;">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-olive">
                        <div class="inner">
                            <h3 id="php_slow_query">0</h3>

                            <p>{{ __('ts.PHP slow query') }}</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-bag"></i>
                        </div>
                        <a href="#" class="small-box-footer" style="display: none;">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-navy">
                        <div class="inner">
                            <h3 id="laravel_framework_error">0</h3>

                            <p>{{ __('ts.Laravel Framework Error') }}</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars"></i>
                        </div>
                        <a href="#" class="small-box-footer" style="display: none;">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-dark">
                        <div class="inner">
                            <h3 id="nginx_error">0</h3>

                            <p>{{ __('ts.Nginx Error') }} H5/Server/GM/Merchant/Analysis</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-person-add"></i>
                        </div>
                        <a href="#" class="small-box-footer" style="display: none;">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

            </div>
            <!-- /.row -->

            <div class="row">
                <div class="col-lg-12 dtables table1">
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="card-title">{{ __('ts.Nginx Access-Log upstream_status Count') }}</h3>
                        </div>
                        <div class="card-body table-responsive p-0 tm1">
                            <!-- <table id="tabMain1" class="table table-striped table-valign-middle"></table> -->
                        </div>
                    </div>
                </div>

                <div class="col-lg-12 dtables table2">
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="card-title">{{ __('ts.Nginx Access-Log status Count') }}</h3>
                        </div>
                        <div class="card-body table-responsive p-0 tm2">
                            <table id="tabMain2" class="table table-striped table-valign-middle"></table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12 dtables table3">
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="card-title">{{ __('ts.Nginx Access-Log Path Analysis') }}</h3>
                        </div>
                        <div class="card-body table-responsive p-0 tm3">
                            <table id="tabMain3" class="table table-striped table-valign-middle"></table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12 dtables table2-1">
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="card-title">{{ __('ts.PHP Log') }}</h3>
                        </div>
                        <div class="card-body table-responsive p-0 tm2-1">
                            <table id="tabMain2-1" class="table table-striped table-valign-middle"></table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12 dtables table2-2">
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="card-title">{{ __('ts.Laravel Log') }}</h3>
                        </div>
                        <div class="card-body table-responsive p-0 tm2-2">
                            <table id="tabMain2-2" class="table table-striped table-valign-middle"></table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12 dtables table3-1">
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="card-title">{{ __('ts.Nginx Error Log') }}</h3>
                        </div>
                        <div class="card-body table-responsive p-0 tm3-1">
                            <table id="tabMain3-1" class="table table-striped table-valign-middle"></table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <p class="tips">Data Last Updated: <span id="updated"></span></p>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>

@append

@section('content')
<!-- edit form -->

@stop

@section('script')
<script>
    var count_date = ''
    var typeData = []
    var rs = []
    var H = 0
    var isToday = 0

    function getColumns1() {
        return [{
            field: "time",
            title: "{{ __('ts.Time') }}",
            align: "center",
            formatter: function(b, c, a) {
                var h = parseInt(b / 3600)
                var m = parseInt((b - (h * 3600)) / 60)
                return (h < 10 ? '0' + h : h) + ": " + (m < 10 ? '0' + m : m)
            },
            sortable: true,
        }, {
            field: "total",
            title: "{{ __('ts.Total') }}",
            align: "center",
            sortable: true,
        }]
    }

    function getColumns2() {
        return [{
            field: "m",
            title: "M",
            align: "center",
            formatter: function(b, c, a) {
                return b.length > 10 ? b.substring(0, 10) + '...' : b;
            }
        }, {
            field: "p",
            title: "P",
            align: "center",
            sortable: true,
            formatter: function(b, c, a) {
                return b.length > 50 ? b.substring(0, 50) + '...' : b;
            }
        }, {
            field: "c",
            title: "RC",
            align: "center",
            sortable: true,
        }, {
            field: "avg_rt",
            title: "AVG RT",
            align: "center",
            sortable: true,
        }, {
            field: "avg_urt",
            title: "AVG URT",
            align: "center",
            sortable: true,
        }, {
            field: "m_t_500_rt_c",
            title: "RT500C",
            align: "center",
            sortable: true,
        }, {
            field: "m_t_500_urt_c",
            title: "URT500C",
            align: "center",
            sortable: true,
        }, {
            field: "us",
            title: "US",
            align: "center",
            formatter: function(b, c, a) {
                var str = ''
                for (var i in b) {
                    str += i + ':' + b[i] + "<br>"
                }
                return str
            }
        }, {
            field: "ss",
            title: "SS",
            align: "center",
            formatter: function(b, c, a) {
                var str = ''
                for (var i in b) {
                    str += i + ':' + b[i] + "<br>"
                }
                return str
            }
        }]
    }

    function getStruct1(data) {
        var arr = []
        var arr24 = []
        var struct = {
            'time': 0,
            "total": 0
        }
        var columns = getColumns1()
        for (var v in data['total']) {
            struct[v] = 0
            columns.push({
                field: v,
                title: "Code:" + v,
                align: "center",
                sortable: true,
            })
        }

        for (var i = 0; i < 24; i++) {

            if (isToday && i > H) {
                continue
            }

            var newStruct = {}
            for (var n in struct) {
                newStruct[n] = struct[n]
            }
            newStruct['time'] = i * 3600
            newStruct['total'] = 0
            arr24.push(newStruct)
        }

        for (var v in data['hours']) {
            var newStruct = {}
            for (var n in struct) {
                newStruct[n] = struct[n]
            }

            var key24 = parseInt(v / 3600)
            if (isToday && key24 > H) {
                continue
            }

            newStruct['time'] = v
            newStruct['total'] = 0
            for (var code in data['hours'][v]) {
                newStruct[code] = data['hours'][v][code]
                newStruct['total'] += newStruct[code]

                arr24[key24]['total'] += newStruct[code]
                arr24[key24][code] += newStruct[code]
            }
            arr.push(newStruct)
        }
        return [columns, arr, arr24]
    }

    function getStruct2(data) {
        var arr = []
        var arr24 = []
        var struct = {
            'time': 0,
            "total": 0
        }
        var columns = getColumns1()
        for (var i = 0; i < 24; i++) {

            if (isToday && i > H) {
                continue
            }

            var newStruct = {}
            for (var n in struct) {
                newStruct[n] = struct[n]
            }
            newStruct['time'] = i * 3600
            newStruct['total'] = 0
            arr24.push(newStruct)
        }

        for (var v in data['hours']) {
            var newStruct = {}
            for (var n in struct) {
                newStruct[n] = struct[n]
            }

            var key24 = parseInt(v / 3600)

            if (isToday && key24 > H) {
                continue
            }

            newStruct['time'] = v
            newStruct['total'] = data['hours'][v]
            arr24[key24]['total'] += data['hours'][v]
            arr.push(newStruct)
        }
        return [columns, arr, arr24]
    }

    function getStruct3(data) {
        var arr2 = []
        for (var v in data) {
            arr2.push(data[v])
        }
        return [arr2]
    }

    function getHtmlCount1() {
        var H5 = 0
        // console.log(rs['nginx'])
        for (var i in rs['nginx']['H5']['struct1']['total']) {
            if (i != 200 && i != 302) {
                H5 += rs['nginx']['H5']['struct1']['total'][i]
            }

        }

        var Server = 0
        for (var i in rs['nginx']['Server']['struct1']['total']) {
            if (i != 200 && i != 302) {
                Server += rs['nginx']['Server']['struct1']['total'][i]
            }
        }

        var GM = 0
        for (var i in rs['nginx']['GM']['struct1']['total']) {
            if (i != 200 && i != 302) {
                GM += rs['nginx']['GM']['struct1']['total'][i]
            }
        }

        var Analysis = 0
        for (var i in rs['nginx']['Analysis']['struct1']['total']) {
            if (i != 200 && i != 302) {
                Analysis += rs['nginx']['Analysis']['struct1']['total'][i]
            }
        }

        var Merchant = 0
        for (var i in rs['nginx']['Merchant']['struct1']['total']) {
            if (i != 200 && i != 302) {
                Merchant += rs['nginx']['Merchant']['struct1']['total'][i]
            }
        }

        $('#nginx_access_exception_status').html(H5 + '/' + Server + '/' + GM + '/' +
            Merchant + '/' + Analysis
        )
    }

    function getHtmlCount2() {
        $('#php_slow_query').html(rs['php']['php']['struct1']['total']['WARNING'])
    }

    function getHtmlCount3() {
        $('#laravel_framework_error').html(rs['laravel']['laravel']['struct1']['total']['ERROR'])
    }

    function getHtmlCount4() {
        var H5 = rs['nginxerror']['H5']['struct1']['total']
        var Server = rs['nginxerror']['Server']['struct1']['total']
        var GM = rs['nginxerror']['GM']['struct1']['total']
        var Analysis = rs['nginxerror']['Analysis']['struct1']['total']
        var Merchant = rs['nginxerror']['Merchant']['struct1']['total']
        $('#nginx_error').html(H5 + '/' + Server + '/' + GM + '/' +
            Merchant + '/' + Analysis
        )
    }
    $(function() {

        function showTables(values) {
            var name = values['nginx']
            var struct = values['nginx2']
            var php = values['php']
            var laravel = values['laravel']
            var nginxerror = values['nginxerror']
            if (name !== undefined && struct != undefined) {
                if (struct == 'struct1' || struct == 'ALL') {
                    var d = getStruct1(rs['nginx'][name]['struct1'])
                    $('.tm1').html('<table id="tabMain1" class="table table-striped table-valign-middle"></table>')
                    $('#tabMain1').bootstrapTable({
                        columns: d[0],
                        data: d[2],
                        sortName: 'time',
                        sortOrder: 'desc',
                    })
                    $('.table1').show()
                }

                if (struct == 'struct2' || struct == 'ALL') {
                    var d = getStruct1(rs['nginx'][name]['struct2'])
                    $('.tm2').html('<table id="tabMain2" class="table table-striped table-valign-middle"></table>')
                    $('#tabMain2').bootstrapTable({
                        columns: d[0],
                        data: d[2],
                        sortName: 'time',
                        sortOrder: 'desc',
                    })
                    $('.table2').show()
                }

                if (struct == 'struct3' || struct == 'ALL') {
                    var d = getStruct3(rs['nginx'][name]['struct3'])
                    $('.tm3').html('<table id="tabMain3" class="table table-striped table-valign-middle"></table>')
                    $('#tabMain3').bootstrapTable({
                        columns: getColumns2(),
                        data: d[0],
                        sortName: 'c',
                        sortOrder: 'desc',
                    })
                    $('.table3').show()
                }
            }

            if (php != undefined && php == 'Show') {
                var d = getStruct1(rs['php']['php']['struct1'])
                $('#tabMain2-1').bootstrapTable({
                    columns: d[0],
                    data: d[2],
                    sortName: 'time',
                    sortOrder: 'desc',
                })
                $('.table2-1').show()
            }

            if (laravel != undefined && laravel == 'Show') {
                var d = getStruct1(rs['laravel']['laravel']['struct1'])
                $('#tabMain2-2').bootstrapTable({
                    columns: d[0],
                    data: d[2],
                    sortName: 'time',
                    sortOrder: 'desc',
                })
                // console.log(1111)
                $('.table2-2').show()
            }

            if (nginxerror != undefined) {
                var d = getStruct2(rs['nginxerror'][nginxerror]['struct1'])
                $('.tm3-1').html('<table id="tabMain3-1" class="table table-striped table-valign-middle"></table>')
                $('#tabMain3-1').bootstrapTable({
                    columns: d[0],
                    data: d[2],
                    sortName: 'time',
                    sortOrder: 'desc',
                })
                $('.table3-1').show()
            }

            if (name == undefined && php == undefined && laravel == undefined && nginxerror == undefined) {
                $('.texttips').show()
            } else {
                $('.texttips').hide()
            }
        }

        $('#btnSearch').click(function() {
            $('.dtables').hide()
            var values = common.getFields("divSearch")
            if (count_date == '' || count_date != values['date']) {
                count_date = values['date']
                common.getAjax(apiPath + "devtools/webloganalysis?count_date=" + values['date'], function(a) {
                    rs = a['result']
                    H = a['H']
                    isToday = a['isToday']
                    $('#updated').html(a['updated'])
                    if (rs != null) {
                        showTables(values)

                        getHtmlCount1()
                        getHtmlCount2()
                        getHtmlCount3()
                        getHtmlCount4()
                    } else {
                        $('.texttips').hide()
                    }

                })
            } else {
                if (rs != null) {
                    showTables(values)
                }
            }
            // console.log('click')
        })

        $('#reservationdate').datetimepicker({
            format: 'L',
            defaultDate: moment()
        })

        common.getAjax(apiPath + "getbasedata?requireItems=nginx1Type,nginx2Type,nginx3Type", function(a) {
            typeData = a.result

            $("#nginx").initSelect(typeData.nginx1Type, "key", "value", "{{ __('Nginx Access Log') }}")
            $("#nginx2").initSelect(typeData.nginx2Type, "key", "value", "{{ __('Nginx Access Log Type') }}")
            $("#nginxerror").initSelect(typeData.nginx1Type, "key", "value", "{{ __('Nginx Error Log') }}")
            $("#laravel").initSelect(typeData.nginx3Type, "key", "value", "{{ __('Laravel Log') }}")
            $("#php").initSelect(typeData.nginx3Type, "key", "value", "{{ __('PHP Log') }}")

            $('#btnSearch').click()
        })

    })
</script>
@stop