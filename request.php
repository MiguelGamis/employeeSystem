<?php
require_once("inc/common.php");
require_once("tempglobals.php");

class Request
{	
	public $requestType = 'entry';
	public $requestID = 0;
	public $requestby = 0;
	public $firstname = "";
	public $lastname = "";
	public $position = "";
	public $officelocation = "";
	public $employeeID = "";
	public $CWLID = "";
	public $tenureStatus = "";
	public $tenureType = "";
	public $startDate = 0;
    public $endDate = 0;
	public $term = "";
	public $ongoing = 0;
	public $newlocation = "";
	public $oldlocation = "";
	public $sharedlocation = "";
	public $otheroccupants = "";
	public $orientationpackage = "";
	public $PATsystem = "";
	
	public $voiceservice = "";
	public $telephonenumber = "";
	public $voiceservicebuildings = array();
	public $serviceschosen = array();
	public $monthlyrental = "";
	public $longdistancecharges = "";
	public $installation = "";
	
	//public $otheroptionschosen = array();
	public $internalPhoneListRegistry;
	public $UBConlineDirectory;
	public $sauderWebsiteStaffDirectory;
	public $sauderStaffPhotoDirectory;
	public $imagename = "";
	public $image = "";
	
	public $individualmailslot = "";
	public $sharedmailslot = "";
	public $mailslotsharer = "";
	
	public $doornameplate = "";
	public $doornameplatetext = "";
	
	public $officelocationaccess = "";
	public $officelocationforaccess = "";
	public $accessID = "";
	public $fobnumber = "";
	
	public $businesscard = "";
	public $businesscardtype = "";

	public $computeraccess = "";
	public $printingaccess = "";
	public $mainspeedchart = "";
	public $secondaryspeedcharts = "";
	public $backupprinterlocation = "";
	
	public $shareddriveaccess = "";
	public $shareddriveid = "";
	public $emailaccess = "";
	public $emailaddress = "";
	public $mailinglists = "";
	public $genericmailaccountsaccess = 0;
	public $editownbookingsaccess = 0;
	public $editallbookingsaccess = 0;
	public $readonlybookingsaccess = 0;
	public $sitecoreaccess = "";
	public $siteCoreLoginUserType ="";
	public $siteCoreTrainingRequired = 0;
	public $siteCoreSection = "";
	public $comments = "";
	
	//protected $dataMgr;
	function __construct(){
		$this->startDate = time();
        $this->endDate = time();
		//$this->dataMgr
	}
	
