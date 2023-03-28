<?php
namespace SQL;
class Handler {
   protected $SQL_Config = array('hostname' => 'localhost', 
                                 'username' => 'root', 
                                 'password' => '', 
                                 'dbname' => 'BRG18',
                                 'dbprefix' => 'br_',
                                 'dbtable' => 'annmcs',
                                 'dieonerr' => True
                                ); // Default Configuration Value(s)
   public $SQLDB;
   protected $ValidKeys = array('hostname', 'username', 'password', 'dbname', 'dbprefix', 'dbtable', 'dieonerr');

   public function __construct(array $config)
   {
      if(is_array($config) && count($config) >= 1)
      {
         foreach($config as $cKey => $cValue)
         {
             for($t = 0; $t < count($this->ValidKeys); $t++)
             {
                if(strcmp($cKey, $this->ValidKeys[$t]) == False)
				{
                   $this->SQL_Config[$cKey] = $cValue;
                   break;
                }
                else
                {
                   if($t == count($this->ValidKeys)-1)
                   {
                      $exception = "[SQL-Handler]: Invalid SQL Configuration Key: " .$cKey. "(Valid Keys: " .join(', ', $this->ValidKeys). ")";
                      if(function_exists("WritetoFile"))
                      {
                         WritetoFile("logs/sql_errors.log", $exception);
                      }
                      else
                      {
                         throw new \Exception($exception);
                      }
                      break;                   
                   }
                   else
                   {
                      continue;
                   }
                }
             }
         }
      }
      else
      {
         $notice = "[SQL-Handler]: Loaded Default SQL Configuration(s) Successfully.";
         if(function_exists("WritetoFile"))
         {
            WritetoFile("logs/sql_debug.log", $notice);
         }
      }
	  $this->SQLConnect($this->SQL_Config['dieonerr']);
   }
   protected function SQLConnect($die_on_error)
   {
      $this->SQLDB = mysqli_connect($this->SQL_Config['hostname'], $this->SQL_Config['username'], $this->SQL_Config['password'], $this->SQL_Config['dbname']);	
      if(mysqli_connect_errno())
      {
         $exception = "[MySQL]: Connection to BrownTurbo Gaming's Database failed: " .(is_bool($this->SQLDB) ? 'SQL_ERROR_NOT_FOUND' : mysqli_error($this->SQLDB));
         if(function_exists("WritetoFile"))
         {
            WritetoFile("logs/sql_errors.log", $exception);
         }
         if($die_on_error)
         {
            throw new \Exception(json_encode(array('errorcode' => 408, 'errormsg' => 'Request Timeout.', 'message' => $exception)));
         }
      }
      else
      {
        $fIns = fopen('installed.lock', 'r');
        if($fIns == False || (filesize('installed.lock') >= 1 ? strpos(fread($fIns, filesize('installed.lock')), 'installed successfully') == False : True))
        {
           $this->SQLQuery("CREATE DATABASE IF NOT EXISTS `" .$this->SQL_Config['dbname']. "`");
           $this->SQLQuery("CREATE TABLE IF NOT EXISTS `" .$this->SQL_Config['dbprefix'] . $this->SQL_Config['dbtable']. "` (`ID` int(11) NOT NULL, `Subject` text NOT NULL, `Announcement` longtext NOT NULL, `Date` text NOT NULL)");
           $this->SQLQuery("ALTER TABLE `" .$this->SQL_Config['dbprefix'] . $this->SQL_Config['dbtable']. "` ADD PRIMARY KEY IF NOT EXISTS (`ID`)");
           $this->SQLQuery("ALTER TABLE `" .$this->SQL_Config['dbprefix'] . $this->SQL_Config['dbtable']. "` MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT");
           $this->SQLQuery("INSERT INTO `" .$this->SQL_Config['dbprefix'] . $this->SQL_Config['dbtable']. "` (`ID`, `Subject`, `Announcement`, `Date`) VALUES(NULL, 'Hey All', '&lt;p&gt;How are you ??? Maryem &amp;lt;3&lt;/p&gt;\r\n', '2018/06/09 - 07:48:13 PM'), (NULL, 'Hello all', '&lt;p&gt;How are you ??? Are you here ??&lt;/p&gt;\r\n', '2018/06/09 - 11:43:34 PM'), (NULL, 'Hey All', '&lt;p&gt;Hey all, How are you ?? Are you here ???&amp;nbsp;&lt;/p&gt;\r\n', '2018/06/09 - 11:46:23 PM')");
           fclose($fIns); $notice = 'BrownTurbo Gaming\'s Announcements Managing Module\'s Database have been installed successfully.';
           if(function_exists("WritetoFile"))
           {
              WritetoFile("installed.lock", $notice, False, '\r');
              WritetoFile("logs/sql_debug.log", "[MySQL]: " .$notice);
           }           
        }
        if(function_exists("WritetoFile"))
		{
           $notice = "[MySQL]: Connected to Brownturbo Gaming's Database Successfully.";
           WritetoFile("logs/sql_debug.log", $notice);
		}
      }
   }

   public function SQLQuery($query, $die_on_error = NULL)
   {
       $SQL = mysqli_query($this->SQLDB, $query);
       if (!$SQL || (is_bool($SQL) ? False : (mysqli_num_rows($SQL) == NULL || mysqli_num_rows($SQL) == False || is_numeric(mysqli_num_rows($SQL)) == False)))
       {
          $exception = "[MySQL]: Failed to Execute the SQL Query: " .(strlen(mysqli_error($this->SQLDB)) ? mysqli_error($this->SQLDB) : 'SQL_ERROR_NOT_FOUND');
          if(function_exists("WritetoFile"))
          {
             WritetoFile("logs/sql_errors.log", $exception);
          }
          switch($die_on_error)
          {
             case NULL: {
                if(boolval($this->SQL_Config['dieonerr']))
                {
                   throw new \Exception(json_encode(array('errorcode' => 408, 'errormsg' => 'Request Timeout.', 'message' => $exception)));
                }
             }
             break;
             case ($die_on_error == 1 || $die_on_error == True): {
                throw new \Exception(json_encode(array('errorcode' => 408, 'errormsg' => 'Request Timeout.', 'message' => $exception)));
             }
             break;
             default: break;
          }
		  $SQL = False;
       }
       return $SQL;
   }
   
   public function SQLCountRows()
   {
	  $query = $this->SQLQuery("SELECT * FROM `" .$this->SQL_Config['dbprefix'] . $this->SQL_Config['dbtable']. "` ORDER BY ID DESC");
	  return mysqli_num_rows($query);
   }

   public function SQLDrop($rowID, $auto_increment = True)
   {
      $response = False;
      if($this->SQLQuery("DELETE FROM `" .$this->SQL_Config['dbprefix'] . $this->SQL_Config['dbtable']. "` WHERE `ID` = '" .$rowID. "' ORDER BY ID DESC"))
      {
         $response = ($auto_increment ? $this->SQLQuery("ALTER TABLE `" .$this->SQL_Config['dbprefix'] . $this->SQL_Config['dbtable']. "` MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT = " .$this->SQLCountRows()) : True);
      }
      return $response;
   }
   //UPDATE `" .$this->SQL_Config['dbprefix'] . $this->SQL_Config['dbtable']. "` SET `Subject` = 'Hey All', `Announcement` = 'Hey all', `Date` = '2018/06' WHERE `ID` = ''" .$id. "'
}
?>