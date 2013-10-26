<?php

$username = "admin";
$password = "adminadmin";

if(!(isset($_SERVER['PHP_AUTH_USER']) && 
			isset($_SERVER['PHP_AUTH_PW']) && 
			$_SERVER['PHP_AUTH_USER'] == $username &&
			$_SERVER['PHP_AUTH_PW'] == $password)){
	header('WWW-Authenticate: Basic realm="Please login to continue"');
	header('Status: 401 Unauthorized');
}else{
	?>
	<!DOCTYPE html>
	<html>
	<center><br><br><br>
	<form action="<?=$_SERVER['PHP_SELF'];?>" method="post">
	<input type="submit" name="submit" value="Run restore">
	</form>

	<?php
	// Used to copy modified files back to their rightful place after updates.
	// 8-2-12 Jason Sayre - jason@jasonsayre.com - Ver. 0.4

	// Create the modified-files folder if it doesn't exist
	if (!is_dir('modified-files')) {
		mkdir('modified-files');
	}

	// Run through the modified-files folder and place all files in an array
	function findfiles($dirname = '.', $relative = false) {
		if($dirname == ''){$dirname = '.';}
		if (!is_dir($dirname) || !is_readable($dirname)) {
			// check whether the directory is valid.
			return false;
		}
		$a = array();
		$handle = opendir($dirname);
		while (false !== ($file = readdir($handle))) {
			if ($file != '.' && $file != '..' && is_readable($dirname . DIRECTORY_SEPARATOR . $file)) {
				if (is_dir($dirname . DIRECTORY_SEPARATOR . $file)) {
					$a[($relative ? $file : $dirname . DIRECTORY_SEPARATOR . $file)] = findfiles($dirname .     DIRECTORY_SEPARATOR . $file, $relative);
				}
				else{
					$a[] = ($relative ? $file : $dirname . DIRECTORY_SEPARATOR . $file);
				}
			}
		}
		closedir($handle);
		return $a;
	}

	// flatten the multidemensional array from findfiles
	function flatten(array $array) {
		$return = array();
		array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
		return $return;
	}



	$files = findfiles('modified-files');
	$ffiles = flatten($files);
	print("<br><br><b>Updating the following files:</b><br>\n");


	$nold = $ffiles;
	$old = str_replace('modified-files/', '', $nold);
	foreach ($old as $k) 
	{ 
		$k = "../" . $k;
		print($k);
		if (file_exists($k)) {
			echo " - <font color=\"#006600\">file exists</font>";
		} else {
			echo " - <font color=\"#800000\">Warning: does not exist in original structure</font>";
		}
		print("<br>\n");
	}


	// Move the old file to a *.bak, then copy over our file
	function move($y,$x)
	{
		if (file_exists($x)) {
			if (copy($x,$x . ".bak")) {
				unlink("$x");
			}
		}

		copy($y, $x) or die("<br><br><font color=\"red\">For some reason I couldn't copy $y to $x. You may want to check permissions, or see if the source file exists.</font>");
	}


	// Once the main button is hit, run the following code
	if(isset($_POST['submit'])) {


		$raw = findfiles('modified-files');
		$modified = flatten($raw);


		foreach ($modified as $l)
		{
			$tttoreplace = $l;
			$ttoreplace = str_replace('modified-files/', '', $tttoreplace);
			$toreplace = "../" . $ttoreplace;
			//bad = toreplace
			$modified = $l;

			print("<br>Successfully copied <i>" . $modified . "</i> to <i>" . $toreplace . "</i>");

			move($modified,$toreplace);

		}
		print('<br><h2>Restore complete!</h2>');
	}
	?>
	</center>
	</html>
<?php 
}
?>
