<section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1> {{ is_array($pageTitle) ? __(end($pageTitle)) : __($pageTitle) }} </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#"> {{ __($pageTitle[0] ?? '') }}</a></li>
            <li class="breadcrumb-item active"> {{ __($pageTitle[1] ?? '')}} </li>
          </ol>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>