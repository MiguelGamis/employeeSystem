<?php

$content .= "<h1>IT division</h1>";

$content .= "<table><tr><td>";
$content .= "<h2>Pending Tasks</h2>";

$content .= "<div class='bigicon computer'></div>";
$computerTasks = $dataMgr->getComputerTasks('pending');
$content .= boxify($computerTasks, 'approve', 'ComputerAccess');

$content .= "<div class='bigicon printing'></div>";
$printingTasks = $dataMgr->getPrintingTasks('pending');
$content .= boxify($printingTasks, 'approve', 'PrintingAccess');

$content .= "<div class='bigicon shareddrive'></div>";
$sharedDriveTasks = $dataMgr->getSharedDriveAccessTasks('pending');
$content .= boxify($sharedDriveTasks, 'approve', 'SharedDriveAccess');

$content .= "<div class='bigicon email'></div>";
$emailTasks = $dataMgr->getEmailTasks('pending');
$content .= boxify($emailTasks, 'approve', 'Email');
$emailGroupingTasks = $dataMgr->getEmailGroupingTasks('pending');
$content .= boxify($emailGroupingTasks, 'approve', 'EmailGrouping');

$content .= "<div class='bigicon sitecore'></div>";
$siteCoreAccessTasks = $dataMgr->getSiteCoreAccessTasks('pending');
$content .= boxify($siteCoreAccessTasks, 'approve', 'siteCoreAccess');

$content .= "</td><td>";

$content .= "<h2>Done Tasks</h2>";

$content .= "<div class='bigicon computer'></div>";
$computerTasks = $dataMgr->getComputerTasks('done');
$content .= boxify($computerTasks, 'undo', 'ComputerAccess');

$content .= "<div class='bigicon printing'></div>";
$printingTasks = $dataMgr->getPrintingTasks('done');
$content .= boxify($printingTasks, 'undo', 'PrintingAccess');

$content .= "<div class='bigicon shareddrive'></div>";
$sharedDriveTasks = $dataMgr->getSharedDriveAccessTasks('done');
$content .= boxify($sharedDriveTasks, 'undo', 'SharedDriveAccess');

$content .= "<div class='bigicon email'></div>";
$emailTasks = $dataMgr->getEmailTasks('pending');
$content .= boxify($emailTasks, 'undo', 'EmailAccess');
$emailGroupingTasks = $dataMgr->getEmailGroupingTasks('done');
$content .= boxify($emailGroupingTasks, 'undo', 'EmailGroupingAccess');

$content .= "<div class='bigicon sitecore'></div>";
$siteCoreAccessTasks = $dataMgr->getSiteCoreAccessTasks('done');
$content .= boxify($siteCoreAccessTasks, 'undo', 'siteCoreAccess');
$content .= "</td></tr></table>";

