<html>
<head>
<meta charset="utf-8">
<title>Zip</title>
</head>
<body>
<form action ="mesfichiers.php" method="POST" enctype="multipart/form-data">
Archive Name: <input type="text" name="name"><br/>
<!--<input type="file" name ="files[]" multiple="true">
--><input type="submit" name="submit" value="Add files to ZIP">
</form>
<?php
/*if(isset($_POST['submit']))
{
	$filesArray = $_FILES["files"];
	//print_r($filesArray);
	for($num=0; $num < count($filesArray["name"]); $num++)
	{
		echo $num;
		$fileName = $filesArray["name"][$num];
		$tempName = $filesArray["tmp_name"][$num];
		move_uploaded_file($tempName, "tmp/".$fileName);
		
	}
	$archiveName =$_POST['name'].".zip";
	$filesArrayNames= $_FILES["files"]["name"];
	//print_r($filesArrayNames);
	$zipsDir = scandir("zips/");
	//print_r($zipsDir);
	$error =false;
	foreach ($zipsDir as $zipDirFile)
	{
		if($zipDirFile == $archiveName)
		{
			$error = true;
			break;
			
		}
	}
	if($error == false)
	{
		$tmpDir = scandir("tmp/");
		$zip = new ZipArchive;
		$zip->open("zips/".$archiveName, ZipArchive::CREATE);
		for($num=0; $num < count($filesArray["name"]); $num++)
			{
				$fileName =$filesArray["name"][$num];
				foreach ($tmpDir as $tmpDirFile)
				{
					if($tmpDirFile == $fileName)
					{
						$zip->addFile("tmp/".$fileName);
						echo "Adding:".$fileName."<br/>";
					}
				}
			}
		$zip->close();
		for($num=0; $num < count($filesArray["name"]); $num++)
		{
			$fileName =$filesArray["name"][$num];
			foreach ($tmpDir as $tmpDirFile)
				{
					if($tmpDirFile == $fileName)
					{
						unlink("tmp/".$fileName);
					}
				}
		}
		
	}
	else 
	{
		echo "Name exist<br>";
	}
}*/
require_once(dirname(dirname(__FILE__)) . '/config.php');
//Devoirs rendu Premier cours de test
echo "Devoirs rendus Premier cours de test<br>";
$sql = "SELECT id,userid FROM `mdl_assign_submission` where assignment =4 and status = 'submitted'";
$sqldevoirrendu = "SELECT distinct f.id, f.contextid, f.component, f.itemid, f.filename, f.userid 
								FROM mdl_files f , mdl_assign a , mdl_assignsubmission_file  s
								where f.itemid= s.submission
								and s.assignment = 4
								and  f.component='assignsubmission_file'
								and a.course = 2
							    and f.filename not like '.%'";

$resultdevoirrendu = $DB->get_recordset_sql($sqldevoirrendu);
/*foreach ($resultdevoirrendu as $devoir)
{
	//echo "$devoir->filename<br>";
	//echo "https://cours.u-cergy.fr/pluginfile.php/$devoir->contextid/assignsubmission_file/submission_files/$devoir->itemid/$devoir->filename?forcedownload=1<br>";
	//echo "<a href = 'https://cours.u-cergy.fr/pluginfile.php/$resultlink->contextid/assignsubmission_file/submission_files/$resultlink->itemid/$resultlink->filename?forcedownload=1'><img src = 'images/txt.png' height='30' width='30'>&nbsp;&nbsp;$resultlink->filename</a><br>
	$n = $devoir->filename;
	$filesArray[] = $devoir->filename;	
	//var_dump($filesArray);
	//print_r($filesArray);
	for($num=0; $num < count($filesArray); $num++)
	{
		//echo $num;
		//echo "<br/>";
		$fileName = $filesArray[$num];
		$tempName = $filesArray[$num];
		move_uploaded_file($tempName, "tmp/".$fileName);
		
	}
	
	$archiveName ="archive.zip";
	echo "<br><br>";
	//$filesArrayNames= $filesArray[$fileName];
	//print_r($filesArrayNames);
	$zipsDir = scandir("zips/");
	//print_r($zipsDir);
	$error =false;
	
	foreach ($zipsDir as $zipDirFile)
	{
		if($zipDirFile == $archiveName)
		{
			$error = true;
			break;
			
		}
	}
	if($error == false)
	{
		$tmpDir = scandir("tmp/");
		$zip = new ZipArchive;
		$zip->open("zips/".$archiveName, ZipArchive::CREATE);
		for($num=0; $num < count($filesArray); $num++)
			{
				$fileName =$filesArray["1"][$num];
				foreach ($tmpDir as $tmpDirFile)
				{
					if($tmpDirFile == $fileName)
					{
						$zip->addFile("tmp/".$fileName);
						echo "Adding:".$fileName."<br/>";
						
					}
						
				}
			
			}
		$zip->close();
		for($num=0; $num < count($filesArray); $num++)
		{
			$fileName =$filesArray[$num];
			foreach ($tmpDir as $tmpDirFile)
				{
					if($tmpDirFile == $fileName)
					{
						unlink("tmp/".$fileName);
					}
				}
		}
		
	}
	else 
	{
		echo "Name exist<br>";
	}
}*/

/*$zip = new ZipArchive;
if ($zip->open('test.zip', ZipArchive::CREATE)) {
	$chemin = "Files/new 16.txt";
	if(isset($chemin))
	{
    $zip->addFile('Files/txt.pngt', 'newname.png');
    echo "Adding<br>";
	}
	else 
	{
		echo "No";
	}
    $zip->close();
     //header('Content-Transfer-Encoding: binary'); //Transfert en binaire (fichier).
	  header('Content-Disposition: attachment; filename="test.zip"'); //Nom du fichier.
	 // header('Content-Length: '.filesize('test.zip')); //Taille du fichier.
    echo 'ok';
} else {
    echo 'échec';
}*/
$zip = new ZipArchive();
$filename = "./test112.zip";

if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
    exit("Impossible d'ouvrir le fichier <$filename>\n");
}

$zip->addFromString("Files/new 16.txt" . time(), "#1 Ceci est une chaîne texte, ajoutée comme new 16.txt.\n");
$zip->addFromString("Files/txt.png" . time(), "#2 Ceci est une chaîne texte, ajoutée comme test.png.\n");
$zip->addFile($thisdir . "/Files/new 16.txt");
echo "Nombre de fichiers : " . $zip->numFiles . "\n";
echo "Statut :" . $zip->status . "\n";
echo "Nom du fichier : " . $zip->filename . "\n";
$zip->close();
//header('Content-Disposition: attachment; filename="test112.zip"'); //Nom du fichier.

?>

</body>
</html>
