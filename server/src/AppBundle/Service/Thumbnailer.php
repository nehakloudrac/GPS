<?php

namespace GPS\AppBundle\Service;

use Imagine\Imagick\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;

class Thumbnailer
{
    protected $config;

    public function __construct($config = [])
    {
        $this->config = array_merge($config, [
            'default' => [
                'height' => 400,
                'width' => 400,
                'extension' => 'jpg'
            ]
        ]);
    }

    public function createThumbnails($infile)
    {
        $imagine = new Imagine();
        $file = $imagine->open($infile);

        $baseFilename = md5(time().uniqid());
        $created = [];

        foreach ($this->config as $preset => $options) {
            $suffix = '_'.$preset.'.'.$options['extension'];
            $newFileName = $baseFilename.$suffix;
            $outfile = dirname($infile).DIRECTORY_SEPARATOR.$newFileName;

            $size = new Box($options['width'], $options['height']);
            $file->thumbnail($size, ImageInterface::THUMBNAIL_OUTBOUND)->save($outfile);

            $created[] = [
                'basename' => $baseFilename,
                'preset' => $preset,
                'suffix' => $suffix,
                'filename' => $newFileName,
                'path' => $outfile
            ];
        }

        return $created;
    }
    
    public function resize($infile, $maxWidth, $maxHeight, $extension = 'png')
    {
        $outfile = dirname($infile).DIRECTORY_SEPARATOR.uniqid().'.'.$extension;
        
        $imagine =  new Imagine();
        $origImage = $imagine->open($infile);
        $origWidth = $origImage->getSize()->getWidth();
        $origHeight = $origImage->getSize()->getHeight();
        
        $ratio = min(array($maxWidth / $origWidth, $maxHeight / $origHeight));
        $newWidth = $ratio * $origWidth;
        $newHeight = $ratio * $origHeight;
        
        $origImage->resize(new Box($newWidth, $newHeight))->save($outfile);
        
        return $outfile;
    }
}
