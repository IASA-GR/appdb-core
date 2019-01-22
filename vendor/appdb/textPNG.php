<?php
/**
 * Copyright (C) 2015 IASA - Institute of Accelerating Systems and Applications (http://www.iasa.gr)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and 
 * limitations under the License.
 */
 
require_once('appdb_configuration.php');

class textPNG {
    public $font;
    public $msg = ""; // default text to display.
    public $size = 24; // default font size.
    public $rot = 0; // rotation in degrees.
    public $pad = 0; // padding.
    public $transparent = 1; // transparency set to on.
    public $red = 0; // black text...
    public $grn = 0;
    public $blu = 0;
    public $bg_red = 255; // on white background.
    public $bg_grn = 255;
	public $bg_blu = 255;

	function __construct() {
		$this->font = ApplicationConfiguration::app('pngfont', 'wine-tahoma.ttf');
	}
    
    function draw() 
    {
        putenv('GDFONTPATH='.$_SERVER['APPLICATION_PATH'].'/../library/fonts');
        $width = 0;
        $height = 0;
        $offset_x = 0;
        $offset_y = 0;
        $bounds = array();
        $image = "";
    
        $bounds = ImageTTFBBox($this->size, $this->rot, $this->font, "W");
        if ($this->rot < 0) {
            $font_height = abs($bounds[7]-$bounds[1]);      
        } else if ($this->rot > 0) {
	        $font_height = abs($bounds[1]-$bounds[7]);
        } else {
            $font_height = abs($bounds[7]-$bounds[1]);
        }

        $bounds = ImageTTFBBox($this->size, $this->rot, $this->font, $this->msg);
        if ($this->rot < 0) {
            $width = abs($bounds[4]-$bounds[0]);
            $height = abs($bounds[3]-$bounds[7]);
            $offset_y = $font_height;
            $offset_x = 0;
        } else if ($this->rot > 0) {
            $width = abs($bounds[2]-$bounds[6]);
            $height = abs($bounds[1]-$bounds[5]);
            $offset_y = abs($bounds[7]-$bounds[5])+$font_height;
            $offset_x = abs($bounds[0]-$bounds[6]);
        } else {
            $width = abs($bounds[4]-$bounds[6]);
            $height = abs($bounds[7]-$bounds[1]);
            $offset_y = $font_height;;
            $offset_x = 0;
        }
        
        $image = imagecreate($width+($this->pad*2)+1,$height+($this->pad*2)+1);
        $background = ImageColorAllocate($image, $this->bg_red, $this->bg_grn, $this->bg_blu);
        $foreground = ImageColorAllocate($image, $this->red, $this->grn, $this->blu);
    
        if ($this->transparent) ImageColorTransparent($image, $background);
        ImageInterlace($image, false);
    
        ImageTTFText($image, $this->size, $this->rot, $offset_x+$this->pad, $offset_y+$this->pad, $foreground, $this->font, $this->msg);
    
        imagePNG($image);
	}
}
?>
