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
	$approvefunction = 'approve'.$item;
	if(in_array($item, array('GeneralInformation', 'ComputerAccess', 'PATSystemAccount', 'VoiceService', 'InternalPhoneListRegistry', 'UBConlineDirectory', 'SauderWebsiteStaffDirectory', 'SauderStaffPhotoDirectory', 'DoorNamePlate', 'LocationAccess', 'BusinessCard', 'Email', 'EmailGrouping', 'SiteCoreLogin')))
	{
		$dataMgr->$approvefunction(new RequestID($_GET['requestid']));
	}	
	elseif ($item == 'MailSlotAccessor') {
		if(!array_key_exists('mailslotaccessid', $_GET))
			throw new Exception("Mailslot access ID does not exist");
		$dataMgr->approveMailSlotAccessor(new RequestID($_GET['requestid']), $_GET['mailslotaccessid']);
	}
	elseif ($item == 'PrintingAccess') {
		if(!array_key_exists('speedchart', $_GET))
			throw new Exception("Printing speedchart does not exist");
		$dataMgr->approvePrintingAccess(new RequestID($_GET['requestid']), $_GET['speedchart']);
	}
	elseif ($item == 'SharedDriveAccess') {
		if(!array_key_exists('shareddriveid', $_GET))
			throw new Exception("Shared Drive ID does not exist");
		$dataMgr->approveSharedDriveAccess(new RequestID($_GET['requestid']), $_GET['shareddriveid']);
	}
	elseif ($item == 'EmailGrouping') {
	
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
