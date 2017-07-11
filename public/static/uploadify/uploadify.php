<?php
/*
Uploadify
Copyright (c) 2012 Reactive Apps, Ronnie Garcia
Released under the MIT License <http://www.opensource.org/licenses/mit-license.php> 
*/

// Define a destination

$targetFolder = '/uploads/img'; // Relative to the root

$verifyToken = md5('unique_salt' . $_POST['timestamp']);
$str=date('YmdHis') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
	$tempFile = $_FILES['Filedata']['tmp_name'];
	$targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
	
	
	// Validate the file type
	$fileTypes = array('jpg','jpeg','gif','png','pem'); // File extensions
	$fileParts = pathinfo($_FILES['Filedata']['name']);
	$sname=$targetFolder.'/'.$str.'.'.$fileParts['extension'];
	$targetFile = rtrim($targetPath,'/') . '/'.$str.'.'.$fileParts['extension'];
	if (in_array($fileParts['extension'],$fileTypes)) {
		move_uploaded_file($tempFile,$targetFile);
	
		echo $sname;
	} else {
		echo 'Invalid file type.';
	}
}
?>