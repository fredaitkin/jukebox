<?php 

namespace App\Jukebox\AudioFile;

class MP3 implements AudioFileInterface {

    const FILE_TYPE = 'mp3';

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
     */
    function __construct($location, $filename, $is_compilation, array $file_info)
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
        $title = $this->file_info["tags"]["id3v2"]["title"][0] ?? '';
        return replaceSpecialFileSystemChars($title);
    }


    /**
     * Return song artist.
     *
     * @return string
     */
    public function artist() {
        $artist = $this->file_info["tags"]["id3v2"]["artist"][0] ?? '';
        return replaceSpecialFileSystemChars($artist);
    }

    /**
     * Return year.
     *
     * @return integer
     */
    public function year() {
        return $this->file_info["tags"]["id3v2"]["year"][0] ?? 9999;
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
        $album = $this->file_info["tags"]["id3v2"]["album"][0] ?? 'Unknown Album';
        if(! empty($album)):
            $album = replaceSpecialFileSystemChars($album);
        else:
            $album = 'Unknown Album';
        endif;
        return $album;
    }

    /**
     * Require the track_no method is implemented.
     *
     * @return string
     */
    public function trackNo() {
        return $this->file_info["tags"]["id3v2"]["track_number"][0] ?? '';
    }

    /**
     * Return genre.
     *
     * @return string
     */
    public function genre() {
        return $this->file_info["tags"]["id3v2"]["genre"][0] ?? '';
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
        return '';
    }
        
    /**
     * Return the playtime.
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
        $notes = '';
        if($this->is_compilation):
           $notes = $this->file_info["tags"]["id3v2"]["artist"][0];
        endif;
        return $notes;
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