/*$content .= "<div class='bigicon computer'></div>";
$currentRowIndex = 0;
$computerTasks = $dataMgr->getPendingComputerTasks();
if(empty($computerTasks))
	$content .= "There currently no pending computer tasks";
foreach($computerTasks as $computerTask)
{
	$rowClass = "rowType".($currentRowIndex % 2);
	$content .= "<div class='box $rowClass'>\n";
	$content .= "<table>";
	$content .= "<td width=300px><h3>Request by ".$computerTask->requestby." for ".$computerTask->firstName." ".$computerTask->lastName."</h3></td>";
	$content .= "<td>Submitted:</td><td width=390px id='computerTaskDate".$computerTask->requestID.$currentRowIndex."'/></td>\n";
	$content .= "<td><a title='Cancel' href='".get_redirect_url("deleterequest.php?requestid=".$computerTask->requestID)."'><div class='icon delete'></div></a></td>\n";
	$content .= "</table>";
	$content .= "</div>";
	$content .= "<script type='text/javascript'>\n";
	$content .= set_element_to_date('computerTaskDate'.$computerTask->requestID.$currentRowIndex, $computerTask->dateSubmitted, "html", "MMMM Do YYYY, HH:mm", false, true);
	$content .= "</script>\n";
	$currentRowIndex++;
}

$content .= "<div class='bigicon printing'></div>";
$currentRowIndex = 0;
$printingTasks = $dataMgr->getPendingPrintingTasks();
if(empty($printingTasks))
	$content .= "There currently no pending printing access tasks";
foreach($printingTasks as $printingTask)
{
	$rowClass = "rowType".($currentRowIndex % 2);
	$content .= "<div class='box $rowClass'>\n";
	$content .= "<table>";
	$content .= "<td width=300px><h3>Request by ".$printingTask->requestby." for ".$printingTask->firstName." ".$printingTask->lastName."</h3></td>";
	$content .= "<td>Submitted:</td><td width=390px id='printingTaskDate".$printingTask->requestID.$currentRowIndex."'/></td>\n";
	$content .= "<td><a title='Cancel' href='".get_redirect_url("deleterequest.php?requestid=".$printingTask->requestID)."'><div class='icon delete'></div></a></td>\n";
	$content .= "</table>";
	$content .= "</div>";
	$content .= "<script type='text/javascript'>\n";
	$content .= set_element_to_date('printingTaskDate'.$printingTask->requestID.$currentRowIndex, $printingTask->dateSubmitted, "html", "MMMM Do YYYY, HH:mm", false, true);
	$content .= "</script>\n";
	$currentRowIndex++;
}

$content .= "<div class='bigicon shareddrive'></div>";
$currentRowIndex = 0;
$sharedDriveTasks = $dataMgr->getPendingSharedDriveAccessTasks();
if(empty($sharedDriveTasks))
	$content .= "There currently no pending shared drive access tasks";
foreach($sharedDriveTasks as $sharedDriveTask)
{
	$rowClass = "rowType".($currentRowIndex % 2);
	$content .= "<div class='box $rowClass'>\n";
	$content .= "<table>";
	$content .= "<td width=300px><h3>Request by ".$sharedDriveTask->requestby." for ".$sharedDriveTask->firstName." ".$sharedDriveTask->lastName."</h3></td>";
	$content .= "<td>Submitted:</td><td width=390px id='sharedDriveTaskDate".$sharedDriveTask->requestID.$currentRowIndex."'/></td>\n";
	$content .= "<td><a title='Cancel' href='".get_redirect_url("deleterequest.php?requestid=".$sharedDriveTask->requestID)."'><div class='icon delete'></div></a></td>\n";
	$content .= "</table>";
	$content .= "</div>";
	$content .= "<script type='text/javascript'>\n";
	$content .= set_element_to_date('sharedDriveTaskDate'.$sharedDriveTask->requestID.$currentRowIndex, $sharedDriveTask->dateSubmitted, "html", "MMMM Do YYYY, HH:mm", false, true);
	$content .= "</script>\n";
	$currentRowIndex++;
}

$content .= "<div class='bigicon email'></div>";
$currentRowIndex = 0;
$emailTasks = $dataMgr->getPendingEmailTasks();
if(empty($emailTasks))
	$content .= "There currently no pending email tasks\n";
foreach($emailTasks as $emailTask)
{
	$rowClass = "rowType".($currentRowIndex % 2);
	$content .= "<div class='box $rowClass'>\n";
	$content .= "<table>";
	$content .= "<td width=300px><h3>Request by ".$emailTask->requestby." for ".$emailTask->firstName." ".$emailTask->lastName."</h3></td>";
	$content .= "<td>Submitted:</td><td width=390px id='emailTaskDate".$emailTask->requestID.$currentRowIndex."'/></td>\n";
	$content .= "<td><a title='Cancel' href='".get_redirect_url("deleterequest.php?requestid=".$emailTask->requestID)."'><div class='icon delete'></div></a></td>\n";
	$content .= "</table>";
	$content .= "</div>";
	$content .= "<script type='text/javascript'>\n";
	$content .= set_element_to_date('emailTaskDate'.$emailTask->requestID.$currentRowIndex, $emailTask->dateSubmitted, "html", "MMMM Do YYYY, HH:mm", false, true);
	$content .= "</script>\n";
	$currentRowIndex++;
}
$currentRowIndex = 0;
$emailGroupingTasks = $dataMgr->getPendingEmailGroupingTasks();
if(empty($emailGroupingTasks))
	$content .= "There currently no pending email grouping tasks";
foreach($emailGroupingTasks as $emailGroupingTask)
{
	$rowClass = "rowType".($currentRowIndex % 2);
	$content .= "<div class='box $rowClass'>\n";
	$content .= "<table>";
	$content .= "<td width=300px><h3>Request by ".$emailGroupingTask->requestby." for ".$emailGroupingTask->firstName." ".$emailGroupingTask->lastName."</h3></td>";
	$content .= "<td>Submitted:</td><td width=390px id='emailGroupingTaskDate".$emailGroupingTask->requestID.$currentRowIndex."'/></td>\n";
	$content .= "<td><a title='Cancel' href='".get_redirect_url("deleterequest.php?requestid=".$emailGroupingTask->requestID)."'><div class='icon delete'></div></a></td>\n";
	$content .= "</table>";
	$content .= "</div>";
	$content .= "<script type='text/javascript'>\n";
	$content .= set_element_to_date('emailGroupingTaskDate'.$emailGroupingTask->requestID.$currentRowIndex, $emailGroupingTask->dateSubmitted, "html", "MMMM Do YYYY, HH:mm", false, true);
	$content .= "</script>\n";
	$currentRowIndex++;
}

$content .= "<div class='bigicon sitecore'></div>";
$currentRowIndex = 0;
$siteCoreAccessTasks = $dataMgr->getPendingSiteCoreAccessTasks();
if(empty($siteCoreAccessTasks))
	$content .= "There currently no pending site core access tasks";
foreach($dataMgr->getPendingSiteCoreAccessTasks() as $siteCoreAccessTask)
{
	$rowClass = "rowType".($currentRowIndex % 2);
	$content .= "<div class='box $rowClass'>\n";
	$content .= "<table>";
	$content .= "<td width=300px><h3>Request by ".$siteCoreAccessTask->requestby." for ".$siteCoreAccessTask->firstName." ".$siteCoreAccessTask->lastName."</h3></td>";
	$content .= "<td>Submitted:</td><td width=390px id='siteCoreAccessTaskDate".$siteCoreAccessTask->requestID.$currentRowIndex."'/></td>\n";
	$content .= "<td><a title='Cancel' href='".get_redirect_url("deleterequest.php?requestid=".$siteCoreAccessTask->requestID)."'><div class='icon delete'></div></a></td>\n";
	$content .= "</table>";
	$content .= "</div>";
	$content .= "<script type='text/javascript'>\n";
	$content .= set_element_to_date('siteCoreAccessTaskDate'.$siteCoreAccessTask->requestID.$currentRowIndex, $siteCoreAccessTask->dateSubmitted, "html", "MMMM Do YYYY, HH:mm", false, true);
	$content .= "</script>\n";
	$currentRowIndex++;
}*/
?>

