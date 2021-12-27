<?php

if(empty($_GET["view"]) || empty($Views[$_GET["view"]])) {
	$_GET["view"] = 0;
}
$view = &$Views[$_GET["view"]];

$genlist = new ListGenerator($view);

if (!empty($_GET['filter'])) {
	$genlist->setFilter($_GET['filter']);
}

$list = $genlist->get();

//include html-site or export/ return as json
if (isset($_GET['type']) && $_GET['type'] == 'json') {
	print json_encode($list);
} else {
	include(PATH_STYLE."module".DS."list.php");
}


class ListGenerator {

	private $provider;
	private $ouPath = "";
	private $view;
	private static $AdConfig;
	private static $AdConnectionName;
	private static $AdOptions;
	private $filter = '*';

	function __construct($view) {
		global $AdConnectionName, $AdConfig, $AdOptions;
		ListGenerator::$AdConfig = $AdConfig;
		ListGenerator::$AdConnectionName = $AdConnectionName;
		ListGenerator::$AdOptions = $AdOptions;

		$this->view = $view;
		$ad = new \Adldap\Adldap();
		$ad->addProvider($AdConfig, $AdConnectionName);

		try {
			$this->provider = $ad->connect($AdConnectionName);
		} catch (\Adldap\Auth\BindException $e) {
			echo $e;
			exit();
		}

		$this->view = &$view;
		return true;
	}

	protected function setOu($ou = '') {
		if (is_array($ou)) {
			$ou = implode(",OU=", $ou).',';
		} elseif(!empty($ou)) {
			$ou .= ",";
		}

		$this->ouPath = $ou . ListGenerator::$AdOptions["base_dn"];
	}

	public function setFilter($filter) {
		$this->filter = $filter;
	}

	public function createAttrTemplate(&$attr){
		$tpl = array();
		foreach ($attr as $i => $name) {
			$name = strtolower($name);
			$tpl[$name] = '';
		}
		return $tpl;
	}

	public function mapAttr($entry, &$tpl) {
		global $settings;
		$attr = $tpl;
		foreach ($entry as $index => $value) {
			if ($index != "count" && is_string($index) && array_key_exists($index,$tpl)) {

				$attr[$index] = $value;

				//specials, create extensions only nr.
				if ($index == "telephonenumber" && isset($attr["officephoneextension"])) {
					$attr["officephoneextension"] = trim(str_replace($settings["phoneCentral"][0],"",$entry[$index]));
					$attr["officephoneextension"] = ($attr["officephoneextension"] == 0) ? '' : $attr["officephoneextension"];
				} elseif ($index == "fax" && isset($attr["faxextension"])) {
					$attr["faxextension"] = trim(str_replace($settings["phoneCentral"][0],"",$entry[$index]));
					$attr["faxextension"] = ($attr["faxextension"] == 0) ? '' : $attr["faxextension"];
				}
			}
		}
		return $attr;
	}

	/**
	 * Execute and return Collection based on actual OU-Settings.
	 *
	 * @return array|\Illuminate\Support\Collection
	 */
	private function getSearch() {
		return $this->provider->search()->setBaseDn($this->ouPath)->get();
	}

	protected function getUsers($attributes) {
		$collection = $this->getSearch();
		$users = array();
		foreach ($collection->keys() as $key) {
			$model = $collection->get($key);
			if ($model instanceof \Adldap\Models\User) {
				$entry = array();
				foreach ($attributes as $attribute) {
					$field = $model->getFirstAttribute($attribute);
					if (!isset($field)) {
						$field = "";
					}
					$entry[$attribute] = $field;
				}
				$users[] = $entry;
			}
		}
		return $users;
	}

	protected function getContacts($attributes) {
		$collection = $this->getSearch();
		$contacts = array();
		foreach ($collection->keys() as $key) {
			$model = $collection->get($key);
			if ($model instanceof \Adldap\Models\Contact) {
				$entry = array();
				foreach ($attributes as $attribute) {
					$field = $model->getFirstAttribute($attribute);
					if (!isset($field)) {
						$field = "";
					}
					$entry[$attribute] = $field;
				}
				$contacts[] = $entry;
			}
		}
		return $contacts;
	}

