<?php
require_once("inc/common.php");
require_once("request.php");
try
{
	$authMgr->enforceLoggedIn();

	$action = require_from_get("action");
	
    if($action == "save")
    {
		$request = new Request();
        $request->loadFromPost($_POST, $_FILES);
        //Try to save this
        $dataMgr->saveEntryRequest($request);
        //redirect_to_main();
	}
	else if($action == "update")
	{
		$request = new Request();
		$requestid = require_from_get('requestid');
		$request->requestID = $requestid;
        $request->loadFromPost($_POST, $_FILES);
		$dataMgr->saveEntryRequest($request);
		//redirect_to_main();
	}
    else if($action == "new")
    {
    	$request = new Request();
		$content .= "<h1>ENTRANCE/MOVE - RESOURCE ALLOCATION CHECKLIST</h1>";
		$content .= "<form id='newrequestform' action='?action=save' method='post' enctype='multipart/form-data'>\n";
		$content .= $request->getFormHTML();
		//The validate script
		$content .= "<script type='text/javascript'> $(document).ready(function(){ $('#newrequestform').submit(function() {";
		$content .= "var error = false;";
		$content .= $request->getValidationCode();
		$content .= "if(error){return false;}else{return true;}\n";
		$content .= "}); }); </script>\n";
		$content .= $request->getFormScripts();
		$content .= "<br><br><input type='submit' value='Submit' />\n";
		$content .= "</form>\n";
		
		$content .= "<button id='autofill'>Autofill</button><button id='autofill2'>Autofill2</button>";
		
		$content .= "<script type='text/javascript'>
			function callRandom(radioGroup){
			   var array = document.getElementsByName(radioGroup);
			   var randomNumber=Math.floor(Math.random()*array.length);	
			   array[randomNumber].checked = true;
			}
		
			$('#autofill').click(function()
			{
				$('#firstname').val('Peter');
				$('#lastname').val('Griffin');
				$('#position').val('Professor');
				$('#term').val('2');
				$('#employeeID').val('23456789');
				$('#CWLID').val('pgriff');
				$('#newlocation').val('HA 447');
				$('#officelocation').val('HA 447');
				callRandom('tenureStatus');
				callRandom('tenureType');
				$('input:radio[name=sharedlocation][value=0]').click();
				$('input:radio[name=orientationpackage][value=0]').click();
				
				$('#voiceservice').click();
				$('#telephonenumber').val('6044444444');
				$('#voiceservicebuilding1').click();
				$('.services').click();
				$('#monthlyrental').val('A1B2');
				
				$('#internalPhoneListRegistry').click();
				$('#UBConlineDirectory').click();
				$('#sauderWebsiteStaffDirectory').click();
				//$('#sauderStaffPhotoDirectory').click();
				
				$('#doornameplate').click();
				$('#doornameplatetext').val('Joe Schmoe');
				$('#individualmailslot').click();
				
				$('#officelocationaccess').click();
				$('#officelocationforaccess').val('DL 229');
				$('#accessID').val('23456789');
				$('#fobnumber').val('567890');
				$('#businesscard').click();
				$('#computeraccess').click();
				$('#printingaccess').click();
				$('#mainspeedchart').val('A1S2');
				$('#secondaryspeedcharts').val('D3F4, G5H6');
				$('#shareddriveaccess').click();
				$('#shareddriveid').val('1');
				$('#email').click();
				$('#emailaddress').val('pgriff@sauder.ubc.ca'); 
				$('#mailinglists').val('3');
				$('#genericmailaccountsaccess').click();
				$('#editownbookingsaccess').click();
				$('#editallbookingsaccess').click();
				$('#readonlybookingsaccess').click();
				$('#siteCoreLogin').click();
				$('#siteCoreSection').val('main');
				$('#siteCoreLoginUserType').val('student');
				$('#trainingrequired').click();
				$('#comments').val('Test Entry Request');
			});	
			
			$('#autofill2').click(function()
			{
				$('#firstname').val('Homer');
				$('#lastname').val('Simpson');
				$('#position').val('PhD Student');
				$('#term').val('both');
				$('#employeeID').val('42389561');
				$('#CWLID').val('dohnuts');
				$('#newlocation').val('HA 441');
				$('#officelocation').val('HA 441');
				callRandom('tenureStatus');
				callRandom('tenureType');
				$('input:radio[name=sharedlocation][value=0]').click();
				$('input:radio[name=orientationpackage][value=0]').click();
				
				$('#PATsystem').click();
				$('#doornameplate').click();
				$('#doornameplatetext').val('Mr. Homer Simpson');
				$('#individualmailslot').click();
				$('#sharedmailslot').click();
				$('#mailslotsharer').val('23456789');
				
				$('#officelocationaccess').click();
				$('#officelocationforaccess').val('DL 229');
				$('#accessID').val('42389561');
				$('#fobnumber').val('545690');
				$('#businesscard').click();
				$('#computeraccess').click();
				$('#printingaccess').click();
				$('#mainspeedchart').val('A1S2');
				$('#secondaryspeedcharts').val('D3F4, G5H6');
				$('#shareddriveaccess').click();
				$('#shareddriveid').val('1');
				$('#email').click();
				$('#emailaddress').val('hsimpson@sauder.ubc.ca'); 
				$('#mailinglists').val('mailingList1');
				$('#editownbookingsaccess').click();
				$('#readonlybookingsaccess').click();
				$('#siteCoreLogin').click();
				$('#siteCoreSection').val('main');
				$('#siteCoreLoginUserType').val('student');
				$('#trainingrequired').click();
			});	
			</script>";
	} else if($action == 'edit')
	{
		$requestid = require_from_get('requestid');
		$request = $dataMgr->loadEntryRequest(new RequestID($requestid));
		print_r($request);
		if($request->requestType == 'entry')
		{
			$content .= "<h1>ENTRANCE/MOVE - RESOURCE ALLOCATION CHECKLIST</h1>";
			$content .= "<form id='editrequestform' action='?action=update&requestid=$requestid' method='post' enctype='multipart/form-data'>\n";
			$content .= $request->getFormHTML();
			//The validate script
			$content .= "<script type='text/javascript'> $(document).ready(function(){ $('#editrequestform').submit(function() {";
			$content .= "var error = false;";
			$content .= $request->getValidationCode();
			$content .= "if(error){return false;}else{return true;}\n";
			$content .= "}); }); </script>\n";
			$content .= $request->getFormScripts();
			$content .= "<br><br><input type='submit' value='Submit' />\n";
			$content .= "</form>\n";
		}
	}
	
    render_page();
}catch(Exception $e){
    render_exception_page($e);
}
