<?php

  $html_title = "Selfservice - Login";
  $html_head_inc = "";

  include(PATH_STYLE . "head.inc.php");
?>

  <h1>Login</h1>

  <div class="card bg-light">
    <div class="card-body">
      <form action="" method="POST" >
        <div class="form-group">
          <div class="input-group mb-2">
            <div class="input-group-prepend">
              <div class="input-group-text selfservice-background grey"><strong>Username</strong></div>
            </div>
            <input type="text" class="form-control" id="ldapUser" name="ldapUser" placeholder="Username">
          </div>
          <div class="input-group mb-2">
            <div class="input-group-prepend">
              <div class="input-group-text selfservice-background grey"><strong>Password</strong></div>
            </div>
            <input type="password" class="form-control" id="ldapPassword" name="ldapPassword" placeholder="Password">
          </div>
        </div>
        <br />
        <button type="submit" class="btn btn-primary float-right">Login</button>
      </form>
    </div>
  </div>


<?php
  include(PATH_STYLE . "foot.inc.php")
?>
