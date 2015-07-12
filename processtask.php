<?php
require_once("inc/common.php");
try
{
    $authMgr->enforceLoggedIn();
	//$dataMgr->requireGroup();
	if(!array_key_exists('type', $_GET))
		throw new Exception("Type of task was not received");
	$type = $_GET['type'];
	if(!array_key_exists('item', $_GET))
		throw new Exception("Item to $type was not received");
	$item = $_GET['item'];
	if(!array_key_exists('requestid', $_GET))
		throw new Exception("Request ID does not exist");
	$requestID = new RequestID($_GET['requestid']);
	
	$function = $type.$item;
	print_r($function);
	if(in_array($item, array('GeneralInformation', 'ComputerAccess', 'PATSystemAccount', 'VoiceService', 'InternalPhoneListRegistry', 'UBConlineDirectory', 'SauderWebsiteStaffDirectory', 'SauderStaffPhotoDirectory', 'DoorNamePlate', 'LocationAccess', 'BusinessCard', 'Email', 'SiteCoreLogin')))
	{
		$dataMgr->$function($requestID);
	}	
	elseif ($item == 'MailSlotAccessor') {
		if(!array_key_exists('mailslotaccessid', $_GET))
			throw new Exception("Mailslot access ID does not exist");
		$dataMgr->$function($requestID, $_GET['mailslotaccessid']);
	}
	elseif ($item == 'PrintingAccess') {
		if(!array_key_exists('speedchart', $_GET))
			throw new Exception("Printing speedchart does not exist");
		$dataMgr->$function($requestID, $_GET['speedchart']);
	}
	elseif ($item == 'SharedDriveAccess') {
		if(!array_key_exists('shareddriveid', $_GET))
			throw new Exception("Shared Drive ID does not exist");
		$dataMgr->$function($requestID, $_GET['shareddriveid']);
	}
	elseif ($item == 'EmailGrouping') {
		if(!array_key_exists('emaillistid', $_GET))
			throw new Exception("Email List ID does not exist");
		$dataMgr->$function($requestID, $_GET['emaillistid']);
	}
	else
		throw new Exception("Error Processing Request");
    //redirect_to_main();
    header("Location: "."index.php");
    //exit();
}catch(Exception $e) {
    render_exception_page($e);
}

?>