	function getFormHTML()
	{
		global $dataMgr, $USERID, $terms, $tenureTypes, $services, $servicedetails, $siteCoreUserTypes;
		
		$html = "";
		$html .= "<script>
			 $(function() {
				 $( '#tabs' ).tabs();
			 });
			 </script>";
        //Make the tab widget
        $html .= "<div id='tabs'><ul>";
            $html .= "<li><a href='#tabs-1'>General Information</a></li>\n";
            $html .= "<li><a href='#tabs-2'>Set Up Requirements</a></li>\n";
        $html .= "</ul>";
		
		$html .= "<div id='tabs-1'>";
		$html .= "Requested by: ".$dataMgr->getUserDisplayName($USERID);
		$html .= "<input type='hidden' name='requestby' id='requestby' value='$USERID'/>\n";
		
		$html .= "<h4>NAME:</h4>";
			$html .= "<table>";
			$html .= "<tr><td>First Name:</td><td><input type='text' name='firstname' id='firstname' value='".htmlentities($this->firstname, ENT_COMPAT|ENT_QUOTES)."'></td>";
			$html .= "<tr><td>Last Name:</td><td><input type='text' name='lastname' id='lastname' value='".htmlentities($this->lastname, ENT_COMPAT|ENT_QUOTES)."'></td>";
			$html .= "</table>";

		$html .= "<h4>EMPLOYEE INFORMATION:</h4>";
			$html .= "<table>";
			$html .= "<tr><td>Title/Position:</td><td><input type='text' name='position' id='position' value='".htmlentities($this->position, ENT_COMPAT|ENT_QUOTES)."'></td></tr>";
			$html .= "<tr><td>Term:</td><td><select name='term' id='term'>";
		foreach($terms as $termkey => $term)
		{
			$tmp = ''; if($termkey == $this->term) $tmp = 'selected';
			$html .= "<option value='$termkey' $tmp>$term</option>";
		}
			$html .= "</select></td><td>".$this->inputError('term')."</td></tr>";
			$html .= "<tr><td>Office Location:</td>";
			$html .= "<td><select name='officelocation' id='officelocation' value='".$this->newlocation."'>";
			foreach($dataMgr->getOfficeLocations() as $officelocation)
			{
				$tmp = ''; if($officelocation == $this->officelocation) $tmp = 'selected';
				$html .= "<option value='$officelocation' $tmp>$officelocation</option>";
			}
			$html .= "</select></td></tr>";
			$html .= "<tr><td>Employee ID/ Student #:</td><td><input type='text' name='employeeID' id='employeeID' value='".htmlentities($this->employeeID, ENT_COMPAT|ENT_QUOTES)."'></td><td></td></tr>";
			$html .= "<tr><td>CWL ID:</td><td><input type='text' name='CWLID' id='CWLID' value='".htmlentities($this->CWLID, ENT_COMPAT|ENT_QUOTES)."'></td></tr>";
			$html .= "</table>";

		$html .= "<h4>STATUS:</h4>";
			$html .= "<table>";
			$i = 0;
			foreach($dataMgr->getTenureStatuses() as $tenureStatus)
			{
				$tmp = ""; if($this->tenureStatus == $tenureStatus) $tmp = "checked";
				if($i == 0)
					$html .= "<tr>";
				$html .= "<td width=250px><input type='radio' name='tenureStatus' value='$tenureStatus' $tmp>$tenureStatus</td>";
				if($i == 2)
				{
					$html .= "</tr>";
					$i = 0;
				}
				else
					$i++;
			}
			$html .= "</table>";
			$html .= $this->inputError('tenureStatus');
			$html .= "<br>";
			$chosenTenureType = htmlentities($this->tenureType, ENT_COMPAT|ENT_QUOTES);
			$html .= "<table><tr><td>";
			foreach($tenureTypes as $tenureType)
			{
				$tmp = "";
				if($chosenTenureType == $tenureType)
					$tmp = "checked";
				$html .= "<input type='radio' name='tenureType' value='$tenureType' $tmp>$tenureType";
			}
			$html .= "</td></tr></table>";
			$html .= $this->inputError('tenureType');

		$html .= "<h4>PERIOD OF STAY:</h4>";
			$html .= "<table>";
			$html .= "<tr><td></td><td>Start/Move Date:</td><td><input type='text' name='startDate' id='startDate' /></td></tr>";
			//$html .= "<tr><td></td><td>Start/Move Date:</td><td><input type='text' name='startDate' id='startDate' value='".htmlentities($this->startDate)."'/></td></tr>";
			$tmp = $this->ongoing ? 'checked' : '';
			$html .= "<tr><td><input type='checkbox' name='ongoing' id='ongoing' $tmp/></td><td>Ongoing</td></tr>";
			$tmp = $this->endDate ? 'checked' : '';
			$html .= "<tr><td><input type='checkbox' name='enddate' id='enddate' $tmp/></td><td>End Date:</td><td><input type='text' name='endDate' id='endDate' /></td></tr>";
			$html .= "</table>";
			
			$html .= "<h4>NEW OFFICE/CUBICLE LOCATION:</h4><table><tr><td><input type='text' name='newlocation' id='newlocation' value='".htmlentities($this->newlocation)."' disabled></td></tr></table>";
			$html .= "<h4>OLD OFFICE/CUBICLE LOCATION (For move only):</h4><table><tr><td><input type='text' name='oldlocation' id='oldlocation'  value='".htmlentities($this->oldlocation)."' disabled></td></tr></table>";
			
			$html .= "<h4>OTHER PEOPLE WHO SHARE THIS OFFICE DURING THE TERM:</h4>";
			$html .= "<table>";
			$tmp1 = ''; $tmp2 = '';
			if(isActive($this->sharedlocation)) $tmp1 = 'checked'; else $tmp2 = 'checked';
			$html .= "<tr><td><input type='radio' name='sharedlocation' id='sharedlocation' value='1' $tmp1 disabled></td><td>Yes</td><td>Names of the other occupants:</td><td><input type='text' name='otheroccupants' id='otheroccupants' disabled/></td><td>".$this->inputError('otheroccupants')."</td></tr>";
			$html .= "<tr><td><input type='radio' name='sharedlocation' id='nosharedlocation' value='0' $tmp2 disabled></td><td>No</td></tr>";
			$html .= "</table>";

		$html .= "</div>";
		
		$html .= "<div id='tabs-2'>";
		$html .= "<h2>SET UP REQUIREMENTS</h2>";
		
		$html .= "<h4>1.) ORIENTATION PACKAGE REQUIRED:</h4>";
		$html .= "<table>";
		$tmp1 = ''; $tmp2 = '';
		if(isActive($this->orientationpackage)) $tmp1 = 'checked'; else $tmp2 = 'checked';
		$html .= "<tr><td><input type='radio' name='orientationpackage' id='orientationpackage' value='1' $tmp1></td><td>Yes</td><td>".$this->inputError('orientationpackage')."</td></tr>";
		$html .= "<tr><td><input type='radio' name='orientationpackage' id='noorientationpackage' value='0' $tmp2></td><td>No</td></tr>";
		$html .= "</table>";		
		
		$html .= "<h4>2.) PERSONAL ATTENDANCE TRACKER (PAT) SYSTEM ACCOUNT:</h4>";
		$html .= "<table>";
		$tmp = '';
		if(isActive($this->PATsystem)) $tmp = 'checked';
		$html .= "<tr><td><input type='checkbox' name='PATsystem' id='PATsystem' $tmp></td><td>Activate</td></tr>";
		$html .= "</table>";	
		
		$html .= "<h4>3.) VOICE SERVICES:</h4>";
		$html .= "<table>";
		$tmp = ''; if(isActive($this->voiceservice)) $tmp = 'checked';
		$html .= "<tr><td><input type='checkbox' name='voiceservice' id='voiceservice' $tmp/>Yes</td></tr>";
		$html .= "<tr><td>Telephone Number:</td><td><input type='text' name='telephonenumber' id='telephonenumber' value='".htmlentities($this->telephonenumber)."'/>".$this->inputError('telephonenumber')."</td></tr>";
		$html .= "<tr><td>";
		$html .= "<ul style='list-style-type: none;'>";
		foreach($dataMgr->getBuildings() as $buildingID => $building)
		{
			//Check matched with loaded buildings from request
			$tmp = ''; if(array_key_exists($buildingID, $this->voiceservicebuildings)) $tmp='checked';
			$html .= "<li><input type='checkbox' name='voiceservicebuilding$buildingID' id='voiceservicebuilding$buildingID' class='voiceservicebuilding' $tmp>$building</li>";
		}
		$html .= "</ul>".$this->inputError('voiceservicebuilding')."</td><td>";
		$html .= "<table>";
		$i = 0;
		$sevicedetailindex = 0;
		foreach($services as $service => $servicetitle)
		{
			$tmp = ''; if(in_array($service, $this->serviceschosen)) $tmp='checked';
			
			if($i%2 == 0)
				$html .= "<tr>";
			$html .= "<td><input type='checkbox' name='$service' id='$service' class='services' $tmp>".$servicetitle;
			if(array_key_exists($service, $servicedetails))
			{
				$numstars = 0;
				while($numstars <= $sevicedetailindex)
					$numstars++; $html.="*";
				$sevicedetailindex++;
			}
			$html .= "</td>";
			if($i%2 == 1)
				$html .= "</tr>";
			$i++;
		}
		$html .= "</table>";
		$html .= "</td></tr></table>";
		
		$html .= "</table>";
		$html .= "<table>";
		$html .= "<tr><td>Billing Information</td></tr>";
		$html .= "<tr><td>Monthly rental:</td><td>Speedchart Code: <input type='text' name='monthlyrental' id='monthlyrental' maxlength='4' size='4' value='".htmlentities($this->monthlyrental)."'/> <strong>4 letter speed chart</strong></td><td>".$this->inputError('monthlyrental')."</td></tr>";
		$html .= "<tr><td>Long distance charges:</td><td>Speedchart Code: <input type='text' name='longdistancecharges' id='longdistancecharges' maxlength='4' size='4' value='".htmlentities($this->longdistancecharges)."'/> <strong>4 letter speed chart</strong></td><td>".$this->inputError('longdistancecharges')."</td></tr>";
		$html .= "<tr><td>Installation:</td><td>Speedchart Code: <input type='text' name='installation' id='installation' maxlength='4' size='4' value='".htmlentities($this->installation)."'/> <strong>4 letter speed chart</strong></td><td>".$this->inputError('installation')."</td></tr>";
		$html .= "</table>";
		
		$html .= "<table>";
		$j = 0;
		foreach($servicedetails as $servicedetail)
		{
			$html .= "<tr><td>";
			$k = 0;
			while($k <= $j)
			{
				$k++; $html.="*";
			}
			$j++; 
			$html .= "</td><td>".$servicedetail."</td></tr>";
		}
		$html .= "</table>";
		
		$html .= "<h4>4) PHONE LIST/ UBC DIRECTORY/ SAUDER WEBSITE</h4>";
		$html .= "<table>";
		/*private $otheroptions = array("internalphonelistregistry"=>"Internal Phone List (Staff/Faculty Telephone List)", "ubconlinedirectory" => "UBC Online Directory (White Pages: Individual & Yellow Pages: Department- <a href='http://www.directory.ubc.ca'>www.directory.ubc.ca</a>)", "sauderwebsitestaffdirectory"=>"Sauder Website Staff Directory", "sauderstaffphotodirectory"=>"Sauder Staff Photo Directory. By selecting the check box, you are consenting to having your photo listed on the Staff Photo Directory.");
		foreach($this->otheroptions as $otheroption => $otheroptiontitle)
		{
			$html .= "<tr><td><input type='checkbox' name='$otheroption' id='$otheroption'/> $otheroptiontitle</td></tr>";
		}*/
		$tmp = ''; if(isActive($this->internalPhoneListRegistry)) $tmp = 'checked';
		$html .= "<tr><td><input type='checkbox' name='internalPhoneListRegistry' id='internalPhoneListRegistry' $tmp/> Internal Phone List (Staff/Faculty Telephone List)</td></tr>";
		$tmp = ''; if(isActive($this->UBConlineDirectory)) $tmp = 'checked';
		$html .= "<tr><td><input type='checkbox' name='UBConlineDirectory' id='UBConlineDirectory' $tmp/> UBC Online Directory (White Pages: Individual & Yellow Pages: Department- <a href='http://www.directory.ubc.ca'>www.directory.ubc.ca</a>)</td></tr>";
		$tmp = ''; if(isActive($this->sauderWebsiteStaffDirectory)) $tmp = 'checked';
		$html .= "<tr><td><input type='checkbox' name='sauderWebsiteStaffDirectory' id='sauderWebsiteStaffDirectory' $tmp/> Sauder Website Staff Directory</td></tr>";
		$tmp = ''; if(isActive($this->sauderStaffPhotoDirectory)) $tmp = 'checked';
		$html .= "<tr><td><input type='checkbox' name='sauderStaffPhotoDirectory' id='sauderStaffPhotoDirectory' $tmp/> Sauder Staff Photo Directory. By selecting the check box, you are consenting to having your photo listed on the Staff Photo Directory.</td></tr>";
		$html .= "<tr><td>\n";
		$html .= "<label for='file'>Please select an image</label>\n";
    	$html .= "<input type='file' name='image' id='image'/><br>\n";
		if($this->image)
			$html .= "<img src='data:image;base64,".$this->image."'/>";
		$html .= $this->inputError('image');
		$html .= "</td></tr>";
		$html .= "</table>";
		
		$html .= "<h4>5) OTHER SERVICES NEEDED:</h4>";
		$html .= "<table>";
		$tmp = ''; if(isActive($this->individualmailslot)) $tmp = 'checked';
		$html .= "<tr><td><input type='checkbox' name='individualmailslot' id='individualmailslot' $tmp/>Individual Mail Slot</td><td></td></tr>";
		$tmp = ''; if(isActive($this->sharedmailslot)) $tmp = 'checked';
		$html .= "<tr><td><input type='checkbox' name='sharedmailslot' id='sharedmailslot' $tmp/>Shared Mail Slot. Shared with (employeeID):</td><td><input type='text' name='mailslotsharer' id='mailslotsharer' value='".htmlentities($this->mailslotsharer)."'>".$this->inputError('mailslotsharer')."</td></tr>";
		$tmp = ''; if(isActive($this->doornameplate)) $tmp = 'checked';
		$html .= "<tr><td><input type='checkbox' name='doornameplate' id='doornameplate' $tmp/>Door Name Plate</td><td><input type='text' name='doornameplatetext' id='doornameplatetext' value='".htmlentities($this->doornameplatetext)."'>".$this->inputError('doornameplatetext')."</td></tr>";
		$html .= "</table>";
		
		$html .= "<h4>6) BUILDING ACCESS (KEY/ALARM/CLASS CARD ACTIVATION):</h4>";
		$html .= "<table>";
		$tmp = ''; if(isActive($this->officelocationaccess)) $tmp = 'checked';
		$html .= "<tr><td><input type='checkbox' name='officelocationaccess' id='officelocationaccess' $tmp/>Building Access</td></tr>";
		$html .= "<tr><td>Office Location (e.g. HA 447, DL 229):</td><td><select name='officelocationforaccess' id='officelocationforaccess'>";
		$html .= "<option value='0'>-- Select One --</option>";
		foreach($dataMgr->getOfficeLocations() as $officelocation)
		{
			$tmp = '';  if($this->officelocationforaccess == $officelocation) $tmp = 'selected';
			$html .= "<option value='$officelocation' $tmp>$officelocation</option>";
		}
		$html .= "</select></td><td>".$this->inputError(('officelocationforaccess'))."</td></tr>";
		$html .= "<tr><td>Employee ID/ Student # (Required for all key requests):</td><td><input type='text' name='accessID' id='accessID' value='".htmlentities($this->accessID)."'></td><td>".$this->inputError('accessID')."</div></td></tr>";
		$html .= "<tr><td>FOB # (5 or 6 digit number on back right corner of UBC card. Eg. '456789 or 2-23466'):</td><td><input type='text' name='fobnumber' id='fobnumber' value='".htmlentities($this->fobnumber)."'></td><td>".$this->inputError('fobnumber')."</td></tr>";
		$html .= "</table>";

		$html .= "<h4>7) BUSINESS CARD</h4>";
		$html .= "<table>";
		$tmp = ''; if(isActive($this->businesscard)) $tmp = 'checked';
		$html .= "<tr><td><input type='checkbox' name='businesscard' id='businesscard' value='Yes' $tmp='checked'/>Business Card Required. <a href='http://www.sauder.ubc.ca/Resources/Faculty_Staff_Resources/Stationery'>Link to order form</a></td><td>";
		$html .= "<select name='businesscardtype' id='businesscardtype'>";
		foreach($dataMgr->getBusinessCardTypes() as $businesscardtype)
		{
			$tmp = ''; if($this->businesscardtype == $businesscardtype) $tmp = 'selected';
			$html .= "<option value='$businesscardtype' $tmp>$businesscardtype</option>";	
		}
		$html .= "</select></td></tr></table>";
		
		$html .= "<h4>8) COMPUTER / PRINTER - SAUDER IT:</h4>";
		$html .= "<table>";
		$tmp = ''; if(isActive($this->computeraccess)) $tmp = 'checked';
		$html .= "<tr><td><input type='checkbox' name='computeraccess' id='computeraccess' $tmp/></td><td><strong>Computer</strong></td></tr>";
		$tmp = ''; if(isActive($this->printingaccess)) $tmp = 'checked';
		$html .= "<tr><td><input type='checkbox' name='printingaccess' id='printingaccess' $tmp/></td><td><strong>Printing</strong></td></tr>";
		$html .= "<tr><td></td><td><ul style='list-style-type: none;'>";
		$html .= "<li>Main Speedchart for printing: <input type='text' name='mainspeedchart' id='mainspeedchart' value='".htmlentities($this->mainspeedchart)."'/>(mandatory for everyone)".$this->inputError('mainspeedchart')."</li>";
		$html .= "<li>Secondary Speedchart(s) for printing:<input type='text' name='secondaryspeedcharts' id='secondaryspeedcharts' value='".htmlentities($this->secondaryspeedcharts)."'/></li>";
		$html .= "<li>Location of back-up printer(s)(if required):<input type='text' name='backupprinterlocation' id='backupprinterlocation' disabled></li>";
		$html .= "</ul></td></tr>";
		$tmp = ''; if($this->shareddriveaccess) $tmp = 'checked';
		$html .= "<tr><td><input type='checkbox' name='shareddriveaccess' id='shareddriveaccess' $tmp/></td><td><strong>Shared Drive Access:</strong></td></tr>";
		$html .= "<tr><td></td><td>Folder Name(s)";
		$html .= "<select name='shareddriveid' id='shareddriveid'/>";
		$html .= "<option value='0'>--Select One--</option>";
		foreach($dataMgr->getSharedDrives() as $sharedDriveID => $sharedDriveName)
		{
			$tmp = ''; if($sharedDriveID == $this->shareddriveid) $tmp = 'selected';
			$html .= "<option value='$sharedDriveID' $tmp>$sharedDriveName</option>";
		}
		$html .= "</select>".$this->inputError('shareddriveid')."<td></tr>";
		$tmp = ''; if($this->emailaccess) $tmp = 'checked';
		$html .= "<tr><td><input type='checkbox' name='email' id='email' $tmp/></td><td><strong>Email</strong></td></tr>";
		$html .= "<tr><td></td><td><ul style='list-style-type: none;'>";
		$html .= "<li>User's personal email address: <input type='text' name='emailaddress' id='emailaddress' value='".htmlentities($this->emailaddress)."'/>".$this->inputError('emailaddress')."</li>";
		$html .= "<li>Add to mailing list(s):";
		$html .= "<select name='mailinglists' id='mailinglists'>";
		$html .= "<option value='0'>None</option>";
		foreach($dataMgr->getEmailLists() as $emailListID => $emailListName)
		{
			$tmp = '';if($emailListID == $this->mailinglists) $tmp ='selected';
			$html .= "<option value='$emailListID' $tmp>$emailListName</option>";
		}
		$html .= "</select>".$this->inputError('mailinglists')."</li>";
		$tmp = ''; if($this->genericmailaccountsaccess) $tmp = 'checked';
		$html .= "<li><input type='checkbox' name='genericmailaccountsaccess' id='genericmailaccountsaccess' $tmp/>Access to generic email account(s)</li>";
		$tmp = ''; if($this->editownbookingsaccess) $tmp = 'checked';
		$html .= "<li><input type='checkbox' name='editownbookingsaccess' id='editownbookingsaccess' $tmp/>Access to room calendar(s): 'create/edit own bookings' access</li>";
		$tmp = ''; if($this->editallbookingsaccess) $tmp = 'checked';
		$html .= "<li><input type='checkbox' name='editallbookingsaccess' id='editallbookingsaccess' $tmp/>Access to room calendar(s): 'create/edit all bookings' access</li>";
		$tmp = ''; if($this->readonlybookingsaccess) $tmp = 'checked';
		$html .= "<li><input type='checkbox' name='readonlybookingsaccess' id='readonlybookingsaccess' $tmp/>Access to room calendar(s): 'read only' booking access</li>";
		$html .= "</ul></td></tr>";
		
		$tmp = ''; if($this->sitecoreaccess) $tmp = 'checked';
		$html .= "<tr><td><input type='checkbox' name='siteCoreLogin' id='siteCoreLogin' $tmp></td><td><strong>SiteCore Login</strong> (access to edit the <a href='http://www.sauder.ubc.ca'>www.sauder.ubc.ca</a> website - adminstrative staff only)</td></tr>";
		$html .= "<tr><td></td><td><ul style='list-style-type: none;'>
			<li>Section of the website user needs to edit: <input type='text' name='siteCoreSection' id='siteCoreSection' value='".htmlentities($this->siteCoreSection)."'/>".$this->inputError('siteCoreSection')."</li>";
		$html .= "<li>Type of user (student/staff): <select name='siteCoreLoginUserType' id='siteCoreLoginUserType'/>
		<option value='0'>--Select One--</option>";
		foreach($siteCoreUserTypes as $siteCoreUserTypeKey => $siteCoreUserType)
		{
			$tmp = ''; if($siteCoreUserTypeKey == $this->siteCoreLoginUserType) $tmp = 'selected';
			$html .= "<option value='$siteCoreUserTypeKey' $tmp>$siteCoreUserType</option>";
		}
		$html .= "</select>".$this->inputError('siteCoreLoginUserType')."</li>";
		$tmp = ''; if($this->siteCoreTrainingRequired) $tmp = 'checked';
		$html .= "<li><input type='checkbox' name='trainingrequired' id='trainingrequired' $tmp/>Training required?</li>
			</ul></td></tr>";
		$html .= "</table>";
		$html .= "<h4>9) COMMENTS/NOTES:</h4><table><tr><td>If additional information is required (e.g. John Doe will bring his own laptop, please connect the printer in H4 447 to thia laptop, etc)</td></tr></table>";
		$html .= "<textarea name='comments' id='comments' style='width: 90%; height: 120px'>".htmlentities($this->comments)."</textarea>";
		$html .= "</div>";
		$html .= "</div>";
		
		$html .= "<input type='hidden' name='startDateSeconds' id='startDateSeconds' />\n";
        $html .= "<input type='hidden' name='endDateSeconds' id='endDateSeconds' />\n";
		
		return $html;
	}
	
