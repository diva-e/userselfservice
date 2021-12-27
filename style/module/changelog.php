<?php

$html_title = "Selfservice - Changelog";

$html_head_inc = "";
include(PATH_STYLE."head.inc.php");

?>

  <div class="card">
      <div class="card-body">
        <?php
            if (isset($output)) {
                echo $output;
            }
        ?>
      </div>
  </div>

<?php
include(PATH_STYLE."foot.inc.php")?>
