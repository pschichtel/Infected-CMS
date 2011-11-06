<?php
    if (!isset($_GET['file']) || !file_exists('./' . $_GET['file']))
    {
        die();
    }
    $newWidth = (isset($_GET['width']) ? $_GET['width'] : null);
    $newHeight = (isset($_GET['height']) ? $_GET['height'] : null);
    $file = &$_GET['file'];
    
    list($width, $height, $type) = getimagesize($file);

    if ($newHeight && $newWidth)
    {
    }
    elseif ($newHeight)
    {
        $rate = $newHeight / $height;
        $newWidth = $width * $rate;
    }
    elseif ($newWidth)
    {
        $rate = $newWidth / $width;
        $newHeight = $height * $rate;
    }
    else
    {
        die();
    }

    if ($type == 1)
    {
        $image = imagecreatefromgif($file);
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        
        header('Content-type: image/gif');
        imagegif($newImage);
    }
    elseif ($type == 2)
    {
        $image = imagecreatefromjpeg($file);
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        header('Content-type: image/jpeg');
        imagejpeg($newImage);
    }
    elseif ($type == 3)
    {
        $image = imagecreatefrompng($file);
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        header('Content-type: image/png');
        imagepng($newImage);
    }
    elseif ($type == 4)
    {
        imagecreatefromwbmp($file);
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        header('Content-type: image/bmp');
        imagewbmp($newImage);
    }
    else
    {
        die();
    }
?>
