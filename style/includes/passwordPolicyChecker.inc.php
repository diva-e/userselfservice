<style type="text/css">
  #message {
    display: none;
    font-size: 18px;
  }

  /* Add a green text color and a checkmark when the requirements are right */
  .valid {
    color: #317b20;
  }

  .valid:before {
    position: relative;
    left: -5px;
    content: "\01F60D";
  }

  /* Add a red text color and an "x" when the requirements are wrong */
  .invalid {
    color: #a6000c;
  }

  .invalid:before {
    position: relative;
    left: -5px;
    content: "\01F621";
  }

  .no-bullet-points {
    list-style-type: none;
  }

  .li-space-bottom {
    margin: 0 0 10px 0;
  }

  #passwordCheckList {
    padding-left: 0;
  }
</style>

<div class="alert alert-info" id="message">
  <h5>Password Policy - for further information check the confluence page!</h5>
  <div class="container">
    <div class="row">
      <div class="col-sm">
        <ul class="no-bullet-points" id="passwordCheckList">
          <li id="length" class="invalid li-space-bottom">Minimum <b>8 characters</b></li>
          <li id="categoriesCheck" class="invalid"><strong>Three</strong> of the following <strong>four
              categories</strong>:
          </li>
          <ul class="no-bullet-points">
            <li id="letter" class="invalid">English <strong>lowercase</strong> characters (a-z)</li>
            <li id="capital" class="invalid">English <strong>capital (uppercase)</strong> characters (A-Z)</li>
            <li id="number" class="invalid"><strong>Number</strong> (0-9)</li>
            <li id="special" class="invalid li-space-bottom"><strong>Non-Alphabetic special</strong> character (!,
              $, #, %, ...)
            </li>
          </ul>
          <li id="newPasswordsEqual" class="invalid">Both <strong>new passwords</strong> must
            <strong>equal</strong>!
          </li>
        </ul>
      </div>
      <div class="col-sm">
        <strong>Be sure to meet the following requirements:</strong>
        <ul>
          <li>Not contain the user's account name or parts of the user's full name that exceed two consecutive
            characters!
          </li>
          <li>No reuse of the last 6 old passwords!</li>
          <li>Only one password change per day!</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<script>
  var newPassword = document.getElementById("newPassword");
  var newPassword2 = document.getElementById("newPassword2");
  var letter = document.getElementById("letter");
  var capital = document.getElementById("capital");
  var number = document.getElementById("number");
  var length = document.getElementById("length");
  var special = document.getElementById("special");
  var categoriesCheck = document.getElementById("categoriesCheck");
  var newPasswordsEqual = document.getElementById("newPasswordsEqual");

  // When the user clicks on one of the new-password fields, show the message box
  newPassword.addEventListener('focus', function() {
    document.getElementById("message").style.display = "block";
  });

  newPassword2.addEventListener('focus', function() {
    document.getElementById("message").style.display = "block";
  });

  // When the user clicks outside of the new-password fields, hide the message box
  newPassword.addEventListener('blur', function() {
    document.getElementById("message").style.display = "none";
  });
  newPassword2.addEventListener('blur', function() {
    document.getElementById("message").style.display = "none";
  });

  // When the user starts to type something inside the password field
  newPassword.onkeyup = function() {
    let count = 0;

    // Validate lowercase letters
    var lowerCaseLetters = /[a-z]/g;
    if (newPassword.value.match(lowerCaseLetters)) {
      letter.classList.remove("invalid");
      letter.classList.add("valid");
      count++;
    } else {
      letter.classList.remove("valid");
      letter.classList.add("invalid");
    }

    // Validate capital letters
    var upperCaseLetters = /[A-Z]/g;
    if (newPassword.value.match(upperCaseLetters)) {
      capital.classList.remove("invalid");
      capital.classList.add("valid");
      count++;
    } else {
      capital.classList.remove("valid");
      capital.classList.add("invalid");
    }

    // Validate numbers
    var numbers = /[0-9]/g;
    if (newPassword.value.match(numbers)) {
      number.classList.remove("invalid");
      number.classList.add("valid");
      count++;
    } else {
      number.classList.remove("valid");
      number.classList.add("invalid");
    }

    // Validate length
    if(newPassword.value.length >= 8) {
      length.classList.remove("invalid");
      length.classList.add("valid");
    } else {
      length.classList.remove("valid");
      length.classList.add("invalid");
    }

    // validate special charactes
    var specialChars = /[0-9]/g;
    //var regex = /\[!@#\$%\^\&*\)\(+=._-\]+$/g;
    var regex = /[!@#$%^&*(),.?":{}|<>§\/=\[\]\\\`\´\+\~'\-;]/g;
    if (newPassword.value.match(regex)) {
      special.classList.remove("invalid");
      special.classList.add("valid");
      count++;
    } else {
      special.classList.remove("valid");
      special.classList.add("invalid");
    }

    // validate categories (three out of four)
    if (count >= 3) {
      categoriesCheck.classList.remove("invalid");
      categoriesCheck.classList.add("valid");
    } else {
      categoriesCheck.classList.remove("valid");
      categoriesCheck.classList.add("invalid");
    }

    if (newPassword.value !== "" && newPassword.value === newPassword2.value) {
      newPasswordsEqual.classList.remove("invalid");
      newPasswordsEqual.classList.add("valid");
    } else {
      newPasswordsEqual.classList.remove("valid");
      newPasswordsEqual.classList.add("invalid");
    }
  }

  newPassword2.onkeyup = function() {
    if (newPassword.value !== "" && newPassword.value === newPassword2.value) {
      newPasswordsEqual.classList.remove("invalid");
      newPasswordsEqual.classList.add("valid");
    } else {
      newPasswordsEqual.classList.remove("valid");
      newPasswordsEqual.classList.add("invalid");
    }
  }
</script>