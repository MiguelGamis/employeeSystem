<?php
require_once("inc/common.php");
try
{
    $authMgr->enforceLoggedIn();
	//$dataMgr->requireGroup();
		
	if(!array_key_exists('item', $_GET))
		throw new Exception("Item type to approve was not received");
	if(!array_key_exists('requestid', $_GET))
		throw new Exception("Request ID does not exist");
	
	$item = $_GET['item'];
	$undofunction = 'undo'.$item;
	if(in_array($item, array('GeneralInformation', 'MainPrintingAccess', 'ComputerAccess', 'PATSystemAccount', 'VoiceService', 'InternalPhoneListRegistry', 'UBConlineDirectory', 'SauderWebsiteStaffDirectory',
	 'SauderStaffPhotoDirectory', 'DoorNamePlate', 'LocationAccess', 'BusinessCard', 'Email', 'SiteCoreLogin', 'SharedDriveAccess')))
	{
		$dataMgr->$undofunction(new RequestID($_GET['requestid']));
	}	
	elseif ($item == 'MailSlotAccessor') {
		if(!array_key_exists('mailslotaccessid', $_GET))
			throw new Exception("Mailslot access ID does not exist");
		$dataMgr->undoMailSlotAccessor(new RequestID($_GET['requestid']), $_GET['mailslotaccessid']);
	}
	else
		throw new Exception("Error Processing Request");
    //redirect_to_main();
    header("Location: "."index.php");
    exit();
}catch(Exception $e) {
    render_exception_page($e);
}

?>