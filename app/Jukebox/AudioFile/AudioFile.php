<?php 

namespace App\Jukebox\AudioFile;

class AudioFile implements AudioFileInterface {

    /**
     * @var string
     */
    private $file_type;

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
        $this->file_type = $file_type;
    }

    /**
     * Return song title.
     *
     * @return string
     */
    public function title() {
        $title = '';
        $idx = strrpos($this->filename, '.');
        if ( $idx !== false ):
            $title = substr($this->filename, 0, $idx );
        endif;
        return $title;
    }

    /**
     * Return year.
     *
     * @return integer
     */
    public function year() {
        return 9999;
    }

    /**
     * Return file_type.
     *
     * @return string
     */
    public function fileType() {
        return $this->file_type;
    }

    /**
     * Return artist.
     *
     * @return string
     */
    public function artist() {
        return '';
    }

    /**
     * Return album.
     *
     * @return string
     */
    public function album() {
        return '';
    }

    /**
     * Return track_no.
     *
     * @return string
     */
    public function trackNo() {
        return '';
    }

    /**
     * Return genre.
     *
     * @return string
     */
    public function genre() {
        return '';
    }

    /**
     * Return file_size.
     *
     * @return integer
     */
    public function fileSize() {
        return $this->file_info['filesize'] ?? 0;
    }

    /**
     * Require the composer method is implemented.
     *
     * @return string
     */
    public function composer() {
        return '';
    }
        
    /**
     * Return playtime.
     *
     * @return string
     */
    public function playtime() {
        return $this->file_info['playtime_string'] ?? '';  
    }

    /**
     * Return file location.
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
