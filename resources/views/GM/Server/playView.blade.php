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
                <input type="text" class="form-control" data-field="method_name" placeholder="{{ __('ts.name') }}" />
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
              <label class="col-form-label">{{ __('ts.GameID') }}</label>
              <input type="text" class="form-control" data-field="game_id" />
            </div>

            <div class="form-group">
              <label class="col-form-label">{{ __('ts.Name') }}</label>
              <input type="text" class="form-control" data-field="method_name" />
            </div>

            <div class="form-group">
              <label class="col-form-label">{{ __('ts.GameType') }}</label>
              <input type="text" class="form-control" data-field="game_type" />
            </div>

            <div class="form-group">
              <label class="col-form-label">{{ __('ts.PictureUrl') }}</label>
              <input type="text" class="form-control" data-field="pict_url" />
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
              <label class="col-form-label">{{ __('ts.GameID') }}</label>
              <input type="text" class="form-control" data-field="game_id" />
            </div>

            <div class="form-group">
              <label class="col-form-label">{{ __('ts.Name') }}</label>
              <input type="text" class="form-control" data-field="method_name" />
            </div>

            <div class="form-group">
              <label class="col-form-label">{{ __('ts.GameType') }}</label>
              <input type="text" class="form-control" data-field="game_type" />
            </div>

            <div class="form-group">
              <label class="col-form-label">{{ __('ts.PictureUrl') }}</label>
              <input type="text" class="form-control" data-field="pict_url" />
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
    cform.get('editModal', apiPath + 'server/game/play/' + data['id'])
  }

  function showCreateModal() {
    $('#createModal').modal()
  }

  function delRow(id) {
    myConfirm.show({
      title: "{{ __('ts.confirm deletion ?') }}",
      sure_callback: function() {
        cform.del(apiPath + "server/game/play/" + id, function(d) {
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
      sortable: true,
    }, {
      field: "game_id",
      title: "{{ __('ts.GameId') }}",
      align: "center",
      sortable: true,
    }, {
      field: "game_type",
      title: "{{ __('ts.GameType') }}",
      align: "center"
    }, {
      field: "method_name",
      title: "{{ __('ts.Name') }}",
      align: "center"
    }, {
      field: "pict_url",
      title: "{{ __('ts.PictureUrl') }}",
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
    $("#btnSearch").initSearch(apiPath + "server/game/play", getColumns())
    $("#btnSubmit").click()
    common.initSection(true)

    $('#updateBtnSubmit').click(function() {
      var id = $('#editModal form').find("[data-field='id']").val()
      cform.patch('editModal', apiPath + 'server/game/play/' + id)
    })

    $('#createBtnSubmit').click(function() {
      cform.post('createModal', apiPath + 'server/game/play')
    })

    $('#btnCreate').click(function() {
      showCreateModal()
    })
  })
</script>
@stop