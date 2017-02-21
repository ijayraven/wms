<?php
	// Including all required classes
	require('class/BCGFont.php');
	require('class/BCGColor.php');
	require('class/BCGDrawing.php'); 
	
	// Including the barcode technology
	include('class/BCGcode128.barcode.php'); 
	

	function Generate_Barcode_Image($psCode, $psBarcodeSaveDir, $psBarFileName)
	{
		// Loading Font
		$font = new BCGFont('common/fonts/arial.ttf', 18);
		
		// The arguments are R, G, B for color.
		$color_black = new BCGColor(0, 0, 0);
		$color_white = new BCGColor(255, 255, 255); 
		$color_yellow = new BCGColor(255,255,0);
		$color_silver = new BCGColor(192,192,192);
		
		//$code = new BCGcode39();
		$code = new BCGcode128();
		$code->setScale(2); // Resolution
		$code->setThickness(30); // Thickness
		$code->setForegroundColor($color_black); // Color of bars
		$code->setBackgroundColor($color_white); // Color of spaces
		//$code->setFont($font); // Font (or 0)
		$code->setFont(0); // Font (or 0)
		$code->parse($psCode); // Text
		
		
		/* Here is the list of the arguments
		1 - Filename (empty : display on screen)
		2 - Background color */
		$drawing = new BCGDrawing($psBarcodeSaveDir .  $psBarFileName, $color_white);
		$drawing->setBarcode($code);
		$drawing->draw();
		
		// Header that says it is an image (remove it if you save the barcode to a file)
		//header('Content-Type: image/png');
		
		// Draw (or save) the image into PNG format.
		$drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
	}
?>