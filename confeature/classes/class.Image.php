<?php
/**
 * Images manipulation class
 */

class Image {
	/**
	 * True if an image is loaded, false otherwise
	 * @var bool
	 */
	private $loaded = false;
	
	/**
	 * Image resource
	 * @var resource
	 */
	private $img;
	
	/**
	 * Width of the image
	 * @var int
	 */
	private $width;
	
	/**
	 * Height of the image
	 * @var int
	 */
	private $height;
	
	/**
	 * Type of the image, relative to IMAGETYPE_* constants
	 * @var int
	 */
	private $type;
	
	/**
	 * Quality of the image in percentage (JPEG only)
	 * @var int
	 */
	private $quality = 95;
	
	
	/**
	 * Loads an image file
	 * 
	 * @param string $filepath	Path of the image file
	 */
	public function load($filepath){
		$this->loaded = false;
		if(File::exists($filepath)){
			list($this->width, $this->height, $type) = getimagesize($filepath);
			unset($this->img);
			if($type==IMAGETYPE_JPEG){
				$this->img = @imagecreatefromjpeg($filepath);
			}else if($type==IMAGETYPE_GIF){
				$this->img = @imagecreatefromgif($filepath);
			}else if($type==IMAGETYPE_PNG){
				$this->img = @imagecreatefrompng($filepath);
			}
			if(!isset($this->img))
				throw new Exception('Error opening image file "'.$filepath.'"');
			if(!in_array($type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)))
				throw new Exception('Unsupported image file type : "'.$filepath.'"');
			
			$this->type = $type;
			
			// Preservation of the transparence / alpha for PNG and GIF
			if($type==IMAGETYPE_GIF || $type==IMAGETYPE_PNG){
				imagealphablending($this->img, false);
				imagesavealpha($this->img, true);
			}
			
			$this->loaded = true;
		}else{
			throw new Exception('The image file "'.$filepath.'" doesn\'t exist');
		}
	}
	
	
	/**
	 * Writes the image file from the image resource
	 *
	 * @param string $filepath	Path of the image file
	 */
	public function save($filepath){
		if(!$this->loaded)
			throw new Exception('No image loaded');
		if($this->type==IMAGETYPE_JPEG){
			imagejpeg($this->img, $filepath, $this->quality);
		}else if($this->type==IMAGETYPE_GIF){
			imagegif($this->img, $filepath);
		}else if($this->type==IMAGETYPE_PNG){
			imagepng($this->img, $filepath);
		}
	}
	
	/**
	 * Set the quality of the image (JPEG only)
	 * 
	 * @param int $quality
	 */
	public function setQuality($quality){
		$this->quality = $quality;
	}
	
	/**
	 * Creates an new image resource
	 *
	 * @param int $width	Width of the image
	 * @param int $height	Height of the image
	 * @param bool $alpha	Activates alpha channel if true (optionnal)
	 * @return resource	New image resource
	 */
	private function create($width, $height, $alpha=null){
		if(!isset($alpha))
			$alpha = $this->type==1 || $this->type==3;
		
		$img = imagecreatetruecolor($width, $height);
		
		// Preservation of the transparence / alpha for PNG and GIF
		if($alpha){
			imagealphablending($img, false);
			imagesavealpha($img, true);
		}
		
		return $img;
	}
	
	/**
	 * Crops the image
	 * 
	 * @param int $src_x	X-coordinate of source point
	 * @param int $src_x	Y-coordinate of source point
	 * @param int $src_w	Width of source
	 * @param int $src_h	Height of source
	 * @param int $dst_w	Width of destination
	 * @param int $dst_h	Height of destination
	 */
	public function crop($src_x, $src_y, $src_w, $src_h, $dst_w, $dst_h){
		if(!$this->loaded)
			throw new Exception('No image loaded');
		
		$img = $this->create($dst_w, $dst_h);
		
		imagecopyresampled($img , $this->img, 0, 0, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
		imagedestroy($this->img);
		$this->img = $img;
		$this->width = $dst_w;
		$this->height = $dst_h;
	}
	
	/**
	 * Resizes the image
	 * 
	 * @param int $width	New width
	 * @param int $height	New height
	 */
	public function resize($width, $height){
		if(!$this->loaded)
			throw new Exception('No image loaded');
		$this->crop(0, 0, $this->width, $this->height, $width, $height);
	}
	
	/**
	 * Resizes the width the image
	 * 
	 * @param int $width	New width
	 * @param bool $prop	Keeps the proportions if true
	 */
	public function setWidth($width, $prop=false){
		if(!$this->loaded)
			throw new Exception('No image loaded');
		if($prop){
			$height = round($this->height*$width/$this->width);
		}else{
			$height = $this->height;
		}
		$this->resize($width, $height);
	}
	
	/**
	 * Resizes the height the image
	 * 
	 * @param int $height	New height
	 * @param bool $prop	Keeps the proportions if true
	 */
	public function setHeight($height, $prop=false){
		if(!$this->loaded)
			throw new Exception('No image loaded');
		if($prop){
			$width = round($this->width*$height/$this->height);
		}else{
			$width = $this->width;
		}
		$this->resize($width, $height);
	}
	
	/**
	 * Miniaturizes the image to the wanted size, with cropping
	 * 
	 * @param int $width	Width of the thumb
	 * @param int $height	Height of the thumb
	 */
	public function thumb($width=null, $height=null){
		if(!$this->loaded)
			throw new Exception('No image loaded');
		
		if(!isset($width) && !isset($height))
			return false;
		if(!isset($width))
			$width = round($this->width*$height/$this->height);
		if(!isset($height))
			$height = round($this->height*$width/$this->width);
		
		$ratio_ex = $this->width / $this->height;
		$ratio = $width / $height;
		
		if($ratio_ex < $ratio){
			$height_ratio = $this->width / $ratio;
			$height_half_diff = round(($this->height - $height_ratio) / 2);
			$this->crop(0, $height_half_diff, $this->width, $height_ratio, $width, $height);
		}else{
			$width_ratio = $this->height * $ratio;
			$width_half_diff = round(($this->width - $width_ratio) / 2);
			$this->crop($width_half_diff, 0, $width_ratio, $this->height, $width, $height);
		}
	}
	
	
	/**
	 * Returns the width of the image
	 *
	 * @return int
	 */
	public function getWidth(){
		if(!$this->loaded)
			throw new Exception('No image loaded');
		return $this->width;
	}
	
	/**
	 * Returns the height of the image
	 *
	 * @return int
	 */
	public function getHeight(){
		if(!$this->loaded)
			throw new Exception('No image loaded');
		return $this->height;
	}
	
	/**
	 * Returns the type of the image, relative to IMAGETYPE_* constants
	 *
	 * @return int
	 */
	public function getType(){
		if(!$this->loaded)
			throw new Exception('No image loaded');
		return $this->type;
	}
	
}