	protected function getGroups($attributes) {
		$collection = $this->getSearch();
		$groups = array();
		foreach ($collection->keys() as $key) {
			$model = $collection->get($key);
			if ($model instanceof \Adldap\Models\Group) {
				$entry = array();
				foreach ($attributes as $attribute) {
					$field = $model->getFirstAttribute($attribute);
					if (!isset($field)) {
						$field = "";
					}
					$entry[$attribute] = $field;
				}
				$groups[] = $entry;
			}
		}
		return $groups;
	}

	//Does only work correctly if the ressources are in an own OU
	protected function getExchangeResources($attributes) {
		$collection = $this->getSearch();
		$exchangeResources = array();
		foreach ($collection->keys() as $key) {
			$model = $collection->get($key);
			//MailResource is User-Model (AdLdap 2)
			if ($model instanceof \Adldap\Models\User) {
				$entry = array();
				foreach ($attributes as $attribute) {
					$field = $model->getFirstAttribute($attribute);
					if (!isset($field)) {
						$field = "";
					}
					$entry[$attribute] = $field;
				}
				$exchangeResources[] = $entry;
			}
		}
		return $exchangeResources;
	}

	public function get() {
		//mix up attributes
		$attributes = array_merge($this->view["attr"],$this->view["attr_hidden"]);
		//create template
		$attrTemplate = $this->createAttrTemplate($attributes);
		$results = array();

		//walk through the searchbases
		foreach($this->view["searchbase"] as $search){
			//convert type given as string to an array
			if(is_string($search["type"])) $search["type"] = array($search["type"]);

			//set OU
			if(empty($search["OU"])) $search["OU"] = '';
			$this->setOu($search["OU"]);

			//search by type and merge to results
			if(in_array("user",$search["type"]))	 {
				$results = array_merge($this->getUsers($attributes),$results);
			}
			if(in_array("contact",$search["type"])) {
				$results = array_merge($this->getContacts($attributes),$results);
			}
			if(in_array("group",$search["type"])) {
				$results = array_merge($this->getGroups($attributes),$results);
			}
			if(in_array("ressource",$search["type"])) {
				$results = array_merge($this->getExchangeResources($attributes),$results);
			}
		}

 		$list = array();

		foreach ($results as $entry) {
			//check if user is enabled, ignore disabled user
			if (isset($entry["useraccountcontrol"])) {
				$enabled = ((($entry["useraccountcontrol"][0]) & 2) == 2) ? false : true;

				if (!$enabled) {
					continue;
				}
			}

			//check if office/room of user is set to 'hidefromselfservice', ignore them
			if (isset($entry["physicaldeliveryofficename"])) {
				$officeRoom = $entry["physicaldeliveryofficename"];
				if (is_string($officeRoom) && $officeRoom == "hidefromselfservice") {
					//skip entry
					continue;
				}
			}

			// Convert the password last set field from a windows timestamp to human readable
			// https://www.php.net/manual/en/ref.ldap.php
			if ($entry["pwdlastset"] != 0) {
                // divide by 10.000.000 to get seconds from 100-nanosecond intervals
                $winInterval = round($entry["pwdlastset"] / 10000000);
                // substract seconds from 1601-01-01 -> 1970-01-01
                $unixTimestamp = ($winInterval - 11644473600);
				// show date/time in local time zone
				$entry["pwdlastset"] = date("Y-m-d H:i:s", $unixTimestamp);
			} else {
				$entry["pwdlastset"] = "Never";
			}

			$list[] = $this->mapAttr($entry,$attrTemplate);
		}

		//sort list if requested
		if (!empty($this->view["sortby"])) {
			$mysort = new mySort($this->view["sortby"]);
			usort($list, array($mysort, 'cmp'));
		}

		return $list;
	}
}

/**

*/

/**
 * Class mySort
 *
 * example: $mysort = new mysort("myKey");
 * usort($list,array($mysort, 'cmp'));
 */
class mySort {
	protected $key = false;
	function __construct($key) {
			$this->key = $key;
	}
	function cmp($a,$b) {
		return strcasecmp($a[$this->key], $b[$this->key]);
	}
}

/**
 * Translate AD-Attribute into 'Canonical Name'
 *
 * @param $attr String to translate
 * @return mixed translation (if available)
 */
function translateAttr($attr) {
	global $AttrTranslate;
	if (array_key_exists($attr,$AttrTranslate)) {
		return $AttrTranslate[$attr];
	}
	return $attr;
}


////////////////////////////////////////////////////////////////////////////////////////



?>
