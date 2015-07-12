<?php
try
{
    if(!array_key_exists("groupid", $_GET)){
        require_once("../inc/common.php");
        //Nope, run up the course picker for people
        $content .= "<h1>Group Select</h1>";
        foreach($dataMgr->getGroups() as $groupObj)
        {
            $content .= "<a href='?groupid=$groupObj->groupID'>$groupObj->groupName</a><br>";
        }
        render_page();
    }
    else{
        $adminFileSkip=true;
        $extraUrl="&groupid=". $_GET["groupid"];
        require_once("../usermanager.php");
    }
}catch(Exception $e){
    render_exception_page($e);
}

