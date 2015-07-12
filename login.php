<?php
require_once("inc/common.php");

/*if(!$dataMgr->courseID)
{
    //Give them a 404
    page_not_found();
}*/

if(!isset($_GET["redir"]) && empty($_SERVER["HTTPS"]))
    redirect_to_page("login.php?redir=1");

try
{
    $loginAttemptFailed = false;
    $username = '';
    if(isset($_POST["username"])){ $username = $_POST['username']; }

    if (array_key_exists("action", $_POST) && $_POST["action"]=="login")
    {
        $loginAttemptFailed = !$authMgr->performLogin($_POST['username'], $_POST['password']);
    }
	
    #Now, if we've gotten here, we need to see if the user is logged in
    if ($authMgr->isLoggedIn()) {
        #Silly user, they are logged in - take them to the main page
        redirect_to_main();
    } else {
        #They are not logged in, we need to give them the option

        $content =
    '<div class="box">
    <form action="?redir=1" method="post"><input type="hidden" name="action" value="login">
    <table>
        <tr><td>Username: </td><td><input type="text" name="username" value="'.$username.'"/></td></tr>
        <tr><td>Password: </td><td><input type="password" name="password" /></td></tr>
    </table>
    <table>
        <tr><td><input type="submit" value="Login" /></td></tr>
    </table>
    </form>
    <a href="'.$SITEURL.'">Go back</a>
    </div>';
    }
	//$dataMgr->authMgr->addUser('sauder_user', 'Carson', 'Woo', 'all', 1, '12345');
    render_page();
}catch(Exception $e){
    render_exception_page($e);
}
?>
