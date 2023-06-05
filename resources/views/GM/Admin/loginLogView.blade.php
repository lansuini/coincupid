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

</div>
@append

@section('content')
<!-- edit form -->

@stop

@section('script')
<script>
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
      field: "admin_id",
      title: "{{ __('ts.AdminID') }}",
      align: "center"
    }, {
      field: "admin_username",
      title: "{{ __('ts.AdminUsername') }}",
      align: "center"
    }, {
      field: "browser",
      title: "{{ __('ts.Browser') }}",
      align: "center"
    }, {
      field: "is_success",
      title: "{{ __('ts.isSuccess') }}",
      align: "center",
      formatter: function(b, c, a) {
        return cform.getValue(typeData['successType'], b)
      }
    }, {
      field: "desc",
      title: "{{ __('ts.Desc') }}",
      align: "center"
    }, {
      field: "ip",
      title: "{{ __('ts.IP') }}",
      align: "center"
    }, {
      field: "created",
      title: "{{ __('ts.Created') }}",
      align: "center",
    }]
  }

  $(function() {
    $('#reservation').daterangepicker({
      "startDate": moment().subtract(30, 'days'),
      "endDate": moment()
    }, function(start, end, label) {
      console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
    })
    common.getAjax(apiPath + "getbasedata?requireItems=successType", function(a) {
      typeData = a.result
      $("#is_success").initSelect(a.result.successType, "key", "value", "{{ __('ts.success status') }}")
      $("#btnSearch").initSearch(apiPath + "manager/loginlog", getColumns(), {
        sortName: "id",
        sortOrder: 'desc'
      })
      $("#btnSubmit").click()
    })

    common.initSection(true)

  })
</script>
@stop