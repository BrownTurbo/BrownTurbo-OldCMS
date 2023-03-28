<?php 
function OnERRORDetected($errno, $errstr, $errfile, $errline)
{
    switch ($errno) 
    {
        case E_USER_ERROR:
        $error = "ERROR: " .$errstr. " Fatal error on line " .$errline. " in file " .$errfile;
        WritetoFile("logs/php_error.log", $error);
        exit(1);
        break;
        case E_USER_WARNING:
        $warning = "WARNING: " .$errstr. " on line " .$errline. " in file " .$errfile;
        WritetoFile("logs/php_error.log", $warning);
        break;
        case E_USER_NOTICE:
        $notice = "NOTICE: " .$errstr. " on line " .$errline. " in file " .$errfile;
        WritetoFile("logs/php_error.log", $notice);
        break;
        default:
        $error = "Unknown error type: " .$errstr. " on line " .$errline. " in file " .$errfile;
        WritetoFile("logs/php_error.log", $error);
        break;
    }
    return true;
}
function WritetoFile($fName, $Data, $dati = True, $addons = '\r\n', $fPermissions = 0777)
{
    if(file_exists($fName) && !is_writeable($fName)) 
    { 
        chmod($fName, $fPermissions);
    }
    $myfile = fopen($fName, "a") or WriteToFile("logs/general_errors.log", "[ERROR]: Couldn't open/find the '". $fName ."' file!");
    if(isset($Data) && strlen($Data) > 0)
    {
        if($dati)
        {
           $date_time = function_exists("GetDateTime") ? GetDateTime(2) : (date("Y/m/d"). " - " .date("h:i:sA"));
           $txt_data = "[" .$date_time. "]: " .$Data. $addons;
        }
        else
        {
           $txt_data = $Data. $addons;
        }       
        fwrite($myfile, $txt_data);
    }
    else
    { 
        WriteToFile("logs/general_errors.log", "[ERROR]: Failed to Write to the file '" .$fName. "' (Reason: Data Length Must be greater than 1+)!");
        // trigger_error("Failed to Write to the file '" .$fName. "' (Reason: Data Length Must be greater than 1+)!", E_USER_ERROR);
    }
    fclose($myfile);   	   
} 
function GetDateTime($type)
{
    switch($type)
    {
        case 1:
        $dati = date('m/d/Y'). " - " .date("h:i:s A");
        break;	
        case 2:
        $dati = date("Y/m/d"). " - " .date("h:i:s A");
        break;
        case 3:
        $dati = date('j F Y'). " - " .date("h:i:s A");
        break;
    }
    return $dati;
}
?>