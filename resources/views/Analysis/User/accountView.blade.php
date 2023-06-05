@extends('/GM/Layout')

@section('content')
<!-- Content Wrapper. Contains page content -->
{{--<link rel="stylesheet" href="/adminlte/cityselect/css/main.css">--}}


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
                                    <select class="form-control" id="accountType" data-field="accountType"></select>
                                    <select class="form-control" id="riskUserType" data-field="riskUserType"></select>
                                    <select class="form-control" id="accountSex" data-field="accountSex"></select>
                                    <select class="form-control" id="userIntention" data-field="userIntention"></select>
                                    <select class="form-control" id="attrTag" data-field="attrTag"></select>

                                </div>

                                <div class="btn-group v-search-bar">
                                    {{--<select class="form-control" data-field="client_id" id="client_id"></select>--}}
                                    <select class="form-control" id="t2" data-field="t2"></select>
                                    <input type="text" class="form-control" style="width:190px;" data-field="created" placeholder="{{ __('ts.created') }}" id="reservation" />
                                    <button type="button" class="btn btn-default" id="btnSearch">
                                        <i class="fas fa-search"></i>{{ __('ts.Search') }}
                                    </button>
                                    <button type="button" class="btn btn-primary" id="btnCreate">
                                        <i class="fas fa-plus"></i>{{ __('bk.Create') }}
                                    </button>
                                </div>

                                <div id="toolbar" class="select">
                                    <!-- <select class="form-control">
                                    <option value="">Export Basic</option>
                                    <option value="all">Export All</option>
                                    <option value="selected">Export Selected</option>
                                </select> -->
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
                    <form class="form-horizontal">
                        <div class="form-group sr-only">
                            <label class="col-form-label">{{ __('ts.UID') }}</label>
                            <input type="text" class="form-control" data-field="uid" />
                        </div>

                        <div class="modal-body">
                            <form class="">
                                <div class="form-group">
                                    <label class="col-form-label">{{ __('ts.Avatar') }}</label>
                                        <img style="width: 15%;height: 15%" src="" id="avatar">
                                        <input type="hidden" class="form-control" data-field="avatar" />
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">{{ __('ts.Username') }}</label>
                                    <input type="text" class="form-control" data-field="username" readonly/>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">{{ __('ts.Nickname') }}</label>
                                    <input type="text" class="form-control" data-field="nickname" />
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">{{ __('ts.MobileNumber') }}</label>
                                    <input type="text" class="form-control" data-field="mobile_number" readonly/>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">{{ __('bk.Sex') }}</label>
                                    <select class="form-control" id="accountSex1" data-field="sex" disabled></select>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">{{ __('bk.Educational') }}</label>
                                    <select class="form-control" id="educational" data-field="educational"  ></select>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">{{ __('bk.Income') }}</label>
                                    <select class="form-control" id="income" data-field="income"  ></select>
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label">{{ __('bk.Birthday') }}</label>
                                    <input type="text" class="form-control" data-field="birthday" readonly/>
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label">{{ __('bk.Bio') }}</label>
{{--                                    <input type="text" class="form-control" data-field="bio" />--}}
                                    <textarea class="form-control" rows="3"  data-field="bio"></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">{{ __('bk.UserIntention') }}</label>
{{--                                    <select class="form-control" id="userIntention2" data-field="userIntention"></select>--}}
                                    <div class="checkbox" id="intentionCheck"></div>
                                    <input type="hidden" id="intention" class="form-control" data-field="intention" />
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label">{{ __('ts.Weight') }}</label>
                                    <input type="number" class="form-control" data-field="weight" />KG
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">{{ __('ts.Height') }}</label>
                                    <input type="number" class="form-control" data-field="height" />
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label" >{{ __('bk.Measurements') }}</label >
                                    <input type="hidden" id="measurements" class="form-control" data-field="measurements" />
                                    <div  class="row" id="appendHere">
                                        <input type="number" class="form-control measurement" />胸围
                                        <input type="number" class="form-control measurement" />腰围
                                        <input type="number" class="form-control measurement" />臀围
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">{{ __('ts.Occupation') }}</label>
{{--                                    <select class="form-control" id="occupationType" data-field="occupation"></select>--}}
                                    <div id="occupation">
                                        <select class="form-control prov"  data-field="occupation_cate"></select>
                                        <select class="form-control city"  data-field="occupation_post" disabled="disabled"></select>
{{--                                        <select class="dist" disabled="disabled"></select>--}}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label">{{ __('ts.Portrait1') }}</label>
                                    <img style="width: 15%;height: 15%" src="" id="portrait1">
                                    <input type="hidden" class="form-control" data-field="portrait1"  />
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label">{{ __('ts.VerifyVideo') }}</label>
                                    <img style="width: 15%;height: 15%" src="" id="verify_video">
                                    <input type="hidden" class="form-control" data-field="verify_video" />
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
                            <label class="col-form-label">{{ __('ts.Username') }}</label>
                            <input type="text" class="form-control" data-field="username"  />
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">{{ __('ts.Nickname') }}</label>
                            <input type="text" class="form-control" data-field="nickname" />
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">{{ __('ts.MobileNumber') }}</label>
                            <input type="text" class="form-control" data-field="mobile_number"  />
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">{{ __('bk.Sex') }}</label>
                            <select class="form-control" id="accountSex2" data-field="sex"  ></select>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">{{ __('bk.Educational') }}</label>
                            <select class="form-control" id="educational2" data-field="educational"  ></select>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">{{ __('bk.Income') }}</label>
                            <select class="form-control" id="income2" data-field="income"  ></select>
                        </div>

                        <div class="form-group">
                            <label class="col-form-label">{{ __('bk.Birthday') }}</label>
                            <input type="text" class="form-control" data-field="birthday"  />
                        </div>

                        <div class="form-group">
                            <label class="col-form-label">{{ __('bk.Bio') }}</label>
                            {{--                                    <input type="text" class="form-control" data-field="bio" />--}}
                            <textarea class="form-control" rows="3"  data-field="bio"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">{{ __('bk.UserIntention') }}</label>
                            {{--                                    <select class="form-control" id="userIntention2" data-field="userIntention"></select>--}}
                            <div class="checkbox" id="intentionCheck2"></div>
                            <input type="hidden" id="intention2" class="form-control" data-field="intention" />
                        </div>

                        <div class="form-group">
                            <label class="col-form-label">{{ __('ts.Weight') }}</label>
                            <input type="number" class="form-control" data-field="weight" />KG
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">{{ __('ts.Height') }}</label>
                            <input type="number" class="form-control" data-field="height" />CM
                        </div>

                        <div class="form-group">
                            <label class="col-form-label" >{{ __('bk.Measurements') }}</label >
                            <input type="hidden" id="measurements2" class="form-control" data-field="measurements" />
                            <div  class="row" >
                                <input type="number" class="form-control measurement" />胸围
                                <input type="number" class="form-control measurement" />腰围
                                <input type="number" class="form-control measurement" />臀围
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">{{ __('ts.Occupation') }}</label>
                            {{--                                    <select class="form-control" id="occupationType" data-field="occupation"></select>--}}
                            <div id="occupation2">
                                <select class="form-control prov"  data-field="occupation_cate"></select>
                                <select class="form-control city"  data-field="occupation_post" disabled="disabled"></select>
                                {{--                                        <select class="dist" disabled="disabled"></select>--}}
                            </div>
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

    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('bk.EditUserStatus') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                <div class="modal-body">
                    <form class="">
                        <div class="form-group sr-only">
                            <label class="col-form-label">{{ __('ts.UID') }}</label>
                            <input type="text" class="form-control" data-field="uid" />
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">{{ __('bk.is_lock') }}</label>
                            <select class="form-control" id="isLock" data-field="is_lock"  ></select>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <input type="button" class="btn btn-default" value="{{ __('bk.Close') }}" data-dismiss="modal" />
                    <input type="button" class="btn btn-primary" value="{{ __('bk.Submit') }}" id="statusBtnSubmit" />
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
{{--    <script src="/adminlte/cityselect/js/jquery.js"></script>--}}
    <script src="/adminlte/cityselect/js/jquery.cityselect.js"></script>
