<?php

$html_header = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"
\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<title>Cloud for all profile manager</title>
</head>
<body>
<h1>Profile manager</h1>\n";

// read the json profile
$json_file="./profile_json.txt";
// load the contents of the json file into the string variable $file_contents
// if it fails, exit with an error message
$file_contents = file_get_contents($json_file) or exit($html_header."<p>Error: Can't open the profile file.</p>\n</body>\n</html>"); 

// transform the string into an array
$profile=json_decode($file_contents,true);
if ($profile == '') {
	exit($html_header."<p>Error: Can't transform the file contents to a json array, maybe bad json syntax</p>\n</body>\n</html>");
}

// python client request
// send profile file (json) as string
if ($_POST['get_json_file'] == '1') {
	print $file_contents;
	exit(0);
}

$html_contents = "<p>Please change the settings of your profile.</p>
<form action='index.php' method='post'>
<p>\n";

$index = 0;
$has_changed = 0;
foreach($profile as $p) {
	// search for relevant parameters in the json file
	// first parameter: speech language
	if (strpos($p[name],'preferred-lang') !== false) {
		if (strlen($_POST['speech_language']) > 0) {
			$profile[$index][value] = htmlspecialchars($_POST['speech_language']);
			$p[value] = htmlspecialchars($_POST['speech_language']);
			$has_changed = 1;
		}
		$html_contents = $html_contents."<label for='lang'>Speech language: </label>\n";
		$html_contents = $html_contents."<input type='text' name='speech_language' id='lang' value='".$p[value]."'><br />\n";
	}

	// second parameter: speech rate
	if (strpos($p[name],'speech-rate') !== false) {
		if (strlen($_POST['speech_rate']) > 0) {
			$profile[$index][value] = (int)$_POST['speech_rate'];
			$p[value] = (int)$_POST['speech_rate'];
			$has_changed = 1;
		}
		$html_contents = $html_contents."<label for='srate'>Speech rate: </label>\n";
		$html_contents = $html_contents."<input type='text' name='speech_rate' id='srate' value='".$p[value]."'><br />\n";
	}
	$index++;
}

$html_contents = $html_contents."<input type='submit' value='send'>
</p>
</form>\n";

// save json file if changed
if ($has_changed == 1) {
	$fh = fopen($json_file, 'w') or exit($html_header."<p>Error: No write permission for the user profile file.</p>\n</body>\n</html>");
	$file_contents = json_encode($profile);
	// if php >= 5.4 is installed
	// makes the json file human readable
	// $file_contents = json_encode($profile, JSON_PRETTY_PRINT);
	// check if the transformation was successful
	if ($file_contents == 'null') {
		exit($html_header."<p>Error: The json array could not be transformed to a string</p>\n</body>\n</html>");
	}
	// write into the file and then close it
	fwrite($fh, $file_contents);
	fclose($fh);
	$html_contents = "<p>Profile data saved successfully".$html_contents;
}

$html_contents = $html_contents."</body>\n</html>";

print $html_header.$html_contents;
?>
