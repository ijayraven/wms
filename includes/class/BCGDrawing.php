<?php
	include_once('BCGBarcode.php');
	
	class BCGDrawing 
	{
		const IMG_FORMAT_PNG = 1;
		const IMG_FORMAT_JPEG = 2;
	
		private $w, $h;		// int
		private $color;		// BCGColor
		private $filename = "xxx.png";	// char *
		private $im;		// {object}
		private $barcode;	// BCGBarcode
	
		/**
		 * Constructor
		 *
		 * @param int $w
		 * @param int $h
		 * @param string filename
		 * @param BCGColor $color
		 */
		public function __construct($filename, BCGColor $color) 
		{
			$this->im = null;
			$this->filename = $filename;
			$this->color = $color;
		}
	
		/**
		 * Destructor
		 */
		public function __destruct() 
		{
			$this->destroy();
		}
	
		/**
		 * Init Image and color background
		 */
		private function init() 
		{
			if($this->im === null) 
			{
				$this->im = imagecreatetruecolor($this->w, $this->h)
				or die('Can\'t Initialize the GD Libraty');
				imagefilledrectangle($this->im, 0, 0, $this->w - 1, $this->h - 1, $this->color->allocate($this->im));
			}
		}
	
		/**
		 * @return resource
		 */
		public function get_im() 
		{
			return $this->im;
		}
	
		/**
		 * @param resource $im
		 */
		public function set_im(&$im) 
		{
			$this->im = $im;
		}
	
		/**
		 * Set Barcode for drawing
		 *
		 * @param BCGBarcode $barcode
		 */
		public function setBarcode(BCGBarcode $barcode) 
		{
			$this->barcode = $barcode;
		}
	
		/**
		 * Draw the barcode on the image $im
		 */
		public function draw() 
		{
			$size = $this->barcode->getMaxSize();
			$this->w = $size[0];
			$this->h = $size[1];
			$this->init();
			$this->barcode->draw($this->im);
		}
	
		/**
		 * Save $im into the file (many format available)
		 *
		 * @param int $image_style
		 * @param int $quality
		 */
		public function finish($image_style = self::IMG_FORMAT_PNG, $quality = 100) 
		{
			if ($image_style === self::IMG_FORMAT_PNG) 
			{
				if (empty($this->filename)) 
				{
					imagepng($this->im);
				} 
				else 
				{
					imagepng($this->im, $this->filename);
				}
			} 
			elseif ($image_style === self::IMG_FORMAT_JPEG) 
			{
				imagejpeg($this->im, $this->filename, $quality);
			}
		}
	
		/**
		 * Free the memory of PHP (called also by destructor)
		 */
		public function destroy() 
		{
			@imagedestroy($this->im);
		}
	};
?>