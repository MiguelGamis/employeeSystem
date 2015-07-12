<?php
require_once('inc/common.php');
try
{
    $authMgr->enforceLoggedIn();
    $dataMgr->requireGroup();

    #What are they trying to do?
    $action = require_from_get("action", false);

    switch($action){
    case 'select':
        //$authMgr->enforceInstructor();
        $title .= " | Become Other User";

		$groups = $dataMgr->getGroups();

		$content .= "<h1>Select User</h1>\n";
		foreach($groups as $groupObj)
		{
	        $content .= "<h1>$groupObj->groupName</h1>\n";
			$content .= "<table>\n";
	        foreach($dataMgr->getUserDisplayMap($groupObj->groupID) as $userID => $name)
	        {
	            $content .= "<tr><td><a href='?action=assign&userid=$userID'>$name</a></td></tr>";
	        }
			
	        $content .= "</table>\n";
		}
        render_page();
        break;
    case 'return':
		$dataMgr->changeGroup(new UserID($_SESSION['oldAllSeeingID']));
        $authMgr->returnToAllSeeing();
        redirect_to_main();
        break;
    case 'assign':
        $authMgr->enforceAllSeeing();
        $userid = require_from_get("userid");
        $authMgr->becomeUser(new UserID($userid));
		$dataMgr->changeGroup(new UserID($userid));
        redirect_to_main();
        break;
    default:
        throw new Exception("unknown action '$action'");
    }
}catch(Exception $e){
    render_exception_page($e);
}
?>

