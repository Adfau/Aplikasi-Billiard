<?php
if (!defined('INCLUDED')) {
    header("HTTP/1.1 404 Not Found");
    exit;
}
?>

<div class="d-flex flex-column flex-shrink-0 p-3 bg-light" style="width: 250px; margin-right: 40px; box-shadow: 0 0 2px #146569; min-height: 93vh">
  <hr style="margin-top: 0px">
  <ul class="nav nav-pills flex-column mb-auto">
    <li class="nav-item">
      <a href="admin-dashboard" class="nav-link align-items-center d-flex <?php $pageType = (defined('PAGE_DASHBOARD')) ? "active active-page" : "link-dark"; echo $pageType; ?>" <?php if (defined('PAGE_DASHBOARD')) echo 'aria-current="page"'; ?>>
      <span style="min-width: 28px"><i class="fa fa-tachometer" aria-hidden="true"></i></span>
        Dashboard
      </a>
    </li>
    <li>
      <a href="admin-account" class="nav-link align-items-center d-flex <?php $pageType = (defined('PAGE_ACCOUNT')) ? "active active-page" : "link-dark"; echo $pageType; ?>" <?php if (defined('PAGE_ACCOUNT')) echo 'aria-current="page"'; ?>>
      <span style="min-width: 28px"><i class="fa fa-users" aria-hidden="true"></i></span>
        Akun Karyawan
      </a>
    </li>
    <li>
      <a href="admin-settings" class="nav-link align-items-center d-flex <?php $pageType = (defined('PAGE_SETTING')) ? "active active-page" : "link-dark"; echo $pageType; ?>" <?php if (defined('PAGE_SETTING')) echo 'aria-current="page"'; ?>>
      <span style="min-width: 28px"><i class="fa fa-wrench" aria-hidden="true"></i></span>
        Setel Harga
      </a>
    </li>
    <li>
      <a href="admin-report" class="nav-link align-items-center d-flex <?php $pageType = (defined('PAGE_REPORT')) ? "active active-page" : "link-dark"; echo $pageType; ?>" <?php if (defined('PAGE_REPORT')) echo 'aria-current="page"'; ?>>
      <span style="min-width: 28px"><i class="fa fa-bar-chart" aria-hidden="true"></i></span>
        Laporan
      </a>
    </li>
    <li>
      <a href="admin-log" class="nav-link align-items-center d-flex <?php $pageType = (defined('PAGE_LOG')) ? "active active-page" : "link-dark"; echo $pageType; ?>" <?php if (defined('PAGE_LOG')) echo 'aria-current="page"'; ?>>
      <span style="min-width: 28px"><i class="fa fa-list" aria-hidden="true"></i></span>
        Log
      </a>
    </li>
  </ul>
  <hr>
      <p>&copy; <?php echo date("Y"); ?> Geni Tech</p>
</div>