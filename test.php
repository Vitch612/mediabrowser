<?php
ini_set('max_execution_time', 0);
ob_implicit_flush(true);
include "include.php";
include "head.php";
show_nav();
echo '<div class="row box"><div class="col-xs-12">';
echo '<input type="button" class="btn btn-primary" value="Open" data-toggle="modal" data-target="#exampleModal">';
echo '
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h5 class="modal-title" id="exampleModalLabel">Choose Playlist</h5>
      </div>
      <div class="modal-body">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Ok</button>
      </div>
    </div>
  </div>
</div>';

echo '</div></div></div></body></html>';