<script>
    var typeData = []

    function showEditModal(data) {
        cform.get('editModal', apiPath + 'user/accountdetail/' + data['uid'],function (res){
            // console.log(res)
            $("#verify_video").attr("src",res.data.verify_video)
            $("#portrait1").attr("src",res.data.portrait1)
            $("#avatar").attr("src",res.data.avatar)

            var str=[]
            var s=res.data.measurements

            var lab=["胸围","腰围","臀围"];
            var htmlStr=''
            if(s!="" && s!=null && s.length>0){
                str=s.split(",")
                for(let key in str){
                    htmlStr+='<input type="number" class="form-control  measurement" value="'+str[key]+'"/>'+lab[key]
                }
            }
            $("#appendHere").html(htmlStr)
            $("#occupation").citySelect({
                data:{"citylist":res.tree},
                prov:res.data.occupation_cate,
                city:res.data.occupation_post,
                dist:"",
                nodata:"none"
            });
            var arrIntention=res.data.intention
            if(arrIntention!="" && arrIntention!=null && arrIntention.length>0){
                arrIntention=arrIntention.split(',')
                var checked=document.getElementsByName('userIntention');
                for (i=0;i<checked.length;i++){
                    if(arrIntention.indexOf(checked[i].value)!=-1){
                        checked[i].checked=true
                        continue
                    }
                }
            }

        })
    }

    //编辑用户状态
    function showStatusEditModal(data){
        $('#statusModal form').find("[data-field='uid']").val(data.uid)
        $('#statusModal form').find("[data-field='is_lock']").val(data.is_lock)
        $("#statusModal").modal()
    }

    function getColumns() {
        return [{
            field: "avatar",
            title: "{{ __('bk.Avatar') }}",
            align: "center",
            formatter:function (b,c,a){
                return "<img style='height: 35px;width: 35px;border-radius: 50%;line-height: 50px!important;' src='"+b+"'>"
            }
        },{
            field: "uid",
            title: "{{ __('ts.UID') }}",
            align: "center",
            sortable: true,
        }, {
            field: "sex",
            title: "{{ __('bk.Sex') }}",
            align: "center",
            formatter: function(b, c, a) {
                return cform.getValue(typeData['accountSex'], b)
            }
        }, {
            field: "intention",
            title: "{{ __('bk.UserIntention') }}",
            align: "center",
            formatter: function(b, c, a) {
                var str=''
                if(b!=null){
                    var arr=[]
                    arr=b.split(',')
                    for(let key in arr){
                        for(let key2 in typeData['userIntention']){
                            if(arr[key]==typeData['userIntention'][key2]['key']){
                                str+="<div style='height:auto;width:auto;background-color:#8FBCBB ;color: white;margin: 5px'>"+typeData['userIntention'][key2]['value']+"</div>"
                            }
                        }

                    }
                }
                return str
            }
        }, {
            field: "username",
            title: "{{ __('bk.UserName') }}",
            align: "center"
        }, {
            field: "nickname",
            title: "{{ __('ts.Nickname') }}",
            align: "center"
        }, {
            field: "mobile_number",
            title: "{{ __('bk.MobileNumber') }}",
            align: "center"
        }, {
            field: "facebook",
            title: "{{ __('bk.Facebook') }}",
            align: "center"
        }, {
            field: "instagram",
            title: "{{ __('bk.Instagram') }}",
            align: "center"
        }, {
            field: "twitter",
            title: "{{ __('bk.Twitter') }}",
            align: "center"
        }, {
            field: "line",
            title: "{{ __('bk.Line') }}",
            align: "center"
        }, {
            field: "telegram",
            title: "{{ __('bk.Telegram') }}",
            align: "center"
        }, {
            field: "account_type",
            title: "{{ __('ts.AccountType') }}",
            align: "center",
            formatter: function(b, c, a) {
                return cform.getValue(typeData['accountType'], b)
            }
        },{
            field: "is_lock",
            title: "{{ __('bk.is_lock') }}",
            align: "center",
            formatter: function(b, c, a) {
                if(b == 0){
                    return "{{ __('bk.Unlock') }}"
                }else if(b == 1){
                    return "{{ __('bk.Locked') }}"
                }else{
                    return "{{ __('bk.Unknow') }}"
                }
            }
        },  {
            field: "last_login_time",
            title: "{{ __('ts.LastLogonTime') }}",
            align: "center",
            sortable: true,
        },  {
                field: "last_login_ip",
                title: "{{ __('bk.LastLoginIp') }}",
                align: "center",
                // sortable: true,
            }, {
            field: "created",
            title: "{{ __('ts.Created') }}",
            align: "center",
            sortable: true,
        }, {
            field: "-",
            title: "{{ __('ts.Action') }}",
            align: "center",
            formatter: function(b, c, a) {
                {{--return "<a class=\"btn btn-xs btn-primary\" onclick='showEditModal(" + JSON.stringify(c) + ")'>{{ __('ts.EditInfo') }}</a><a class=\"btn btn-xs btn-primary\" onclick='showEditModal(" + JSON.stringify(c) + ")'>{{ __('ts.EditTag') }}</a><a class=\"btn btn-xs btn-primary\" onclick='showEditModal(" + JSON.stringify(c) + ")'>{{ __('ts.UpdatePwd') }}</a>"--}}
                    return "<a class=\"btn btn-info btn-rounded\" onclick='showStatusEditModal(" + JSON.stringify(c) + ")'>{{ __('bk.EditUserStatus') }}</a><a class=\"btn btn-xs btn-primary\" onclick='showEditModal(" + JSON.stringify(c) + ")'>{{ __('ts.Edit') }}</a>"
            }
        }]
    }

    $(function() {
        $('#reservation').daterangepicker({
            "startDate": moment().subtract(30, 'days'),
            "endDate": moment()
        }, function(start, end, label) {
            console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
        })

        common.getAjax(apiPath + "getbasedata?requireItems=accountSearchType,accountSex,accountType,userIntention,accountSearchTimeType,riskUserType,attrTag,educational,income,isLock", function(a) {
            typeData = a.result
            {{--$("#client_id").initSelect(a.result.customerType, "key", "value", "{{ __('ts.Client') }}")--}}
            $("#t").initSelect(a.result.accountSearchType, "key", "value", "{{ __('bk.accountSearchType') }}")
            $("#t2").initSelect(a.result.accountSearchTimeType, "key", "value", "{{ __('bk.accountSearchTimeType') }}")
            $("#accountType").initSelect(a.result.accountType, "key", "value", "{{ __('ts.accountType') }}")
            $("#riskUserType").initSelect(a.result.riskUserType, "key", "value", "{{ __('ts.riskUserType') }}")
            $("#accountSex").initSelect(a.result.accountSex, "key", "value", "{{ __('bk.AccountSex') }}")
            $("#accountSex1").initSelect(a.result.accountSex, "key", "value", "{{ __('bk.AccountSex') }}")
            $("#accountSex2").initSelect(a.result.accountSex, "key", "value", "{{ __('bk.AccountSex') }}")
            $("#educational").initSelect(a.result.educational, "key", "value", "{{ __('bk.Educational') }}")
            $("#educational2").initSelect(a.result.educational, "key", "value", "{{ __('bk.Educational') }}")
            $("#income").initSelect(a.result.income, "key", "value", "{{ __('bk.Income') }}")
            $("#income2").initSelect(a.result.income, "key", "value", "{{ __('bk.Income') }}")
            $("#userIntention").initSelect(a.result.userIntention, "key", "value", "{{ __('bk.UserIntention') }}")
            $("#userIntention2").initSelect(a.result.userIntention, "key", "value", "{{ __('bk.UserIntention') }}")
            $("#attrTag").initSelect(a.result.attrTag, "key", "value", "{{ __('bk.AttrNames') }}")
            $("#isLock").initSelect(a.result.isLock, "key", "value", "{{ __('bk.is_lock') }}")

          for (let i = 0; i < a.result.userIntention.length; i++) {
                $("#intentionCheck").append(`<label style="margin-right: 15px;">
      <input type="checkbox" value="`+a.result.userIntention[i]['key']+`" name="userIntention" >`+a.result.userIntention[i]['value']+`</label>`);
            }

            // $('#client_id').select2()
            $("#btnSearch").initSearch(apiPath + "user/account", getColumns(), {
                sortName: "uid",
                sortOrder: 'desc',
                showColumns: true,
                toolbar: '#toolbar',
                // showExport: true,
                // exportTypes: ['csv'],
                // exportDataType: "all"

            })
            $("#btnSubmit").click()


        })

        $('#updateBtnSubmit').click(function() {
            var mea=[]
            $(".measurement").each(function (i,e){
                mea[i]=$(this).val()
            })
           var meaStr=mea.join()
            $("#measurements").val(meaStr)

            var obj=document.getElementsByName("userIntention")
            check_val=[]
            for (k in obj){
                if (obj[k].checked){
                    check_val.push(obj[k].value)
                }
            }
            $("#intention").val(check_val.join())

            var id = $('#editModal form').find("[data-field='uid']").val()
            cform.patch('editModal', apiPath + 'user/accountEdit/' + id, function(d) {
                myAlert.success(d.result)
                $('#editModal').modal('hide');
                $('#btnSearch').click()
            })
        })

        $('#btnCreate').click(function() {
            showCreateModal()
        })
        function showCreateModal() {
            cform.get('createModal', apiPath + 'user/getOccupationTree',function (res){
                $("#occupation2").citySelect({
                    data:{"citylist":res.tree},
                    prov:"",
                    city:"",
                    dist:"",
                    nodata:"none"
                });
            })

            $('#createModal').modal()
        }


        $('#statusBtnSubmit').click(function() {
            var id = $('#statusModal form').find("[data-field='uid']").val()
            cform.patch('statusModal', apiPath + 'user/accountStatusEdit/' + id, function(d) {
                myAlert.success(d.result)
                $('#statusModal').modal('hide');
                $('#btnSearch').click()
            })
        })


        // common.initSection(true)

        // console.log(111)
        // $('#ttt').datetimepicker({
        //     format: 'L'
        // })

        // $('#reservation').daterangepicker()
        // common.initDateTime('reservation', 1)
        // var $table = $('#tabMain')
        // $('#toolbar').find('select').change(function() {
        //     $table.bootstrapTable('destroy').bootstrapTable({
        //         exportDataType: $(this).val(),
        //         exportTypes: ['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'pdf'],
        //         columns: [{
        //                 field: 'state',
        //                 checkbox: true,
        //                 visible: $(this).val() === 'selected'
        //             },
        //             {
        //                 field: 'id',
        //                 title: 'ID'
        //             }, {
        //                 field: 'name',
        //                 title: 'Item Name'
        //             }, {
        //                 field: 'price',
        //                 title: 'Item Price'
        //             }
        //         ]
        //     })
        // }).trigger('change')

    })
</script>
@stop
