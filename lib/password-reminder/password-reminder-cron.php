<?php
require_once("../../vendor/autoload.php");
require_once("../misc.func.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


//--------------------------------------------
//--------------- CONSTANTS ------------------
//--------------------------------------------
const REMINDER_DAYS = 14;
const SMTP_RELAY_HOST = "smtp-relay.FIXME.com";
const FROM_MAIL = "selfservice@FIXME.com";
const FROM_NAME = "Password Expiry Service";
const REPLY_TO_MAIL = "selfservice@FIXME.com";
const REPLY_TO_NAME = "Selfservice";


// for output with browser
header('Content-type: text/plain');

echo "\n\n\n~~~ RUNNING PASSWORD-REMINDER-CRON JOB ~~~\n";

//configuration for AD-Connection (with Admin-Account)
$AdConfig = [
    // Mandatory Configuration Options
    'hosts'          => explode(",", getenv("LDAP_SELFSERVICE_SERVER")),
    'base_dn'        => 'dc=FIXME,dc=FIXME,dc=com',
    'username'       => strval(getenv("LDAP_SELFSERVICE_USER")),
    'password'       => strval(getenv("LDAP_SELFSERVICE_PASSWORD")),
    'account_suffix' => '@FIXME.com',

    // Optional Configuration Options
    'schema'           => Adldap\Schemas\ActiveDirectory::class,
    'port'             => 636,
    'follow_referrals' => false,
    'use_ssl'          => true,
    'use_tls'          => true,
    'version'          => 3,
    'timeout'          => 5
];
$AdConnectionName = "connection-one";

$ad = new \Adldap\Adldap();
$ad->addProvider($AdConfig, $AdConnectionName);
$provider = $ad->connect($AdConnectionName);

$users = $provider->search()->setBaseDn("OU=FIXME,OU=FIXME,DC=FIXME,DC=FIXME,DC=com")->get();
$admins = $provider->search()->setBaseDn("OU=FIXME,OU=FIXME,DC=FIXME,DC=FIXME,DC=com")->get();

// combine users and admins in one collection
$collection = new \Illuminate\Support\Collection;
$collection = $users->concat($admins);

foreach ($collection as $key => $user) {
    // Skip non-user objects
    if (!$user instanceof \Adldap\Models\User) {
        continue;
    }

    // check if user is an admin (-> different password expiry)
    $adminAccount = isUserAdmin($user->getDistinguishedName());

    $userLastPasswordSet = convertWindowsTimestampToUnix($user->getFirstAttribute("pwdLastSet"));
    $passwordExpiry = passwordExpiryTimestamp(intval($userLastPasswordSet), $adminAccount);

    $today = new DateTime(); // This object represents current date/time
    $today->setTimezone(new DateTimeZone("Europe/Berlin"));
    $today->setTime(0, 0, 0); // reset time part, to prevent partial comparison

    $pwExpiryDate = new DateTime("@{$passwordExpiry}");
    $pwExpiryDate->setTimezone(new DateTimeZone("Europe/Berlin"));
    $pwExpiryDate->setTime(0, 0, 0); // reset time part, to prevent partial comparison

    $diff = $today->diff($pwExpiryDate);
    $diffDays = (integer)$diff->format("%R%a"); // Extract days count in interval


    $userMail = $user->getFirstAttribute("mail");

    $log = "\n" . $user->getFirstAttribute("cn") . ", " . $userMail . ": expires in: " . $diffDays . ", "
        . "last-set-timestamp: " . $userLastPasswordSet . " ("
        . date("d.m.Y H:i:s", $userLastPasswordSet) . "), "
        . "password-expiry-timestamp: " . $passwordExpiry . " ("
        . date("d.m.Y H:i:s", $passwordExpiry) . "); ";

    // Skip user when no reminder has to be sent
    if (!($diffDays <= REMINDER_DAYS && $diffDays > 0)) {
        echo $log;
        continue;
    }
    // Skip user when no email address is available
    if (!isset($userMail)) {
        $log .= "!NO EMAIL SET, BUT REMINDER SHOULD BE SENT!";
        echo $log;
        continue;
    }

    $log .= "!REMINDER!";

    // replace german umlauts for html mail
    $fullName = $user->getFirstAttribute("cn");
    $fullName = str_replace("ä", "&auml;", $fullName);
    $fullName = str_replace("ö", "&ouml;", $fullName);
    $fullName = str_replace("ü", "&uuml;", $fullName);

    $template = new MailTemplateHandler("email-template.php");
    $template->set('fullName', $fullName);
    $template->set('passwordExpiryDays', $diffDays);
    $template->set('adAccountName', $user->getFirstAttribute("samaccountname"));

    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = 0;
        // Use SMTP
        $mail->isSMTP();
        $mail->Host = SMTP_RELAY_HOST;
        $mail->SMTPAuth = false;

        $mail->setFrom(FROM_MAIL, FROM_NAME);
        $mail->addReplyTo(REPLY_TO_MAIL, REPLY_TO_NAME);

        // add recipient for mail depending on target branch
        if (getenv("TARGET_BRANCH") !== "master") {
            // in "develop"-mode send mails to selfservice user
            $mail->addAddress('selfservice@FIXME.com', 'selfservice');
        } else {
            // otherwise (master) send mails to corresponding user
            $mail->addAddress($userMail, $user->getFirstAttribute("cn"));
        }

        // set high priority for the message
        $mail->Priority = 1;
        // set custom headers for high priority (Outlook)
        $mail->AddCustomHeader("X-MSMail-Priority: High");
        $mail->AddCustomHeader("Importance: High");

        // add favicon as embedded image to prevent outlook from blocking external image
        $mail->AddEmbeddedImage('../../style/favicon.png', 'favicon', 'favicon.png');

        // Content, email format is html
        $mail->isHTML(true);
        $mail->Subject = "Password for " . $user->getFirstAttribute("samaccountname") . " is going to expire soon!";
        $mail->Body = $template->render();
        $mail->AltBody = "Password for " . $user->getFirstAttribute("samaccountname")
            . "expires in " . $diffDays . ", please change it!";

        $mail->send();
    } catch (Exception $e) {
        $log .= " EMail couldn't be sent! Mailer Error: " . $mail->ErrorInfo;
    }

    echo $log;
}

echo "\n\n~~~ PASSWORD-REMINDER-CRON JOB FINISHED ~~~";


class MailTemplateHandler
{
    protected $_file;
    protected $_data = array();

    public function __construct($file = null)
    {
        $this->_file = $file;
    }

    public function set($key, $value)
    {
        $this->_data[$key] = $value;
        return $this;
    }

    public function render()
    {
        extract($this->_data);
        ob_start();
        include($this->_file);
        return ob_get_clean();
    }
}

?>
