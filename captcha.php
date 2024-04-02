<?php 

	session_start();

	// Generate a random string
	$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	$captchaString = '';
	for ($i = 0; $i < 6; $i++) {
	    $captchaString .= $characters[rand(0, strlen($characters) - 1)];
	}

	// Store the string in the session
	$_SESSION['captcha'] = $captchaString;

	// Create an image and allocate colors
	$image = imagecreatetruecolor(120, 30);
	$background = imagecolorallocate($image, 255, 255, 255);
	$textColor = imagecolorallocate($image, 0, 0, 0);

	// Fill the background color
	imagefilledrectangle($image, 0, 0, 120, 30, $background);

	// Add the string to the image using a built-in font
	// The 5 here refers to one of the built-in GD fonts that is included with PHP
	imagestring($image, 5, 30, 10, $captchaString, $textColor);

	// Output the image
	header('Content-type: image/png');
	imagepng($image);
	imagedestroy($image);

?>