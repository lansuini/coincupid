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
                <input type="text" class="form-control" data-field="username" placeholder="{{ __('ts.username') }}" />
                <select class="form-control" data-field="is_lock" id="selLockType"></select>
                <select class="form-control" data-field="client_id" id="client_id"></select>
                <select class="form-control" data-field="is_bind_google_code" id="selBindGoogleCodeType"></select>
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
              <label class="col-form-label">{{ __('ts.Nickname') }}</label>
              <input type="text" class="form-control" data-field="nickname" />
            </div>

            <div class="form-group">
              <label class="col-form-label">{{ __('ts.Username') }}</label>
              <input type="text" class="form-control" data-field="username" readonly />
            </div>

            <div class="form-group">
              <label class="col-form-label">{{ __('ts.Client') }}</label>
              <select class="form-control client_id" data-field="client_id"></select>
            </div>

            <div class="form-group">
              <label class="" for="txtRole">{{ __('ts.Password') }}<a class="btn btn-xs btn-dark autors">{{ __('ts.AutoFixed') }}</a></label>
              <input type="text" class="form-control" data-field="password" />
            </div>

            <div class="form-group">
              <label class="col-form-label">{{ __('ts.Role') }}</label>
              <select class="form-control role_id" data-field="role_id"></select>
            </div>

            <div class="form-group form-edit">
              <label class="col-form-label" for="selStatus">{{ __('ts.Lock') }}</label>
              <select class="form-control is_lock" data-field="is_lock"></select>
            </div>

            <div class="form-group form-edit">
              <label class="col-form-label">{{ __('ts.UseGoogleCode') }}</label>
              <select class="form-control is_bind_google_code" data-field="is_bind_google_code"></select>
            </div>

            <div class="form-group">
              <label class="col-form-label">{{ __('ts.IPWhite') }}</label>
              <input type="text" class="form-control" data-field="ip_white" />
            </div>

            <div class="form-group">
              <label class="col-form-label">{{ __('ts.LastUpdatePasswordTime') }}</label>
              <input type="text" class="form-control" data-field="last_update_password_time" readonly />
            </div>

            <div class="form-group">
              <label class="col-form-label">{{ __('ts.LastLoginTime') }}</label>
              <input type="text" class="form-control" data-field="last_login_time" readonly />
            </div>

            <div class="form-group">
              <label class="col-form-label">{{ __('ts.LastBindGoogleCodeTime') }}</label>
              <input type="text" class="form-control" data-field="last_bind_google_code_time" readonly />
            </div>

            <div class="form-group">
              <label class="col-form-label">{{ __('ts.LastLoginIP') }}</label>
              <input type="text" class="form-control" data-field="last_login_ip" readonly />
            </div>

            <div class="form-group">
              <label class="col-form-label">{{ __('ts.LoginErrorCount') }}</label>
              <input type="text" class="form-control" data-field="err_login_cnt" readonly />
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
              <label class="col-form-label">{{ __('ts.Nickname') }}</label>
              <input type="text" class="form-control" data-field="nickname" />
            </div>

            <div class="form-group">
              <label class="col-form-label">{{ __('ts.Username') }}</label>
              <input type="text" class="form-control" data-field="username" />
            </div>

            <div class="form-group">
              <label class="col-form-label">{{ __('ts.Client') }}</label>
              <select class="form-control client_id" data-field="client_id"></select>
            </div>

            <div class="form-group">
              <label class="" for="txtRole">{{ __('ts.Password') }}<a class="btn btn-xs btn-dark autors">{{ __('ts.AutoFixed') }}</a></label>
              <input type="text" class="form-control" data-field="password" />
            </div>

            <div class="form-group">
              <label class="col-form-label">{{ __('ts.Role') }}</label>
              <select class="form-control role_id" data-field="role_id"></select>
            </div>

            <div class="form-group form-edit">
              <label class="col-form-label">{{ __('ts.Lock') }}</label>
              <select class="form-control is_lock" data-field="is_lock"></select>
            </div>

            <div class="form-group">
              <label class="col-form-label">{{ __('ts.IPWhite') }}</label>
              <input type="text" class="form-control" data-field="ip_white" />
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

  function randomString(e) {
    e = e || 32;
    var t = "ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678",
      a = t.length,
      n = "";
    for (i = 0; i < e; i++) n += t.charAt(Math.floor(Math.random() * a));
    return n
  }

  $('.autors').click(function() {
    $(this).parent().parent().find('input').val(randomString(10))
  })

  function showEditModal(data) {
    cform.get('editModal', apiPath + 'manager/account/' + data['id'])
  }

  function showCreateModal() {
    $('#createModal').modal()
  }

  function delAccount(id) {
    myConfirm.show({
      title: "{{ __('ts.confirm deletion ?') }}",
      sure_callback: function() {
        cform.del(apiPath + "manager/account/" + id, function(d) {
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
      field: "username",
      title: "{{ __('ts.Username') }}",
      align: "center"
    }, {
      field: "nickname",
      title: "{{ __('ts.Nickname') }}",
      align: "center"
    }, {
      field: "client_id",
      title: "{{ __('ts.Client') }}",
      align: "center",
      formatter: function(b, c, a) {
        return cform.getValue(typeData['customerType'], b)
      }
    }, {
      field: "is_lock",
      title: "{{ __('ts.Locked') }}",
      align: "center",
      formatter: function(b, c, a) {
        return cform.getValue(typeData['lockType'], b)
      }
    }, {
      field: "is_bind_google_code",
      title: "{{ __('ts.UseGoogleCode') }}",
      align: "center",
      formatter: function(b, c, a) {
        return cform.getValue(typeData['bindGoogleCodeType'], b)
      }
    }, {
      field: "role_id",
      title: "{{ __('ts.Role') }}",
      align: "center",
      formatter: function(b, c, a) {
        return cform.getValue(typeData['roleType'], b)
      }
    }, {
      field: "last_login_ip",
      title: "{{ __('ts.LastLoginIP') }}",
      align: "center",
    }, {
      field: "last_login_time",
      title: "{{ __('ts.LastLoginTime') }}",
      align: "center",
      sortable: true,
    }, {
      field: "last_update_password_time",
      title: "{{ __('ts.LastUpdatePasswordTime') }}",
      align: "center",
      visible: false
    }, {
      field: "created",
      title: "{{ __('ts.Created') }}",
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

    common.getAjax(apiPath + "getbasedata?requireItems=customerType,lockType,bindGoogleCodeType,roleType", function(a) {
      typeData = a.result
      $("#selLockType").initSelect(a.result.lockType, "key", "value", "{{ __('ts.lock status') }}")
      $("#selBindGoogleCodeType").initSelect(a.result.bindGoogleCodeType, "key", "value", "{{ __('ts.bind google code status') }}")

      $(".role_id").initSelect(a.result.roleType, "key", "value", "{{ __('ts.role') }}")
      $(".is_lock").initSelect(a.result.lockType, "key", "value", "{{ __('ts.lock status') }}")
      $(".is_bind_google_code").initSelect(a.result.bindGoogleCodeType, "key", "value", "{{ __('ts.bind google code status') }}")
      $(".client_id").initSelect(a.result.customerType, "key", "value", "{{ __('ts.client') }}")
      $("#client_id").initSelect(a.result.customerType, "key", "value", "{{ __('ts.client') }}")
      $("#client_id").select2()
      $("#btnSearch").initSearch(apiPath + "manager/account", getColumns(), {
        sortName: "id",
        sortOrder: 'desc'
      })

      $("#btnSubmit").click()
      common.initSection(true)
    })


    $('#updateBtnSubmit').click(function() {
      var id = $('#editModal form').find("[data-field='id']").val()
      cform.patch('editModal', apiPath + 'manager/account/' + id, function(d) {
        myAlert.success(d.result)
        $('#editModal').modal('hide');
        $('#btnSearch').click()
      })
    })

    $('#createBtnSubmit').click(function() {
      cform.post('createModal', apiPath + 'manager/account')
    })

    $('#btnCreate').click(function() {
      showCreateModal()
    })
  })
</script>
@stop