<?php

  $html_title = "Contacts";
  $html_head_inc = <<<HTML
    <link rel="stylesheet" type="text/css" href="style/dynatable/jquery.dynatable.css"/>
    <script type="text/javascript" src="style/dynatable/jquery.dynatable.js"></script>
    <style type="text/css">
      @media print {
        a[href]:after {
          content: none !important;
        }
        td {
          font-size:8pt;
        }
      }
    </style>

    <script type="text/javascript">

      function validateEmail(email) {
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
      }

      var ia_abook = function(TableID,DBName,TableView){
        var _self = this
        _self.searchdelay = (function(){
          var timer = 0;
          return function(callback, ms){
          clearTimeout (timer);
          timer = setTimeout(callback, ms);
          };
        })();
          //Create dynatable and bind event
          $("#"+TableID).bind('dynatable:preinit',
            //Hook up and change some
            function(e, dynatable) {
                dynatable.utility.textTransform.myNewStyle = function(text) {
                    return text;
                };
            }).bind('dynatable:afterProcess',
              //change search type for better bootstrap support
              function(e, data) {
                $("#dynatable-query-search-"+TableID+"")[0].type = "text";
                $("#dynatable-query-search-"+TableID+"").keyup(function(e){
                  if(e != 13){
                     _self.searchdelay(function(){
                      var e = jQuery.Event("keypress");
                      e.which = 13; //choose the one you want
                      e.keyCode = 13;
                      $("#dynatable-query-search-"+TableID+"").trigger(e);
                    }, 500 );
                  }
                  });
                $("#dynatable-search-"+TableID+"").addClass("hidden-print");

            }).dynatable({
              features: {
                paginate: false,
                sort: true,
                pushState: true,
                search: true,
                recordCount: true,
                perPageSelect: false
              },
              table: {
                defaultColumnIdStyle: 'myNewStyle'
              }

            });
      }

      $(document).ready(function() {
        //init user table
        var usertable = ia_abook("addressbook","user","default");
      });
    </script>
HTML;

  include(PATH_STYLE . "head.inc.php");

  /** Add mailto: link if attribute is an email-address. */
  function printAttr($attr) {
    if (filter_var($attr, FILTER_VALIDATE_EMAIL)) {
      $attr = '<a href="mailto:' . $attr . '">' . $attr . '</a>';
    }
    echo $attr;
  }
?>

  <div class="clearfix">
    <h3 class="float-left">Contact-List - <?= $view["name"] ?></h3>
    <h3 class="float-right"><?= $settings["phoneCentral"][0] ?></h3>
  </div>

  <br />

  <table class="table table-hover table-striped table-bordered table-sm" id="addressbook" cellpadding="0"
         cellspacing="0" style="margin-top:10px;">
    <thead>
    <tr>
      <?php foreach ($view["attr"] as $i => $name) {
        ?>
        <th><?= translateAttr($name) ?></th>
        <?php
      }
      ?>
      <?php if (!empty($_SESSION["admin"])) { ?>
        <th>🖊</th>
      <?php } ?>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($list as $i => $entry) { ?>
      <tr>
        <?php foreach ($view["attr"] as $i => $name) { ?>
          <td><?php printAttr($entry[$name]) ?></td><?php } ?>
        <?php if (!empty($_SESSION["admin"])) { ?>
          <td>
            <?php if (!empty($entry["samaccountname"])) { ?>
              <a class="btn-sm btn-warning" role="button" href="?p=user&amp;userid=<?= $entry["samaccountname"] ?>">🖊</a>
            <?php } ?>
          </td>
        <?php } ?>
      </tr>

    <?php } ?>


    </tbody>
  </table>


<?php include(PATH_STYLE . "foot.inc.php") ?>
