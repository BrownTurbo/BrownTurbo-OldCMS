<?php
    include "functions.php";
    include "SQLHandler.php";
    try
    {
       $configuration = simplexml_load_file("config.xml"); 
    }
    catch(Exception $e)
    {
	   $error = "[ERROR]: " .$e->getMessage();
	   WritetoFile("logs/general_error.log", $error);
	   trigger_error("We're facing some techincal issues.", E_USER_ERROR); 
	}

	$SQLHandler = new SQL\Handler(array('hostname' => $configuration->db->hostname, 'username' => $configuration->db->username, 'password' => $configuration->db->password, 'dbname' => $configuration->db->dbname, 'dbtable' => $configuration->db->dbtable, 'dbprefix' => $configuration->db->dbprefix));
    error_reporting($configuration->general->errorlogging);
    set_error_handler("OnERRORDetected");
	date_default_timezone_set($configuration->general->timezone);
	if($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		if(strlen(htmlspecialchars($_POST["editor1"])) >= 1 || strlen(htmlspecialchars($_POST["subject"])) >= 1)
		{
			$SQLHandler->SQLQuery("INSERT INTO `" .$configuration->db->dbprefix.$configuration->db->dbtable. "` (`ID`, `Subject`, `Announcement`, `Date`) VALUES (NULL, '" .$_POST["subject"]. "', '" .htmlspecialchars($_POST["editor1"]). "', '" .(date("Y/m/d"). " - " .date("h:i:s A")). "')");
			unset($_POST["editor1"]); unset($_POST["subject"]);
		}
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?= $configuration->general->servernick ?> Gaming's Announcements Managing Module</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
		<link rel="stylesheet" href="style.css" />
		<link href="favicon.ico" rel="shortcut icon" />
	</head>
	<body>
		<div id="page-wrapper">
			<div id="headbar">
				<?php echo $configuration->general->servernick. " Gaming's Announcements Managing Module - Admin"; ?>
				<a href="index.php" class="button force-right">Home</a>
			</div>
						
			<div id="announcements">
		        <?php 
				   $query = $SQLHandler->SQLQuery("SELECT * FROM `" .$configuration->db->dbprefix.$configuration->db->dbtable. "` WHERE ID = " .$SQLHandler->SQLCountRows(). " ORDER BY ID DESC");
				   if($query && (is_bool($query) ? False : mysqli_num_rows($query) >= 1))
				   {
			          echo '<div class="announcements"><p>';
					  while ($row = mysqli_fetch_array($query, MYSQL_ASSOC))
					  {
	                     echo '<b>' .mb_strimwidth($row["Subject"], 0, 8, "..."). ' - ' .$row["Date"]. '</b><br />' .mb_strimwidth(strip_tags(htmlspecialchars_decode(stripslashes($row["Announcement"]))), 0, 30, "..."). ' <a id="moreinfo" href="index.php?annc=' .$row["ID"]. '" target="_blank">More info</a>';
					  }
	                  echo '</p></div>';
				   }
				?>
				<form action="" method="post">
				   <p style="font-size: 13px;">Administration Password:</p> <input style="margin-top: -28px;margin-left: 150px;" type="password" name="secure" placeholder="Secure password" />
				   <p style="font-size: 13px;">Subject:</p> <input style="margin-top: -28px;margin-left:50px;" type="text" name="subject" placeholder="Type a subject" />
				   <p style="font-size: 13px;">Announcement:</p> <textarea  style="margin-top: -28px;margin-left: 150px;" class="ckeditor" name="editor1"></textarea>
				   <br /><hr /><input type="submit" value="Submit" />
				</form>
				<?php
   			       if(isset($_POST["editor1"]) && isset($_POST["subject"]) && isset($_POST["secure"]))
				   {
				      if(!strlen(htmlspecialchars_decode(stripslashes($_POST["editor1"]))) || !strlen($_POST["subject"]))
   			          {
      		             echo "<div class='warning'>Fill out the fields to create a new announcement!</div>";
      		          }				
      		          else if(strlen(htmlspecialchars_decode(stripslashes($_POST["editor1"]))) >= 1 && strlen($_POST["subject"]) >= 1)
      		          {
						 if(strlen($_POST["subject"]) >= 35)
						 {
						    echo "<div class=\"error\">Announcement Subject's Length Limit excceded. (" .strlen($_POST["subject"]). "/35 chr)</div>";
						 }
						 else
						 {
                            echo "<div class=\"success\">You`ve submitted a new announcement.</div>";
						 }
					  }
					  if(strcmp($_POST["secure"], $configuration->general->securitypass))
					  {
					     //exit(json_encode(array('errorcode' => 401, 'errormsg' => 'Unauthorized.', 'message' => 'You\'ve entered the wrong password. Contact the Administrator to get it.')));
						 echo "<div class='error'>You've entered the wrong password. Contact the Administrator to get it.</div>";
						 unset($_POST["secure"]);
					  }						
				   }					   
   		        ?>
			</div>
	    </div>
		<div class="footer" style="margin-top:60px;">
		   <?php 
		      echo '<p style="font-size: 15px;">Copyright <strong onclick="var win = window.open(\'https://www.br-gaming.ovh/?p=1.0\', \'_blank\');win.focus();">' .$configuration->general->servernick. ' Gaming\'s Announcements Managing Module</strong>&trade; &copy; 2015-' .date('Y'). ' All Rights Reserved.</p>'; 
		   ?>
		</div>

		<script src="ckeditor/ckeditor.js"></script>
		<script src="ckeditor/config.js"></script>
	</body>
</html>