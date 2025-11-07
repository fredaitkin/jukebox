<?php 

namespace App\Jukebox\AudioFile;

use DateTime;

class MP4 implements AudioFileInterface {

    const FILE_TYPE = 'mp4';

    /**
     * @var string
     */
    private $location;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var bool
     */
    private $is_compilation;

    /**
     * @var array
     */
    private $file_info;

    /**
     * @param string $location
     * @param string $filename
     * @param bool $is_compilation
     * @param array $file_info
     * @param string $file_type
     */
    function __construct($location, $filename, $is_compilation, array $file_info, $file_type = null)
    {
        $this->location = $location;
        $this->filename = $filename;
        $this->is_compilation = $is_compilation;
        $this->file_info = $file_info;
    }

    /**
     * Return song title.
     *
     * @return string
     */
    public function title() {
        $title = $this->file_info["quicktime"]["comments"]["title"][0] ?? '';
        return replaceSpecialFileSystemChars($title);
    }

    /**
     * Return song artist.
     *
     * @return string
     */
    public function artist() {
        $artist = $this->file_info["quicktime"]["comments"]["artist"][0] ?? '';
        return replaceSpecialFileSystemChars($artist);
    }

    /**
     * Return year.
     *
     * Comes through in multiple formats 1984-01-23T08:00:00Z and 1988.
     *
     * @return integer
     */
    public function year() {
        $date_str = $this->file_info["quicktime"]["comments"]["creation_date"][0] ?? '';
        if(empty($date_str)):
            $year = 9999;
        else:
            if (strlen($date_str) == 4):
                $year = $date_str;
            else:
                $date_time = new DateTime($date_str);
                $year = $date_time->format('Y');
            endif;
        endif;
        return $year;
    }

    /**
     * Return file_type.
     *
     * @return string
     */
    public function fileType() {
        return self::FILE_TYPE;
    }

    /**
     * Return song album.
     *
     * @return string
     */
    public function album() {
        $album = $this->file_info["quicktime"]["comments"]["album"][0] ?? 'Unknown Album';
        if (! empty($album)):
            $album = replaceSpecialFileSystemChars($album);
        else:
            $album = 'Unknown Album';
        endif;
        return $album;
    }

    /**
     * Return track_no.
     *
     * @return string
     */
    public function trackNo() {
        return $this->file_info["quicktime"]["comments"]["track_number"][0] ?? '';
    }

    /**
     * Return genre.
     *
     * @return string
     */
    public function genre() {
        return $this->file_info["quicktime"]["comments"]["genre"][0] ?? '';
    }

    /**
     * Return file_size.
     *
     * @return integer
     */
    public function fileSize() {
        return $this->file_info["filesize"] ?? 0;
    }

    /**
     * Return composer.
     *
     * @return string
     */
    public function composer() {
        return $this->file_info["quicktime"]["comments"]["composer"][0] ?? '';  
    }
        
    /**
     * Require the playtime.
     *
     * @return string
     */
    public function playtime() {
        return $this->file_info["playtime_string"] ?? '';  
    }

    /**
     * Return the file location.
     *
     * @return string
     */
    public function location() {
        return $this->location;
    }

    /**
     * Return notes.
     *
     * @return string
     */
    public function notes() {
        return '';
    }

    /**
     * Return whether song is part of a compilation.
     *
     * @return bool
     */
    public function isCompilation() {
        return $this->is_compilation;
    }

}
