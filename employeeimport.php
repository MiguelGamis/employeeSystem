<?php
require_once("inc/common.php");
try
{
    if(!isset($adminFileSkip) || !$adminFileSkip)
        $authMgr->enforceLoggedIn();
    if(!isset($extraUrl))
        $extraUrl = "";
    //$dataMgr->requireGroup();
	//$groupName = $dataMgr->groupName;

   function getTypeRow($currentType = '')
    {
        $html = "<tr><td>Type</td><td><select name='userType'>\n";
        foreach(array("student"=>"Student", "marker"=>"Marker", "instructor"=>"Instructor") as $type => $name){
            $html .= "<option";
            if($currentType == $type)
                $html .= " selected='selected'";
            $html .= " value='$type'>$name</option>\n";
        }
        $html .= "</select></td></tr>";
        return $html;
    }

    if(array_key_exists("save", $_GET)){
        //Do we have a password - if so update it?
        if(array_key_exists("password", $_POST) && strlen($_POST["password"]) > 0){
            $authMgr->addUserAuthentication($_POST["username"], $_POST["password"]);
        }
        //Do everything else
        if(array_key_exists("userID", $_POST)){
            $dataMgr->updateUser(new UserID($_POST["userID"]), $_POST["username"], $_POST["firstname"], $_POST["lastname"], $_POST["studentid"], $_POST["userType"]);
        }else{
            $dataMgr->addUser($_POST["username"], $_POST["firstname"], $_POST["lastname"], $_POST["studentid"], $_POST["userType"]);
        }
        //The save completed without issue, fall back to the main page
    }
    
    if(array_key_exists("uploadpost", $_GET))
    {
        //Parse through the uploaded file and insert all the users as required
        if ($_FILES["file"]["error"] > 0)
        {
            throw new RuntimeException("Error reading uploaded CSV: " . $_FILES["file"]["error"]);
        }
        else
        {
            foreach(file($_FILES["file"]["tmp_name"]) as $lineNum => $line){
                try
                {
                    $row = explode(",", $line);
                    $lastName = trim($row[0]);
                    $firstName = trim($row[1]);
                    $employeeID = trim($row[2]);
                    $comments = trim($row[3]);
                    $CWLID = trim($row[4]);
					$requestBy = trim($row[5]);
					$dateSubmitted = trim($row[6]);
					$businessCard = trim($row[7]);
					$businessCardType = trim($row[8]);
					$PATSystemAccount = trim($row[9]);
					$locationName = trim($row[10]);
					$startDate = trim($row[10]);
					$endDate = trim($row[11]);
					$ongoing = trim($row[12]);
					$term = trim($row[13]);
					$telephoneNumber = trim($row[14]);
					$Installsinglelinephone = 1;
				    $Installmultilinephone = 1;
				    $Moveupdatelocation = 1;
				    $Updatedisplaynameonphone = 1;
				    $Installvoicemail = 1;
				    $Installunifiedmessaging = 1;
			  	    $Resetvoicemailpassword = 1;
			   	    $Requestvoicemailpassword = 1;
				    $Requestlongdistancecode = 1;
				    $Setupcallersmenu = 1;
				    $monthlyRental = 1;
				    $longDistanceCharges = 1;
				    $installation = 1;
					$voiceServiceBuilding = 1;
					$internalPhoneListRegistry = 1;
					$UBConlineDirectory = 1;
					$sauderWebsiteStaffDirectory = 1;
					$sauderStaffPhotoDirectory = 1;

                    if($type != "student" && $type != "marker" && $type != "instructor")
                        throw new Exception("'$type' is not a valid user type"); 

                    try
                    {
                        $id = $dataMgr->getUserID($username);
                        $dataMgr->updateUser($id, $username, $firstName, $lastName, $studentID, $type);
                        $content .= "Updating $firstName $lastName<br>";
                    }catch(Exception $e){
                        //This is a new user, add them in
                        $dataMgr->addUser($username, $firstName, $lastName, $studentID, $type);
                    }
                }catch(Exception $e){
                    $content .= "At line $lineNum: " . $e->getMessage() . "<br\n>";
                }
            }
        }
    }

    if(array_key_exists("new", $_GET))
    {
        //If we're editing, then these variables have all been filled up
        $content .= $authMgr->getRegistrationFormHTML("", "", "", "", getTypeRow(), !$authMgr->supportsSettingPassword(), "?courseid=$dataMgr->courseID&save=1", true);
    }
    else if (array_key_exists("edit", $_GET))
    {
        $user = $dataMgr->getUserInfo(new UserID($_GET["edit"]));
        $content .= $authMgr->getRegistrationFormHTML($user->username, $user->firstName, $user->lastName, $user->studentID, getTypeRow($user->userType) . "<input type='hidden' name='userID' value='$user->userID' />", !$authMgr->supportsSettingPassword(), "?courseid=$dataMgr->courseID&save=1", true);
    }
    else if(array_key_exists("upload", $_GET))
    {
        //Run up the message about how to upload a list
        $content .= '<h2>Upload Class List</h2>';
        $content .= "Class lists must be headerless CSV files (not tab separated like Open Office does by default), with the following order:<br>Last Name, First Name, Employee ID/Student ID Number,  individualMailSlot, sharedMailSlot, User Type (one of student ,instructor or marker)<br><br>";
        $content .= "<form action='?uploadpost=1$extraUrl' method='post' enctype='multipart/form-data'>\n";
        $content .= "<label for='file'>Filename:</label>\n";
        $content .= "<input type='file' name='file' id='file'><br>\n";
        $content .= "<input type='submit' name='submit' value='Upload'>\n";
        $content .= "</form>\n";
    }
    else
    {
        //Give the option to add a student
        $content .= "<a href='?new=1$extraUrl'>New User</a> <a href='?upload=1$extraUrl'>Upload Class List</a><br><br>\n";
        $content .= "<h2>$groupName Registered Users</h2>\n";
        //We need to display a list of all the users...
        $userMap = $dataMgr->getUserDisplayMap();
        foreach($userMap as $id => $displayName)
        {
            $content .= "<a href='?edit=$id"."$extraUrl'>$displayName</a><br>\n";
        }
    }
    render_page();
}catch(Exception $e){
    render_exception_page($e);
}
