<?php
global $AdOptions;

// check for login-status
$auth = authUser(true);

// exit if authentication failed
if (!$auth) {
    exit();
}



// connect to Active Directory
try {
    $ad = new \Adldap\Adldap();
    $ad->addProvider($AdConfig, $AdConnectionName);

    $provider = $ad->connect($AdConnectionName);
} catch (\Adldap\Auth\BindException $e) {
    echo $e->getMessage();
    exit();
}

// attributes that can be updated by the user
$attributes_update = array(
    "title",
    "department",
    "physicaldeliveryofficename",
    "mobile",
    "telephonenumber",
    "sshpublickey"
);
// all attributes (that will be shown)
$attributes = array_merge(array("displayname", "givenname", "sn", "mail", "samaccountname"), $attributes_update);

/*
 * Check if actual logged in user is allowed to manipulate the user.
 * Either he wan'ts to manipulate himself -> that's allowed
 * Or he wants to manipulate user with id 'userid', then he needs to be an admin.
 */
$modify_user = false;
if (!empty($_GET["userid"])) {
    if (!empty($_SESSION["admin"]) && $_SESSION["admin"]) {
        $modify_user = $_GET["userid"];
    } else {
        $msg["danger"][] = "You have not enough rights to view this page!";
    }
} else {
    $modify_user = $_SESSION["username"];
}

if ($modify_user) {
    // get user object from AD-Server
    $user = $provider->search()->setDn($AdOptions["base_dn_user_page"])->findBy('samaccountname', $modify_user);

    // check if user has been found and that it's of the right class
    if ($user != null && $user instanceof \Adldap\Models\User) {
        // update user values (if "requested" by 'userupdate')
        if (isset($_POST["userupdate"])) {

            $changed = false;

            foreach ($attributes_update as $attr) {
                // check if the attribute changed and update only if so
                if ($user->getFirstAttribute($attr) != $_POST[$attr]) {

                    // for sshkey: only take the first of (possible) multiple lines
                    if ($attr === "sshpublickey") {
                        $_POST[$attr] = (preg_split('/\n|\r/', $_POST[$attr]))[0];
                    }

                    $user->setFirstAttribute($attr, $_POST[$attr]);
                    $changed = true;
                }
            }

            // try to save the user (only if at least one attribute changed)
            if ($changed) {
                if ($user->save()) {
                    $msg["success"][] = "<strong>Master Data:</strong> Changes have been saved successfully.";
                } else {
                    $msg["danger"][] = "Problems while trying to save the data!";
                }
            }

            // try password change if old password is set (-> that implicates the user wants to change his password)
            if (!empty($_POST["oldPassword"])) {
                if (!empty($_POST["newPassword"]) && $_POST["newPassword"] == $_POST["newPassword2"]) {
                    try {
                        // try to change password
                        if (getenv("TARGET_BRANCH") !== "master") {
                            $msg["warning"][] = "<strong>Password not (!) changed in AD, because not in production-mode. PUS-Script should run anyway.</strong>";
                        } else {
                            $user->changePassword($_POST["oldPassword"], $_POST["newPassword"], false);
                            $user->save();
                        }

                        // trigger PUS to update passwords.yaml
                        triggerPUS($_POST["newPassword"], $user->getFirstAttribute("samaccountname"));

                        $msg["success"][] = "<strong>Password</strong> has been changed successfully!";
                    } catch (\Adldap\Connections\ConnectionException $e) {
                        $msg["danger"][] = "Connection-Issues while trying to change the password, probably the AD-Server is not connected via TLS.";
                    } catch (\Adldap\Models\UserPasswordIncorrectException $e) {
                        $msg["warning"][] = "Your old password is incorrect!";
                    } catch (\Adldap\Models\UserPasswordPolicyException $e) {
                        $msg["warning"][] = "Your new password does not match the password policy! Have a look at the Policy-Page on Confluence ;)";
                    } catch (\adLDAP\adLDAPException $e) {
                        $msg["danger"][] = $e;
                    }
                } else {
                    $msg["danger"][] = "<strong>Password</strong>: New password must not be empty & both passwords must equal!";
                }
            }
        }

        // get user attributes from User-Object
        foreach ($attributes as $attr) {
            if (!empty($user->getFirstAttribute($attr))) {
                $userinfo[$attr] = trim($user->getFirstAttribute($attr));
            } else {
                $userinfo[$attr] = "";
            }
        }

        $additionalInfo = array();

        // get 'cn' of user-groups
        $userGroups = $user->getGroups(["cn"], false);
        $userGroupsNested = $user->getGroups(["cn"], true);

        // initialize empty group and groupNested Arrays
        $additionalInfo["groups"] = array();
        $additionalInfo["groupsNested"] = array();

        // get group names from "not-nested" groups and check for admin account
        foreach ($userGroups as $group) {
            $additionalInfo["groups"][] = $group->getFirstAttribute("cn");
        }

        // get group names for nested groups
        foreach ($userGroupsNested as $group) {
            $additionalInfo["groupsNested"][] = $group->getFirstAttribute("cn");
        }

        // remove "not-nested" groupd (those in which the user is directly) from the nested ones
        $additionalInfo["groupsNested"] = array_diff($additionalInfo["groupsNested"], $additionalInfo["groups"]);

        // check if user is an admin (-> different password expiry)
        $additionalInfo["adminAccount"] = isUserAdmin($user->getDistinguishedName());

        // convert the windows timestamp to unix timestamp
        $userLastPasswordSet = convertWindowsTimestampToUnix($user->getFirstAttribute("pwdLastSet"));
        $additionalInfo["passwordLastSet"] = date("d.m.Y H:i:s", intval($userLastPasswordSet));
        $additionalInfo["passwordExpires"] =
            date("d.m.Y H:i:s",
                passwordExpiryTimestamp(intval($userLastPasswordSet), $additionalInfo["adminAccount"]));

        // check if password of user does never expire, if so -> adjust passwordExpires Message
        $uacObject = $user->getUserAccountControlObject();
        $additionalInfo["passwordNeverExpires"] = $uacObject->has(Adldap\Models\Attributes\AccountControl::DONT_EXPIRE_PASSWORD);

        if ($additionalInfo["passwordNeverExpires"]) {
            $additionalInfo["passwordExpires"] = "never";
        }
    } else {
        $msg["danger"][] = "$modify_user has not been found!";
    }
}

// include user style-module
include(PATH_STYLE . "module" . DS . "user.php");


/**
 * Trigger PUS-Script to update the password of the user.
 *
 * @param string $password new password
 * @param string $username user that changed the password
 */
function triggerPUS($password, $username)
{
    // SHA512
    $salt = "$6$" . generateRandomString();
    $password_hash_sha512 = preg_quote(crypt($password, $salt));

    // SHA256 HEX
    $password_hash_sha256 = preg_quote(hash("sha256", $password));

    // MYSQL
    $password_hash_mysql = preg_quote(
        strtoupper(
            sha1(sha1($password, true))
        )
    );

    $command = "/var/www/html/lib/pus/passwordChange.sh " . $username . " " . $password_hash_mysql . " "
        . $password_hash_sha256 . " " . $password_hash_sha512 . " " . time();

    // execute command and log output to log file
    $str = shell_exec($command . " 2>&1 >> /var/www/html/lib/logs/php-pw-update.log");
}

?>
