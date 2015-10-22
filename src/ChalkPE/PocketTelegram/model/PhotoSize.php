<?php

/**
 * @author ChalkPE <chalkpe@gmail.com>
 * @since 2015-10-22 22:21
 */

namespace ChalkPE\PocketTelegram\model;


class PhotoSize extends Model implements Identifiable {
    /** @var string */
    private $fileId;

    /** @var int */
    private $width, $height;

    /** @var int|null */
    private $fileSize = null;

    /**
     * @param string $fileId
     * @param int $width
     * @param int $height
     * @param int|null $fileSize
     */
    public function __construct($fileId, $width, $height, $fileSize = null){
        parent::__construct();

        $this->fileId = $fileId;
        $this->width = $width;
        $this->height = $height;
        $this->fileSize = $fileSize;
    }

    /**
     * @param array $array
     * @return PhotoSize
     */
    public static function create(array $array){
        return new PhotoSize(intval($array['file_id']), intval($array['width']), intval($array['height']),
            isset($array['file_size']) ? intval($array['file_size']) : null);
    }

    /**
     * @return string
     */
    public function getId(){
        return $this->getFileId();
    }

    /**
     * @return string
     */
    public function getFileId(){
        return $this->fileId;
    }

    /**
     * @return int
     */
    public function getWidth(){
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight(){
        return $this->height;
    }

    /**
     * @return int|null
     */
    public function getFileSize(){
        return $this->fileSize;
    }
}