<!DOCTYPE html>

<html lang="en">

<head>
  <title>Password Reminder</title>
  <style type="text/css">
    #content {
      /*padding: 10px; padding not working in outlook 2010 properly */
      background-color: #ededed;
      border: 3px solid #A4AF29;
      border-radius: 3px;
      text-align: center;
      font-family: Helvetica;
    }

    #content a {
      color: tomato;
      text-decoration: none;
    }
  </style>
</head>

<body>
  <div id="content">
    <br />
    <img  alt="Logo" src="cid:favicon" />
    <br />
    <br />
    <p>Good Morning <?php echo $fullName; ?>,</p>
    <h1>Your password for <span style="color: #A4AF29"><?php echo $adAccountName; ?></span> is going to expire in <?php echo $passwordExpiryDays; ?> days!</h1>
    <p style="font-size: 20px;">
      Please <strong>don't forget to change</strong> your password <span style="font-size: 28px;">
        <a href="https://FIXME.com/?p=user" target="_blank">right here in the Selfservice</a></span>!
    </p>
    <p>
      If you don't change your password within the next days and it expires, you will <strong>not be able to log in to
      any service</strong> where you are using your Active-Directory Credentials (<?php echo $adAccountName ?>).
      <br /><br />
      <strong>Best would be to immediately change your password!</strong>
    </p>
    <br />
  </div>
</body>
</html>
