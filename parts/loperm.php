<?php
// Include config file
require_once 'config.php';
 
// Define variables and initialize with empty values
$username = $password = $permission = $first = "";
$username_err = $password_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    if(empty(trim($_POST["username"]))){
        $username_err = 'Zadejte, prosím, svoje uživatelské jméno.';
    } else{
        $username = trim($_POST["username"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST['password']))){
        $password_err = 'Zadejte, prosím, svoje heslo.';
    } else{
        $password = trim($_POST['password']);
    }
    
    // Validate credentials
    if(empty($username_err) && empty($password_err)){
        // Prepare a select statement
        $sql = "SELECT username, password, permission, first FROM login WHERE username = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = $username;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Store result
                mysqli_stmt_store_result($stmt);
                
                // Check if username exists, if yes then verify password
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $username, $hashed_password, $permission, $first);
                    if(mysqli_stmt_fetch($stmt)){
                        if($password==$hashed_password){
							//Check if user is not paused
								if($permission!="p"){
								/* Password is correct, so start a new session and
								save the username to the session */
								session_start();
								$_SESSION['username'] = $username;
								$_SESSION['permission'] = $permission;  
								$_SESSION['first'] = $first; 
								header("location: index.php");
								}else{
									$username_err = 'Váš účet je zablokován. Pokud si myslíte, že se jedná o omyl, neprodleně mě kontaktujte.';
								}
                        } else{
                            // Display an error message if password is not valid
                            $password_err = 'Vaše heslo není správné. Napište jej znovu, příp. si zažádejte o heslo nové.';
                        }
                    }
                } else{
                    // Display an error message if username doesn't exist
                    $username_err = 'Překontrolujte své uživatelské jméno.';
                }
            } else{
                echo "Vznikla nečekaná chyba. Zkuste se, prosím, znovu připojit trochu později.";
            }
        }
        
        // Close statement
        mysqli_stmt_close($stmt);
    }
    
    // Close connection
    mysqli_close($link);
}
?>