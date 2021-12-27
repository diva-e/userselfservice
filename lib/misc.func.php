<?php
/**
 * Miscellaneous functions.
 *
 */


// constants for password expiry
const PASSWORD_VALIDITY_NORMAL = 60 * 60 * 24 * 180;
const PASSWORD_VALIDITY_ADMIN =  60 * 60 * 24 * 95;
// constants for windows -> unix timestamp conversion
const WINDOWS_TICK = 10000000;
const SEC_TO_UNIX_EPOCH = 11644473600;


/**
 * Generate random string with specified length.
 *
 * @param int $length   of the random string
 * @return string       random string
 */
function generateRandomString($length = 16)
{
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ./';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}

/**
 * Converts given Windows-Timestamp to Unix-Timestamp.
 *
 * @param int $timestamp   windows timestamp
 * @return float|int       unix timestamp
 *
 * @see https://stackoverflow.com/questions/6161776/convert-windows-filetime-to-second-in-unix-linux
 */
function convertWindowsTimestampToUnix($timestamp)
{
    return floor(($timestamp / (WINDOWS_TICK)) - SEC_TO_UNIX_EPOCH);
}

/**
 * Calculate password expiry timestamp for given last-changed timestamp. Depending on account type different expiry
 * times are being calculated.
 *
 * @param int $lastChanged      timestamp of last password change
 * @param bool $adminAccount    if its admin account or not
 * @return float|int            timestamp of expiry date (and time)
 */
function passwordExpiryTimestamp($lastChanged = 0, $adminAccount = false) {
    return $adminAccount ? ($lastChanged + PASSWORD_VALIDITY_ADMIN) : ($lastChanged + PASSWORD_VALIDITY_NORMAL);
}

/**
 * Check if a user is an Admin. Admin means to be placed in the Admins
 * folder in the Active Directory.
 *
 * @param string $userDn  Distinguished name of user-object
 * @return bool           if user is an Admin
 */
function isUserAdmin($userDn)
{
    $userDnArray = explode(",", $userDn);
    return $userDnArray[1] === "OU=Admins" && $userDnArray[2] === "OU=FIXME";
}




/**
 * Encodes the data to a json string
 *
 * @param mixed Value to enocde
 * @return string json encoded data
*/
function json_encode_data($data)
{
	if (strnatcmp(phpversion(),'5.3.0') >= 0)
		return json_encode($data,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
	else
		return json_encode($data);
}

/**
 * Decodes the string into a assoziative array
 *
 * @param string json encoded data
 * @return mixed
*/
function json_decode_data($str)
{
	$val = json_decode($str,true);
	/* If something went wrong with the string return false, decryption failed */
 	if (json_last_error() != JSON_ERROR_NONE) {
 	    //retry with utf8 encode
 		$val = json_decode(utf8_encode($str),true);
		if(json_last_error() != JSON_ERROR_NONE) return false;
 	}
 	return $val;
}

/**
 * Prints a compatible json string
 *
 * @param mixed Value to enocde
 * @return bool
 */
function json_print($data)
{
	echo json_encode_data($data);
	return true;
}

/**
 * Prints a compatible jsonp string
 *
 * @param mixed Value to enocde
 * @return bool
 */
function jsonp_print($data)
{
	header("content-type: application/javascript");
	if(!isset($_GET['callback'])) $_GET['callback'] = '';
	echo $_GET['callback']. '('. json_encode_data($data).')';
	return true;
}

function object2array($object)
{
    return @json_decode(@json_encode($object),1);
}


?>
