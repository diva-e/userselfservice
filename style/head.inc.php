<?php
global $Views, $msg, $navigationActiveSite;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset=utf-8/>
  <title><?= $html_title ?></title>
  <link rel="icon" href="style/favicon.png" type="image/png">
  <script type="text/javascript" src="style/jquery-3.3.1.min.js"></script>

  <!-- Bootstrap 4 -->
  <link rel="stylesheet" type="text/css" href="style/bootstrap-4.2.1-dist/css/bootstrap.min.css" media="all"/>
  <link rel="stylesheet" type="text/css" href="style/bootstrap-4.2.1-dist/css/bootstrap-grid.min.css" media="all"/>
  <script type="text/javascript" src="style/bootstrap-4.2.1-dist/js/bootstrap.min.js"></script>

  <style type="text/css">
    .selfservice-background {
      background-color: rgba(164, 175, 41, 0.16);
    }

    .grey {
      color: #4b4b4b;
    }
  </style>

  <?= $html_head_inc ?>


</head>
<body>
<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="?">
    <img style="height: 28px;" src="style/favicon.png">
  </a>
  <a class="navbar-brand" href="?">
    FIXME
  </a>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item dropdown <?=$navigationActiveSite["list"]?>">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown"
           aria-haspopup="true" aria-expanded="false">
          Contacts
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
          <?php
            foreach ($Views as $i => $v) {
              ?>
              <a class="dropdown-item" href="?p=list&view=<?= $i ?>"><?= $v["name"] ?></a>
              <?php
            }
          ?>
        </div>
      </li>
      <li class="nav-item <?=$navigationActiveSite["links"]?>">
        <a class="nav-link" href="?p=links">Linklist</a>
      </li>
      <li class="nav-item <?=$navigationActiveSite["user"]?>">
        <a class="nav-link" href="?p=user">My Profile</a>
      </li>
    </ul>

    <span class="navbar-text">
      <?php
        $from = "";
        if (isset($_GET["p"])) {
          $from = "&from=" . $_GET["p"];
        }
        if (!empty($_SESSION["username"])) { ?>
        Logged in as: <?= strtolower($_SESSION["username"]) ?> (<a href="?logout=y<?=$from?>">Logout</a>)
      <?php
        } else {
      ?>
        <a href="?p=login<?=$from?>">Login</a>
      <?php } ?>
     </span>
  </div>
</nav>

<!-- Alert Messages -->
<div class="container">
  <br/>
  <?php
    foreach ($msg as $type => $msgs) {
      foreach ($msgs as $i => $text) {
        ?>
        <div class="alert alert-<?= $type ?> alert-dismissible fade show" role="alert">
          <?= $text ?>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <?php
      }
    }
  ?>
</div>


<div class="container">
  <br/>
