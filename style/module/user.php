<?php

  $html_title = "My Profile";
  $html_head_inc = <<<HTML
    <style type="text/css">
      .groupName {
        background-color: rgba(59,128,91,0.85);
        padding: 3px;
        margin-top: 2px;
        border-radius: 3px;
        color: white;
        display: inline-block;
      }
    </style>
    <script>
      $(document).ready(function() {
        // set all required inputs to readonly and add onfocus function to remove readonly again
        $(".readonly-until-onfocus").each(function(index) {
          $(this).attr('readonly', true)
              .attr('style', 'background-color: white;')
              .attr('onfocus', 'this.readOnly = false');
        });

        // Toggle text of advanced-settings-button
        $('#detailedInformationToggleButton').click(function() {
           $(this).text(function(i,old) {
             if ($(this).attr('aria-expanded') === 'false') {
               return "Hide Advanced Settings";
             } else {
               return "Show Advanced Settings";
             }
           });
        });
      });
    </script>
HTML;

  include(PATH_STYLE . "head.inc.php");
  if (isset($userinfo)) {
    ?>

    <h1><?= $userinfo["displayname"] ?></h1>

    <div class="card bg-white">
      <div class="card-body">
        <form action="?p=user<?= (!empty($_GET["userid"])) ? '&amp;userid=' . $userinfo["samaccountname"] : '' ?>"
              method="POST">
          <!-- hidden input to signal userupdate -->
          <input type="hidden" value="<?= $userinfo["samaccountname"] ?>" name="userupdate">

          <nav class="card bg-light">
            <div class="card-header">
              <span class="h5">Master Data</span>
            </div>
            <div class="card-body">
              <!-- Givenname, Surname, Email -->
              <div class="form-row">
                <div class="col">
                  <div class="input-group mb-2">
                    <div class="input-group-prepend">
                      <div class="input-group-text selfservice-background grey"><strong>Given Name</strong></div>
                    </div>
                    <input type="text" class="form-control" id="userGivenname" value="<?= $userinfo["givenname"] ?>"
                           disabled>
                  </div>
                </div>
                <div class="col">
                  <div class="input-group mb-2">
                    <div class="input-group-prepend">
                      <div class="input-group-text selfservice-background grey"><strong>Surname</strong></div>
                    </div>
                    <input type="text" class="form-control" id="userSurname" value="<?= $userinfo["sn"] ?>" disabled>
                  </div>
                </div>
                <div class="col">
                  <div class="input-group mb-2">
                    <div class="input-group-prepend">
                      <div class="input-group-text selfservice-background grey"><strong>E-Mail</strong></div>
                    </div>
                    <input type="text" class="form-control" id="userMail" value="<?= $userinfo["mail"] ?>" disabled>
                  </div>
                </div>
              </div>

              <!-- Title, Department, Room -->
              <div class="form-row">
                <div class="col">
                  <div class="input-group mb-2">
                    <div class="input-group-prepend">
                      <div class="input-group-text selfservice-background grey"><strong>Title</strong></div>
                    </div>
                    <input type="text" class="form-control" id="userTitle" name="title"
                           value="<?= $userinfo["title"] ?>" placeholder="Title">
                  </div>
                </div>
                <div class="col">
                  <div class="input-group mb-2">
                    <div class="input-group-prepend">
                      <div class="input-group-text selfservice-background grey"><strong>Department</strong></div>
                    </div>
                    <input type="text" class="form-control" id="userDepartment" name="department"
                           value="<?= $userinfo["department"] ?>" placeholder="Department">
                  </div>
                </div>
                <div class="col">
                  <div class="input-group mb-2">
                    <div class="input-group-prepend">
                      <div class="input-group-text selfservice-background grey"><strong>Room</strong></div>
                    </div>
                    <input type="text" class="form-control" id="userRoom" name="physicaldeliveryofficename"
                           value="<?= $userinfo["physicaldeliveryofficename"] ?>" placeholder="Room">
                  </div>
                </div>
              </div>

              <!-- Phone, Mobile Phone -->
              <div class="form-row">
                <div class="col-4">
                  <div class="input-group mb-2">
                    <div class="input-group-prepend">
                      <div class="input-group-text selfservice-background grey"><strong>Office Phone</strong></div>
                    </div>
                    <input type="text" class="form-control readonly-until-onfocus" id="userPhone" name="telephonenumber"
                           value="<?= $userinfo["telephonenumber"] ?>" placeholder="Office Phone Number" readonly>
                  </div>
                </div>
                <div class="col-4">
                  <div class="input-group mb-2">
                    <div class="input-group-prepend">
                      <div class="input-group-text selfservice-background grey"><strong>Mobile Phone</strong></div>
                    </div>
                    <input type="text" class="form-control readonly-until-onfocus" id="userMobile" name="mobile"
                           value="<?= $userinfo["mobile"] ?>" placeholder="Mobile Phone Number" readonly>
                  </div>
                </div>
              </div>
            </div>
          </nav>

          <br/><br/>

          <!-- Password change -->
          <div class="card bg-light">
            <div class="card-header">
              <span class="h5">Change Password</span>
            </div>
            <div class="card-body">
              <h6 class="card-subtitle mb-2 text-muted">Please fill out all three fields to change the password!</h6>

              <div class="form-row">
                <div class="col-5">
                  <div class="input-group mb-2">
                    <div class="input-group-prepend">
                      <div class="input-group-text selfservice-background grey"><strong>old password</strong></div>
                    </div>
                    <input type="password" class="form-control readonly-until-onfocus" id="oldPassword"
                           name="oldPassword" placeholder="old Password" readonly>
                  </div>
                </div>
              </div>
              <div class="form-row">
                <div class="col-5">
                  <div class="input-group mb-2">
                    <div class="input-group-prepend">
                      <div class="input-group-text selfservice-background grey"><strong>new password</strong></div>
                    </div>
                    <input type="password" class="form-control readonly-until-onfocus" id="newPassword"
                           name="newPassword" aria-describedby="passwordHelpBlock" placeholder="new Password" readonly>
                    <small id="passwordHelpBlock" class="form-text text-muted">
                      Read the Password Policy page on Confluence :)
                    </small>
                  </div>
                </div>
                <div class="col-7">
                  <div class="input-group mb-2">
                    <div class="input-group-prepend">
                      <div class="input-group-text selfservice-background grey"><strong>repeat new password</strong></div>
                    </div>
                    <input type="password" class="form-control readonly-until-onfocus" id="newPassword2"
                           name="newPassword2" placeholder="new Password" readonly>
                  </div>
                </div>
              </div>

              <!-- Message Box for Password Validation -->
              <?php
                // don't include the checker for Admin-Accounts (they have other policies and should be able to set
                // valid passwords ;)
                if (!$additionalInfo["adminAccount"]) {
                  include(PATH_STYLE . "includes" . DS . "passwordPolicyChecker.inc.php");
                }
              ?>

              <br/>

              <!-- Password info -->
              <div class="form-row">
                <div class="col-5">
                  <div class="input-group mb-2">
                    <div class="input-group-prepend">
                      <div class="input-group-text selfservice-background grey"><strong>Last password-change</strong>
                      </div>
                    </div>
                    <input type="text" class="form-control" value="<?= $additionalInfo["passwordLastSet"] ?>" readonly>
                  </div>
                </div>
                <div class="col-7">
                  <div class="input-group mb-2">
                    <div class="input-group-prepend">
                      <div class="input-group-text selfservice-background grey"><strong>Password expires at</strong></div>
                    </div>
                    <input type="text" class="form-control" value="<?= $additionalInfo["passwordExpires"] ?>" readonly>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- detailed information section, collapsed by default -->
          <div class="card collapse" id="detailedInformation" style="margin-top: 50px;">
            <div class="card-header">
              <span class="h4">Additional user information/ settings</span>
            </div>
            <div class="card-body">
              <!-- SSH KEY -->
              <div class="card bg-light">
                <div class="card-header">
                  <span class="h5">SSH-Key</span>
                </div>
                <div class="card-body">
                  <div class="form-row">
                    <div class="col-12">
                      <div class="input-group mb-2">
                        <div class="input-group-prepend">
                          <div class="input-group-text selfservice-background grey"><strong>SSH-KEY</strong></div>
                        </div>
                        <textarea class="form-control" name="sshpublickey" rows="3" id="sshpublickey"
                                  placeholder="insert your ssh key here..."><?= $userinfo["sshpublickey"] ?></textarea>
                      </div>
                    </div>
                  </div>
                  <!-- Message Box for Password Validation -->
                  <?php
                  // don't include the checker for Admin-Accounts (they have other policies and should be able to set
                  // valid passwords ;)
                  include(PATH_STYLE . "includes" . DS . "sshKeyChecker.inc.php");
                  ?>
                </div>
              </div>

              <br/><br/>

              <!-- AD-Groups -->
              <div class="card">
                <div class="card-header">
                  <span class="h5">Your AD-Groups</span>
                </div>
                <div class="card-body">
                  <?php
                    foreach ($additionalInfo["groups"] as $groupName) {
                      echo "<span class='groupName'>$groupName</span> ";
                    }
                  ?>
                  <hr />
                  <span class="font-weight-light">nested ones (groups that your groups are part of):</span><br />
                  <?php
                    foreach ($additionalInfo["groupsNested"] as $groupName) {
                      echo "<span class='groupName'>$groupName</span> ";
                    }
                  ?>
                </div>
              </div>
            </div>
          </div>

          <br/><br/>

          <button type="submit" class="btn btn-primary mb-2 float-right">Save</button>
        </form>

        <!-- Expand link / button for detailed information -->
        <a class="btn btn-secondary" data-toggle="collapse" href="#detailedInformation" role="button" aria-expanded="false"
                        aria-controls="collapseExample" id="detailedInformationToggleButton">
          Show Advanced Settings</span>
        </a>
      </div>
    </div>

    <br/><br/><br/>


    <?php
  }
  include(PATH_STYLE . "foot.inc.php") ?>
