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
                <input type="text" class="form-control" data-field="admin_username" placeholder="{{ __('ts.admin username') }}" />
                <select class="form-control" data-field="is_success" id="is_success"></select>
                <select class="form-control" data-field="key" id="key"></select>
                <input type="text" class="form-control" style="width:190px;" data-field="created" placeholder="{{ __('ts.created') }}" id="reservation" />
                <button type="button" class="btn btn-default" id="btnSearch">
                  <i class="fas fa-search"></i>{{ __('ts.Search') }}
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

  <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">{{ __('ts.Detail') }}</h4>
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        </div>

        <div class="modal-body">
          <form class="row g-3">

            <div class="col-md-4">
              <label class="col-form-label">{{ __('ts.Key') }}</label>
              <input type="text" class="form-control" data-field="key" />
            </div>
            <div class="col-md-4">
              <label class="col-form-label">{{ __('ts.ID') }}</label>
              <input type="text" class="form-control" data-field="id" />
            </div>

            <div class="col-md-4">
              <label class="col-form-label">{{ __('ts.AdminID') }}</label>
              <input type="text" class="form-control" data-field="admin_id" />
            </div>

            <div class="col-md-12">
              <label class="col-form-label">{{ __('ts.Browser') }}</label>
              <input type="text" class="form-control" data-field="browser" />
            </div>



            <div class="col-md-6">
              <label class="col-form-label">{{ __('ts.Before') }}</label>
              <textarea rows="15" class="form-control" data-field="before"></textarea>
            </div>

            <div class="col-md-6">
              <label class="col-form-label">{{ __('ts.After') }}</label>
              <textarea rows="15" class="form-control" data-field="after"></textarea>
            </div>

            <div class="col-md-12">
              <label class="col-form-label">{{ __('ts.URL') }}</label>
              <input type="text" class="form-control" data-field="url" />
            </div>

            <div class="col-md-4">
              <label class="col-form-label">{{ __('ts.TargetID') }}</label>
              <input type="text" class="form-control" data-field="target_id" />

            </div>

            <div class="col-md-4">
              <label class="col-form-label">{{ __('ts.IP') }}</label>
              <input type="text" class="form-control" data-field="ip" />
            </div>

            <div class="col-md-4">
              <label class="col-form-label">{{ __('ts.Method') }}</label>
              <input type="text" class="form-control" data-field="method" />
            </div>

            <div class="col-md-6">
              <label class="col-form-label">{{ __('ts.Params') }}</label>
              <textarea rows="15" class="form-control" data-field="params"></textarea>
            </div>

            <div class="col-md-6">
              <label class="col-form-label">{{ __('ts.Desc') }}</label>
              <input type="text" class="form-control" data-field="desc" />
            </div>
          </form>
        </div>

        <div class="modal-footer">
          <input type="button" class="btn btn-default" value="{{ __('ts.Close') }}" data-dismiss="modal" />
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

  function showDetailModal(data) {
    cform.get('detailModal', apiPath + 'manager/actionlog/' + data['id'])
  }

  function getColumns() {
    return [{
      field: "id",
      title: "{{ __('ts.ID') }}",
      align: "center",
      sortable: true,
    }, {
      field: "admin_username",
      title: "{{ __('ts.username') }}",
      align: "center"
    }, {
      field: "key",
      title: "{{ __('ts.key') }}",
      align: "center",
      formatter: function(b, c, a) {
        return cform.getValue(typeData['successType'], b)
      }
    }, {
      field: "desc",
      title: "{{ __('ts.Desc') }}",
      align: "center"
    }, {
      field: "is_success",
      title: "{{ __('ts.isSuccess') }}",
      align: "center",
      formatter: function(b, c, a) {
        return cform.getValue(typeData['successType'], b)
      },
    }, {
      field: "created",
      title: "{{ __('ts.Created') }}",
      align: "center",
    }, {
      field: "-",
      title: "{{ __('ts.Action') }}",
      align: "center",
      formatter: function(b, c, a) {
        return "<a class=\"btn btn-xs btn-info\" onclick='showDetailModal(" + JSON.stringify(c) + ")'>{{ __('ts.Detail') }}</a>"
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
    
    common.getAjax(apiPath + "getbasedata?requireItems=successType,actionType", function(a) {
      typeData = a.result
      $("#is_success").initSelect(a.result.successType, "key", "value", "{{ __('ts.success status') }}")
      $("#key").initSelect(a.result.actionType, "key", "value", "{{ __('ts.action type') }}")

      $("#btnSearch").initSearch(apiPath + "manager/actionlog", getColumns(), {
        sortName: "id",
        sortOrder: 'desc'
      })
      $("#btnSubmit").click()

      $('#key').select2()
    })


    common.initSection(true)

  })
</script>
@stop