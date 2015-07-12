<?php
require_once("inc/common.php");

$dataMgr->getPendingRequests();

$content = "";

$content .= "<div class='bigicon tenure'></div>";
$generalInformationTasks = $dataMgr->getGeneralInformationTasks();
$content .= boxify($generalInformationTasks);

$content .= "<div class='bigicon doornameplate'></div>";
$doorNamePlateTasks = $dataMgr->getDoorNamePlateTasks();
$content .= boxify($doorNamePlateTasks);

$content .= "<div class='bigicon sharedmailbox'></div>";
$sharedMailBoxAccessTasks= $dataMgr->getSharedMailBoxAccessTasks();
$content .= boxify($sharedMailBoxAccessTasks);

$content .= "<div class='bigicon mailbox'></div>";
$mailBoxAccessTasks = $dataMgr->getMailBoxAccessTasks();
$content .= boxify($mailBoxAccessTasks);

$content .= "<div class='bigicon businesscard'></div>";
$businessCardTasks= $dataMgr->getBusinessCardTasks();
$content .= boxify($businessCardTasks);

$content .= "<div class='bigicon attendancetracker'></div>";
$PATSystemAccountTasks = $dataMgr->getPATSystemAccountTasks();
$content .= boxify($PATSystemAccountTasks);

render_page();

function boxify($dataArray)
{
	$html = "";
	if(empty($dataArray))
		$html .= "There are currently no pending tasks";
	else
	{
		$currentRowIndex = 0;
		foreach($dataArray as $data)
		{
			$rowClass = "rowType".($currentRowIndex % 2);
			$html .= "<div class='box $rowClass'>\n";
			$html .= "<table>";
			$currentRow = 0;
			foreach($data as $field => $value)
			{
			if($currentRow % 2 == 0)
				$html .= "<tr>";
			$html .= "<td>$field</td>";
			if(is_array($value))
			{
				$html .= "<td><table><ul>";
				foreach($value as $subvalue)
					$html .= "<li>$subvalue</li>";
				$html .="</ul></table></td>";
			}
			else {
				$html .= "<td>$value</td>";
			}
			if($currentRow % 2 == 1)
				$html .= "</tr>";
				$currentRow ++;
			}
			$html .= "</table>";
			$html .= "</div>\n";
			$currentRowIndex++;
		}
	}
	return $html;
}

?>

