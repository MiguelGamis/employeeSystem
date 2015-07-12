<?php

class EmployeeDataManager
{

	private $view;
	private $db;
	public $authMgr;
	public $groupID = NULL;
	public $groupName = NULL;
	public $shortName = NULL;
    function prepareQuery($name, $query)
    {
        if(!isset($this->$name)) {
            $this->$name = $this->db->prepare($query);
        }
        return $this->$name;
    }
	
    function __construct()
    {
			
			global $MTA_DATAMANAGER_PDO_CONFIG;
			if(!isset($MTA_DATAMANAGER_PDO_CONFIG["dsn"])) { die("PDO Data manager needs a DSN"); }
			if(!isset($MTA_DATAMANAGER_PDO_CONFIG["username"])) { die("PDODataManager needs a database user name"); }
			if(!isset($MTA_DATAMANAGER_PDO_CONFIG["password"])) { die("PDODataManager needs a database user password"); }
			//Load up a connection to the database
			$this->db = new PDO($MTA_DATAMANAGER_PDO_CONFIG["dsn"],
								$MTA_DATAMANAGER_PDO_CONFIG["username"],
								$MTA_DATAMANAGER_PDO_CONFIG["password"],
								array(PDO::ATTR_PERSISTENT => true));

			$this->db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
			$this->db->exec("SET NAMES 'utf8';");

			$this->isUserQuery = $this->db->prepare("SELECT userID FROM users WHERE userID=? ;"); //&& userType IN ('instructor', 'student', 'marker');");
			$this->isStudentQuery = $this->db->prepare("SELECT userID FROM users WHERE userID=? && userType = 'student';");
			$this->isUserByNameQuery = $this->db->prepare("SELECT userID FROM users WHERE groupID=? AND username=?;");
			$this->userIDQuery = $this->db->prepare("SELECT userID FROM users WHERE username=? ;");
			
			$this->isAllSeeingQuery = $this->db->prepare("SELECT userID FROM users WHERE userID=? AND groupID = '1';");
			$this->getBuildingsQuery = $this->db->prepare("SELECT buildingID, building FROM building;");
			$this->addNEWEmployeeQuery = $this->db->prepare("INSERT INTO newemployee (employeeID) VALUES (:employeeID);");
			$this->addEmployeeQuery = $this->db->prepare("INSERT INTO employee (employeeID, firstName, lastName, CWLID, isNew) VALUES (:employeeID, :firstName, :lastName, :CWLID, '1') ON DUPLICATE KEY UPDATE firstName=:firstName, lastName=:lastName, CWLID=:CWLID;");
			$this->isEmployeeQuery = $this->db->prepare("SELECT employeeID FROM employee WHERE employeeID = ?;");
			$this->addVoiceServiceQuery = $this->db->prepare("INSERT INTO voiceService (employeeID, building, singleline, multiline, moveupdatelocation, updatedisplayname, voicemail, unifiedmessaging, resetvoicemailpassword, requestlongdistancecode, setupcallersmenu, monthlyRental, longDistanceCharges, installation) 
															VALUES (:employeeID, :building, :singleline, :multiline, :moveupdatelocation, :updatedisplayname, :voicemail, :unifiedmessaging, :resetvoicemailpassword, :requestlongdistancecode, :setupcallersmenu, :monthlyRental, :longDistanceCharges, :installation);");
			$this->addLocationAccessQuery = $this->db->prepare("INSERT INTO locationAccess (employeeID, locationID, FOBNumber) VALUES (:employeeID, :locationID, :FOBNumber);");
			$this->addMailAccessQuery = $this->db->prepare("INSERT INTO mailAccess (mailSlotID, employeeID, requestID) VALUES (:mailSlotID, :employeeID, :requestID)");
			$this->addSharedDriveAccessQuery = $this->db->prepare("INSERT INTO sharedDriveAccess (sharedDriveID, employeeID) VALUES (:sharedDriveID, :employeeID);");
			$this->addDivisionMembershipQuery = $this->db->prepare("INSERT INTO divisionMembership (divisionID, employeeID) VALUES (:divisionID, :employeeID);");
			$this->addEmailToGroupQuery = $this->db->prepare("INSERT INTO emailGrouping (emailAddress, emailListID, requestID) VALUES (:emailAddress, :emailListID, :requestID);");
			$this->addSiteCoreLoginQuery = $this->db->prepare("INSERT INTO siteCoreLogin (employeeID, userType, trainingRequired) VALUES (:employeeID, :userType, :trainingRequired);");
						
			$this->doesLocationExistQuery = $this->db->prepare("SELECT locationID FROM location WHERE locationName = ?");
			$this->doesMailExistQuery = $this->db->prepare("SELECT mailSlotID FROM mailSlot WHERE doorNamePlate = ?");
			$this->doesDriveExistQuery = $this->db->prepare("SELECT sharedDriveID FROM sharedDrive WHERE sharedDriveName = ?");
			$this->doesEmailGroupExistQuery = $this->db->prepare("SELECT emailListID FROM emailList WHERE emailListName = ?");
			$this->getUserDisplayMapQuery = $this->db->prepare("SELECT userID, username, firstName, lastName FROM users WHERE groupID = ?");
			$this->getUserDisplayNameQuery = $this->db->prepare("SELECT firstName, lastName FROM users WHERE userID=?;");
			$this->getUsernameQuery = $this->db->prepare("SELECT username FROM users WHERE userID=?;");
    }
	
	function createAuthManager()
    {
            //Figure out what kind of auth manager we are using
            require_once(MTA_ROOTPATH."inc/authmanager.php");
			$this->authMgr = new Authmanager($this);
            return $this->authMgr;
    }
	
	function getDatabase()
    {
        return $this->db;
    }
	
    function isUserByName($username)
    {
        $this->isUserByNameQuery->execute(array($this->groupID, $username));
        return $this->isUserByNameQuery->fetch() != NULL;
    }
	
	function getUserID($username)
    {
        $this->userIDQuery->execute(array($username));
        $res = $this->userIDQuery->fetch();
        if(!$res)
            throw new Exception("Could not get a user id for '$username'");
        return new UserID($res->userID);
    }
	
	function getUsername(UserID $userID)
    {
        $this->getUsernameQuery->execute(array($userID));
        if(!$res = $this->getUsernameQuery->fetch())
        {
            throw new Exception("No user with id '$userID'");
        }
        else
        {
            return $res->username;
        }
    }
	
	function isUser(UserID $userid)
    {
        $this->isUserQuery->execute(array($userid));
        return $this->isUserQuery->fetch() != NULL;
    }
	
	function setViewFromID(CourseID $id)
    {
        //Get the course information
        $sh = $this->db->prepare("SELECT name, displayName, authType, registrationType FROM course WHERE courseID = ?;");
        $sh->execute(array($id));
        if(!$res = $sh->fetch())
        {
            throw new Exception("Invalid course id '$id'");
        }
        $this->courseID = new CourseID($id);
    }
	
	function getBuildings()
    {
        //Get all the buildings
        $this->getBuildingsQuery->execute();
        $buildings = array();
        while($res = $this->getBuildingsQuery->fetch())
        {
           $buildings[$res->buildingID] = $res->building;
        }
		return $buildings;
    }
	
	function saveEntryRequest($request)
    {
    	if(!$request->requestID)
		{
	        $requestAdded = false;
			$this->addEmployee($request);
			//add request to db and assign request id to request
	        $this->addEntryRequest($request);
			$this->addTenure($request);
	        $requestAdded = true;
	
			if($request->orientationpackage)
				$this->pendOrientationPackage($request);
			if($request->PATsystem)
				$this->pendPATSystemAccount($request);
			if($request->individualmailslot)
				$this->pendIndividualMailSlotAccessor($request);
			if($request->internalPhoneListRegistry)
				$this->pendInternalPhoneListRegistry($request);
			if($request->UBConlineDirectory)
				$this->pendUBConlineDirectory($request);
			if($request->sauderWebsiteStaffDirectory)
				$this->pendSauderWebsiteStaffDirectory($request);
			if($request->sauderStaffPhotoDirectory)
				$this->pendSauderStaffPhotoDirectory($request);
			if($request->voiceservice)
				$this->pendVoiceService($request);
			if($request->sharedmailslot)
				$this->pendSharedMailSlotAccessor($request);
			if($request->doornameplate)
				$this->pendDoorNamePlate($request);
			if($request->officelocationaccess)
				$this->pendLocationAccess($request);
			if($request->businesscard)
				$this->pendBusinessCard($request);
			if($request->computeraccess)
				$this->pendComputerAccess($request);
			if($request->printingaccess)
			{
				$this->pendMainPrintingAccess($request);
				$this->pendSecondaryPrintingAccess($request);
			}
			if($request->shareddriveid)
				$this->pendSharedDriveAccess($request);
			if($request->emailaccess)
			{
				$this->pendEmail($request);
				if($request->mailinglists)
					$this->pendEmailGrouping($request);
			}
			if($request->sitecoreaccess)
				$this->pendSiteCoreLogin($request);
			
	        //We have to remove the assignment if anything else fails
	        /*try
	        {
	            $this->saveRequestDetails($request, $requestAdded);
	        }catch(Exception $e) {
	            if($requestAdded)
	                $this->removeRequest($request->requestID);
	            throw $e;
	        }*/
        } else {
        	$requestUpdated = false;
			$this->addEmployee($request);
			//add request to db and assign request id to request
	        $this->updateEntryRequest($request);
			$this->updateTenure($request);
			$requestUpdated = true;
			
			if($request->orientationpackage)
				$this->pendOrientationPackage($request);
			else
				$this->removeTask(new RequestID($request->requestID), 'orientationPackage');
			
			if($request->PATsystem)
				$this->pendPATSystemAccount($request);
			else
				$this->removeTask(new RequestID($request->requestID), 'PATSystemAccount');
			
			if($request->voiceservice)
				$this->pendVoiceService($request);
			else
				$this->removeTask(new RequestID($request->requestID), 'voiceService');
			
			
			if($request->internalPhoneListRegistry)
				$this->pendInternalPhoneListRegistry($request);
			else
				$this->removeTask(new RequestID($request->requestID), 'internalPhoneListRegistry');
			if($request->UBConlineDirectory)
				$this->pendUBConlineDirectory($request);
			else
				$this->removeTask(new RequestID($request->requestID), 'UBConlineDirectory');
			if($request->sauderWebsiteStaffDirectory)
				$this->pendSauderWebsiteStaffDirectory($request);
			else
				$this->removeTask(new RequestID($request->requestID), 'sauderWebsiteStaffDirectory');
			if($request->sauderStaffPhotoDirectory)
				$this->pendSauderStaffPhotoDirectory($request);	
			else
				$this->removeTask(new RequestID($request->requestID), 'sauderStaffPhotoDirectory');			
			
			if($request->individualmailslot)
				$this->pendIndividualMailSlotAccessor($request);
			else 
				$this->removeIndividualMailSlotAccessor(new RequestID($request->requestID));
			if($request->sharedmailslot)
				$this->pendSharedMailSlotAccessor($request);
			else
				$this->removeSharedMailSlotAccessor(new RequestID($request->requestID));
			if($request->doornameplate)
				$this->pendDoorNamePlate($request);
			else
				$this->removeTask(new RequestID($request->requestID), 'doorNamePlate');
			if($request->officelocationaccess)
				$this->pendLocationAccess($request);
			else
				$this->removeTask(new RequestID($request->requestID), 'locationAccess');
			if($request->businesscard)
				$this->pendBusinessCard($request);
			else
				$this->removeTask(new RequestID($request->requestID), 'businessCard');
			if($request->computeraccess)
				$this->pendComputerAccess($request);
			else 
				$this->removeTask(new RequestID($request->requestID), 'computer');
			if($request->printingaccess)
			{
				foreach($this->getSpeedChartsByEmployee($request->employeeID, 'pending') as $speedChart)
				{
					//Cancel unapproved printing access
					$this->cancelPrintingAccess(new RequestID($request->requestID), $speedChart);
				}
				foreach($this->getSpeedChartsByEmployee($request->employeeID, 'done') as $speedChart)
				{
					//Set approved printing access to be removed
					$this->removePrintingAccess(new RequestID($request->requestID), $speedChart);
				}
				
				$this->pendMainPrintingAccess($request);
				$this->pendSecondaryPrintingAccess($request);
			}
			else
			{
				foreach($this->getSpeedChartsByRequest(new RequestID($request->requestID), 'pending') as $speedChart)
				{
					//Cancel unapproved printing access
					$this->cancelPrintingAccess(new RequestID($request->requestID), $speedChart);
				}
				foreach($this->getSpeedChartsByRequest(new RequestID($request->requestID), 'done') as $speedChart)
				{
					//Set approved printing access to be removed
					$this->removePrintingAccess(new RequestID($request->requestID), $speedChart);
				}
			}
			
			if($request->shareddriveid)
			{
				foreach($this->getSharedDriveAccessesByRequest(new RequestID($request->requestID), 'pending') as $sharedDrive)
				{
					$this->cancelSharedDriveAccess(new RequestID($request->requestID), $sharedDrive);
				}
				foreach($this->getSharedDriveAccessesByRequest(new RequestID($request->requestID), 'done') as $sharedDrive)
				{
					$this->removeSharedDriveAccess(new RequestID($request->requestID), $sharedDrive);
				}
				$this->pendSharedDriveAccess($request);
			}
			else
			{
				foreach($this->getSharedDriveAccessesByRequest(new RequestID($request->requestID), 'pending') as $sharedDrive)
				{
					$this->cancelSharedDriveAccess(new RequestID($request->requestID), $sharedDrive);
				}
				foreach($this->getSharedDriveAccessesByRequest(new RequestID($request->requestID), 'done') as $sharedDrive)
				{
					$this->removeSharedDriveAccess(new RequestID($request->requestID), $sharedDrive);
				}
			}
				
			if($request->emailaccess)
			{
				$this->pendEmail($request);
				if($request->mailinglists)
				{
					foreach($this->getEmailGroupsByRequest(new RequestID($request->requestID), 'pending') as $emailListID)
					{ 
						$this->cancelEmailGrouping(new RequestID($request->requestID), $emailListID);
					}
					foreach($this->getEmailGroupsByRequest(new RequestID($request->requestID), 'done') as $emailListID)
					{ 
						$this->removeEmailGrouping(new RequestID($request->requestID), $emailListID);
					}
					$this->pendEmailGrouping($request);
				}
				else
				{
					foreach($this->getEmailGroupsByRequest(new RequestID($request->requestID), 'pending') as $emailListID)
					{ 
						$this->cancelEmailGrouping(new RequestID($request->requestID), $emailListID);
					}
					foreach($this->getEmailGroupsByRequest(new RequestID($request->requestID), 'done') as $emailListID)
					{ 
						$this->removeEmailGrouping(new RequestID($request->requestID), $emailListID);
					}
				}
			}
			else 
			{
				$this->removeEmail(new RequestID($request->requestID));
				$this->cancelEmail(new RequestID($request->requestID));
				foreach($this->getEmailGroupsByRequest(new RequestID($request->requestID), 'pending') as $emailList)
				{ 
					$this->cancelEmailGrouping(new RequestID($request->requestID), $emailListID);
				}
				foreach($this->getEmailGroupsByRequest(new RequestID($request->requestID), 'done') as $emailList)
				{ 
					$this->removeEmailGrouping(new RequestID($request->requestID), $emailListID);
				}
			}
			
			if($request->sitecoreaccess)
			{
				$this->pendSiteCoreLogin($request);
			}
			else 
			{
				$this->removeSiteCoreLogin(new RequestID($request->requestID));
				$this->cancelSiteCoreLogin(new RequestID($request->requestID));
			}
			
	        //We have to remove the assignment if anything else fails
	        /*try
	        {
	            $this->saveRequestDetails($request, $requestAdded);
	        }catch(Exception $e) {
	            if($requestAdded)
	                $this->removeRequest($request->requestID);
	            throw $e;
	        }*/
        }
    }

	function addEntryRequest($request)
	{
		$this->addEntryRequestQuery = $this->db->prepare("INSERT INTO request (requestBy, requestType, status, dateSubmitted, comments, employeeID) VALUES (:requestBy, :requestType, 'pending', FROM_UNIXTIME(:dateSubmitted), :comments, :employeeID);");
	    $this->addEntryRequestQuery->execute(array('requestBy'=>$request->requestby, 'requestType'=>'entry', 'dateSubmitted'=>time(), 'comments'=>$request->comments, 'employeeID'=>$request->employeeID));
		$request->requestID = new RequestID($this->db->lastInsertID());
	}
	
	function updateEntryRequest($request)
	{
		$this->updateEntryRequestQuery = $this->db->prepare("UPDATE request SET requestBy=:requestBy, requestType=:requestType, status='pending', dateSubmitted=:dateSubmitted, comments=:comments, employeeID=:employeeID WHERE requestID = :requestID;");
		$this->updateEntryRequestQuery->execute(array('requestBy'=>$request->requestby, 'requestType'=>'entry', 'dateSubmitted'=>time(), 'comments'=>$request->comments, 'employeeID'=>$request->employeeID, 'requestID'=>$request->requestID));
	}
	
	function loadEntryRequest(RequestID $requestID)
	{
		$request = new Request();
		$request->requestID = $requestID->id;
		$request->requestType = 'entry';
		$this->loadEntryRequestQuery = $this->db->prepare("SELECT requestBy, status, UNIX_TIMESTAMP(dateSubmitted) AS dateSubmitted, comments, employeeID FROM request WHERE requestID = ?");
		$this->loadEntryRequestQuery->execute(array($requestID));
		$res_request = $this->loadEntryRequestQuery->fetch();
		$request->comments = $res_request->comments;
		$request->dateSubmitted = $res_request->dateSubmitted;
		$request->employeeID = $res_request->employeeID;
		
		$this->loadEmployeeQuery = $this->db->prepare("SELECT firstName, lastName, CWLID, isNew FROM employee WHERE employeeID = ?");
		$this->loadEmployeeQuery->execute(array($request->employeeID));
		$res_employee = $this->loadEmployeeQuery->fetch();
		$request->firstname = $res_employee->firstName;
		$request->lastname = $res_employee->lastName;
		$request->CWLID = $res_employee->CWLID;
		
		$this->loadTenureQuery = $this->db->prepare("SELECT requestID, employeeID, position, locationName, UNIX_TIMESTAMP(startDate) as startDate, UNIX_TIMESTAMP(endDate) as endDate, ongoing, term, tenureStatus, tenureType, status FROM tenure WHERE requestID = ?");
		$this->loadTenureQuery->execute(array($requestID));
		$res_tenure = $this->loadTenureQuery->fetch();
		$request->position = $res_tenure->position;
		$request->officelocation = $res_tenure->locationName;
		$request->tenureStatus = $res_tenure->tenureStatus;
		$request->tenureType = $res_tenure->tenureType;
		$request->startDate = $res_tenure->startDate;
		$request->endDate = $res_tenure->endDate;
		$request->ongoing = $res_tenure->ongoing;
		$request->term = $res_tenure->term;
		
		$this->loadOrientationPackageQuery = $this->db->prepare("SELECT status FROM orientationPackage WHERE requestID = ?");
		$this->loadOrientationPackageQuery->execute(array($requestID));
		$res_orientation = $this->loadOrientationPackageQuery->fetch();
		if($res_orientation)
			$request->orientationpackage = $res_orientation->status;
		
		$this->loadPATSystemAccountQuery = $this->db->prepare("SELECT status FROM PATSystemAccount WHERE requestID = ?");
		$this->loadPATSystemAccountQuery->execute(array($requestID));
		$res_PATSystem = $this->loadPATSystemAccountQuery->fetch();
		if($res_PATSystem)
			$request->PATsystem = $res_PATSystem->status;
		
		$this->loadVoiceServiceQuery = $this->db->prepare("SELECT telephoneNumber, status FROM voiceService WHERE requestID = ?");
		$this->loadVoiceServiceQuery->execute(array($requestID));
		$res_voiceService = $this->loadVoiceServiceQuery->fetch();
		if($res_voiceService)
		{
			$request->voiceservice = $res_voiceService->status;
			$request->telephonenumber = $res_voiceService->telephoneNumber;
			$request->serviceschosen = $this->loadVoiceServiceServicesRequested($requestID);
			$request->voiceservicebuildings = $this->loadVoiceServiceBuildingsRequest($requestID);
			$this->loadVoiceServiceSpeedChartsRequest($request);
		}
		
		$this->loadInternalPhoneListRegistryQuery = $this->db->prepare("SELECT status FROM internalPhoneListRegistry WHERE requestID = ?");
		$this->loadInternalPhoneListRegistryQuery->execute(array($requestID));
		$res_internalPhoneListRegistry = $this->loadInternalPhoneListRegistryQuery->fetch();
		if($res_internalPhoneListRegistry)
			$request->internalPhoneListRegistry = $res_internalPhoneListRegistry->status;
		
		$this->loadUBConlineDirectoryQuery = $this->db->prepare("SELECT status FROM UBConlineDirectory WHERE requestID = ?");
		$this->loadUBConlineDirectoryQuery->execute(array($requestID));
		$res_UBConlineDirectory = $this->loadUBConlineDirectoryQuery->fetch();
		if($res_UBConlineDirectory)
			$request->UBConlineDirectory = $res_UBConlineDirectory->status;
		
		$this->loadSauderWebsiteStaffDirectory = $this->db->prepare("SELECT status FROM sauderWebsiteStaffDirectory WHERE requestID = ?");
		$this->loadSauderWebsiteStaffDirectory->execute(array($requestID));
		$res_sauderWebsiteStaffDirectory = $this->loadSauderWebsiteStaffDirectory->fetch();
		if($res_sauderWebsiteStaffDirectory)
			$request->sauderWebsiteStaffDirectory = $res_sauderWebsiteStaffDirectory->status;
		
		$this->loadSauderStaffPhotoDirectory = $this->db->prepare("SELECT imageName, image, status FROM sauderStaffPhotoDirectory WHERE requestID = ?");
		$this->loadSauderStaffPhotoDirectory->execute(array($requestID));
		$res_sauderStaffPhotoDirectory = $this->loadSauderStaffPhotoDirectory->fetch();
		if($res_sauderStaffPhotoDirectory)
		{
			$request->sauderStaffPhotoDirectory = $res_sauderStaffPhotoDirectory->status;
			$request->imagename = $res_sauderStaffPhotoDirectory->imageName;
			$request->image = $res_sauderStaffPhotoDirectory->image;
		}
		
		/*$this->loadIndividualMailSlotQuery = $this->db->prepare("SELECT status FROM mailSlotAccess")
		$request->individualmailslot = $res->ownMail;
		$request->sharedmailslot = $res->sharedMail;*/
		
		$this->loadDoorNamePlateQuery = $this->db->prepare("SELECT status, doorNamePlateText FROM doorNamePlate WHERE requestID = ?");
		$this->loadDoorNamePlateQuery->execute(array($requestID));
		$res_doorNamePlate = $this->loadDoorNamePlateQuery->fetch();
		if($res_doorNamePlate)
		{
			$request->doornameplate = $res_doorNamePlate->status;
			$request->doornameplatetext = $res_doorNamePlate->doorNamePlateText;
		}
		
		$this->loadOfficeLocationAccessQuery = $this->db->prepare("SELECT accessID, locationName, FOBNumber, status FROM locationAccess WHERE requestID = ?");
		$this->loadOfficeLocationAccessQuery->execute(array($requestID));
		$res_officelocation = $this->loadOfficeLocationAccessQuery->fetch();
		if($res_officelocation)
		{
			$request->officelocationaccess = $res_officelocation->status;
			$request->officelocationforaccess = $res_officelocation->locationName;
			$request->fobnumber = $res_officelocation->FOBNumber;
			$request->accessID= $res_officelocation->accessID;
		}
		
		$this->loadBusinessCardQuery = $this->db->prepare("SELECT businessCardType, status FROM businessCard WHERE requestID = ?");
		$this->loadBusinessCardQuery->execute(array($requestID));
		$res_businessCard = $this->loadBusinessCardQuery->fetch();
		if($res_businessCard)
		{
			$request->businesscard = $res_businessCard->status;
			$request->businesscardtype = $res_businessCard->businessCardType;
		}
		
		$this->loadComputerAccessQuery = $this->db->prepare("SELECT status FROM computer WHERE requestID = ?");
		$this->loadComputerAccessQuery->execute(array($requestID));
		$res_computer = $this->loadComputerAccessQuery->fetch();
		if($res_computer)
		{
			$request->computeraccess = $res_computer->status;
		}
		
		$this->loadPrintingAccessQuery = $this->db->prepare("SELECT status FROM printingAccess WHERE requestID = ?");
		$this->loadPrintingAccessQuery->execute(array($requestID));
		$res_printingAccessQuery = $this->loadPrintingAccessQuery->fetch();
		if($res_printingAccessQuery)
		{
			$request->printingaccess = $res_printingAccessQuery->status;
		}
		$this->loadMainSpeedChartQuery = $this->db->prepare("SELECT speedChart FROM printingAccess WHERE main = '1' AND (status = 'pending' OR status = 'done') AND requestID = ?");
		$this->loadMainSpeedChartQuery->execute(array($request->requestID));
		$res_printingAccessQuery = $this->loadMainSpeedChartQuery->fetch();
		$request->mainspeedchart = $res_printingAccessQuery->speedChart;
		$this->loadSecondarySpeedChartQuery = $this->db->prepare("SELECT speedChart FROM printingAccess WHERE main = '0' AND (status = 'pending' OR status = 'done') AND requestID = ?");
		$this->loadSecondarySpeedChartQuery->execute(array($request->requestID));
		$secondaryspeedcharts = array();
		while($res_printingAccessQuery = $this->loadSecondarySpeedChartQuery->fetch())
		{
			$secondaryspeedcharts[] = $res_printingAccessQuery->speedChart;
		}
		if(sizeof($secondaryspeedcharts))
		{
			$string = implode(', ', $secondaryspeedcharts);
			$request->secondaryspeedcharts = $string;
		}
		
		$this->loadSharedDriveAccessQuery = $this->db->prepare("SELECT sharedDriveID, status FROM sharedDriveAccess WHERE requestID = ?");
		$this->loadSharedDriveAccessQuery->execute(array($requestID));
		while($res_shareddrive = $this->loadSharedDriveAccessQuery->fetch())
		{
			$request->shareddriveaccess = $res_shareddrive->status;
			$request->shareddriveid = $res_shareddrive->sharedDriveID;
		}
		
		$this->loadEmailQuery = $this->db->prepare("SELECT employeeID, requestID, emailAddress, genericmailaccount, editownbookingsaccess, editallbookingsaccess, readonlybookingsaccess, status FROM email WHERE requestID = ?");
		$this->loadEmailQuery->execute(array($requestID));
		$res_emailQuery = $this->loadEmailQuery->fetch();
		if($res_emailQuery)
		{
			$request->emailaccess = $res_emailQuery->status;
			$request->emailaddress = $res_emailQuery->emailAddress;
			$request->genericmailaccountsaccess = $res_emailQuery->genericmailaccount;
			$request->editownbookingsaccess = $res_emailQuery->editownbookingsaccess;
			$request->editallbookingsaccess = $res_emailQuery->editallbookingsaccess;
			$request->readonlybookingsaccess = $res_emailQuery->readonlybookingsaccess;
		}
		
		$this->loadEmailGroupingQuery = $this->db->prepare("SELECT emailListID FROM emailGrouping WHERE requestID = ?");
		$this->loadEmailGroupingQuery->execute(array($requestID));
		while($res_emailgroup = $this->loadEmailGroupingQuery->fetch())
		{
			$request->mailinglists = $res_emailgroup->emailListID;
		}
		
		$this->loadSiteCoreLoginQuery = $this->db->prepare("SELECT sectionName, userType, trainingRequired, status FROM siteCoreLogin WHERE requestID = ?");
		$this->loadSiteCoreLoginQuery->execute(array($requestID));
		$res_sitecorelogin = $this->loadSiteCoreLoginQuery->fetch();
		if($res_sitecorelogin)
		{
			$request->sitecoreaccess = $res_sitecorelogin->status;
			$request->siteCoreSection = $res_sitecorelogin->sectionName;
			$request->siteCoreLoginUserType = $res_sitecorelogin->userType;
			$request->siteCoreTrainingRequired = $res_sitecorelogin->trainingRequired;			
		}
		/*
		if($res->siteCoreLogin)
		{
			$siteCoreLoginDetails = $this->loadSiteCoreLoginRequest(new RequestID($requestID));
			$request->siteCoreSection = $siteCoreLoginDetails->sectionName;
			$request->siteCoreLoginUserType = $siteCoreLoginDetails->userType;
			$request->siteCoreTrainingRequired = $siteCoreLoginDetails->trainingRequired;
		}
		//doorNamePlate
		if($res->doorNamePlate)
		{
			$doorNamePlateDetails = $this->loadDoorNamePlateRequest(new RequestID($requestID));
			$request->doornameplatetext = $doorNamePlateDetails->doorNamePlateText;
		}
		//sharedMailSlot
		if($res->sharedMail)
		{
			$request->mailslotsharer = $this->loadSharedMailSlotSharer(new RequestID($requestID));
		}
		//locationAccess
		if($res->locationAccess)
		{
			$this->loadLocationAccessRequest($request);
		}
		//sharedDriveAccess
		if($res->sharedDriveAccess)
		{
			$this->loadSharedDriveAccessRequest($request);
		}
		//printingAccess
		if($res->printingAccess)
		{
			$this->loadPrintingAccessRequest($request);
		}
		//email
		if($res->email)
		{
			$this->loadEmailRequest($request);
			$this->loadEmaiListRequest($request);
		}
		//voiceService
		if($res->voiceService)
		{
			$this->loadVoiceServiceRequest($request);
		}
		//businessCard	
		if($res->businessCard)
		{
			$businessCardDetails = $this->loadBusinessCardRequest($requestID);
			$request->businesscardtype = $businessCardDetails->businessCardType;
		}
		//sauderStaffPhotoDirectory
		if($res->sauderStaffPhotoDirectory)
		{
			$sauderStaffPhotoDirectoryDetails = $this->loadSauderStaffPhotoDirectoryRequest($requestID);
			$request->imageName = $sauderStaffPhotoDirectoryDetails->imageName;
			$request->image = $sauderStaffPhotoDirectoryDetails->image;
		}*/
		
		return $request;
	}

	function loadSiteCoreLoginRequest(RequestID $requestID)
	{
		$this->loadSiteCoreLoginRequestQuery = $this->db->prepare("SELECT sectionName, userType, trainingRequired FROM siteCoreLogin WHERE requestID = ?");
		$this->loadSiteCoreLoginRequestQuery->execute(array($requestID));
		return $this->loadSiteCoreLoginRequestQuery->fetch();
	}
	
	function loadSharedMailSlotSharer(RequestID $requestID)
	{
		$this->getEmployeeOfRequest = $this->db->prepare("SELECT employeeID FROM request WHERE requestID = ?");
		$this->getEmployeeOfRequest->execute(array($requestID));
		$res = $this->getEmployeeOfRequest->fetch();
		$employeeID = $res->employeeID;
		$this->loadSharedMailSlotSharerQuery = $this->db->prepare("SELECT employeeID FROM mailSlotAccessor JOIN mailSlotAccess ON mailSlotAccessor.mailSlotAccessID = mailSlotAccess.mailSlotAccessID WHERE requestID = :requestID AND employeeID <> :employeeID AND shared = '1'");
		$this->loadSharedMailSlotSharerQuery->execute(array('requestID'=>$requestID, 'employeeID'=>$employeeID));
		$res = $this->loadSharedMailSlotSharerQuery->fetch();
		if($res)
			return $res->employeeID;
	}
	
	function loadDoorNamePlateRequest(RequestID $requestID)
	{
		$this->loadDoorNamePlateRequestQuery = $this->db->prepare("SELECT doorNamePlateText, status FROM doorNamePlate WHERE requestID = ?");
		$this->loadDoorNamePlateRequestQuery->execute(array($requestID));
		return $this->loadDoorNamePlateRequestQuery->fetch(); 
	}
	
	function loadLocationAccessRequest(Request $request)
	{
		$this->loadLocationAccessRequestQuery = $this->db->prepare("SELECT employeeID, locationName, FOBNumber, status FROM locationAccess WHERE requestID = ?");
		$this->loadLocationAccessRequestQuery->execute(array($request->requestID));
		$locations = array();
		$employeeID = NULL; $FOBNumber = NULL;
		while($res = $this->loadLocationAccessRequestQuery->fetch())
		{
			//TODO: Remove this and do something better
			if(is_null($employeeID))
				$employeeID = $res->employeeID;
			if($employeeID != $res->employeeID)
				throw new Exception('Location Access requests with the same request ID has different employee ID');
			if(!$FOBNumber)
				$FOBNumber = $res->FOBNumber;
			if($FOBNumber != $res->FOBNumber)
				throw new Exception('Location Access requests with the same request ID has different FOB Numbers'); 
			$locations[] = $res->locationName;
		}
		//TODO: assign all locations
		$request->officelocationforaccess = $locations[0];
		$request->accessID = $employeeID;
		$request->fobnumber = $FOBNumber;
	}
	
	function loadEmailRequest(Request $request)
	{
		$this->loadEmailRequestQuery = $this->db->prepare("SELECT employeeID, requestID, emailAddress, genericmailaccount, editownbookingsaccess, editallbookingsaccess, readonlybookingsaccess, status FROM email WHERE requestID = ?");
		$this->loadEmailRequestQuery->execute(array($request->requestID));
		if($res = $this->loadEmailRequestQuery->fetch())
		{
			$request->emailaddress = $res->emailAddress;
			$request->genericmailaccountsaccess = $res->genericmailaccount;
			$request->editownbookingsaccess = $res->editownbookingsaccess;
			$request->editallbookingsaccess = $res->editallbookingsaccess;
			$request->readonlybookingsaccess = $res->readonlybookingsaccess;
		}
	}

	function loadEmaiListRequest(Request $request)
	{
		$this->loadEmaiListRequestQuery = $this->db->prepare("SELECT emailListID, status FROM emailGrouping WHERE requestID = ?");
		$this->loadEmaiListRequestQuery->execute(array($request->requestID));
		if($res = $this->loadEmaiListRequestQuery->fetch())
			$request->mailinglists = $res->emailListID;
	}

	function loadPrintingAccessRequest(Request $request)
	{
		$this->loadMainSpeedChartQuery = $this->db->prepare("SELECT speedChart FROM printingAccess WHERE main = '1' AND (status = 'pending' OR status = 'done') AND requestID = ?");
		$this->loadMainSpeedChartQuery->execute(array($request->requestID));
		$res = $this->loadMainSpeedChartQuery->fetch();
		$request->mainspeedchart = $res->speedChart;
		$this->loadSecondarySpeedChartQuery = $this->db->prepare("SELECT speedChart FROM printingAccess WHERE main = '0' AND (status = 'pending' OR status = 'done') AND requestID = ?");
		$this->loadSecondarySpeedChartQuery->execute(array($request->requestID));
		$secondaryspeedcharts = array();
		while($res = $this->loadSecondarySpeedChartQuery->fetch())
		{
			$secondaryspeedcharts[] = $res->speedChart;
		}
		if(sizeof($secondaryspeedcharts))
		{
			$string = implode(', ', $secondaryspeedcharts);
			$request->secondaryspeedcharts = $string;
		}
	}

	function loadSharedDriveAccessRequest(Request $request)
	{
		$this->loadSharedDriveAccessRequest = $this->db->prepare("SELECT sharedDriveID, employeeID, status FROM sharedDriveAccess WHERE requestID = ? AND (status = 'pending' OR status = 'done')");
		$this->loadSharedDriveAccessRequest->execute(array($request->requestID));
		if($res = $this->loadSharedDriveAccessRequest->fetch())
			$request->shareddriveid = $res->sharedDriveID;
	}	

	function loadVoiceServiceRequest(Request $request)
	{
		$this->loadVoiceServiceFields($request);		
		$request->voiceservicebuildings = $this->loadVoiceServiceBuildingsRequest(new RequestID($request->requestID));
		$request->serviceschosen = $this->loadVoiceServiceServicesRequested(new RequestID($request->requestID));
		$this->loadVoiceServiceSpeedChartsRequest($request);
	}

	function loadVoiceServiceServicesRequested(RequestID $requestID)
	{
		$this->loadVoiceServiceRequestedQuery = $this->db->prepare("SELECT singleline, multiline, moveupdatelocation, updatedisplayname, voicemail, unifiedmessaging, resetvoicemailpassword, requestvoicemailpassword, requestlongdistancecode, setupcallersmenu FROM voiceService WHERE requestID = ?");
		$this->loadVoiceServiceRequestedQuery->execute(array($requestID));
		$services = array();
		$res = $this->loadVoiceServiceRequestedQuery->fetch();
		foreach((array) $res as $service => $bool)
		{
			if($bool)
				$services[]	= $service;			
		}
		return $services;
	}
	
	function loadVoiceServiceBuildingsRequest(RequestID $requestID)
	{
		$this->loadVoiceServiceBuildingsRequestQuery = $this->db->prepare("SELECT building.buildingID, building FROM voiceServiceBuilding JOIN building ON voiceServiceBuilding.buildingID = building.buildingID WHERE requestID = ?");
		$this->loadVoiceServiceBuildingsRequestQuery->execute(array($requestID));
		$buildings = array();
		while($res = $this->loadVoiceServiceBuildingsRequestQuery->fetch())
		{
			$buildings[$res->buildingID] =  $res->building;
		}
		return $buildings;
	}
	
	function loadVoiceServiceSpeedChartsRequest(Request $request)
	{
		$this->loadVoiceServiceSpeedChartsRequestQuery = $this->db->prepare("SELECT monthlyRental, longDistanceCharges, installation FROM voiceService WHERE requestID = ?");
		$this->loadVoiceServiceSpeedChartsRequestQuery->execute(array($request->requestID));
		if($res = $this->loadVoiceServiceSpeedChartsRequestQuery->fetch())
		{
			$request->monthlyrental = $res->monthlyRental;
			$request->longdistancecharges = $res->longDistanceCharges;
			$request->installation = $res->installation;
		}
	}

	function loadBusinessCardRequest(RequestID $requestID)
	{
		$this->loadBusinessCardRequestQuery = $this->db->prepare("SELECT employeeID, requestID, businessCardType, status FROM businessCard WHERE requestID = ?");
		$this->loadBusinessCardRequestQuery->execute(array($requestID));
		return $this->loadBusinessCardRequestQuery->fetch();
	}
	
	function loadSauderStaffPhotoDirectoryRequest(RequestID $requestID)
	{
		$this->loadSauderStaffPhotoDirectoryRequestQuery = $this->db->prepare("SELECT employeeID, requestID, imageName, image, status FROM sauderStaffPhotoDirectory WHERE requestID = ?");
		$this->loadSauderStaffPhotoDirectoryRequestQuery->execute(array($requestID));
		return $this->loadSauderStaffPhotoDirectoryRequestQuery->fetch();
	}
	
	function isEmployee($employeeID)
	{
		$this->isEmployeeQuery->execute(array($employeeID));
		return $this->isEmployeeQuery->fetch() != NULL;
	}
	
	function addEmployee($request){
		$this->addEmployeeQuery->execute(array('employeeID'=>$request->employeeID, 'firstName'=>$request->firstname, 'lastName'=>$request->lastname, 'CWLID'=>$request->CWLID));	
	}
	
	function addTenure($request)
	{
		$this->addTenureQuery = $this->db->prepare("INSERT INTO tenure (requestID, employeeID, position, startDate, ongoing, endDate, term, locationName, tenureStatus, tenureType, status) VALUES (:requestID, :employeeID, :position, FROM_UNIXTIME(:startDate), :ongoing, FROM_UNIXTIME(:endDate), :term, :locationName, :tenureStatus, :tenureType, 'pending');");
		
		if($request->endDate)
			$endDate = $request->endDate;
		else
			$endDate = 0;
		$this->addTenureQuery->execute(array('requestID'=>$request->requestID, 'employeeID'=>$request->employeeID, 'position'=>$request->position ,'startDate'=>$request->startDate, 'endDate'=>$endDate, 'ongoing'=>$request->ongoing, 'term'=>$request->term, 'locationName'=>$request->officelocation, 'tenureStatus'=>$request->tenureStatus, 'tenureType'=>$request->tenureType));
	}
	
	function updateTenure($request)
	{
		$this->updateTenureQuery = $this->db->prepare("UPDATE tenure SET employeeID = :employeeID, position = :position, startDate = FROM_UNIXTIME(:startDate), endDate = FROM_UNIXTIME(:endDate), ongoing = :ongoing, term = :term, locationName = :locationName, tenureStatus = :tenureStatus, tenureType = :tenureType WHERE requestID = :requestID");
		
		if($request->endDate)
			$endDate = $request->endDate;
		else
			$endDate = 0;
		$this->updateTenureQuery->execute(array('requestID'=>$request->requestID, 'employeeID'=>$request->employeeID, 'position'=>$request->position ,'startDate'=>$request->startDate, 'endDate'=>$endDate, 'ongoing'=>$request->ongoing, 'term'=>$request->term, 'locationName'=>$request->officelocation, 'tenureStatus'=>$request->tenureStatus, 'tenureType'=>$request->tenureType));
	}

	function addVoiceService($request){
		$this->addVoiceServiceQuery->execute(array('employeeID'=>$request->employeeID, 'building'=>$request->building, 'singleline'=>$request->singleline, 'multiline'=>$request->multiline, 'moveupdatelocation'=>$request->moveupdatelocation, 'updatedisplayname'=>$request->updatedisplayname, 'voicemail'=>$request->voicemail, 'unifiedmessaging'=>$request->unifiedmessaging, 'resetvoicemailpassword'=>$request->resetvoicemailpassword, 
		'requestlongdistancecode'=>$request->requestlongdistancecode, 'setupcallersmenu'=>$request->setupcallersmenu, 'monthlyRental'=>$request->monthlyRental, 'longDistanceCharges'=>$request->longDistanceCharges, 'installation'=>$request->installation));
	}
	
	function addLocationAccess($request){
		$this->addLocationAccessQuery->execute(array('employeeID'=>$request->employeeID, 'locationID'=>$request->locationID, 'FOBNumber'=>$request->FOBNumber));
	}

	function addMailAccess($request){
		$this->addMailAccessQuery->execute(array('mailSlotID'=>$request->mailSlotID, 'employeeID'=>$request->employeeID, 'requestID'=>$request->requestID));
	}
	
	function addSharedDriveAccess($request){
		$this->addSharedDriveAccessQuery->execute(array('sharedDriveID'=>$request->sharedDriveID, 'employeeID'=>$request->employeeID));
	}
	
	function addDivisionMembership($request){
		$this->addDivisionMembershipQuery->execute(array('divisionID'=>$request->divisionID, 'employeeID'=>$request->employeeID));
	}
	
	function addEmailToGroup($request){
		$this->addEmailToGroupQuery->execute(array(emailID=>$request->emailID, emailListID=>$request->emailListID));
	}
	
	function addSiteCoreLogin($request){
		$this->addSiteCoreLoginQuery->execute(array(employeeID=>$request->employeeID, userType=>$request->userType, trainingRequired=>$request->trainingRequired));
	}

	function getPendingRequests(EmployeeID $requestby){
		$this->getPendingRequestsQuery = $this->db->prepare("SELECT request.requestID, UNIX_TIMESTAMP(dateSubmitted) as dateSubmitted, firstName, lastName, employee.employeeID FROM request JOIN employee ON request.employeeID = employee.employeeID WHERE requestBy = ? ORDER BY request.requestID ASC");
		$this->getPendingRequestsQuery->execute(array($requestby));

		$results = array();
        while($res = $this->getPendingRequestsQuery->fetch())
        {
			$rq = new stdClass();
			$rq->firstName = $res->firstName;
			$rq->lastName = $res->lastName;
			$rq->dateSubmitted = $res->dateSubmitted;
			$rq->employeeID = $res->employeeID;
            $results[$res->requestID] = $rq;
        }
        return $results;
	}
	
	function getAdminChecklist(RequestID $requestID){
		$this->getITChecklistQuery = $this->db->prepare("SELECT tenure.status as 'Tenure', voiceService.status as 'Voice Service', doorNamePlate.status as 'Door Name Plate', PATSystemAccount.status as 'PAT System Account', individualMailSlotAccessor.status as 'Individual Mail Slot', sharedMailSlotAccessor.status as 'Shared Mail Slot', businessCard.status as 'Business Card' FROM request
		LEFT JOIN tenure ON request.requestID = tenure.requestID 
		LEFT JOIN voiceService ON request.requestID = voiceService.requestID
		LEFT JOIN doorNamePlate ON request.requestID = doorNamePlate.requestID 
		LEFT JOIN PATSystemAccount ON request.requestID = PATSystemAccount.requestID 
		LEFT JOIN (mailSlotAccessor as individualMailSlotAccessor JOIN mailSlotAccess as individualMailSlotAccess ON individualMailSlotAccessor.mailSlotAccessID = individualMailSlotAccess.mailSlotAccessID AND individualMailSlotAccess.shared = '0') ON request.requestID = individualMailSlotAccessor.requestID
		LEFT JOIN (mailSlotAccessor as sharedMailSlotAccessor JOIN mailSlotAccess as sharedMailSlotAccess ON sharedMailSlotAccessor.mailSlotAccessID = sharedMailSlotAccess.mailSlotAccessID AND sharedMailSlotAccess.shared = '1') ON request.requestID = sharedMailSlotAccessor.requestID
		LEFT JOIN businessCard ON request.requestID = businessCard.requestID
		WHERE request.requestID = ? LIMIT 1;");
		$this->getITChecklistQuery->execute(array($requestID));
		return (array) $this->getITChecklistQuery->fetch();
	}
	
	function getITChecklist(RequestID $requestID){
		$this->getITChecklistQuery = $this->db->prepare("SELECT computer.status as computerStatus, printingAccess.status as printingAccessStatus, sharedDriveAccess.status as sharedDriveAccessStatus, email.status as emailStatus, emailGrouping.status as emailGroupingStatus, siteCoreLogin.status as siteCoreLoginStatus FROM request
		LEFT JOIN computer ON request.requestID = computer.requestID 
		LEFT JOIN printingAccess ON request.requestID = printingAccess.requestID 
		LEFT JOIN sharedDriveAccess ON request.requestID = sharedDriveAccess.requestID 
		LEFT JOIN email ON request.requestID = email.requestID
		LEFT JOIN emailGrouping ON request.requestID = emailGrouping.requestID
		LEFT JOIN siteCoreLogin ON request.requestID = siteCoreLogin.requestID
		WHERE request.requestID = ? LIMIT 1");
		$this->getITChecklistQuery->execute(array($requestID));
		return (array) $this->getITChecklistQuery->fetch();
	}
	
	function getGroups()
    {
        $sh = $this->db->prepare("SELECT groupID, groupName, shortName, type FROM groups;");
        $sh->execute(array());
        return $sh->fetchAll();
    }
    
    function requireGroup()
    {
        if(!$this->groupID)
            throw new Exception("No Group was specified");
    }
    
    function isAllSeeing(UserID $userid)
    {
        $this->isAllSeeingQuery->execute(array($userid));
        return $this->isAllSeeingQuery->fetch() != NULL;
    }
    
    function isAdmin(UserID $userid)
    {    
    	$this->isAdminQuery = $this->db->prepare("SELECT userID FROM users WHERE userID=? AND groupID = '2';");
        $this->isAdminQuery->execute(array($userid));
        return $this->isAdminQuery->fetch() != NULL;
    }
    
	function isIT(UserID $userid)
    {    
    	$this->isITQuery = $this->db->prepare("SELECT userID FROM users WHERE userID=? AND groupID = '3';");
        $this->isITQuery->execute(array($userid));
        return $this->isITQuery->fetch() != NULL;
    }
	
	function setGroupFromUserID(UserID $userid)
    {
        //Get the course information
        $sh = $this->db->prepare("SELECT groupID FROM users WHERE userID = ?;");
        $sh->execute(array($userid));
        if(!$res = $sh->fetch())
        {
            throw new Exception("Invalid userID has no group");
        }
        $this->groupID = $res->groupID;
    }
	
	function setGroupFromID(GroupID $id)
    {
        //Get the course information
        $sh = $this->db->prepare("SELECT groupID, shortName FROM groups WHERE groupID = ?;");
        $sh->execute(array($id));
        if(!$res = $sh->fetch())
        {
            throw new Exception("Invalid group id '$id'");
        }
        $this->groupID = new GroupID($id);
        $this->shortName = $res->shorName;
    }
	
	function setGroupFromName($name)
    {
        $sh = $this->db->prepare("SELECT groupID, groupName FROM groups WHERE shortName = ?;");
        $sh->execute(array($name));
        if(!$res = $sh->fetch())
        {
            throw new Exception("Invalid group '$name'");
        }
        $this->groupID = new GroupID($res->groupID);
        $this->groupName = $name;
    }
	
    function getUserDisplayMap($groupid)
    {
        $this->getUserDisplayMapQuery->execute(array($groupid));
        $users = array();
        while($res = $this->getUserDisplayMapQuery->fetch())
        {
            $users[$res->userID] = $res->firstName." ".$res->lastName;
        }
        return $users;
    }
    
    function getUserDisplayName(UserID $userID)
    {
        $this->getUserDisplayNameQuery->execute(array($userID));
        if(!$res = $this->getUserDisplayNameQuery->fetch())
        {
            throw new Exception("No user with id '$userID'");
        }
        else
        {
            return $res->firstName." ".$res->lastName;
        }
    }
    
   	private $fieldTypes = array('tinyint');
    
    function getFields($table, $type)
	{
		if(!in_array($type, $this->fieldTypes))
			throw new Exception("field type '$type' is invalid");
		$this->getFieldsQuery = $this->db->prepare("SELECT column_name
		FROM INFORMATION_SCHEMA.COLUMNS
		WHERE TABLE_NAME = '$table' AND DATA_TYPE = '$type'");
		$this->getFieldsQuery->execute();
		$fields = array();
		while($res = $this->getFieldsQuery->fetch())
		{
			$fields[] = $res->column_name;
		}
		return $fields;
	}
    
    function getOfficeLocations()
    {
    	$this->getOfficeLocationsQuery = $this->db->prepare("SELECT locationName FROM location;");
		$this->getOfficeLocationsQuery->execute();
		$officelocations = array();
		while($res = $this->getOfficeLocationsQuery->fetch())
		{
			$officelocations[] = $res->locationName;	
		}
		return $officelocations;
    }
	
	function getBusinessCardTypes()
	{
		$this->getBusinessCardTypesQuery = $this->db->prepare("SELECT businessCardType FROM businessCardType;");
		$this->getBusinessCardTypesQuery->execute();
		$businessCardTypes = array();
		while($res = $this->getBusinessCardTypesQuery->fetch())
		{
			$businessCardTypes[] = $res->businessCardType;	
		}
		return $businessCardTypes;
	}
	
	function getTenureStatuses()
	{
		$this->getTenureStatusesQuery = $this->db->prepare("SELECT tenureStatus FROM tenureStatus;");
		$this->getTenureStatusesQuery->execute();
		$tenureStatuses = array();
		while($res = $this->getTenureStatusesQuery->fetch())
		{
			$tenureStatuses[] = $res->tenureStatus;	
		}
		return $tenureStatuses;
	}
	
	function getEmailLists()
	{
		$this->getEmailListsQuery = $this->db->prepare("SELECT emailListID, emailListName FROM emailList;");
		$this->getEmailListsQuery->execute();
		$emailLists = array();
		while($res = $this->getEmailListsQuery->fetch())
		{
			$emailLists[$res->emailListID] = $res->emailListName;	
		}
		return $emailLists;
	}
	
	function getSharedDrives()
	{
		$this->getSharedDrivesQuery = $this->db->prepare("SELECT sharedDriveID, sharedDriveName FROM sharedDrive;");
		$this->getSharedDrivesQuery->execute();
		$sharedDrives = array();
		while($res = $this->getSharedDrivesQuery->fetch())
		{
			$sharedDrives[$res->sharedDriveID] = $res->sharedDriveName;	
		}
		return $sharedDrives;
	}
	
	function deleteRequest(RequestID $requestID)
	{
		$this->deleteRequestQuery = $this->db->prepare("DELETE FROM request WHERE requestID = ?;");
		$this->deleteRequestQuery->execute(array($requestID->id));
	}

	function deleteIfNewEmployee(EmployeeID $employeeID)
	{
		$this->deleteIfNewEmployeeQuery = $this->db->prepare("DELETE FROM employee WHERE isNew = '1' AND employeeID = ?;");
		$this->deleteIfNewEmployeeQuery->execute(array($employeeID));
	}
	
	function deleteUnusedMailAccess()
	{
		$this->deleteUnusedMailAccessQuery = $this->db->prepare("DELETE FROM mailSlotAccess WHERE mailSlotAccessID NOT IN (SELECT mailSlotAccessID FROM mailSlotAccessor);");
		$this->deleteUnusedMailAccessQuery->execute();
	}

	function approveGeneralInformation(RequestID $requestID)
	{
		$this->approveOrientationPackageQuery = $this->db->prepare("UPDATE tenure SET status = 'done' WHERE requestID = ?;");
		$this->approveOrientationPackageQuery->execute(array($requestID));
	}
	
	function undoGeneralInformation(RequestID $requestID)
	{
		$this->undoOrientationPackageQuery = $this->db->prepare("UPDATE tenure SET status = 'pending' WHERE requestID = ?;");
		$this->undoOrientationPackageQuery->execute(array($requestID));
	}
	
	function pendOrientationPackage(Request $request)
	{
		$this->pendOrientationPackageQuery = $this->db->prepare("INSERT INTO orientationPackage (employeeID, requestID, status) VALUES (:employeeID, :requestID, 'pending') ON DUPLICATE KEY UPDATE employeeID=:employeeID, requestID=:requestID, status='pending';");
		$this->pendOrientationPackageQuery->execute(array('employeeID'=>$request->employeeID, 'requestID'=>$request->requestID));
	}
	
	function approveOrientationPackage(RequestID $requestID)
	{
		$this->approveOrientationPackageQuery = $this->db->prepare("UPDATE orientationPackage SET status = 'done' WHERE requestID = ?;");
		$this->approveOrientationPackageQuery->execute(array($requestID));
	}
	
	function undoOrientationPackage(RequestID $requestID)
	{
		$this->undoOrientationPackageQuery = $this->db->prepare("UPDATE orientationPackage SET status = 'pending' WHERE requestID = ?;");
		$this->undoOrientationPackageQuery->execute(array($requestID));
	}
	
	function pendPATSystemAccount(Request $request)
	{
		$this->pendPATSystemAccountQuery = $this->db->prepare("INSERT INTO PATSystemAccount (employeeID, requestID, status) VALUES (:employeeID, :requestID, 'pending') ON DUPLICATE KEY UPDATE employeeID=:employeeID, requestID=:requestID, status='pending';");
		$this->pendPATSystemAccountQuery->execute(array('employeeID'=>$request->employeeID, 'requestID'=>$request->requestID));
	}
	
	function removeTask(RequestID $requestID, $table)
	{
		$this->removeTaskQuery = $this->db->prepare("SELECT status FROM $table WHERE requestID = ?;");
		$this->removeTaskQuery->execute(array($requestID));
		$res = $this->removeTaskQuery->fetch();
		if($res)
		{
			if($res->status == 'done')
			{
				$this->removeTaskQuery2 = $this->db->prepare("UPDATE $table SET status = 'remove' WHERE requestID = ?;");
				$this->removeTaskQuery2->execute(array($requestID));	
			}
			else if($res->status == 'pending')
			{
				$this->removeTaskQuery3 = $this->db->prepare("DELETE FROM $table WHERE requestID = ?;");
				$this->removeTaskQuery3->execute(array($requestID));	
			}
		}
	}
	
	function removeIndividualMailSlotAccessor(RequestID $requestID)
	{
		$this->removeIndividualMailSlotAccessorQuery = $this->db->prepare("SELECT status FROM mailSlotAccessor WHERE requestID = ? AND mailSlotAccessID IN (SELECT mailSlotAccessID FROM mailSlotAccess WHERE shared = '0');");
		$this->removeIndividualMailSlotAccessorQuery->execute(array($requestID));
		$res = $this->removeIndividualMailSlotAccessorQuery->fetch();
		if($res)
		{
			if($res->status == 'done')
			{
				$this->removeIndividualMailSlotAccessorQuery2 = $this->db->prepare("UPDATE mailSlotAccessor SET status = 'remove' WHERE requestID = ? AND mailSlotAccessID IN (SELECT mailSlotAccessID FROM mailSlotAccess WHERE shared = '0');");
				$this->removeIndividualMailSlotAccessorQuery2->execute(array($requestID));
			}
			else if($res->status == 'pending')
			{
				$this->removeIndividualMailSlotAccess($requestID);
			}
		}
	}
	
	function removeSharedMailSlotAccessor(RequestID $requestID)
	{
		$this->removeSharedMailSlotAccessorQuery = $this->db->prepare("SELECT status FROM mailSlotAccessor WHERE requestID = ? AND mailSlotAccessID IN (SELECT mailSlotAccessID FROM mailSlotAccess WHERE shared = '1');");
		$this->removeSharedMailSlotAccessorQuery->execute(array($requestID));
		$res = $this->removeSharedMailSlotAccessorQuery->fetch();
		if($res)
		{
			if($res->status == 'done')
			{
				$this->removeSharedMailSlotAccessorQuery2 = $this->db->prepare("UPDATE mailSlotAccessor SET status = 'remove' WHERE requestID = ? AND mailSlotAccessID IN (SELECT mailSlotAccessID FROM mailSlotAccess WHERE shared = '1');");
				$this->removeSharedMailSlotAccessorQuery2->execute(array($requestID));
			}
			else if($res->status == 'pending')
			{
				$this->removeSharedMailSlotAccess($requestID);
			}
		}
	}
	
	function removeIndividualMailSlotAccess(RequestID $requestID)
	{
		$this->removeIndividualMailSlotAccessQuery = $this->db->prepare("DELETE FROM mailSlotAccess WHERE shared = '0' AND mailSlotAccessID IN (SELECT mailSlotAccessID FROM mailSlotAccessor WHERE requestID = ?);");
		$this->removeIndividualMailSlotAccessQuery->execute(array($requestID));
	}
	
	function removeSharedMailSlotAccess(RequestID $requestID)
	{
		$this->removeSharedMailSlotAccessQuery = $this->db->prepare("DELETE FROM mailSlotAccessor WHERE requestID = ? AND mailSlotAccessID IN (SELECT mailSlotAccessID FROM mailSlotAccess WHERE shared = '1');");
		$this->removeSharedMailSlotAccessQuery->execute(array($requestID));
		$this->deleteUnusedMailAccess();
	}
	
	function approvePATSystemAccount(RequestID $requestID)
	{
		$this->approvePATSystemAccountQuery = $this->db->prepare("UPDATE PATSystemAccount SET status = 'done' WHERE requestID = ?;");
		$this->approvePATSystemAccountQuery->execute(array($requestID));
	}
	
	function undoPATSystemAccount(RequestID $requestID)
	{
		$this->undoPATSystemAccountQuery = $this->db->prepare("UPDATE PATSystemAccount SET status = 'pending' WHERE requestID = ?;");
		$this->undoPATSystemAccountQuery->execute(array($requestID));
	}

	function pendVoiceService(Request $request)
	{
		$this->pendVoiceServiceQuery = $this->db->prepare("INSERT INTO voiceService (employeeID, requestID, 
		  telephoneNumber,
		  singleline,
		  multiline,
		  moveupdatelocation,
		  updatedisplayname,
		  voicemail,
		  unifiedmessaging,
		  resetvoicemailpassword,
		  requestvoicemailpassword,
		  requestlongdistancecode,
		  setupcallersmenu,
		  monthlyRental,
		  longDistanceCharges,
		  installation,
		  status) VALUES (:employeeID, :requestID,
		  :telephoneNumber,
		  :singleline,
		  :multiline,
		  :moveupdatelocation,
		  :updatedisplayname,
		  :voicemail,
		  :unifiedmessaging,
		  :resetvoicemailpassword,
		  :requestvoicemailpassword,
		  :requestlongdistancecode,
		  :setupcallersmenu,
		  :monthlyRental,
		  :longDistanceCharges,
		  :installation,
		  'pending')
		  ON DUPLICATE KEY
		  UPDATE
		  employeeID = :employeeID,  
		  telephoneNumber = :telephoneNumber,
		  singleline = :singleline,
		  multiline = :multiline,
		  moveupdatelocation = :moveupdatelocation,
		  updatedisplayname = :updatedisplayname,
		  voicemail = :voicemail,
		  unifiedmessaging = :unifiedmessaging,
		  resetvoicemailpassword = :resetvoicemailpassword,
		  requestvoicemailpassword = :requestvoicemailpassword,
		  requestlongdistancecode = :requestlongdistancecode,
		  setupcallersmenu = :setupcallersmenu,
		  monthlyRental = :monthlyRental,
		  longDistanceCharges = :longDistanceCharges,
		  installation = :installation,
		  status = 'pending';");
		$this->pendVoiceServiceQuery->execute(array('employeeID'=>$request->employeeID, 'requestID'=>$request->requestID,
		  'telephoneNumber'=>$request->telephonenumber,
		  'singleline'=>$request->serviceschosen['singleline'],
		  'multiline'=>$request->serviceschosen['multiline'],
		  'moveupdatelocation'=>$request->serviceschosen['moveupdatelocation'],
		  'updatedisplayname'=>$request->serviceschosen['updatedisplayname'],
		  'voicemail'=>$request->serviceschosen['voicemail'],
		  'unifiedmessaging'=>$request->serviceschosen['unifiedmessaging'],
		  'resetvoicemailpassword'=>$request->serviceschosen['resetvoicemailpassword'],
		  'requestvoicemailpassword'=>$request->serviceschosen['resetvoicemailpassword'],
		  'requestlongdistancecode'=>$request->serviceschosen['requestlongdistancecode'],
		  'setupcallersmenu'=>$request->serviceschosen['setupcallersmenu'],
		  'monthlyRental'=>$request->monthlyrental,
		  'longDistanceCharges'=>$request->longdistancecharges,
		  'installation'=>$request->installation));
		$this->clearVoiceServiceBuildingsQuery = $this->db->prepare("DELETE FROM voiceServiceBuilding WHERE requestID = ?");
		$this->clearVoiceServiceBuildingsQuery->execute(array($request->requestID));
		foreach($request->voiceservicebuildings as $voiceservicebuildingID)
			$this->pendVoiceServiceBuilding(new RequestID($request->requestID), $voiceservicebuildingID);
	}
	
	function pendVoiceServiceBuilding(RequestID $requestid, $voiceservicebuildingID)
	{
		$this->pendVoiceServiceBuildingQuery = $this->db->prepare("INSERT INTO voiceServiceBuilding (requestID, buildingID) VALUES (:requestID, :buildingID) ON DUPLICATE KEY UPDATE buildingID = buildingID");
		$this->pendVoiceServiceBuildingQuery->execute(array('requestID'=>$requestid, 'buildingID'=>$voiceservicebuildingID));
	}
	
	function approveVoiceService(RequestID $requestID)
	{
		$this->approveVoiceServiceQuery = $this->db->prepare("UPDATE voiceService SET status = 'done' WHERE requestID = ?;");
		$this->approveVoiceServiceQuery->execute(array($requestID));
	}
	
	function undoVoiceService(RequestID $requestID)
	{
		$this->undoVoiceServiceQuery = $this->db->prepare("UPDATE voiceService SET status = 'pending' WHERE requestID = ?;");
		$this->undoVoiceServiceQuery->execute(array($requestID));
	}
	
	function pendInternalPhoneListRegistry(Request $request)
	{
		$this->pendInternalPhoneListRegistryQuery = $this->db->prepare("INSERT INTO internalPhoneListRegistry (employeeID, requestID, status) VALUES (:employeeID, :requestID, 'pending') ON DUPLICATE KEY UPDATE employeeID=:employeeID, status='pending';");
		$this->pendInternalPhoneListRegistryQuery->execute(array('employeeID'=>$request->employeeID, 'requestID'=>$request->requestID));
	}
	
	function approveInternalPhoneListRegistry(RequestID $requestID)
	{
		$this->approveInternalPhoneListRegistryQuery = $this->db->prepare("UPDATE internalPhoneListRegistry SET status = 'done' WHERE requestID = ?;");
		$this->approveInternalPhoneListRegistryQuery->execute(array($requestID));
	}
	
	function undoInternalPhoneListRegistry(RequestID $requestID)
	{
		$this->undoInternalPhoneListRegistryQuery = $this->db->prepare("UPDATE internalPhoneListRegistry SET status = 'pending' WHERE requestID = ?;");
		$this->undoInternalPhoneListRegistryQuery->execute(array($requestID));
	}
	
	function pendUBConlineDirectory(Request $request)
	{
		$this->pendUBConlineDirectoryQuery = $this->db->prepare("INSERT INTO UBConlineDirectory (employeeID, requestID, status) VALUES (:employeeID, :requestID, 'pending') ON DUPLICATE KEY UPDATE employeeID=:employeeID, status='pending';");
		$this->pendUBConlineDirectoryQuery->execute(array('employeeID'=>$request->employeeID, 'requestID'=>$request->requestID));
	}
	
	function approveUBConlineDirectory(RequestID $requestID)
	{
		$this->approveUBConlineDirectoryQuery = $this->db->prepare("UPDATE UBConlineDirectory SET status = 'done' WHERE requestID = ?;");
		$this->approveUBConlineDirectoryQuery->execute(array($requestID));
	}
	
	function undoUBConlineDirectory(RequestID $requestID)
	{
		$this->undoUBConlineDirectoryQuery = $this->db->prepare("UPDATE UBConlineDirectory SET status = 'pending' WHERE requestID = ?;");
		$this->undoUBConlineDirectoryQuery->execute(array($requestID));
	}
	
	function pendSauderWebsiteStaffDirectory(Request $request)
	{
		$this->pendSauderWebsiteStaffDirectoryQuery = $this->db->prepare("INSERT INTO sauderWebsiteStaffDirectory (employeeID, requestID, status) VALUES (:employeeID, :requestID, 'pending') ON DUPLICATE KEY UPDATE employeeID=:employeeID, status='pending';");
		$this->pendSauderWebsiteStaffDirectoryQuery->execute(array('employeeID'=>$request->employeeID, 'requestID'=>$request->requestID));
	}
	
	function approveSauderWebsiteStaffDirectory(RequestID $requestID)
	{
		$this->approveSauderWebsiteStaffDirectoryQuery = $this->db->prepare("UPDATE sauderWebsiteStaffDirectory SET status = 'done' WHERE requestID = ?;");
		$this->approveSauderWebsiteStaffDirectoryQuery->execute(array($requestID));
	}

	function undoSauderWebsiteStaffDirectory(RequestID $requestID)
	{
		$this->undoSauderWebsiteStaffDirectoryQuery = $this->db->prepare("UPDATE sauderWebsiteStaffDirectory SET status = 'pending' WHERE requestID = ?;");
		$this->undoSauderWebsiteStaffDirectoryQuery->execute(array($requestID));
	}
	
	function pendSauderStaffPhotoDirectory(Request $request)
	{
		$this->pendSauderStaffPhotoDirectoryQuery = $this->db->prepare("INSERT INTO sauderStaffPhotoDirectory (employeeID, requestID, imageName, image, status) VALUES (:employeeID, :requestID, :imageName, :image, 'pending') ON DUPLICATE KEY UPDATE employeeID=:employeeID, imageName=:imageName, image=:image, status='pending';");
		$this->pendSauderStaffPhotoDirectoryQuery->execute(array('employeeID'=>$request->employeeID, 'requestID'=>$request->requestID, 'imageName'=>$request->imagename, 'image'=>$request->image));
	}
	
	function approveSauderStaffPhotoDirectory(RequestID $requestID)
	{
		$this->approveSauderStaffPhotoDirectoryQuery = $this->db->prepare("UPDATE sauderStaffPhotoDirectory SET status = 'done' WHERE requestID = ?;");
		$this->approveSauderStaffPhotoDirectoryQuery->execute(array($requestID));
	}
	
	function undoSauderStaffPhotoDirectory(RequestID $requestID)
	{
		$this->undoSauderStaffPhotoDirectoryQuery = $this->db->prepare("UPDATE sauderStaffPhotoDirectory SET status = 'pending' WHERE requestID = ?;");
		$this->undoSauderStaffPhotoDirectoryQuery->execute(array($requestID));
	}
	
	function pendIndividualMailSlotAccessor(Request $request)
	{
		$this->checkForIndividualMailSlotAccessorQuery = $this->db->prepare("SELECT mailSlotAccessID FROM mailSlotAccessor WHERE employeeID = ? AND mailSlotAccessID IN (SELECT mailSlotAccessID FROM mailSlotAccess WHERE shared = '0');");
		$this->checkForIndividualMailSlotAccessorQuery->execute(array($request->employeeID));
		if($this->checkForIndividualMailSlotAccessorQuery->fetch())
			return;
		$this->pendIndividualMailSlotAccessQuery = $this->db->prepare("INSERT INTO mailSlotAccess (mailSlotID, shared) VALUES (NULL, 0);");
		$this->pendIndividualMailSlotAccessQuery->execute();
		$mailSlotAccessID = $this->db->lastInsertID();
		$this->pendIndividualMailSlotAccessorQuery = $this->db->prepare("INSERT INTO mailSlotAccessor (employeeID, mailSlotAccessID, requestID, status) VALUES (:employeeID, :mailSlotAccessID, :requestID, 'pending');");
		$this->pendIndividualMailSlotAccessorQuery->execute(array('employeeID'=>$request->employeeID, 'mailSlotAccessID'=>$mailSlotAccessID, 'requestID'=>$request->requestID));
	}
	
	function pendSharedMailSlotAccessor(Request $request)
	{
		$this->pendSharedMailSlotAccessQuery = $this->db->prepare("INSERT INTO mailSlotAccess (mailSlotID, shared) VALUES (NULL, 1);");
		$this->pendSharedMailSlotAccessorQuery = $this->db->prepare("INSERT INTO mailSlotAccessor (employeeID, mailSlotAccessID, requestID, status) VALUES (:employeeID, :mailSlotAccessID, :requestID, 'pending');");
		if(!$request->mailslotsharer)
		{
			$this->pendSharedMailSlotAccessQuery->execute();
			$mailSlotAccessID = $this->db->lastInsertID();
			$this->pendSharedMailSlotAccessorQuery->execute(array('employeeID'=>$request->employeeID, 'mailSlotAccessID'=>$mailSlotAccessID, 'requestID'=>$request->requestID));
		}
		else
		{
			//Check if mailslotsharer is an employee in system
			if(!$this->isEmployee($request->mailslotsharer))
				throw new Exception("EmployeeID entered in mailslot sharer field could not be found");
			//Check if mailslotsharer already has mail access requested
			$this->isEmployeeASharedMailSlotAccessorQuery = $this->db->prepare("SELECT mailSlotAccessor.mailSlotAccessID, mailSlotID FROM mailSlotAccess JOIN mailSlotAccessor ON mailSlotAccess.mailSlotAccessID = mailSlotAccessor.mailSlotAccessID WHERE shared = '1' AND employeeID = ?");
			$this->isEmployeeASharedMailSlotAccessorQuery->execute(array($request->mailslotsharer));
			$res = $this->isEmployeeASharedMailSlotAccessorQuery->fetch();
			//If has mail access
			if($res)
			{
				$this->pendSharedMailSlotAccessorQuery->execute(array('employeeID'=>$request->employeeID, 'mailSlotAccessID'=>$res->mailSlotAccessID, 'requestID'=>$request->requestID));
			}
			//does not have mail access
			else 
			{	
				$this->pendSharedMailSlotAccessQuery->execute();
				$mailSlotAccessID = $this->db->lastInsertID();
				$this->pendSharedMailSlotAccessorQuery->execute(array('employeeID'=>$request->employeeID, 'mailSlotAccessID'=>$mailSlotAccessID, 'requestID'=>$request->requestID));
				$this->pendSharedMailSlotAccessorQuery->execute(array('employeeID'=>$request->mailslotsharer, 'mailSlotAccessID'=>$mailSlotAccessID, 'requestID'=>$request->requestID));
			}
		}
	}
	
	//TODO: enforce mailslotaccessid
	function approveMailSlotAccessor(RequestID $requestID, $mailSlotAccessID)
	{
		$this->approveMailSlotAccessorQuery = $this->db->prepare("UPDATE mailSlotAccessor SET status = 'done' WHERE requestID = ? AND mailSlotAccessID = ?;");
		$this->approveMailSlotAccessorQuery->execute(array($requestID, $mailSlotAccessID));
	}
	
	function undoMailSlotAccessor(RequestID $requestID, $mailSlotAccessID)
	{
		$this->undoMailSlotAccessorQuery = $this->db->prepare("UPDATE mailSlotAccessor SET status = 'pending' WHERE requestID = ? AND mailSlotAccessID = ?;");
		$this->undoMailSlotAccessorQuery->execute(array($requestID, $mailSlotAccessID));
	}

	function pendDoorNamePlate(Request $request)
	{
		$this->pendDoorNamePlateQuery = $this->db->prepare("INSERT INTO doorNamePlate (employeeID, requestID, doorNamePlateText, status) VALUES (:employeeID, :requestID, :doorNamePlateText, 'pending') ON DUPLICATE KEY UPDATE employeeID=:employeeID, requestID=:requestID, doorNamePlateText=:doorNamePlateText, status='pending'	;");
		$this->pendDoorNamePlateQuery->execute(array('employeeID'=>$request->employeeID, 'requestID'=>$request->requestID, 'doorNamePlateText'=>$request->doornameplatetext));
	}
	
	function approveDoorNamePlate(RequestID $requestID)
	{
		$this->approveDoorNamePlateQuery = $this->db->prepare("UPDATE doorNamePlate SET status = 'done' WHERE requestID = ?;");
		$this->approveDoorNamePlateQuery->execute(array($requestID));
	}
	
	function undoDoorNamePlate(RequestID $requestID)
	{
		$this->undoDoorNamePlateQuery = $this->db->prepare("UPDATE doorNamePlate SET status = 'pending' WHERE requestID = ?;");
		$this->undoDoorNamePlateQuery->execute(array($requestID));
	}
	
	function pendLocationAccess(Request $request)
	{
		$this->pendLocationAccessQuery = $this->db->prepare("INSERT INTO locationAccess (employeeID, accessID, locationName, requestID, FOBNumber, status) VALUES (:employeeID, :accessID, :locationName, :requestID, :FOBNumber, 'pending') ON DUPLICATE KEY UPDATE employeeID=:employeeID, accessID=:accessID, locationName=:locationName, requestID=:requestID, FOBNumber=:FOBNumber, status='pending';");
		$this->pendLocationAccessQuery->execute(array('employeeID'=>$request->employeeID, 'accessID'=>$request->accessID, 'locationName'=>$request->officelocationforaccess, 'requestID'=>$request->requestID, 'FOBNumber'=>$request->fobnumber));
	}
	
	function approveLocationAccess(RequestID $requestID)
	{
		$this->approveLocationAccessQuery = $this->db->prepare("UPDATE locationAccess SET status = 'done' WHERE requestID = ?;");
		$this->approveLocationAccessQuery->execute(array($requestID));
	}
	
	function undoLocationAccess(RequestID $requestID)
	{
		$this->undoLocationAccessQuery = $this->db->prepare("UPDATE locationAccess SET status = 'pending' WHERE requestID = ?;");
		$this->undoLocationAccessQuery->execute(array($requestID));
	}
	
	function pendBusinessCard(Request $request)
	{
		$this->pendBusinessCardQuery = $this->db->prepare("INSERT INTO businessCard (employeeID, requestID, businessCardType, status) VALUES (:employeeID, :requestID, :businessCardType, 'pending') ON DUPLICATE KEY UPDATE employeeID=:employeeID, businessCardType=:businessCardType, status='pending';");
		$this->pendBusinessCardQuery->execute(array('employeeID'=>$request->employeeID, 'requestID'=>$request->requestID, 'businessCardType'=>$request->businesscardtype));
	}
	
	function approveBusinessCard(RequestID $requestID)
	{
		$this->approveBusinessCardQuery = $this->db->prepare("UPDATE businessCard SET status = 'done' WHERE requestID = ?;");
		$this->approveBusinessCardQuery->execute(array($requestID));
	}
	
	function undoBusinessCard(RequestID $requestID)
	{
		$this->undoBusinessCardQuery = $this->db->prepare("UPDATE businessCard SET status = 'pending' WHERE requestID = ?;");
		$this->undoBusinessCardQuery->execute(array($requestID));
	}
	
	function pendComputerAccess(Request $request)
	{
		$this->pendComputerAccessQuery = $this->db->prepare("INSERT INTO computer (employeeID, requestID, status) VALUES (:employeeID, :requestID, 'pending') ON DUPLICATE KEY UPDATE employeeID=:employeeID, status='pending';");
		$this->pendComputerAccessQuery->execute(array('employeeID'=>$request->employeeID, 'requestID'=>$request->requestID));
	}
	
	function approveComputerAccess(RequestID $requestID)
	{
		$this->approveComputerAccessQuery = $this->db->prepare("UPDATE computer SET status = 'done' WHERE requestID = ?;");
		$this->approveComputerAccessQuery->execute(array($requestID));
	}
	
	function undoComputerAccess(RequestID $requestID)
	{
		$this->undoComputerAccessQuery = $this->db->prepare("UPDATE computer SET status = 'pending' WHERE requestID = ?;");
		$this->undoComputerAccessQuery->execute(array($requestID));
	}
	
	function pendMainPrintingAccess(Request $request)
	{
		$this->pendComputerAccessQuery = $this->db->prepare("INSERT INTO printingAccess (employeeID, requestID, speedChart, main, status) VALUES (:employeeID, :requestID, :speedChart, '1', 'pending') ON DUPLICATE KEY UPDATE employeeID=:employeeID, main='1', status='pending'");
		$this->pendComputerAccessQuery->execute(array('employeeID'=>$request->employeeID, 'requestID'=>$request->requestID, 'speedChart'=>$request->mainspeedchart));
	}
	
	function pendSecondaryPrintingAccess(Request $request)
	{
		foreach($request->secondaryspeedcharts as $secondaryspeedchart)
		{
			$this->pendComputerAccessQuery = $this->db->prepare("INSERT INTO printingAccess (employeeID, requestID, speedChart, main, status) VALUES (:employeeID, :requestID, :speedChart, '0', 'pending') ON DUPLICATE KEY UPDATE employeeID=:employeeID, main='0', status='pending';;");
			$this->pendComputerAccessQuery->execute(array('employeeID'=>$request->employeeID, 'requestID'=>$request->requestID, 'speedChart'=>$secondaryspeedchart));
		}
	}
	
	function cancelPrintingAccess(RequestID $requestID, $speedChart)
	{
		$this->cancelPrintingAccessQuery = $this->db->prepare("DELETE FROM printingAccess WHERE requestID = ? AND speedChart = ? AND status = 'pending';");
		$this->cancelPrintingAccessQuery->execute(array($requestID, $speedChart));
	}
	
	//TODO: Be more strict and assure status is done
	function removePrintingAccess(RequestID $requestID, $speedChart)
	{
		$this->removePrintingAccessQuery = $this->db->prepare("UPDATE printingAccess SET status = 'remove' WHERE requestID = ? AND speedChart = ? ;");
		$this->removePrintingAccessQuery->execute(array($requestID, $speedChart));
	}
	
	function takePrintingAccess(RequestID $requestID, $speedChart)
	{
		$this->takePrintingAccessQuery = $this->db->prepare("DELETE FROM printingAccess WHERE requestID = ? AND speedChart = ? AND status = 'remove';");
		$this->takePrintingAccessQuery->execute(array($requestID, $speedChart));
	}
	
	//TODO: Be more strict and assure status is pending
	function approvePrintingAccess(RequestID $requestID, $speedChart)
	{
		$this->approvePrintingAccessQuery = $this->db->prepare("UPDATE printingAccess SET status = 'done' WHERE requestID = ? AND speedChart = ?;");
		$this->approvePrintingAccessQuery->execute(array($requestID, $speedChart));
	}
	
	//TODO: Be more strict and assure status is done
	function undoPrintingAccess(RequestID $requestID, $speedChart)
	{
		$this->undoPrintingAccessQuery = $this->db->prepare("UPDATE printingAccess SET status = 'pending' WHERE requestID = ? AND speedChart = ?;");
		$this->undoPrintingAccessQuery->execute(array($requestID, $speedChart));
	}
	
	function pendEmail(Request $request)
	{
		$this->pendEmailQuery = $this->db->prepare("INSERT INTO email (employeeID, requestID, emailaddress, genericmailaccount, editownbookingsaccess, editallbookingsaccess, readonlybookingsaccess, status) VALUES (:employeeID, :requestID, :emailaddress, :genericmailaccount,  :editownbookingsaccess, :editallbookingsaccess, :readonlybookingsaccess, 'pending')
		ON DUPLICATE KEY UPDATE employeeID=:employeeID, emailaddress=:emailaddress, genericmailaccount=:genericmailaccount, editownbookingsaccess=:editownbookingsaccess, editallbookingsaccess=:editallbookingsaccess, readonlybookingsaccess=:readonlybookingsaccess, status='pending';");
		$this->pendEmailQuery->execute(array('employeeID'=>$request->employeeID, 'requestID'=>$request->requestID, 'emailaddress'=>$request->emailaddress, 'genericmailaccount'=>$request->genericmailaccountsaccess, 'editownbookingsaccess'=>$request->editownbookingsaccess, 'editallbookingsaccess'=>$request->editallbookingsaccess, 'readonlybookingsaccess'=>$request->readonlybookingsaccess));
	}
    
   	//TODO: Be more strict and assure status is done
	function removeEmail(RequestID $requestID)
	{
		$this->removeEmailQuery = $this->db->prepare("UPDATE email SET status = 'remove' WHERE requestID = ?;");
		$this->removeEmailQuery->execute(array($requestID));
	}
	
	function takeEmail(RequestID $requestID)
	{
		$this->takeEmailQuery = $this->db->prepare("DELETE FROM email WHERE requestID = ? AND status = 'remove';");
		$this->takeEmailQuery->execute(array($requestID));
	}
	
	function cancelEmail(RequestID $requestID)
	{
		$this->cancelEmailQuery = $this->db->prepare("DELETE FROM email WHERE requestID = ? AND status = 'pending';");
		$this->cancelEmailQuery->execute(array($requestID));
	}
    
	//TODO: Be more strict and assure status is pending
	function approveEmail(RequestID $requestID)
	{
		$this->approveEmailQuery = $this->db->prepare("UPDATE email SET status = 'done' WHERE requestID = ?;");
		$this->approveEmailQuery->execute(array($requestID));
	}
	
	//TODO: Be more strict and assure status is done
	function undoEmail(RequestID $requestID)
	{
		$this->undoEmailQuery = $this->db->prepare("UPDATE email SET status = 'pending' WHERE requestID = ?;");
		$this->undoEmailQuery->execute(array($requestID));
	}
	
	function pendEmailGrouping(Request $request)
	{
		$this->pendEmailGroupingQuery = $this->db->prepare("INSERT INTO emailGrouping (emailAddress, requestID, emailListID, status) VALUES (:emailAddress, :requestID, :emailListID, 'pending') ON DUPLICATE KEY UPDATE emailAddress=:emailAddress, status='pending';");
		$this->pendEmailGroupingQuery->execute(array('emailAddress'=>$request->emailaddress, 'requestID'=>$request->requestID, 'emailListID'=>$request->mailinglists));
	}
	
	//TODO: Be more strict and assure status is done
	function removeEmailGrouping(RequestID $requestID, $emailListID)
	{
		$this->removeEmailGroupingQuery = $this->db->prepare("UPDATE emailGrouping SET status = 'remove' WHERE requestID = ? AND emailListID = ?;");
		$this->removeEmailGroupingQuery->execute(array($requestID, $emailListID));
	}
	
	function takeEmailGrouping(RequestID $requestID, $emailListID)
	{
		$this->takeEmailGroupingQuery = $this->db->prepare("DELETE FROM emailGrouping WHERE requestID = ? AND status = 'remove' AND emailListID = ?;");
		$this->takeEmailGroupingQuery->execute(array($requestID, $emailListID));
	}
	
	function cancelEmailGrouping(RequestID $requestID, $emailListID)
	{
		$this->cancelEmailGroupingQuery = $this->db->prepare("DELETE FROM emailGrouping WHERE requestID = ? AND status = 'pending'  AND emailListID = ?;");
		$this->cancelEmailGroupingQuery->execute(array($requestID, $emailListID));
	}
	
	function approveEmailGrouping(RequestID $requestID, $emailListID)
	{
		$this->approveEmailGroupingQuery = $this->db->prepare("UPDATE emailGrouping SET status = 'done' WHERE requestID = ? AND emailListID = ? ;");
		$this->approveEmailGroupingQuery->execute(array($requestID, $emailListID));
	}
	
	function undoEmailGrouping(RequestID $requestID, $emailListID)
	{
		$this->undoEmailGroupingQuery = $this->db->prepare("UPDATE emailGrouping SET status = 'pending' WHERE requestID = ? AND emailListID = ?;");
		$this->undoEmailGroupingQuery->execute(array($requestID, $emailListID));
	}
	
	function pendSiteCoreLogin(Request $request)
	{
		$this->pendSiteCoreLoginQuery = $this->db->prepare("INSERT INTO siteCoreLogin (sectionName, employeeID, requestID, userType, trainingRequired, status) VALUES (:sectionName, :employeeID, :requestID, :userType, :trainingRequired, 'pending') ON DUPLICATE KEY UPDATE sectionName=:sectionName, employeeID=:employeeID, userType=:userType, trainingRequired=:trainingRequired, status='pending';");
		$this->pendSiteCoreLoginQuery->execute(array('sectionName'=>$request->siteCoreSection, 'employeeID'=>$request->employeeID, 'requestID'=>$request->requestID, 'userType'=>$request->siteCoreLoginUserType, 'trainingRequired'=>$request->siteCoreTrainingRequired));
	}
	
	//TODO: Be more strict and assure status is done
	function removeSiteCoreLogin(RequestID $requestID)
	{
		$this->removeSiteCoreLoginQuery = $this->db->prepare("UPDATE siteCoreLogin SET status = 'remove' WHERE requestID = ?;");
		$this->removeSiteCoreLoginQuery->execute(array($requestID));
	}
	
	function takeSiteCoreLogin(RequestID $requestID)
	{
		$this->takeSiteCoreLoginQuery = $this->db->prepare("DELETE FROM siteCoreLogin WHERE requestID = ? AND status = 'remove';");
		$this->takeSiteCoreLoginQuery->execute(array($requestID));
	}
	
	function cancelSiteCoreLogin(RequestID $requestID)
	{
		$this->cancelSiteCoreLoginQuery = $this->db->prepare("DELETE FROM siteCoreLogin WHERE requestID = ? AND status = 'pending';");
		$this->cancelSiteCoreLoginQuery->execute(array($requestID));
	}
	
	//TODO: Be more strict and assure status is pending
	function approveSiteCoreLogin(RequestID $requestID)
	{
		$this->approveSiteCoreLoginQuery = $this->db->prepare("UPDATE siteCoreLogin SET status = 'done' WHERE requestID = ?;");
		$this->approveSiteCoreLoginQuery->execute(array($requestID));
	}
	
	//TODO: Be more strict and assure status is done
	function undoSiteCoreLogin(RequestID $requestID)
	{
		$this->undoSiteCoreLoginQuery = $this->db->prepare("UPDATE siteCoreLogin SET status = 'pending' WHERE requestID = ?;");
		$this->undoSiteCoreLoginQuery->execute(array($requestID));
	}
	
	function pendSharedDriveAccess(Request $request)
	{
		$this->pendSiteCoreLoginQuery = $this->db->prepare("INSERT INTO sharedDriveAccess (sharedDriveID, employeeID, requestID, status) VALUES (:sharedDriveID, :employeeID, :requestID, 'pending') ON DUPLICATE KEY UPDATE employeeID=:employeeID, status='pending';");
		$this->pendSiteCoreLoginQuery->execute(array('sharedDriveID'=>$request->shareddriveid, 'employeeID'=>$request->employeeID, 'requestID'=>$request->requestID));
	}
	
	//TODO: Be more strict and assure status is done
	function removeSharedDriveAccess(RequestID $requestID, $sharedDrive)
	{
		$this->removePrintingAccessQuery = $this->db->prepare("UPDATE sharedDriveAccess SET status = 'remove' WHERE requestID = ? AND sharedDriveID = ?;");
		$this->removePrintingAccessQuery->execute(array($requestID, $sharedDrive));
	}
	
	function takeSharedDriveAccess(RequestID $requestID, $sharedDrive)
	{
		$this->takeSharedDriveAccessQuery = $this->db->prepare("DELETE FROM sharedDriveAccess WHERE requestID = ? AND status = 'remove' AND sharedDriveID = ?;");
		$this->takeSharedDriveAccessQuery->execute(array($requestID, $sharedDrive));
	}
	
	function cancelSharedDriveAccess(RequestID $requestID, $sharedDrive)
	{
		$this->takeSharedDriveAccessQuery = $this->db->prepare("DELETE FROM sharedDriveAccess WHERE requestID = ? AND status = 'pending' AND sharedDriveID = ?;");
		$this->takeSharedDriveAccessQuery->execute(array($requestID, $sharedDrive));
	}
	
	//TODO: Be more strict and assure status is done
	function approveSharedDriveAccess(RequestID $requestID, $sharedDrive)
	{
		$this->approveSharedDriveAccessQuery = $this->db->prepare("UPDATE sharedDriveAccess SET status = 'done' WHERE requestID = ? AND sharedDriveID = ?;");
		$this->approveSharedDriveAccessQuery->execute(array($requestID, $sharedDrive));
	}
	
	//TODO: Be more strict and assure status is done
	function undoSharedDriveAccess(RequestID $requestID, $sharedDrive)
	{
		$this->undoSharedDriveAccessQuery = $this->db->prepare("UPDATE sharedDriveAccess SET status = 'pending' WHERE requestID = ? AND sharedDriveID = ?;");
		$this->undoSharedDriveAccessQuery->execute(array($requestID, $sharedDrive));
	}
	
	//IT Section
	
	function getITTasks(RequestID $requestID)
	{
		$this->getITTasksQuery = $this->db->prepare("SELECT requestBy, requestType, status, UNIX_TIMESTAMP(dateSubmitted) as dateSubmitted, comments, employeeID WHERE requestID = ?;");
	}
	
	function getEmailTasks($status)
	{
		$this->getEmailTasksQuery = $this->db->prepare("SELECT
		  requestby,
		  employee.employeeID,
		  firstName,
		  lastName,
		  request.requestID,
		  emailAddress,
		  genericmailaccount,
		  editownbookingsaccess,
		  editallbookingsaccess,
		  readonlybookingsaccess,
		  UNIX_TIMESTAMP(request.dateSubmitted) as dateSubmitted FROM email JOIN request ON request.requestID = email.requestID JOIN employee ON employee.employeeID = request.employeeID WHERE email.status = '$status'");
		$this->getEmailTasksQuery->execute();
		return $this->getEmailTasksQuery->fetchAll();
	}
	
	function getEmailGroupingTasks($status)
	{
		$this->getEmailGroupingTasksQuery = $this->db->prepare("SELECT
		  requestby,
		  employee.employeeID,
		  firstName,
		  lastName,
		  request.requestID,
		  email.emailAddress,
		  emailList.emailListName,
		  UNIX_TIMESTAMP(request.dateSubmitted) as dateSubmitted FROM email JOIN request ON request.requestID = email.requestID JOIN employee ON employee.employeeID = request.employeeID JOIN emailGrouping ON emailGrouping.emailAddress = email.emailAddress JOIN emailList ON emailList.emailListID = emailGrouping.emailListID WHERE email.status = '$status'");
		$this->getEmailGroupingTasksQuery->execute();
		return $this->getEmailGroupingTasksQuery->fetchAll();
	}
	
	function getComputerTasks($status)
	{
		$this->getComputerTasksQuery = $this->db->prepare("SELECT
		  requestby,
		  employee.employeeID,
		  firstName,
		  lastName,
		  request.requestID,
		  UNIX_TIMESTAMP(request.dateSubmitted) as dateSubmitted FROM computer JOIN request ON request.requestID = computer.requestID JOIN employee ON employee.employeeID = request.employeeID WHERE computer.status = '$status'");
		$this->getComputerTasksQuery->execute();
		return $this->getComputerTasksQuery->fetchAll();
	}
	
	function getInternalPhoneListRegistryTasks($status)
	{
		$this->getInternalPhoneListRegistryTasksQuery = $this->db->prepare("SELECT
		  requestby,
		  employee.employeeID,
		  firstName,
		  lastName,
		  request.requestID,
		  UNIX_TIMESTAMP(request.dateSubmitted) as dateSubmitted FROM internalPhoneListRegistry JOIN request ON request.requestID = internalPhoneListRegistry.requestID JOIN employee ON employee.employeeID = request.employeeID WHERE internalPhoneListRegistry.status = '$status'");
		$this->getInternalPhoneListRegistryTasksQuery->execute();
		return $this->getInternalPhoneListRegistryTasksQuery->fetchAll();
	}
	
	function getVoiceServiceTasks($status)
	{
		  $this->getVoiceServiceTasksQuery = $this->db->prepare("SELECT
		  requestby,
		  employee.employeeID,
		  firstName,
		  lastName,
		  request.requestID,
		  telephoneNumber,
		  singleline,
		  multiline,
		  moveupdatelocation,
		  updatedisplayname,
		  voicemail,
		  unifiedmessaging,
		  resetvoicemailpassword,
		  requestvoicemailpassword,
		  requestlongdistancecode,
		  setupcallersmenu,
		  monthlyRental,
		  longDistanceCharges,
		  installation,
		  building,
		  UNIX_TIMESTAMP(request.dateSubmitted) as dateSubmitted FROM voiceService JOIN request ON request.requestID = voiceService.requestID JOIN voiceServiceBuilding ON voiceServiceBuilding.requestID = voiceService.requestID JOIN building ON voiceServiceBuilding.buildingID = building.buildingID JOIN employee ON employee.employeeID = request.employeeID WHERE voiceService.status = '$status'");
		$this->getVoiceServiceTasksQuery->execute();
		$results = array();
		$i = 1;
		while($res = $this->getVoiceServiceTasksQuery->fetch())
		{
			if(!array_key_exists($res->requestID, $results))
			{
				$res->building0 = $res->building;
				unset($res->building);
				$results[$res->requestID] = $res;
			}
			else {
				$prop = 'building'.$i;
				$results[$res->requestID]->$prop = $res->building;
				$i++; 
			}
		}
		return $results;
	}
	
	function getUBConlineDirectoryTasks($status)
	{
		  $this->getUBConlineDirectoryTasksQuery = $this->db->prepare("SELECT
		  requestby,
		  employee.employeeID,
		  firstName,
		  lastName,
		  request.requestID,
		  UNIX_TIMESTAMP(request.dateSubmitted) as dateSubmitted FROM UBConlineDirectory JOIN request ON request.requestID = UBConlineDirectory.requestID JOIN employee ON employee.employeeID = request.employeeID WHERE UBConlineDirectory.status = '$status'");
		$this->getUBConlineDirectoryTasksQuery->execute();
		return $this->getUBConlineDirectoryTasksQuery->fetchAll();
	}
	
	function getSauderWebsiteStaffDirectoryTasks($status)
	{
		  $this->getSauderWebsiteStaffDirectoryTasksQuery = $this->db->prepare("SELECT
		  requestby,
		  employee.employeeID,
		  firstName,
		  lastName,
		  request.requestID,
		  UNIX_TIMESTAMP(request.dateSubmitted) as dateSubmitted FROM sauderWebsiteStaffDirectory JOIN request ON request.requestID = sauderWebsiteStaffDirectory.requestID JOIN employee ON employee.employeeID = request.employeeID WHERE sauderWebsiteStaffDirectory.status = '$status'");
		$this->getSauderWebsiteStaffDirectoryTasksQuery->execute();
		return $this->getSauderWebsiteStaffDirectoryTasksQuery->fetchAll();
	}
	
	function getSauderStaffPhotoDirectoryTasks($status)
	{
		$this->getSauderStaffPhotoDirectoryTasksQuery = $this->db->prepare("SELECT
		  requestby,
		  employee.employeeID,
		  firstName,
		  lastName,
		  request.requestID,
		  imageName,
		  image,
		  UNIX_TIMESTAMP(request.dateSubmitted) as dateSubmitted FROM sauderStaffPhotoDirectory JOIN request ON request.requestID = sauderStaffPhotoDirectory.requestID JOIN employee ON employee.employeeID = request.employeeID WHERE sauderStaffPhotoDirectory.status = '$status'");
		$this->getSauderStaffPhotoDirectoryTasksQuery->execute();
		return $this->getSauderStaffPhotoDirectoryTasksQuery->fetchAll();
	}
	/*function getPendingEmailGroupingTasks()
	{
		$this->getPendingEmailGroupingTasks = $this->db->prepare("SELECT
		  requestby,
		  employee.employeeID,
		  firstName,
		  lastName,
		  request.requestID,
		  email.emailAddress,
		  emailList.emailListName,
		  UNIX_TIMESTAMP(request.dateSubmitted) as dateSubmitted FROM email JOIN request ON request.requestID = email.requestID JOIN employee ON employee.employeeID = request.employeeID JOIN emailGrouping ON emailGrouping.emailAddress = email.emailAddress JOIN emailList ON emailList.emailListID = emailGrouping.emailListID WHERE email.status = 'done'");
		$this->getPendingEmailTasksQuery->execute();
		return $this->getPendingEmailTasksQuery->fetchAll();
	}
	
	function getDoneEmailGroupingTasks()
	{
		$this->getDoneEmailGroupingTasks = $this->db->prepare("SELECT
		  requestby,
		  employee.employeeID,
		  firstName,
		  lastName,
		  request.requestID,
		  email.emailAddress,
		  emailList.emailListName,
		  UNIX_TIMESTAMP(request.dateSubmitted) as dateSubmitted FROM email JOIN request ON request.requestID = email.requestID JOIN employee ON employee.employeeID = request.employeeID JOIN emailGrouping ON emailGrouping.emailAddress = email.emailAddress JOIN emailList ON emailList.emailListID = emailGrouping.emailListID WHERE email.status = 'done'");
		$this->getDoneEmailTasksQuery->execute();
		return $this->getDoneEmailTasksQuery->fetchAll();
	}*/
	
	function getPrintingTasks($status)
	{
		$this->getComputerTasksQuery = $this->db->prepare("SELECT
		  requestby,
		  employee.employeeID,
		  firstName,
		  lastName,
		  request.requestID,
		  speedChart,
		  main,
		  UNIX_TIMESTAMP(request.dateSubmitted) as dateSubmitted FROM printingAccess JOIN request ON request.requestID = printingAccess.requestID JOIN employee ON employee.employeeID = request.employeeID WHERE printingAccess.status = '$status'");
		$this->getComputerTasksQuery->execute();
		return $this->getComputerTasksQuery->fetchAll();
	}
	
	function getSpeedChartsByEmployee(EmployeeID $employeeID, $status)
	{
		$this->getPrintingTasksByEmployeeQuery = $this->db->prepare("SELECT
		  speedChart
		  FROM printingAccess WHERE status = '$status' AND employeeID = ?");
		$this->getPrintingTasksByEmployeeQuery->execute(array($employeeID));
		$speedCharts = array();
		while($res = $this->getPrintingTasksByEmployeeQuery->fetch())
		{
			$speedCharts[] = $res->speedChart;		
		}	
		return $speedCharts;
	}
	
	function getSpeedChartsByRequest(RequestID $requestID, $status)
	{
		$this->getSpeedChartsByRequestQuery = $this->db->prepare("SELECT
		  speedChart
		  FROM printingAccess WHERE status = '$status' AND requestID = ?");
		$this->getSpeedChartsByRequestQuery->execute(array($requestID));
		$speedCharts = array();
		while($res = $this->getSpeedChartsByRequestQuery->fetch())
		{
			$speedCharts[] = $res->speedChart;		
		}	
		return $speedCharts;
	}
	
	function getSharedDriveAccessesByEmployee(EmployeeID $employeeID, $status)
	{
		$this->getSharedDriveAccessesByEmployeeQuery = $this->db->prepare("SELECT
		  sharedDriveID
		  FROM sharedDriveAccess WHERE status = '$status' AND employeeID = ?");
		$this->getSharedDriveAccessesByEmployeeQuery->execute(array($employeeID));
		$sharedDrives = array();
		while($res = $this->getSharedDriveAccessesByEmployeeQuery->fetch())
		{
			$sharedDrives[] = $res->sharedDriveID;		
		}	
		return $sharedDrives;
	}
	
	function getSharedDriveAccessesByRequest(RequestID $requestID, $status)
	{
		$this->getSharedDriveAccessesByRequestQuery = $this->db->prepare("SELECT
		  sharedDriveID
		  FROM sharedDriveAccess WHERE status = '$status' AND requestID = ?");
		$this->getSharedDriveAccessesByRequestQuery->execute(array($requestID));
		$sharedDrives = array();
		while($res = $this->getSharedDriveAccessesByRequestQuery->fetch())
		{
			$sharedDrives[] = $res->sharedDriveID;		
		}	
		return $sharedDrives;
	}
	
	function getEmailGroupsByRequest(RequestID $requestID, $status)
	{
		$this->getEmailGroupsByRequestQuery = $this->db->prepare("SELECT
		  emailListID
		  FROM emailGrouping WHERE status = '$status' AND requestID = ?");
		$this->getEmailGroupsByRequestQuery->execute(array($requestID));
		$emailGroups = array();
		while($res = $this->getEmailGroupsByRequestQuery->fetch())
		{
			$emailGroups[] = $res->emailListID;		
		}	
		return $emailGroups;
	}
	
	function getSiteCoreAccessTasks($status)
	{
		$this->getSiteCoreAccessTasksQuery = $this->db->prepare("SELECT
		  requestby,
		  employee.employeeID,
		  firstName,
		  lastName,
		  request.requestID,
		  sectionName,
		  userType,
		  trainingRequired,
		  UNIX_TIMESTAMP(request.dateSubmitted) as dateSubmitted FROM siteCoreLogin JOIN request ON request.requestID = siteCoreLogin.requestID JOIN employee ON employee.employeeID = request.employeeID WHERE siteCoreLogin.status = '$status'");
		$this->getSiteCoreAccessTasksQuery->execute();
		return $this->getSiteCoreAccessTasksQuery->fetchAll();
	}
	
	function getSharedDriveAccessTasks($status)
	{
		$this->getSharedDriveAccessTasksQuery = $this->db->prepare("SELECT
		  requestby,
		  employee.employeeID,
		  firstName,
		  lastName,
		  request.requestID,
		  UNIX_TIMESTAMP(request.dateSubmitted) as dateSubmitted FROM sharedDriveAccess JOIN request ON request.requestID = sharedDriveAccess.requestID JOIN employee ON employee.employeeID = request.employeeID WHERE sharedDriveAccess.status = '$status'");
		$this->getSharedDriveAccessTasksQuery->execute();
		return $this->getSharedDriveAccessTasksQuery->fetchAll();
	}
	
	//Admin Section
	function getGeneralInformationTasks($status)
	{
		$this->getGeneralInformationTasksQuery = $this->db->prepare("SELECT
		  requestby,
		  employee.employeeID,
		  firstName,
		  lastName,
		  position,
		  locationName,
		  UNIX_TIMESTAMP(startDate) as startDate,
		  UNIX_TIMESTAMP(endDate) as endDate,
		  ongoing,
		  term,
		  request.requestID,
		  UNIX_TIMESTAMP(request.dateSubmitted) as dateSubmitted FROM employee JOIN request ON request.employeeID = employee.employeeID JOIN tenure ON tenure.requestID = request.requestID WHERE tenure.status = '$status'");
		$this->getGeneralInformationTasksQuery->execute();
		return $this->getGeneralInformationTasksQuery->fetchAll();
	}
	
	function getDoorNamePlateTasks($status)
	{
		$this->getDoorNamePlateTasksQuery = $this->db->prepare("SELECT
		  requestby,
		  employee.employeeID,
		  firstName,
		  lastName,
		  request.requestID,
		  doorNamePlateText,
		  UNIX_TIMESTAMP(request.dateSubmitted) as dateSubmitted FROM doorNamePlate JOIN request ON request.requestID = doorNamePlate.requestID JOIN employee ON employee.employeeID = request.employeeID WHERE doorNamePlate.status = '$status'");
		$this->getDoorNamePlateTasksQuery->execute();
		return $this->getDoorNamePlateTasksQuery->fetchAll();
	}
	
	
	function getPATSystemAccountTasks($status)
	{
		$this->getPATSystemAccountTasksQuery = $this->db->prepare("SELECT
		  requestby,
		  employee.employeeID,
		  firstName,
		  lastName,
		  request.requestID,
		  UNIX_TIMESTAMP(request.dateSubmitted) as dateSubmitted FROM PATSystemAccount JOIN request ON request.requestID = PATSystemAccount.requestID JOIN employee ON employee.employeeID = request.employeeID WHERE PATSystemAccount.status = '$status'");
		$this->getPATSystemAccountTasksQuery->execute();
		return $this->getPATSystemAccountTasksQuery->fetchAll();
	}
	
	function getBusinessCardTasks($status)
	{
		$this->getBusinessCardTasksQuery = $this->db->prepare("SELECT
		  requestby,
		  employee.employeeID,
		  firstName,
		  lastName,
		  request.requestID,
		  businessCardType,
		  UNIX_TIMESTAMP(request.dateSubmitted) as dateSubmitted FROM businessCard JOIN request ON request.requestID = businessCard.requestID JOIN employee ON employee.employeeID = request.employeeID WHERE businessCard.status = '$status'");
		$this->getBusinessCardTasksQuery->execute();
		return $this->getBusinessCardTasksQuery->fetchAll();
	}
	
	function getMailBoxAccessTasks($status)
	{
		$this->getMailBoxAccessTasksQuery = $this->db->prepare("SELECT
		  requestby,
		  employee.employeeID,
		  firstName,
		  lastName,
		  request.requestID,
		  mailSlotAccess.mailSlotAccessID,
		  UNIX_TIMESTAMP(request.dateSubmitted) as dateSubmitted FROM mailSlotAccessor JOIN request ON request.requestID = mailSlotAccessor.requestID JOIN employee ON employee.employeeID = request.employeeID JOIN mailSlotAccess ON mailSlotAccess.mailSlotAccessID = mailSlotAccessor.mailSlotAccessID WHERE mailSlotAccess.shared = '0' AND mailSlotAccessor.status = '$status'");
		$this->getMailBoxAccessTasksQuery->execute();
		return $this->getMailBoxAccessTasksQuery->fetchAll();
	}
	
	function getSharedMailBoxAccessTasks($status)
	{
		$this->getSharedMailBoxAccessTasksQuery = $this->db->prepare("SELECT
		  requestby,
		  employee.employeeID,
		  firstName,
		  lastName,
		  request.requestID as requestID,
		  mailSlotAccess.mailSlotAccessID,
		  UNIX_TIMESTAMP(request.dateSubmitted) as dateSubmitted FROM mailSlotAccessor JOIN request ON request.requestID = mailSlotAccessor.requestID JOIN employee ON employee.employeeID = mailSlotAccessor.employeeID JOIN mailSlotAccess ON mailSlotAccess.mailSlotAccessID = mailSlotAccessor.mailSlotAccessID WHERE mailSlotAccess.shared = '1' AND mailSlotAccessor.status = '$status' ORDER BY request.requestID ASC");
		$this->getSharedMailBoxAccessTasksQuery->execute();
		$sharedMailBoxAccessTasks = array();
		while($res = $this->getSharedMailBoxAccessTasksQuery->fetch())
		{
			if(!array_key_exists($res->requestID, $sharedMailBoxAccessTasks))
			{
				$sharedMailBoxAccessTask = array();
				$sharedMailBoxAccessTask['requestby'] = $res->requestby;
				$sharedMailBoxAccessTask['sharers'] = array();
				$sharedMailBoxAccessTask['sharers'][$res->employeeID] = $res->firstName." ".$res->lastName;
				$sharedMailBoxAccessTask['requestID'] = $res->requestID;
				$sharedMailBoxAccessTask['mailSlotAccessID'] = $res->mailSlotAccessID;
				$sharedMailBoxAccessTask['dateSubmitted'] = $res->dateSubmitted;
				$sharedMailBoxAccessTasks[$res->requestID] = $sharedMailBoxAccessTask;
			}
			else
				$sharedMailBoxAccessTasks[$res->requestID]['sharers'][$res->employeeID] = $res->firstName." ".$res->lastName;
		}
		return $sharedMailBoxAccessTasks;
	}
	
	function changeGroup(UserID $userID)
	{
		$this->changeGroupQuery = $this->db->prepare("SELECT groups.groupID, groupName, shortName FROM groups JOIN (SELECT groupID FROM users WHERE userID = ?) as A ON A.groupID = groups.groupID");
		$this->changeGroupQuery->execute(array($userID));
		$res = $this->changeGroupQuery->fetch();
		$this->groupID = $res->groupID;
		$this->groupName = $res->shortName;
	}
	
	//Advance Stuff 	

	function addColumn($table, $columnname, $datatype, $notnull = false, $defaultvalue = NULL)
	{
		$query = "ALTER TABLE $table ADD $columnname $datatype";
		if($notnull)
			$query .= " NOT NULL";
		if($defaultvalue)
			$query .= " DEFAULT VALUE '$defaultvalue'";
		$query .= ";";
		$this->addColumnQuery = $this->db->prepare($query);
		$this->addColumnQuery->execute();
	}
	
	function restoreColumn()
	{
		$this->restoreColumnQuery = $this->db->prepare();
	}
	
	function describeTable($table, $column = NULL)
	{
		if($column)
			$this->describeTableQuery = $this->db->prepare("DESCRIBE $table;");
		else
			$this->describeTableQuery = $this->db->prepare("DESCRIBE $table $column;");
		$this->describeTableQuery->execute();
		return $this->describeTableQuery->fetchAll();
	}
	
	function deactivateColumn($table, $columnname)
	{
		$details = $this->describeTable($table, $column);
		
		if($details['Null'])
		
		$this->describeTableQuery = $this->db->prepare("CREATE TABLE archived_column_$columnname ;");
		$this->deactivateColumnsQuery = $this->db->prepare("ALTER TABLE $table DROP $columnname $datatype");
	}
	
};

