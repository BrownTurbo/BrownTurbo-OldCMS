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
	//$SQLHandler->SQLDrop(5);
?>

<!DOCTYPE HTML>
<html>
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
				<?php 
				   if(isset($_GET["annc"]))
				   {
					  $query = $SQLHandler->SQLQuery("SELECT * FROM `" .$configuration->db->dbprefix.$configuration->db->dbtable. "` WHERE ID = '" .$_GET['annc']. "' ORDER BY ID DESC");
					  if($query && (is_bool($query) ? False : mysqli_num_rows($query) >= 1))
					  {
					     while ($row = mysqli_fetch_array($query, MYSQL_ASSOC))
					     {
						    echo $configuration->general->servernick. " Gaming's Announcements Managing Module - " .$row['Subject'];
						 }
				  	  }
					  else 
					  {
						 echo $configuration->general->servernick. " Gaming's Announcements Managing Module - Home";
						 unset($_GET["annc"]);
					  }
				   }
				   else
				   {
					  echo $configuration->general->servernick. " Gaming's Announcements Managing Module - Home";
				   }
				?>
				<a href="admin.php" class="button force-right">Admin</a>
			</div>
			
			<div id="announcements">
				<?php
				if(empty($_GET["annc"]))
				{
					$query = $SQLHandler->SQLQuery("SELECT * FROM `" .$configuration->db->dbprefix.$configuration->db->dbtable. "` ORDER BY ID DESC");
					
					echo '<table id="anncms">';
					echo "<th>Subject</th><th>Announcement</th><th>Date</th><tr>";;
					if($query && (is_bool($query) ? False : mysqli_num_rows($query) >= 1))
					{					
					   while ($row = mysqli_fetch_array($query, MYSQL_ASSOC))
					   {
						  $id = $row['ID'];
						  echo "<tr>";
						  echo "<td>" .$row['Subject']. "</td>";
						  echo "<td>" .mb_strimwidth(htmlspecialchars_decode(stripslashes($row["Announcement"])), 0, 23, "..."). " <a href=\"?annc=".$id."\" class=\"read-link\">More info</a></td>";
						  echo "<td>" .$row['Date']. "</td>";
						  echo "</tr>";
					  }
					}
					else
					{
					   echo '<div class="warning">There isn\'t any kind of Valid Announcements.</div><script type="text/javascript">document.getElementById("anncms").style.display = "none";</script>';	
					}
					echo "</table>";
				}
				else
				{
					echo "</div><div id=\"showannouncement\">";
					$query = $SQLHandler->SQLQuery("SELECT * FROM `" .$configuration->db->dbprefix.$configuration->db->dbtable. "` WHERE ID = '" .$_GET['annc']. "' ORDER BY ID DESC");
					
					if($query && (is_bool($query) ? False : mysqli_num_rows($query) >= 1))
					{
 					   while ($row = mysqli_fetch_array($query, MYSQL_ASSOC))
					   {
					      echo "<div class=\"success\">Watching announcement about <b>".$row['Subject']."</b> from <b>".$row['Date']."</b>.</div>";
					      echo htmlspecialchars_decode(stripslashes($row['Announcement']));
					   }
					}
					else 
					{
					   echo "<div class=\"error\">Invalid Announcement ID: " .$_GET['annc']. "</div>";
					}
					echo "</div><a href=\"index.php\" class=\"button\" style=\"margin-top: 7px;padding: 3px 23px 10px 6px;\">Go back to Homepage</a>";
				}
				?>
			</div>
		</div>
		<div class="footer" style="margin-top:60px;">
		   <?php 
		      echo '<p style="font-size: 15px;">Copyright <strong onclick="var win = window.open(\'https://www.br-gaming.ovh/?p=1.0\', \'_blank\');win.focus();">' .$configuration->general->servernick. ' Gaming\'s Announcements Managing Module</strong>&trade; &copy; 2015-' .date('Y'). ' All Rights Reserved.</p>'; 
		   ?>
		</div>
	</body>
</html>