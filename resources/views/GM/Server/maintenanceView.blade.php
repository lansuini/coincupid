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
              <div class="btn-group v-search-bar" id="divSearch" style="float:right">
                <button type="button" class="btn btn-default" id="btnSearch" style="display: none">
                  <i class="fa-solid fa-arrows-rotate"></i> Refresh
                </button>
                <button type="button" class="btn btn-info" id="btnPushConfig">
                  <i class="fa-solid fa-arrows-rotate"></i> PushConfig
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
      field: "gameid",
      title: "{{ __('ts.GameID') }}",
      align: "center",
      formatter: function(b, c, a) {
        return b
      }
    }, {
      field: "name",
      title: "{{ __('ts.Name') }}",
      align: "center"
    }, {
      field: "-",
      title: "{{ __('ts.Maintenance') }}",
      align: "center",
      formatter: function(b, c, a) {
        var gameId = c['gameid'];
        // return "<a class=\"btn btn-xs btn-primary\" onclick='showEditModal(" + JSON.stringify(c) + ")'>{{ __('ts.Edit') }}</a>"
        return "<input class=\"\" type=\"checkbox\" data-field=\"m-0-" + gameId + "\" value=\"1\"/>"
      }
    }]
  }
  $(function() {
    // common.getAjax(apiPath + "getbasedata?requireItems=gameAliasType", function(a) {
    //   typeData = a.result

    //   $("#btnSubmit").click()
    //   common.initSection(true)

    //   common.getAjax(apiPath + "server/game/maintenance", function (a) {
    //     console.log(1)
    //   })
    $("#btnSearch").initSearch(apiPath + "server/game/maintenance", getColumns(), {
      pagination: false,
      success_callback: function(a) {
        for (var i = 0; i < a.gameStatus.length; i++) {
          var g = a.gameStatus[i]
          for (var j = 0; j < g['gameid'].length; j++) {
            var t = 'm-' + g['plat'] + '-' + g['gameid'][j]
            $('#tabMain').find('input[data-field="' + t + '"]').prop('checked', true)
          }
        }
      }
    })

    $('#btnPushConfig').click(function() {
      Swal.fire({
        title: 'Loading...',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading()
          cform.patch("tabMain", apiPath + 'server/game/maintenance')
        }
      })
    })
    // })

  })
</script>
@stop