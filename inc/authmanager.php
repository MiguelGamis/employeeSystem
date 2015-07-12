<?php

class AuthManager
{
	private $salt = "default_salt_fish";
    private $dataMgr;
	private $db;	
	
    function __construct($dataMgr)
    {
        $this->db = $dataMgr->getDatabase();
        $this->dataMgr = $dataMgr;
    }

    function isLoggedIn()
    {
        global $_SESSION;
        return array_key_exists("loggedID", $_SESSION) && $this->dataMgr->isUser(new UserID($_SESSION["loggedID"]));
    }

    function enforceLoggedIn(){
        if (!$this->isLoggedIn()) {
            redirect_to_page("login.php");
        }
    }

    function performLogin($username, $password)
    {
        global $_SESSION;

        $this->validateUserName($username);

        #Check to make sure we should even bother with the authentication
        if($username != "" && $password != "") {
            #Is this person a registered user?
            if($this->dataMgr->isUserByName($username)) {
                #They do exist, time to preform the actual authentication
                if($this->checkAuthentication($username, $password))
                {
                    $_SESSION["loggedID"] = $this->dataMgr->getUserID($username)->id;
                    $_SESSION["logged"] = $username;
                    return true;
                }
            }
        }
        return false;
    }

    function getCurrentUsername()
    {
        if(isset($_SESSION["logged"]))
            return $_SESSION["logged"];
        else return NULL;
    }

    function userNameExists($username)
    {
        $sh = $this->db->prepare("SELECT username FROM users WHERE username=?;");
        $sh->execute(array($username));
        return $sh->fetch() != NULL;
    }

    function checkAuthentication($username, $password)
    {
        $hash = $this->getHash($password);
        $sh = $this->db->prepare("SELECT username FROM users WHERE username=? && passwordHash=?;");
        $sh->execute(array($username, $hash));
        return $sh->fetch() != NULL;
    }

    function addUser($username, $firstName, $lastname, $userType, $password)
    {
        //TODO: Make this tied to username/courseID instead of just username
        $hash = $password;//$this->getHash($password);
        $sh = $this->db->prepare("INSERT INTO users (username, userType, active, passwordHash) VALUES (?, ?, 1, ?) ON DUPLICATE KEY UPDATE userType = ?, passwordHash = ?;");
        return $sh->execute(array($username, $userType, $hash, $userType, $hash));
    }

    function removeUserAuthentication($username)
    {
        throw new Exception("Not implemented");
    }

    function getHash($password)
    {
        return "".sha1($this->salt.sha1($this->salt.sha1($password)));
    }

	function validateUserName($username)
    {
        if(preg_match("/.*([^a-zA-Z0-9]).*/", $username))
        {
            throw new Exception("Invalid users name");
        }
        return true;
    }
	
    function supportsSettingPassword()
    {
        return true;
    }
	
	function enforceAllSeeing(){
        #Make sure that they are logged in, and an instructor
        global $USERID;
        if(!$this->isLoggedIn() || ! $this->dataMgr->isAllSeeing($USERID)) {
            redirect_to_page("login.php");
        }
    }
    
    function becomeUser(UserID $userid)
    {
        $this->enforceAllSeeing();
        #Do the assignment
        $_SESSION["oldAllSeeingID"] = $_SESSION["loggedID"];
        $_SESSION["oldAllSeeing"] = $_SESSION["logged"];
        $_SESSION['loggedID'] = $userid->id;
        $_SESSION['logged'] = $this->dataMgr->getUsername($userid);
    }
	
	function returnToAllSeeing()
    {
        global $_SESSION;
        if(!array_key_exists('oldAllSeeingID', $_SESSION) || !array_key_exists('oldAllSeeing', $_SESSION)){
            throw new Exception("Session does not contain the return user");
        }
        #Set the USER variable properly
        $_SESSION["loggedID"] = $_SESSION["oldAllSeeingID"];
        $_SESSION["logged"] = $_SESSION["oldAllSeeing"];
        unset($_SESSION['oldAllSeeingID']);
        unset($_SESSION['oldAllSeeing']);
    }
}


