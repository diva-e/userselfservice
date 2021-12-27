<?php
define("DS", DIRECTORY_SEPARATOR);
define("PATH", dirname(__FILE__) . DS);
define("PATH_STYLE", PATH . 'style' . DS);

session_start();

require_once(PATH . "vendor" . DS . "autoload.php");
require_once(PATH . "lib" . DS . "misc.func.php");
require_once(PATH . 'lib' . DS . 'auth.func.php');

global $AdConfig, $AdConnectionName, $AdOptions, $settings, $Views, $msg, $navigationActiveSite;

//initial array for alter-messages
$msg = array("success" => array(), "info" => array(), "warning" => array(), "danger" => array());

//configuration for AD-Connection (with Admin-Account)
$AdConfig = [
    // Mandatory Configuration Options
    'hosts' => explode(",", getenv("SAMBA_SELFSERVICE_SERVER")),
    'base_dn' => 'dc=FIXME,dc=FIXME,dc=com',
    'username' => strval(getenv("SAMBA_SELFSERVICE_USER")),
    'password' => strval(getenv("SAMBA_SELFSERVICE_PASSWORD")),
    'account_suffix' => '@FIXME.com',

    // Optional Configuration Options
    'schema' => Adldap\Schemas\ActiveDirectory::class,
    'port' => 636,
    'follow_referrals' => false,
    'use_ssl' => true,
    'use_tls' => true,
    'version' => 3,
    'timeout' => 5,
];
$AdConnectionName = "connection-one";

//OLD
// use ENV vars ;-)
$AdOptions = array(
    "account_suffix" => "@FIXME.com",
    "account_prefix" => "FIXME\\",
    "base_dn" => "DC=FIXME,DC=FIXME,DC=com",
    "base_dn_user_page" => "dc=FIXME,dc=FIXME,dc=com",
    "adminGroup" => "AdminAccounts"
);

$settings = array(
    "phoneCentral" => array("+12 34 1234567"),
    "groups" => array("admin" => "userselfservice-admins"),
    "address" => array(
        array(
            "street" => "Teststreet. 1a",
            "plz" => "12345",
            "city" => "Testcity",
            "state" => "Teststate"
        )
    )
);

//parsed in Frontend
$AttrTranslate = array(
    "name" => "Username",
    "displayname" => "Name",
    "telephoneNumber" => "Tel.",
    "officephoneextension" => "Tel. DW",
    "givenname" => "Vorname",
    "pwdlastset" => "Password Last Set",
    "department" => "Abteilung",
    "homephone" => "Tel. Privat",
    "title" => "Titel",
    "faxextension" => "Fax DW",
    "facsimiletelephonenumber" => "Fax",
    "mail" => "E-Mail",
    "mobile" => "Handy",
    "sn" => "Nachname",
    "manager" => "Vorgesetzer",
    "description" => "Beschreibung"
);

//Magic Attributes, FaxExtension and OfficePhoneExtension, needs facsimileTelephoneNumber or telephoneNumber as well in attr or attrhidden
$Views[] = array(
    "name" => "Standard",
    //Displayname
    "searchbase" => array(
        array("OU" => "OU=Users,OU=FIXME", "type" => array("user", "contact")),
    ),
    //array
    "attr" => array("name", "displayname", "department", "telephonenumber", "pwdlastset"),
    //Attr lowercased
    "sortby" => "name",
    "attr_hidden" => array(
        "samaccountname",
        "telephonenumber",
        "title",
        "department",
        "givenname",
        "sn",
        "facsimiletelephonenumber",
        "faxextension",
        "homephone"
    )
    //Hidden Attributes
);
$Views[] = array(
    "name" => "Expanded",
    //Displayname
    "searchbase" => array(
        array("OU" => "OU=Users,OU=FIXME", "type" => array("user", "contact")),
    ),
    //array
    "attr" => array(
        "givenname",
        "sn",
        "title",
        "department",
        "pwdlastset",
        "officephoneextension",
        "mobile",
        "mail"
    ),
    //Attr lowercased
    "sortby" => "name",
    "attr_hidden" => array("samaccountname", "telephonenumber", "faxextension", "facsimiletelephonenumber", "homephone")
    //Hidden Attributes
);

$Views[] = array(
    "name" => "Rooms",
    //Displayname
    "searchbase" => array(
        array("OU" => "OU=Rooms,OU=MailRessources,OU=FIXME", "type" => array("ressource"))
    ),
    //array
    "attr" => array("displayname", "description", "physicaldeliveryofficename", "officephoneextension", "mail"),
    //Attr lowercased
    "sortby" => "displayname",
    "attr_hidden" => array("telephonenumber", "faxextension", "facsimiletelephonenumber", "homephone")
    //Hidden Attributes
);

$Views[] = array(
    "name" => "Distribution Groups + Team Mailboxes", //Displayname
    "searchbase" => array(
        array("OU" => "OU=MailRessources,OU=FIXME", "type" => "group")
    ), //array
    "attr" => array("displayname", "description", "mail"), //Attr lowercased
    "sortby" => "displayname",
    "attr_hidden" => array() //Hidden Attributes
);




// list page as standard page
if (empty($_GET["p"])) {
    $_GET["p"] = "user";
}

// array with all available sites -> the active one has "active" as value, used in navigation bar to indicate active site
$navigationActiveSite = [
    "changelog" => "",
    "links" => "",
    "login" => "",
    "user" => "",
    "list" => ""
];

$navigationActiveSite[$_GET["p"]] = "active";

// Load required page
switch ($_GET["p"]) {
    case "changelog":
        require_once("module" . DS . "changelog.php");
        break;
    case "links":
        require_once("module" . DS . "links.php");
        break;
    case "list":
        require_once("module" . DS . "list.php");
        break;
    case "login":
        require_once("module" . DS . "login.php");
        break;
    case "user":
        require_once("module" . DS . "user.php");
        break;
    default:
        require_once("module" . DS . "user.php");
        break;
}
