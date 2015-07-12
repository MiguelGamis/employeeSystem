<?php

require_once("../inc/sauderdatamanager.php");
require_once("../config.php");
require_once("../inc/ids.php");

$dataMgr = new EmployeeDataManager();

$group = ucfirst($_GET['group']);
$requestid = intval($_GET['requestid']);
$func = "get".$group."Checklist";

$checkList = $dataMgr->$func(new RequestID($requestid));

echo "<table>";
foreach($checkList as $item => $status)
{
	if($status)
	{
		echo "<tr><td>$item</td>";
		if($status == 'pending')
			echo "<td><div class='icon pending'></div></td></tr>";
		elseif($status == 'done')
			echo "<td><div class='icon checkmark'></div></td></tr>";
	}
}
echo "<tr></tr>";

?>