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
      field: "game_id",
      title: "{{ __('ts.Game') }}",
      align: "center",
      sortable: true,
    }, {
      field: "win_lose",
      title: "{{ __('ts.Win Lose') }}",
      align: "center",
      formatter: function(b, c, a) {
          return common.ya(b)
      },
    }, {
      field: "post_time",
      title: "{{ __('ts.Date') }}",
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

    common.getAjax(apiPath + "getbasedata?requireItems=gameAliasType,testItems", function(a) {
      typeData = a.result
      $("#game_id").initSelect(a.result.gameAliasType, "key", "value", "{{ __('ts.Games') }}")

      $("#btnSearch").initSearch(apiPath + "report/day", getColumns())
      $("#btnSubmit").click()
      common.initSection(true)
      $('#game_id').select2()
    })


  })
</script>
@stop