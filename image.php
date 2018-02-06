<?php    
    /*
     *  Pixel Browser (image.php) - Clean PHP Image resizing
     *
     *  Written by Joseph Stevens
     *
     *  Cannot handle resizing animations
     *  So GIF support is now stripped
     *
     */

    $Zoom = 1;
    //zoom
    if ($_GET['Zoom']){
	    $Zoom = $_GET['Zoom'];
    }

    //filename
    if ($_GET['File']){
	    $ImgName = stripslashes($_GET['File']);
    }

    //filename exists: resize it
    if ($ImgName){
        /*
        //find image type: PNG or GIF
        $posGIF = stripos($ImgName, ".gif");
        $posPNG = stripos($ImgName, ".png");
        */
        //set the header
        header ('Content-type: image/png');
        /*
        if($posPNG){
            header ('Content-type: image/png');        
        }else if($posGIF){
            header ('Content-type: image/gif');
        }
        */
        //calculate height/width
	    list($ImgWidth, $ImgHeight) = getimagesize("$ImgName");
        $ImgHeightZoomed = $ImgHeight * $Zoom;
        $ImgWidthZoomed  = $ImgWidth * $Zoom;
        //debug
        //create image
        /*
        if($posGIF){
            //image is a GIF
            $OldImg = @imagecreatefromgif($ImgName);
        }else if($posPNG){
            //image is a PNG
            $OldImg = @imagecreatefrompng($ImgName);
        }
        */
        $OldImg = @imagecreatefrompng($ImgName);
        $NewImg = @imagecreatetruecolor($ImgWidthZoomed, $ImgHeightZoomed);
        //set trans color
        $color = imagecolortransparent($OldImg);
        imagefilledrectangle($NewImg, 0, 0, $ImgWidthZoomed, $ImgHeightZoomed, $color);
        imagecolortransparent($NewImg, $color);
        //set variables
        $dst_X = 0;
        $dst_Y = 0;
        $src_X = 0;
        $src_Y = 0;
        $dst_W = $ImgWidthZoomed;
        $dst_H = $ImgHeightZoomed;
        $src_W = $ImgWidth;
        $src_H = $ImgHeight;
        //resize the image
        imagecopyresized($NewImg, $OldImg, $dst_X, $dst_Y, $src_X, $src_Y, $dst_W, $dst_H, $src_W, $src_H);
        imagepng($NewImg);
        imagedestroy($NewImg);
    }
?>
