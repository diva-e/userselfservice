<?php
/**
 * Authenticate User. Redirect to login-page if {@code $forceLogin} is true.
 *
 * @param bool $forceLogin  if it should be redirected to login-page (if user is not logged in)
 * @return bool             if user is authenticated (= logged in)
 */
function authUser($forceLogin = true)
{
    $timeout = 60 * 15;
    global $msg;

    if (!empty($_SESSION["username"])) {
        // Auto logout after timeout
        if (time() > ($_SESSION["lastAction"] + $timeout)) {
            $_SESSION = array();
            session_destroy();
            $msg["warning"][] = "You have been logged out due to inactivity.";
        } else {
            // user is logged in -> increase last Action time
            $_SESSION["lastAction"] = time();
            return true;
        }
    }

    // redirect to login page if login should be forced
    if ($forceLogin) {
        $from = "";
        // if user came from specific page set from-attribute to redirect back to this page after login again
        if (isset($_GET["p"])) {
            $from = "&from=" . $_GET["p"];
        }

        header("Location: https://{$_SERVER['SERVER_NAME']}/?p=login" . $from);
        die();
    }

    return false;
}

// log user out
if (isset($_GET['logout'])) {
    // destroy the sessions
    $_SESSION = array();
    session_destroy();

    $msg["info"][] = "You have been successfully logged out.";

    // if user came from specific page redirect him back to this page after logout
    if (isset($_GET["from"])) {
        header("Location: https://{$_SERVER['SERVER_NAME']}/?p=" . $_GET["from"]);
    }

    return false;
}
?>