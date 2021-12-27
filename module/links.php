<?php

  use adLDAP\adLDAP;

  class linkList {
    private $AdOptions;
    private $adldap;

    function __construct($AdOptions) {
      $this->AdOptions = $AdOptions;

      try {
        $this->adldap = new adLDAP($this->AdOptions);
      } catch (adLDAPException $e) {
        echo $e;
        exit();
      }
    }

    public function getUserDepartment() {
      $auth = authUser(false);
      if ($auth) {
        //get user data (only department attribute)
        $results = $this->adldap->user()->info($_SESSION["username"], array("department"));

        if ($results) {
          return $results[0]["department"][0];
        } else {
          $msg["warning"][] = "Abteilung vom User konnte nicht abgefragt werden.";
        }
      }
      return "";
    }
  }

  /**
   * Check if user is in specific department
   *
   * @param $department that should be checked
   * @return bool if the user is in the department
   */
  function userInDepartment($department) {
    global $AdOptions;
    $linkList = new linkList($AdOptions);

    if (strpos($linkList->getUserDepartment(), $department) !== false) {
      return true;
    } else {
      return false;
    }
  }

  include(PATH_STYLE . "module" . DS . "links.php");
?>