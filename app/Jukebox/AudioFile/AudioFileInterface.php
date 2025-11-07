<?php 

namespace App\Jukebox\AudioFile;

interface AudioFileInterface {

    /**
     * Require the title method is implemented.
     *
     * @return string
     */
    public function title();

    /**
     * Require the year method is implemented.
     *
     * @return integer
     */
    public function year();

    /**
     * Require the artist method is implemented.
     *
     * @return string
     */
    public function artist();

    /**
     * Require the album method is implemented.
     *
     * @return string
     */
    public function album();

    /**
     * Require the fileType method is implemented.
     *
     * @return string
     */
    public function fileType();

    /**
     * Require the trackNo method is implemented.
     *
     * @return string
     */
    public function trackNo();

    /**
     * Require the genre method is implemented.
     *
     * @return integer
     */
    public function genre();

    /**
     * Require the file_size method is implemented.
     *
     * @return integer
     */
    public function fileSize();

    /**
     * Require the composer method is implemented.
     *
     * @return string
     */
    public function composer();
        
    /**
     * Require the playtime method is implemented.
     *
     * @return string
     */
    public function playtime();

    /**
     * Require the location method is implemented.
     *
     * @return string
     */
    public function location();

     /**
     * Require the notes method is implemented.
     *
     * @return string
     */
    public function notes();

     /**
     * Require the iscompilation method is implemented.
     *
     * @return bool
     */
    public function isCompilation();

}
