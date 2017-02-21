<?php
/**
Error Codes
1 Success
2 MD5 not match
3 Error uploading of file
4 Error moving uploaded file
*/

//echo "UR CONNECTED";exit();
//	header("Content-Type: application/json");

	if($_FILES['file']['error']!=0)
	{
	    echo json_encode( array('response' => 3) );
	    exit;
	}
	
    define("FILE_DESTINATION_FOLDER",       "/var/www/html/wms/modules/itemassembly/PMS_IMAGE/");
	if(is_dir(FILE_DESTINATION_FOLDER))
	{
	   chmod(FILE_DESTINATION_FOLDER,0777);
	}
	else
	{
	   mkdir(FILE_DESTINATION_FOLDER);
	   chmod(FILE_DESTINATION_FOLDER,0777);
	}
//	exit();
	
	$ms5DetailSent = $_POST['md5file'];
	$ms5LocalFile = md5_file($_FILES['file']['tmp_name']);
	
	if( move_uploaded_file($_FILES['file']['tmp_name'],FILE_DESTINATION_FOLDER."/".$_POST['basename']) == false )
	{
	    echo json_encode( array('response' => 4) );exit;
	}
	//copy(FILE_DESTINATION_FOLDER."/".$_POST['basename'],FILE_DESTINATION_FOLDER_EXECUTOR."/".$_POST['basename']);


?>
