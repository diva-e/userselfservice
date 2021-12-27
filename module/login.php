<?php
  // load global vars
  global $AdConfig, $AdConnectionName, $AdOptions, $msg, $settings;

  // check for login-status
  $auth = authUser(false);

  // redirect to root page if user is already logged in
  if ($auth) {
    header("Location: http://{$_SERVER['SERVER_NAME']}/");
    die();
  }

  if (!empty($_POST["ldapUser"]) && !empty($_POST["ldapPassword"])) {
    $ad = new \Adldap\Adldap();
    $ad->addProvider($AdConfig, $AdConnectionName);

    $username = strtoupper($_POST["ldapUser"]);
    $password = $_POST["ldapPassword"];

    try {
      // try to connect with user credentials
      $provider = $ad->connect($AdConnectionName, ($AdOptions["account_prefix"] . $username), $password);

      $_SESSION["username"] = $username;
      $_SESSION["lastAction"] = time();

      // check if user is in admin group for selfservice
      $_SESSION["admin"] = false;
      $adminGroupMembers = $provider->search()->groups()->find($settings["groups"]["admin"])->getMembers();
      foreach ($adminGroupMembers as $member) {
        $_SESSION["admin"] = $_SESSION["admin"] || (strtolower($username) == $member->getFirstAttribute("samaccountname"));
      }

      // After successful login redirect to GET-variable "from" if it exists and to the root page otherwise
      if (isset($_GET["from"])) {
        header("Location: https://{$_SERVER['SERVER_NAME']}/?p=" . $_GET["from"]);
        die();
      } else {
        header("Location: https://{$_SERVER['SERVER_NAME']}/");
        die();
      }
    } catch (\Adldap\Auth\BindException $e) {
      $msg["danger"][] = "Login failed, please check your username and password again.";
    }
  }

  include(PATH_STYLE . "module" . DS . "login.php");

?>
