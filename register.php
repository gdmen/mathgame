<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Prevent the user visiting the logged in page if he/she is already logged in
if(isUserLoggedIn()) { header("Location: index.php"); die(); }

//Forms posted
if(!empty($_POST))
{
	$errors = array();
	$email = trim($_POST["email"]);
	$username = trim($_POST["username"]);
	$displayname = $username;
	$password = trim($_POST["password"]);
	$confirm_pass = trim($_POST["passwordc"]);
	$captcha = md5($_POST["captcha"]);
	
	
	if ($captcha != $_SESSION['captcha'])
	{
		$errors[] = lang("CAPTCHA_FAIL");
	}
	if(minMaxRange(5,25,$username))
	{
		$errors[] = lang("ACCOUNT_USER_CHAR_LIMIT",array(5,25));
	}
	if(!ctype_alnum($username)){
		$errors[] = lang("ACCOUNT_USER_INVALID_CHARACTERS");
	}
	if(minMaxRange(8,50,$password) && minMaxRange(8,50,$confirm_pass))
	{
		$errors[] = lang("ACCOUNT_PASS_CHAR_LIMIT",array(8,50));
	}
	else if($password != $confirm_pass)
	{
		$errors[] = lang("ACCOUNT_PASS_MISMATCH");
	}
	if(!isValidEmail($email))
	{
		$errors[] = lang("ACCOUNT_INVALID_EMAIL");
	}
	//End data validation
	if(count($errors) == 0)
	{	
		//Construct a user object
		$user = new User($username,$displayname,$password,$email);
		
		//Checking this flag tells us whether there were any errors such as possible data duplication occured
		if(!$user->status)
		{
			if($user->username_taken) $errors[] = lang("ACCOUNT_USERNAME_IN_USE",array($username));
			if($user->displayname_taken) $errors[] = lang("ACCOUNT_DISPLAYNAME_IN_USE",array($displayname));
			if($user->email_taken) 	  $errors[] = lang("ACCOUNT_EMAIL_IN_USE",array($email));		
		}
		else
		{
			//Attempt to add the user to the database, carry out finishing  tasks like emailing the user (if required)
			if(!$user->userCakeAddUser())
			{
				if($user->mail_failure) $errors[] = lang("MAIL_ERROR");
				if($user->sql_failure)  $errors[] = lang("SQL_ERROR");
			}
		}
	}
	if(count($errors) == 0) {
		$successes[] = $user->success;
	}
}
?>
<?php require_once('components/session.php'); require_once("models/header.php"); ?>
<?php
if(isUserLoggedIn()){
  header('Location: /');
} else {
?>
<div id="content" role="main">
  <?php echo resultBlock($errors,$successes); ?>
  <div id='regbox'>
    <form name='newUser' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='post'>
      <table>
        <tr>
          <td><label>Username:</label></td>
          <td><input type='text' name='username' /></td>
        </tr>
        <tr>
          <td><label>Password:</label></td>
          <td><input type='password' name='password' /></td>
        </tr>
        <tr>
          <td><label>Confirm:</label></td>
          <td><input type='password' name='passwordc' /></td>
        </tr>
        <tr>
          <td><label>Email:</label></td>
          <td><input type='text' name='email' /></td>
        </tr>
        <tr>
          <td colspan="2"><img src='models/captcha.php'></td>
        </tr>
        <tr>
          <td><label>Enter Code:</label></td>
          <td><input name='captcha' type='text'></td>
        </tr>
        <tr><td colspan=2><input type='submit' value='Register' class='submit' /></td></tr>
        <tr><td></td><td class="small align-right"><a href='index.php'>Log in</a></td></tr>
      </table>
    </form>
  </div>

</div>
<?php } ?>

<?php require_once("components/footer.php"); ?>
</body>
</html>

</div>
<div id='bottom'></div>
</div>
</body>
</html>
