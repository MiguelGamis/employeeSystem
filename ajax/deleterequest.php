<?php
require_once(MTA_ROOTPATH."inc/common.php");
try
{
    $authMgr->enforceLoggedIn();
	//$dataMgr->requireGroup();

	if(array_key_exists('requestid', $_GET))
		$dataMgr->deleteRequest(new RequestID($_GET['requestid']));
	else
		throw new Exception("Request ID does not exist", 1);
		
	if(array_key_exists('employeeid', $_GET))
		$dataMgr->deleteIfNewEmployee(new EmployeeID($_GET['employeeid']));
	else
		throw new Exception("Employee ID does not exist", 1);

	$dataMgr->deleteUnusedMailAccess();

    redirect_to_main();
}catch(Exception $e) {
    render_exception_page($e);
}

?>
