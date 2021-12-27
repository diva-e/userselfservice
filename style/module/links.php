<?php

  $html_title = "Selfservice - Linklist";

//make the cards to appear next to each other
  $html_head_inc = <<<_TEXT_
<style type='text/css'>
.card {
    display:inline-block;
    margin-bottom: 5px;
 }
</style>
_TEXT_;

  include(PATH_STYLE . "head.inc.php");

?>

  <h1>Linklist</h1>

  <br/>

  <div class="alert alert-info alert-dismissible fade show" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
    Give Office IT a hint if you miss a link 😉
  </div>

  <nav class="card bg-light align-top" style="width: 18rem;">
    <div class="card-header">
      <span class="h5">General</span>
    </div>
    <div class="card-body">
      <ul>
        <li><a target="_blank" href="https://FIXME.com/">FIXME</a></li>
      </ul>
    </div>
  </nav>

  <nav class="card bg-light align-top" style="width: 18rem;">
    <div class="card-header">
      <span class="h5">Developer</span>
    </div>
    <div class="card-body">
      <ul>
        <li><a target="_blank" href="https://FIXME.com/">FIXME</a></li>
        <li><a target="_blank" href="https://FIXME.com/">FIXME</a></li>
      </ul>
    </div>
  </nav>


<?php
  include(PATH_STYLE . "foot.inc.php") ?>
