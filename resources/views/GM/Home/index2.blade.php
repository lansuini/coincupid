@extends('/GM/Layout')
  
@section('content')
  <?php
  $name = isset($proj) ? env(strtoupper($proj).'_APP_NAME') : env('APP_NAME');
  ?>
  <style type="text/css">
    /* .sidebar-dark-primary {
      background-color: #28a745;
    } */
    /* .sidebar-dark-primary .form-control-sidebar {
      background-color: #28a745;
    } */
  </style>
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li cl  WS2EAQSS  ass="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <!-- <li class="nav-item d-none d-sm-inline-block">
        <a href="index3.html" class="nav-link">Home</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contact</a>
      </li> -->
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Navbar Search -->
      <li class="nav-item" style="display: none">
        <a class="nav-link" data-slide="true" href="#" onclick="javascript: window.location='/loginout'" role="button">
          <i class="fa fa-sign-out-alt"></i>
        </a>
      </li>

      <li class="nav-item m-auto">
          <select name="lang" id="lang">
            <option value="en" @if(Session::get('language') == 'en') selected @endif>English</option>
            <option value="zh-cn" @if(Session::get('language') == 'zh-cn') selected @endif>中文</option>

          </select>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="fas fa-users"></i>
        {{ $username }}
        <!-- <span class="badge badge-warning navbar-badge">15</span> -->
        </a>

        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <!-- <span class="dropdown-item dropdown-header">15 Notifications</span> -->
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item" onclick="javascript: window.location='/manager/account/password/view'">
            <i class="fas fa-lock mr-2"></i> {{ __('ts.update password') }}
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item" onclick="javascript: window.location='/manager/account/googlecode/view'">
            <i class="fas fa-lock mr-2"></i> {{ __('ts.update google code') }}
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item" onclick="javascript: window.location='/loginout'">
            <i class="fa fa-sign-out-alt mr-2"></i> {{ __('ts.login out') }}
          </a>
        </div>
      </li>

    </ul>
  </nav>
  <!-- /.navbar -->
  <?php
  $classMaps = ['GM' => 'sidebar-dark-primary', 'Analysis' => 'sidebar-dark-green'];
  
  ?>
  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
      <img src="/adminlte/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">{{ __('ts.'.$name) }}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <!-- <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="/adminlte/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block"> {{ $username }}</a>
        </div>
      </div> -->

      <!-- SidebarSearch Form -->
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <?php foreach($menu as $mk => $mv): ?>
          <?php if ($mv['is_menu'] == 2): ?>
          <li class="nav-item">
            <a href="<?=$urlBasic . $mv['url']?>" class="nav-link">
              <i class="fas fa-circle nav-icon"></i>
              <p><?=__($mv['name'])?></p>
            </a>
          </li>
            <?php elseif($mv['is_menu'] == 1):?>
            <li class="nav-item">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>
                <?=__($mv['name'])?>
                <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
              <?php foreach($mv['sub_menu_list'] as $k => $v): ?>
              <?php if ($v['is_menu'] != 0): ?>
              <li class="nav-item">
                <a href="<?=$urlBasic . $v['url']?>" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p><?=__($v['name'])?></p>
                </a>
              </li>
              <?php endif;?>
              <?php endforeach;?>
            </ul>
            <?php endif?>
            <?php endforeach;?>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper iframe-mode" data-widget="iframe" data-loading-screen="750">
    <div class="nav navbar navbar-expand navbar-white navbar-light border-bottom p-0">
      <div class="nav-item dropdown">
        <a class="nav-link bg-danger dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Close</a>
        <div class="dropdown-menu mt-0">
          <a class="dropdown-item" href="#" data-widget="iframe-close" data-type="all">Close All</a>
          <a class="dropdown-item" href="#" data-widget="iframe-close" data-type="all-other">Close All Other</a>
        </div>
      </div>
      <a class="nav-link bg-light" href="#" data-widget="iframe-scrollleft"><i class="fas fa-angle-double-left"></i></a>
      <ul class="navbar-nav overflow-hidden" role="tablist"></ul>
      <a class="nav-link bg-light" href="#" data-widget="iframe-scrollright"><i class="fas fa-angle-double-right"></i></a>
      <a class="nav-link bg-light" href="#" data-widget="iframe-fullscreen"><i class="fas fa-expand"></i></a>
    </div>
    <div class="tab-content">
      <div class="tab-empty">
        <h2 class="display-4">No tab selected!</h2>
      </div>
      <div class="tab-loading">
        <div>
          <h2 class="display-4">Tab is loading <i class="fa fa-sync fa-spin"></i></h2>
        </div>
      </div>
    </div>
  </div>
  <!-- /.content-wrapper -->
  <footer class="main-footer" style="display: none">

  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark" style="display: none">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->
@endsection
