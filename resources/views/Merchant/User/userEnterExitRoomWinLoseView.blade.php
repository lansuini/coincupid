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
                <input type="text" class="form-control" data-field="player_name" placeholder="{{ __('ts.player name') }}" />
                <select class="form-control" data-field="node_id" id="node_id"></select>
                <input type="text" class="form-control" style="width:200px;" data-field="created" placeholder="{{ __('ts.created') }}" id="reservation" />
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
  var typeData = []

  function getColumns() {
    return [{
      field: "player_name",
      title: "{{ __('ts.Player Name') }}",
      align: "center",
      sortable: true,
    },{
      field: "nodeid",
      title: "{{ __('ts.Room') }}",
      align: "center",
      sortable: true,
      formatter: function(b, c, a) {
        return cform.getValue(typeData['nodeType'], b)
      }
    }, {
      field: "last_gold",
      title: "{{ __('ts.Before Gold') }}",
      align: "center",
      sortable: true,
      formatter: function(b, c, a) {
          return common.ya(b)
      },
    }, {
      field: "now_gold",
      title: "{{ __('ts.After Gold') }}",
      align: "center",
      sortable: true,
      formatter: function(b, c, a) {
          return common.ya(b)
      },
    }, {
      field: "change_gold",
      title: "{{ __('ts.Profit and loss') }}",
      align: "center",
      sortable: true,
      formatter: function(b, c, a) {
          return common.ya(b)
      },
    }, {
      field: "enter_time",
      title: "{{ __('ts.Enter Time') }}",
      align: "center",
      sortable: true,
    }, {
      field: "post_time",
      title: "{{ __('ts.Departure Time') }}",
      align: "center",
      sortable: true,
    }
    ]
  }

  $(function() {

    $('#reservation').daterangepicker({
        "startDate": moment(),
        "endDate": moment()
    }, function(start, end, label) {
        // console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
    })

    common.getAjax(apiPath + "getbasedata?requireItems=nodeType", function(a) {
      typeData = a.result
      $("#node_id").initSelect(a.result.nodeType, "key", "value", "{{ __('ts.room') }}")

      $("#btnSearch").initSearch(apiPath + "user/enterexitroomwinlose", getColumns())
      $("#btnSubmit").click()
      common.initSection(true)
      $('#node_id').select2()
    })


  })
</script>
@stop