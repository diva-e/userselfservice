<style type="text/css">

</style>

<div class="alert alert-danger collapse" id="sshKeyMessage">
    <strong>Attention!</strong><br />
    Your SSH-Key has more than one line! If you save, only the first entered line will be saved as your key!
</div>

<script>
  function countLines(value) {
    return value.split(/\r?\n|\r/).length;
  }

  var sshKeyTextarea = document.getElementById("sshpublickey");
  var sshKeyMessageBox = document.getElementById("sshKeyMessage");

  // check if ssh-key has more than one line and show warning message if so
  sshKeyTextarea.onkeyup = function() {
    if (countLines(sshKeyTextarea.value) > 1) {
      sshKeyMessageBox.classList.remove("collapse");
    } else {
      sshKeyMessageBox.classList.add("collapse");
    }
  };
</script>