	function getValidationCode()
    {
        $code = "$('#startDateSeconds').val(moment($('#startDate').val(), 'MM/DD/YYYY HH:mm').unix());\n";
        $code .= "$('#endDateSeconds').val(moment($('#endDate').val(), 'MM/DD/YYYY HH:mm').unix());\n";
		
		$code .= "
		if($('#term').val() == 0){\n
			$('#error-term').html('Need to enter term');\n
			$('#error-term').show();\n
    		error = true;}
    	else{
    		$('#error-term').html('');\n
    		$('#error-term').hide();\n
    	}";
		
		//Validate if all fields are inputted
		$code .= "if(!$('input[name=tenureStatus]').is(':checked')) {";
        $code .= "$('#error-tenureStatus').html('Need to choose one status');\n";
        $code .= "$('#error-tenureStatus').show();\n";
        $code .= "error = true;}";
        $code .= "else{";
        $code .= "$('#error-tenureStatus').html('');\n";
        $code .= "$('#error-tenureStatus').hide();\n";
		$code .= "}";
		
		$code .= "if(!$('input[name=tenureType]').is(':checked')) {";
        $code .= "$('#error-tenureType').html('Need to choose one status');\n";
        $code .= "$('#error-tenureType').show();\n";
        $code .= "error = true;}";
        $code .= "else{";
        $code .= "$('#error-tenureType').html('');\n";
        $code .= "$('#error-tenureType').hide();\n";
		$code .= "}";
		
		$code .= "if(!$('#sharedlocation').is(':checked') && !$('#nosharedlocation').is(':checked') ) {";
        $code .= "$('#error-sharedlocation').html('Need to choose a location');\n";
        $code .= "$('#error-sharedlocation').show();\n";
        $code .= "error = true;}";
        $code .= "else{";
        $code .= "$('#error-sharedlocation').html('');\n";
        $code .= "$('#error-sharedlocation').hide();\n";
		$code .= "}";
        
        $code .= "if(!$('#orientationpackage').is(':checked') && !$('#noorientationpackage').is(':checked') ) {";
        $code .= "$('#error-orientationpackage').html('Need to choose one');\n";
        $code .= "$('#error-orientationpackage').show();\n";
        $code .= "error = true;}";
        $code .= "else{";
		$code .= "$('#error-orientationpackage').html('');\n";
        $code .= "$('#error-orientationpackage').hide();\n";
		$code .= "}";
		
		$code .= "if($('#voiceservice').is(':checked')){";
		$code .= "var telno = $('#telephonenumber').val().replace(/[\s\.]+/g, '');";
		$code .= "if(telno.length < 10 || telno.length > 11) {";
        $code .= "$('#error-telephonenumber').html('Need to enter valid telephone number');\n";
        $code .= "$('#error-telephonenumber').show();\n";
        $code .= "error = true;}";
        $code .= "else{";
		$code .= "$('#error-telephonenumber').html('');\n";
        $code .= "$('#error-telephonenumber').hide();\n";
		$code .= "}";
		$code .= "if($('.voiceservicebuilding:checkbox:checked').length == 0) {";
        $code .= "$('#error-voiceservicebuilding').html('Need to select at least one building');\n";
        $code .= "$('#error-voiceservicebuilding').show();\n";
        $code .= "error = true;}";
        $code .= "else{";
		$code .= "$('#error-voiceservicebuilding').html('');\n";
        $code .= "$('#error-voiceservicebuilding').hide();\n";
		$code .= "}";
		/*$code .= "if($('#monthlyrental').val().length != 0 || $('#monthlyrental').val().length != 4 || $('#monthlyrental').val().replace(/[\d]+/g, '') != 0){ ";
		$code .= "$('#error-monthlyrental').html('Need to enter valid monthly rental speedchart');\n";
        $code .= "$('#error-monthlyrental').show();\n";
        $code .= "error = true;}";
        $code .= "else{";
		$code .= "$('#error-monthlyrental').html('');\n";
        $code .= "$('#error-monthlyrental').hide();\n";
		$code .= "}";
		$code .= "if($('#longdistancecharges').val().length != 0 || $('#longdistancecharges').val().length != 4 || $('#monthlyrental').val().replace(/[\d]+/g, '') != 0){ ";
		$code .= "$('#error-longdistancecharges').html('Need to enter valid long distance charges speedchart');\n";
        $code .= "$('#error-longdistancecharges').show();\n";
        $code .= "error = true;}";
        $code .= "else{";
		$code .= "$('#error-longdistancecharges').html('');\n";
        $code .= "$('#error-longdistancecharges').hide();\n";
		$code .= "}";
		$code .= "if($('#installation').val().length != 0 || $('#installation').val().length != 4 || $('#installation').val().replace(/[\d]+/g, '') != 0){ ";
		$code .= "$('#error-installation').html('Need to enter valid installation speedchart');\n";
        $code .= "$('#error-installation').show();\n";
        $code .= "error = true;}";
        $code .= "else{";
		$code .= "$('#error-installation').html('');\n";
        $code .= "$('#error-installation').hide();\n";
		$code .= "}";*/
		$code .= "}";
		
		$code .= "if($('#doornameplate').is(':checked')) {
				if($('#doornameplatetext').val().length == 0){\n
					$('#error-doornameplatetext').html('Need to enter text for door name plate');\n
					$('#error-doornameplatetext').show();\n
	        		error = true;}
	        	else{
	        		$('#error-doornameplatetext').html('');\n
	        		$('#error-doornameplatetext').hide();\n
	        	}
        	}";
		
