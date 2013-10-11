<?php
  //Forms posted
  if(!empty($_POST))
  {
    $errors = array();
    $username = sanitize(trim($_POST["username"]));
    $password = trim($_POST["password"]);
    
    //Perform some validation
    //Feel free to edit / change as required
    if($username == "")
    {
      $errors[] = lang("ACCOUNT_SPECIFY_USERNAME");
    }
    if($password == "")
    {
      $errors[] = lang("ACCOUNT_SPECIFY_PASSWORD");
    }

    if(count($errors) == 0)
    {
      //A security note here, never tell the user which credential was incorrect
      if(!usernameExists($username))
      {
        $errors[] = lang("ACCOUNT_USER_OR_PASS_INVALID");
      }
      else
      {
        $userdetails = fetchUserDetails($username);
        //See if the user's account is activated
        if($userdetails["active"]==0)
        {
          $errors[] = lang("ACCOUNT_INACTIVE");
        }
        else
        {
          //Hash the password and use the salt from the database to compare the password.
          $entered_pass = generateHash($password,$userdetails["password"]);
          
          if($entered_pass != $userdetails["password"])
          {
            //Again, we know the password is at fault here, but lets not give away the combination incase of someone bruteforcing
            $errors[] = lang("ACCOUNT_USER_OR_PASS_INVALID");
          }
          else
          {
            //Passwords match! we're good to go'
            
            //Construct a new logged in user object
            //Transfer some db data to the session object
            $loggedInUser = new loggedInUser();
            $loggedInUser->email = $userdetails["email"];
            $loggedInUser->user_id = $userdetails["id"];
            $loggedInUser->hash_pw = $userdetails["password"];
            $loggedInUser->title = $userdetails["title"];
            $loggedInUser->displayname = $userdetails["display_name"];
            $loggedInUser->username = $userdetails["user_name"];
            
            //Update last sign in
            $loggedInUser->updateLastSignIn();
            $_SESSION["userCakeUser"] = $loggedInUser;
            
            setUser($loggedInUser->user_id);
            
            //Redirect to user menu page
            header("Location: index.php");
            die();
          }
        }
      }
    }
  }
?>
<?php
echo resultBlock($errors,$successes);
?>
<div id='regbox'>
  <form id="login" name='login' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='post'>
    <table>
      <tr>
        <td><label>Username:</label></td>
        <td><input type='text' name='username' /></td>
      </tr>
      <tr>
        <td><label>Password:</label></td>
        <td><input type='password' name='password' /></td>
      </tr>
      <tr><td colspan=2><input type='submit' value='Log in' class='submit' /></td></tr>
      <tr><td></td><td class="small align-right"><a href='register.php'>Register</a></td></tr>
      <tr><td></td><td class="small align-right"><a href='forgot-password.php'>Forgot Password</a></td></tr>
    </table>
  </form>
</div>