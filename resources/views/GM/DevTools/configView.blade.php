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

                        </div>

                        <div class="card-body" id="editModal">
                            <form class="">
                                <div class="form-group">
                                    <label class="col-form-label">{{ __('ts.API Maintenance') }}</label>
                                    <select class="form-control maintenanceType" data-field="maintenance"></select>
                                </div>
                            </form>
                        </div>

                        <div class="card-footer">
                            <input type="button" class="btn btn-primary" value="{{ __('ts.Submit') }}" id="updateBtnSubmit" />
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
    $(function() {
        common.getAjax(apiPath + "getbasedata?requireItems=maintenanceType", function(a) {
            $(".maintenanceType").initSelect(a.result.maintenanceType, "key", "value", "{{ __('ts.API Maintenance') }}")
            cform.get('editModal', apiPath + 'devtools/config', function (d) {
                // $('#editModal').modal('hide')
                $('.modal-backdrop').hide()
            })

        })
        $('#updateBtnSubmit').click(function() {

            cform.patch('editModal', apiPath + 'devtools/config', function(d) {
                myAlert.success(d.result)
                // $('#editModal').modal('hide');
            })
        })
    })
</script>
@stop