		$code .= "if($('#officelocationaccess').is(':checked')) {
				if($('#officelocationforaccess').val() == 0){\n
					$('#error-officelocationforaccess').html('Need to select an office location');\n
					$('#error-officelocationforaccess').show();\n
	        		error = true;}
	        	else{
	        		$('#error-officelocationforaccess').html('');\n
	        		$('#error-officelocationforaccess').hide();\n
	        	}
				if($('#accessID').val() == 0){\n
					$('#error-accessID').html('Need to enter an employee ID or student number');\n
					$('#error-accessID').show();\n
	        		error = true;}
	        	else{
	        		$('#error-accessID').html('');\n
	        		$('#error-accessID').hide();\n
	        	}
				if($('#fobnumber').val() == 0){\n
					$('#error-fobnumber').html('Need to enter FOB number');\n
					$('#error-fobnumber').show();\n
	        		error = true;}
	        	else{
	        		$('#error-fobnumber').html('');\n
	        		$('#error-fobnumber').hide();\n
	        	}
        	}";
		
		$code .= "if($('#printingaccess').is(':checked')) {
				if($('#mainspeedchart').val() == 0){\n
					$('#error-mainspeedchart').html('Need to enter main speedchart');\n
					$('#error-mainspeedchart').show();\n
	        		error = true;}
	        	else{
	        		$('#error-mainspeedchart').html('');\n
	        		$('#error-mainspeedchart').hide();\n
	        	}
        	}";
		
		$code .= "if($('#shareddriveaccess').is(':checked')) {
				if($('#shareddriveid').val() == 0){\n
					$('#error-shareddriveid').html('Need to enter a shared drive');\n
					$('#error-shareddriveid').show();\n
	        		error = true;}
	        	else{
	        		$('#error-shareddriveid').html('');\n
	        		$('#error-shareddriveid').hide();\n
	        	}
        	}";
		
		$code .= "if($('#sauderstaffphotodirectory').is(':checked')) {
				if(document.getElementById('image').files.length == 0){\n
					$('#error-image').html('Need to attach a photo');\n
					$('#error-image').show();\n
	        		error = true;}
	        	else{
	        		$('#error-image').hide();\n
	        	}
	        }";
	        /*	if($('#mailinglists').val() == '0'){
	        		$('#error-mailinglists').html('Need to enter an email list');\n
	        		$('#error-mailinglists').show();\n
	        		error = true;}
				else{
					$('#error-mailinglists').hide();\n
	        	}
        	}*/
			
		$code .= "if($('#siteCoreLogin').is(':checked')) {
				if($('#siteCoreSection').val().length == 0){\n
					$('#error-siteCoreSection').html('Need to enter a section of the website');\n
					$('#error-siteCoreSection').show();\n
	        		error = true;}
	        	else{
	        		$('#error-siteCoreSection').html('');\n
	        		$('#error-siteCoreSection').hide();\n
	        	}
	        	if($('#siteCoreLoginUserType').val() == '0'){
	        		$('#error-siteCoreLoginUserType').html('Need to enter a type of user');\n
	        		$('#error-siteCoreLoginUserType').show();\n
	        		error = true;}
				else{
					$('#error-siteCoreLoginUserType').html('');\n
					$('#error-siteCoreLoginUserType').hide();\n
	        	}
        	}";
		/*$code .= "if($('#accessID').val()){
			if($('#accessID').val() != $('#employeeID').val())
			{
				$('#error-accessID).html('Does not match employee ID in general information.');
				$('#error-accessID').parent().show();
				error = true;
			}
		}";*/
		
        return $code;
    }
	
	function getFormScripts()
	{
		$code = "<script>";
		$code .= init_tiny_mce(false);
		$code .= $this->getScriptForDatePickers('startDate','endDate',$this->startDate, $this->endDate);
		$code .= "</script>";
		return $code;
	}
	function loadFromPost($POST, $FILES)
	{
		global $dataMgr, $services;
        #Validate the submission times
        #TODO: should probably do something smarter here
		if(array_key_exists('requestID', $POST))
			$this->requestID = intval($POST['requestID']);
		$this->requestby = $POST['requestby'];
		$this->firstname = $POST['firstname'];
		$this->lastname = $POST['lastname'];
		$this->position = $POST['position'];
		$this->officelocation = $POST['officelocation'];
		$this->employeeID = new EmployeeID($POST['employeeID']);
		$this->CWLID = $POST['CWLID'];
		$this->tenureStatus = $POST['tenureStatus'];
		$this->tenureType = $POST['tenureType'];
        $this->startDate  = intval($POST['startDateSeconds']);
        $this->stopDate  = array_key_exists('stopDate', $POST) ? intval($POST['stopDateSeconds']) : 0;
		$this->ongoing = array_key_exists('ongoing',$POST) ? 1 : 0;
		$this->term = $POST['term'];
		//$this->newlocation = $POST['newlocation'];
		//$this->oldlocation = $POST['oldlocation'];
		//$this->sharedlocation = $POST['sharedlocation'];
		//$this->otheroccupants = $POST['otheroccupants'];
		$this->orientationpackage = $POST['orientationpackage'];
		$this->PATsystem = array_key_exists('PATsystem', $POST) ? '1' : '0' ;
		$this->voiceservice = array_key_exists('voiceservice', $POST) ? '1' : '0' ;
		
		if($this->voiceservice)
		{
			$this->telephonenumber = $POST['telephonenumber'];
			foreach($dataMgr->getBuildings() as $buildingID => $building)
			{
				if(array_key_exists("voiceservicebuilding$buildingID", $POST))
					$this->voiceservicebuildings[] = $buildingID;
			}
			foreach($services as $service => $servicetitle)
			{
				$this->serviceschosen[$service] = array_key_exists($service, $POST) ? '1' : '0';
	 		}
			$this->monthlyrental = $POST['monthlyrental'];
			$this->longdistancecharges = $POST['longdistancecharges'];
			$this->installation = $POST['installation'];
		}

		/*foreach($this->otheroptions as $otheroption => $otheroptiontitle)
		{		
			$this->otheroptionschosen[$otheroption] = array_key_exists($otheroption, $POST) ? '1' : '0';
		}*/
		$this->internalPhoneListRegistry = array_key_exists('internalPhoneListRegistry' , $POST) ? '1' : '0';
		$this->UBConlineDirectory = array_key_exists('UBConlineDirectory' , $POST) ? '1' : '0';
		$this->sauderWebsiteStaffDirectory = array_key_exists('sauderWebsiteStaffDirectory' , $POST) ? '1' : '0';
		$this->sauderStaffPhotoDirectory = array_key_exists('sauderStaffPhotoDirectory', $POST) ? '1' : '0';
		if($this->sauderStaffPhotoDirectory)
		{
			$this->image = $FILES['image']['tmp_name'];
			$this->image = file_get_contents($this->image);
			$this->image = base64_encode($this->image);
			$this->imagename = $FILES['image']['name'];
			$imagesize = getimagesize($FILES['image']['tmp_name']);
		}
		
		$this->individualmailslot = array_key_exists('individualmailslot', $POST) ? '1' : '0';
		$this->sharedmailslot = array_key_exists('sharedmailslot', $POST) ? '1' : '0';
		if($this->sharedmailslot)
			$this->mailslotsharer = intval($POST['mailslotsharer']);
		$this->doornameplate = array_key_exists('doornameplate', $POST) ? '1' : '0';
		if($this->doornameplate)
			$this->doornameplatetext = cleanString($POST['doornameplatetext']);
		
		$this->officelocationaccess = array_key_exists('officelocationaccess', $POST) ? '1' : '0';
		if($this->officelocationaccess)
		{
			$this->officelocationforaccess = $POST['officelocationforaccess'];
			$this->accessID = $POST['accessID'];
			$this->fobnumber = $POST['fobnumber'];
		}
		
		$this->businesscard = array_key_exists('businesscard', $POST) ? '1' : '0' ;
		if($this->businesscard)
		{
			$this->businesscardtype = $POST['businesscardtype'];
		}

		$this->computeraccess = array_key_exists('computeraccess', $POST) ? '1' : '0';
		
		$this->printingaccess = array_key_exists('printingaccess', $POST) ? '1' : '0';
		if($this->printingaccess)
		{
			$this->mainspeedchart = $POST['mainspeedchart'];
			$this->secondaryspeedcharts = explode(', ', $POST['secondaryspeedcharts']);
			//$this->backupprinterlocation = $POST['backupprinterlocation'];
		}
		$this->shareddriveaccess = array_key_exists('shareddriveaccess', $POST) ? '1' : '0';
		if($this->shareddriveaccess)
		{
			$this->shareddriveid = $POST['shareddriveid'];
		}
		$this->emailaccess = array_key_exists('email', $POST) ? '1' : '0';
		if($this->emailaccess)
		{
			$this->mailinglists = $POST['mailinglists'];
			$this->genericmailaccountsaccess = array_key_exists('genericmailaccountsaccess', $POST) ? '1' : '0';
			$this->editownbookingsaccess = array_key_exists('editownbookingsaccess', $POST) ? '1' : '0';
			$this->editallbookingsaccess = array_key_exists('editallbookingsaccess', $POST) ? '1' : '0';
			$this->readonlybookingsaccess = array_key_exists('readonlybookingsaccess', $POST) ? '1' : '0';
			$this->emailaddress = $POST['emailaddress'];
		}
		$this->sitecoreaccess = array_key_exists('siteCoreLogin', $POST) ? '1' : '0';
		if($this->sitecoreaccess)
		{
			$this->siteCoreLoginUserType = $POST['siteCoreLoginUserType'];
			$this->siteCoreSection = $POST['siteCoreSection'];
			$this->siteCoreTrainingRequired = array_key_exists('trainingrequired', $POST) ? '1' : '0';
		}
		$this->comments = $POST['comments'] ? $POST['comments'] : "";
		//print_r($this);
	}

	function inputError($name)
	{
		return "<div style='color:red' id='error-$name'></div>";
	}
		
	#Function for the date pickers
	static private function getScriptForDatePickers($startID, $stopID, $startDate='', $stopDate='')
	{
		$minDate = 'null';
		$maxDate = 'null';
		if($startDate != '')
			$minDate = "new Date(".($startDate*1000).")";
		if($stopDate != '')
			$maxDate = "new Date(".($stopDate*1000).")";
		return "<script type='text/javascript'>
				$('#$startID').datetimepicker({
					//maxDate: $maxDate,
					showOtherMonths: true,
					selectOtherMonths: true,
					defaultDate : $minDate,
					onClose: function(dateText, inst) {
						var endDateTextBox = $('#$stopID');
						if (endDateTextBox.val() != '') {
							var testStartDate = new Date(dateText);
							var v = endDateTextBox.val();
							var testEndDate = new Date(endDateTextBox.val());
							if (testStartDate > testEndDate)
								endDateTextBox.val(dateText);
						}
						else {
							endDateTextBox.val(dateText);
						}
					},
					onSelect: function (selectedDateTime){
						var start = $(this).datetimepicker('getDate');
						var d = new Date($('#$stopID').datetimepicker('getDate'));
						$('#$stopID').datetimepicker('option', 'minDate', new Date(start.getTime()));
						$('#$stopID').val(zeroFill(d.getMonth() + 1, 2) + '/' + zeroFill(d.getDate(), 2) + '/' + d.getFullYear() + ' ' + zeroFill(d.getHours(), 2) + ':' + zeroFill(d.getMinutes(), 2));
					},
				});
				$('#$stopID').datetimepicker({
					//minDateTime: $minDate,
					showOtherMonths: true,
					selectOtherMonths: true,
					defaultDate : $maxDate,
					onClose: function(dateText, inst) {
						var startDateTextBox = $('#$startID');
						if (startDateTextBox.val() != '') {
							var testStartDate = new Date(startDateTextBox.val());
							var testEndDate = new Date(dateText);
							if (testStartDate > testEndDate)
								startDateTextBox.val(dateText);
						}
						else {
							startDateTextBox.val(dateText);
						}
					},
					onSelect: function (selectedDateTime){
						var end = $(this).datetimepicker('getDate');
						var d = new Date($('#$startID').datetimepicker('getDate'));
						$('#$startID').datetimepicker('option', 'maxDate', new Date(end.getTime()));
						$('#$startID').val(zeroFill(d.getMonth() + 1, 2) + '/' + zeroFill(d.getDate(), 2) + '/' + d.getFullYear() + ' ' + zeroFill(d.getHours(), 2) + ':' + zeroFill(d.getMinutes(), 2));
					}
	});".
		set_element_to_date($startID, $startDate, "val", "MM/DD/YYYY HH:mm", false, true).
		set_element_to_date($stopID, $stopDate, "val", "MM/DD/YYYY HH:mm", false, true).
	   "</script>";
	}
}

function isActive($var)
{
	return $var == 'pending' || $var == 'done';
}
?>