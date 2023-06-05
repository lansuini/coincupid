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
                <input type="username" class="form-control" data-field="name" placeholder="{{ __('ts.name') }}" />
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
              <label class="col-form-label">{{ __('ts.Name') }}</label>
              <input type="text" class="form-control" data-field="name" />
            </div>

            <div class="form-group">
              <label class="col-form-label">{{ __('ts.Created') }}</label>
              <input type="text" class="form-control" data-field="created" readonly />
            </div>

            <div class="form-group sr-only">
              <label class="col-form-label">{{ __('ts.RoleKeys') }}</label>
              <input type="text" class="form-control" data-field="role_keys" value="{}" />
            </div>

            <div class="" id="menu" style="">
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
    cform.get('editModal', apiPath + 'manager/role/' + data['id'], function(d) {
      if (data.id != 1) {
        var html = '<h3>Permissions</h3><hr/><br/>'
        for (var i = 0; i < d.menu.length; i++) {
          var cx = d.data.role_keys != null && d.data.role_keys.indexOf(d.menu[i]['key']) != -1 ? 'checked="checked"' : ''
          var checkbox = "<div><input class='chk' type='checkbox' value='" + d.menu[i]['key'] + "' " + cx + "> <b>" +
            d.menu[i]['name'] + '</b><br>';
          var subCheckbox = ""
          if ('sub_menu_list' in d.menu[i]) {
            for (var j = 0; j < d.menu[i]['sub_menu_list'].length; j++) {
              try {
                var h = d.menu[i]['sub_menu_list'][j]['is_menu'] == 0 ? '*' : ''
              } catch(e) {
                var h = ''
                console.log(e)
              }
              var cx = d.data.role_keys != null && d.data.role_keys.indexOf(d.menu[i]['sub_menu_list'][j]['key']) != -1  ? 'checked="checked"' : ''
              var checkbox2 = "<input type='checkbox' value='" + d.menu[i]['sub_menu_list'][j]['key'] + "' " + cx + "> " + h +
                d.menu[i]['sub_menu_list'][j]['name'] + '&nbsp;';
              subCheckbox += checkbox2
            }
          }
          html += checkbox
          html += subCheckbox
          html += '</div><hr/>'
        }

        $('#menu').html(html)
        $('.chk').change(function() {
          $(this).parent().find('input[type=checkbox]').prop('checked', $(this).is(':checked'))
        })
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
        cform.del(apiPath + "manager/role/" + id, function(d) {
          location.href = location.href
        })
      }
    })
  }

  function getColumns() {
    return [{
      field: "id",
      title: "{{ __('ts.ID') }}",
      align: "center"
    }, {
      field: "name",
      title: "{{ __('ts.Name') }}",
      align: "center"
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
          "<a class=\"btn btn-xs btn-danger\" onclick='delRow(\"" + c.id + "\")'>{{ __('ts.Del') }}</a>"
      }
    }]
  }

  $(function() {
    $("#btnSearch").initSearch(apiPath + "manager/role", getColumns())
    $("#btnSubmit").click()
    common.initSection(true)

    $('#updateBtnSubmit').click(function() {
      var id = $('#editModal form').find("[data-field='id']").val()

      var role_keys = []
      $('#menu input[type=checkbox]').each(function() {
        if ($(this).prop("checked")) {
          role_keys.push($(this).val())
        }
      })
      $('#editModal form').find("[data-field='role_keys']").val(JSON.stringify(role_keys))

      cform.patch('editModal', apiPath + 'manager/role/' + id, function(d) {
        myAlert.success(d.result)
        $('#editModal').modal('hide');
        $('#btnSearch').click()
      })
    })

    $('#createBtnSubmit').click(function() {
      cform.post('createModal', apiPath + 'manager/role')
    })

    $('#btnCreate').click(function() {
      showCreateModal()
    })
  })
</script>
@stop