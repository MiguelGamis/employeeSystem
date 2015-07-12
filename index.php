<?php
require_once("inc/common.php");
try
{
    //Has the course been set?
    if(!$dataMgr->groupName)
    {
        //Nope, run up the course picker for people
        $content .= "<h1>Group Select</h1>";
        foreach($dataMgr->getGroups() as $groupObj)
        {
            //if($courseObj->browsable)
                $content .= "<a href='$SITEURL$groupObj->shortName/'>$groupObj->groupName</a><br>";
        }
        render_page();
    }
    else
    {
        $authMgr->enforceLoggedIn();
        #$dataMgr->numStudents();
        $content .= show_timezone();

        //Give them the option of creating an assignment, or running global scripts
        $content .= "<table align='left'><tr>\n";
        $content .= "<td><a title='Run Scripts' href='".get_redirect_url("runscript.php")."'><div class='icon script'></div></a></td>\n";
        $content .= "<td><a title='Entry Form' href='".get_redirect_url("entryform.php?action=new")."'><div class='icon userManager'></div></a></td>\n";
		$content .= "<td><a title='Create new Assignment' href='".get_redirect_url("editassignment.php?action=new")."'><div class='icon deleteUser'></div></a></td>\n";
        $content .= "</tr></table><br>\n";

		if($dataMgr->isAdmin($USERID))
			require_once("index_admin.php");
		elseif($dataMgr->isIT($USERID))
			require_once("index_IT.php");
		else	
        {
	        $content .= "<h1>Pending Requests</h1>\n";
	        $currentRowIndex = 0;
			$requests = $dataMgr->getPendingRequests(new EmployeeID($USERID));
			if(empty($requests))
				$content .= "You currently have no pending requests";
			else{
				$currentRowIndex = 0;
				foreach($requests as $requestID => $request)
				{
					$ITChecklist = $dataMgr->getITChecklist(new RequestID($requestID));
					$ITstatus = array_reduce($ITChecklist, function ($carry, $item){if($item == 'pending'){$carry+=1; return $carry;}return $carry; });
					if($ITstatus>0)
						$ITmessage = '<strong style="color:red;">Incomplete</strong>';
					else
						$ITmessage = '<strong style="color:green;">Complete</strong>';
						
					$adminChecklist = $dataMgr->getAdminChecklist(new RequestID($requestID));
					$adminstatus = array_reduce($adminChecklist, function ($carry, $item){if($item == 'pending'){$carry+=1; return $carry;}return $carry; });
					if($adminstatus>0)
						$adminmessage = '<strong style="color:red;">Incomplete</strong>';
					else
						$adminmessage = '<strong style="color:green;">Complete</strong>';
					
					$rowClass = "rowType".($currentRowIndex % 2);
					$currentRowIndex++;
					$content .= "<div class='box $rowClass'>\n";
					$content .= "<table>";
					$content .= "<td width=300px><h3>Entry request for ".$request->firstName." ".$request->lastName."</h3></td>";
					$content .= "<td>Submitted:</td><td width=390px id='submissionDate$requestID'/></td>\n";
					$content .= "<td><a title='Edit' href='entryform.php?action=edit&requestid=$requestID'><div class='icon edit'></div></a><a title='Cancel' href='".get_redirect_url("deleterequest.php?requestid=".$requestID."&employeeid=".$request->employeeID)."'><div class='icon delete'></div></a></td>\n";
					$content .= "</table>";
					$content .= "<table cellpadding='20'width='100%'><col width='1*'><col width='1*'><col width='1*'><tr>
					<td><h4><div class='bigicon admin'></div>Administration Tasks</h4>$adminmessage</div><button name='adminexpand' onclick='expand(\"admin\", $requestID)'><div class='icon expand'></div></button><br></td>
					<td><h4><div class='bigicon HR'></div>HR Tasks</h4><span style='color:red'>Incomplete</span></div><div class='icon expand'></div></td>
					<td><h4><div class='bigicon IT'></div>Sauder IT Tasks</h4>$ITmessage</div><button name='ITexpand' onclick='expand(\"IT\", $requestID)'><div class='icon expand'></div></button></td></tr>";
					$content .= "<tr><td><div id='adminItems'></div></td><td></td><td><div id='ITItems'></td></tr>";
					$content .= "</tr></table>";
					$content .= "</div>";
					$content .= "<script type='text/javascript'>\n";
					$content .= "return confirm('Are you sure you want to delete');";
					$content .= "</script>";
					$content .= "<script type='text/javascript'>\n";
					$content .= set_element_to_date("submissionDate$requestID", $request->dateSubmitted, "html", "MMMM Do YYYY, HH:mm", false, true);
					$content .= "</script>\n";
					$content .= "<script type='text/javascript'>\n";
					$content .= "function expand(group, requestid){
						var ajaxRequest;
						 try{
						   // Opera 8.0+, Firefox, Safari
						   ajaxRequest = new XMLHttpRequest();
						 }catch (e){
						   // Internet Explorer Browsers
						   try{
						      ajaxRequest = new ActiveXObject('Msxml2.XMLHTTP');
						   }catch (e) {
						      try{
						         ajaxRequest = new ActiveXObject('Microsoft.XMLHTTP');
						      }catch (e){
						         // Something went wrong
						         alert('Your browser broke!');
						         return false;
						      }
						   }
						 }
						 // Create a function that will receive data 
						 // sent from the server and will update
						 // div section in the same page.
						 ajaxRequest.onreadystatechange = function(){
						   if(ajaxRequest.readyState == 4){
						      if($('#'+group+'Items').html() == '')
						      	$('#'+group+'Items').html(ajaxRequest.responseText);
						      else if($('#'+group+'Items').is(':visible'))
						      	$('#'+group+'Items').hide();
						      else
								$('#'+group+'Items').show();
						   }
						 }
						 // Now get the value from user and pass it to
						 // server script.
						 var queryString = '?requestid=' + requestid ;
						 queryString += '&group=' + group;
						 ajaxRequest.open('GET', 'ajax/expand.php' + queryString, true);
						 ajaxRequest.send(null);
					}";
					$content .= "</script>\n";
				}
			}
			$content .= "\n";
		}

        render_page();
    }
}catch(Exception $e) {
    render_exception_page($e);
}

?>

