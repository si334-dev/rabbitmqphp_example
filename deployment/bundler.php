#!/usr/bin/php
<?php
require_once('./rabbitStuff/path.inc');
require_once('./rabbitStuff/get_host_info.inc');
require_once('./rabbitStuff/rabbitMQLib.inc');

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
$bundleFile = $argv[1]; //get json file name based on what the user enters
$jsonContents = file_get_contents($bundleFile);
$bundleInfo = json_decode($jsonContents, true);
$name = $bundleInfo['bundleName'];
$files = $bundleInfo['filesToCopy'];
$tmpName = $name.uniqid();
$tmpD = "/tmp/$tmpName";
mkdir($tmpD);
foreach($files as $file){
	$source = $file['source'];
	if(copy($source, "$tmpD/" . basename($source))){
		//echo "success";
	}else{
		echo "failed copying file to tmp";
		exit();
	}
}

copy($bundleFile, "$tmpD/" . basename($bundleFile));
$tmpZip = $tmpD . ".zip";

//adapted from https://www.geeksforgeeks.org/php/how-to-zip-a-directory-in-php/
$zip = new ZipArchive;
 
if($zip -> open($tmpZip, ZipArchive::CREATE ) === TRUE) {
    
    $dir = opendir($tmpD);
     
    while($file = readdir($dir)) {
        if(is_file("$tmpD/$file")) {
            $zip -> addFile("$tmpD/$file", $file);
        }
    }
    $zip ->close();
}

$rabbit_ip = parse_ini_file("./rabbitStuff/testRabbitMQ.ini", true)["testServer"]["BROKER_HOST"];

$scp = ssh2_connect("$rabbit_ip", 22);
if(!$scp){ die("failed to copy to remote");}
if(!ssh2_auth_password($scp, 'vboxuser', 'admin')){die("password failed lol");}
$remote = "bundles/" . basename($tmpZip);
if(!ssh2_scp_send($scp, "$tmpZip", "$remote")){die('transfer failed lol');}


$request = array();
$request['type'] = "deploySend";
$request['tmpName'] = basename($tmpZip);

$response = $client->send_request($request);
//$response = $client->publish($request);

echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;

