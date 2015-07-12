<?php
session_start();

//First off, we need to figure out the path to the MTA install
$pos = strpos(dirname(__FILE__),DIRECTORY_SEPARATOR.'inc');
$path = substr(__FILE__, 0, $pos);
if( substr($path, strlen($path) - 1) != '/' ) { $path .= '/'; }
define('MTA_ROOTPATH', $path);

//Add the MTA root path into the includes
set_include_path(get_include_path().PATH_SEPARATOR.MTA_ROOTPATH);

//Standard includes
require_once(MTA_ROOTPATH.'inc/basic.php');
require_once(MTA_ROOTPATH.'inc/themefunctions.php');
require_once(MTA_ROOTPATH.'inc/ids.php');
//require_once(MTA_ROOTPATH.'inc/authmanager.php');
//require_once(MTA_ROOTPATH.'inc/datamanager.php');
//require_once(MTA_ROOTPATH.'inc/htmlpurifier/HTMLPurifier.auto.php');

//Load the config
require_once(MTA_ROOTPATH.'config.php');

function mta_error_handler($errno, $errstr, $errfile, $errline) {
  //if ( E_RECOVERABLE_ERROR===$errno ) {
    render_exception_page(new ErrorException($errstr, $errno, 0, $errfile, $errline));
  //}
  return false;
}
//set_error_handler('mta_error_handler');
#error_reporting(E_ALL);
#ini_set('display_errors','On');

try
{
    //Go get us something that can load some data
    if(!isset($MTA_DATAMANAGER)) { die("The MTA_DATAMANAGER must be set in the config file"); }
    require_once(MTA_ROOTPATH."inc/sauderdatamanager.php");
    //$dataMgrType = $MTA_DATAMANAGER."DataManager";
    $dataMgr = new EmployeeDataManager();
	
    //Now, do a couple of checks to see if we have the course or course ID in get
    if(array_key_exists("groupid", $_GET))
    {
        $id = $_GET["groupid"];
        if(preg_match("/[^\d]/", $id))
        {
            die("Invalid group id '$id'");
        }
        $dataMgr->setGroupFromID(new GroupID($id));
    }
    else if(array_key_exists("group", $_GET))
    {
        $group = $_GET["group"];
        if(preg_match("/[^a-zA-Z0-9]/", $group))
        {
            die("Invalid group name '$group'");
        }
        $dataMgr->setGroupFromName($group);
    }

    //Get the global auth manager
    $authMgr = $dataMgr->createAuthManager();

    #And as a helper, whenever we include this file let's set $USER to be
    #who the session thinks is logged in
    if(array_key_exists("loggedID", $_SESSION) && $dataMgr->isUser(new UserID($_SESSION["loggedID"])))
        $USERID = new UserID($_SESSION["loggedID"]);
    else
        $USERID = NULL;

    //Leave a global for the HTML purifier
    $HTML_PURIFIER = NULL;
    $PRETTYURLS = isset($_GET["prettyurls"]);
    $NOW = time();

    /** Stuff that's needed by the template */
    $content="";
    $page_scripts = array();
    $title = "Sauder Employees";
    $menu=get_default_menu_items();


}catch(Exception $e) {
    render_exception_page($e);
}
