<?php

/**
 * Words.php
 *
 * @package Jukebox
 * @author  Melissa Aitkin
 */

namespace App\Console\Commands;

use App\Jukebox\Dictionary\DictionaryInterface as Dictionary;
use App\Jukebox\Dictionary\WordCloudInterface as WordCloud;
use App\Jukebox\Song\SongInterface as Song;
use Exception;
use Illuminate\Console\Command;
use Log;

/**
 * Process words via the command line
 */
class Words extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:words
                            {--cloud : Word cloud}
                            {--sids= : Comma separated list of song ids}
                            {--aids= : Comma separated list of artist ids}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs word utilities';

    /**
     * Save to database
     *
     * @var bool
     */
    protected $store = false;

    /**
     * The word cloud
     *
     * @var array
     */
    protected $word_cloud = [];

    /**
     * The song interface
     *
     * @var App\Jukebox\Song\SongInterface
     */
    private $song;

    /**
     * The dictionary interface
     *
     * @var App\Jukebox\Dictionary\DictionaryInterface
     */
    private $dictionary;

    /**
     * The wordcloud interface
     *
     * @var App\Jukebox\Dictionary\WordCloudInterface
     */
    private $wordCloud;

    /**
     * Constructor
     *
     * @param App\Jukebox\Song\SongInterface             $song       Song interface
     * @param App\Jukebox\Dictionary\DictionaryInterface $dictionary Dictionary interface
     * @param App\Jukebox\Dictionary\WordCloudInterface  $wordCloud  WordCloud interface
     */
    public function __construct(Song $song, Dictionary $dictionary, WordCloud $wordCloud)
    {
        parent::__construct();
        $this->song = $song;
        $this->dictionary = $dictionary;
        $this->wordCloud = $wordCloud;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $options = $this->options();

        // Store is no longer valid.
        $this->store = false;

        $song_ids = null;
        if(! empty($options['sids'])):
            $song_ids = explode(',', $options['sids']);
        endif;

        $artist_ids = null;
        if(! empty($options['aids'])):
            $artist_ids = explode(',', $options['aids']);
        endif;

        if(! empty($options['cloud'])):
            $this->getWordCloud($song_ids, $artist_ids);
        endif;
    }


    /**
     * Get word by formatted case and return type of word.
     * 
     * @param string $word The word.
     *
     * @return array
     */
    public function setCaseInfo($word)
    {
        $tmp_word = mb_strtolower($word);

        $country = $this->getCountry($tmp_word);
        if ($country):
            return $country;
        endif;

        $state = $this->getState($tmp_word);
        if ($state):
            return $state;
        endif;

        $town = $this->getTown($tmp_word);
        if ($town):
            return $town;
        endif;

        $place = $this->getPlace($tmp_word);
        if ($place):
            return $place;
        endif;

        $street = $this->getStreet($tmp_word);
        if ($street):
            return $street;
        endif;

        $month = $this->getMonth($tmp_word);
        if ($month):
            return $month;
        endif;

        $day = $this->getDay($tmp_word);
        if ($day):
            return $day;
        endif;

        $name = $this->getPersonName($tmp_word);
        if ($name):
            return $name;
        endif;

        $honorific = $this->getHonorific($tmp_word);
        if ($honorific):
            return $honorific;
        endif;

        $brand = $this->getBrand($tmp_word);
        if ($brand):
            return $brand;
        endif;

        $organisation = $this->getOrganisation($tmp_word);
        if ($organisation):
            return $organisation;
        endif;

        $acronym = $this->getAcronym($tmp_word);
        if ($acronym) {
            return $acronym;
        }

        $religion = $this->getReligion($tmp_word);
        if ($religion):
            return $religion;
        endif;

        $alphabet = $this->getAlphabet($tmp_word);
        if ($alphabet):
            return $alphabet;
        endif;

        $capitalized = $this->getCapitalized($tmp_word);
        if ($capitalized):
            return $capitalized;
        endif;

        $language = $this->getLanguage($tmp_word);
        if ($language):
            return $language;
        endif;

        if ($this->isMadeUp($tmp_word)) {
            return ['word' => $tmp_word, 'type' => 'made_up'];
        }

        return ['word' => $tmp_word, 'type' => ''];
    }

    /**
     * Get country.
     *
     * @param string $word The word
     *
     * @return mixed
     */
    private function getCountry($word)
    {
        if (isset(config('countries_expanded')[$word])):
            return ['word' => config('countries_expanded')[$word], 'type' => 'country'];
        else:
            return false;
        endif;
    }

    /**
     * Get state.
     *
     * @param string $word The word
     *
     * @return mixed
     */
    private function getState($word)
    {
        if (isset(config('states_expanded')[$word])):
            return ['word' => config('states_expanded')[$word], 'type' => 'state'];
        else:
            return false;
        endif;
    }

    /**
     * Get town.
     *
     * @param string $word The word
     *
     * @return mixed
     */
    private function getTown($word)
    {
        if (isset(config('towns')[$word])):
            return ['word' => config('towns')[$word], 'type' => 'town'];
        else:
            return false;
        endif;
    }

    /**
     * Get place
     *
     * @param string $word The word
     *
     * @return mixed
     */
    private function getPlace($word)
    {
        if (isset(config('places')[$word])):
            return ['word' => config('places')[$word], 'type' => 'place'];
        else:
            return false;
        endif;
    }

    /**
     * Get street
     *
     * @param string $word The word
     *
     * @return mixed
     */
    private function getStreet($word)
    {
        if (isset(config('streets')[$word])):
            return ['word' => config('streets')[$word], 'type' => 'street'];
        else:
            return false;
        endif;
    }

    /**
     * Get month
     *
     * @param string $word The word
     *
     * @return mixed
     */
    private function getMonth($word)
    {
        if (isset(config('months_expanded')[$word])):
            return ['word' => config('months_expanded')[$word], 'type' => 'month'];
        else:
            return false;
        endif;
    }

    /**
     * Get day
     *
     * @param string $word The word
     *
     * @return mixed
     */
    private function getDay($word)
    {
        if (isset(config('days_expanded')[$word])):
            return ['word' => config('days_expanded')[$word], 'type' => 'day'];
        else:
            return false;
        endif;
    }

    /**
     * Get formatted name if it is a name?
     *
     * @param string $word The word
     *
     * @return mixed
     */
    private function getPersonName($word)
    {
        //Ho Chi Minh, Chou En-Lai, Christina Applegate, Clarence Thomas, Santa Claus, Kurt Cobain, Leonard Cohen, John Coltrane, Perry Como, Billy Connolly, Sean Connery, Don Corleone etc
        if (isset(config('names')[$word])):
            return ['word' => config('names')[$word], 'type' => 'name'];
        else:
            return false;
        endif;
    }

    /**
     * Get honorific
     *
     * @param string $word The word
     *
     * @return mixed
     */
    private function getHonorific($word)
    {
        if (isset(config('honorifics')[$word])):
            return ['word' => config('honorifics')[$word], 'type' => 'honorific'];
        else:
            return false;
        endif;
    }

    /**
     * Get religion
     *
     * @param string $word The word
     *
     * @return mixed
     */
    private function getReligion($word)
    {
        if (isset(config('religions')[$word])):
            return ['word' => config('religions')[$word], 'type' => 'religion'];
        else:
            return false;
        endif;
    }

    /**
     * Get alphabet
     *
     * @param string $word The word
     *
     * @return mixed
     */
    private function getAlphabet($word)
    {
        if (isset(config('alphabet')[$word])):
            return ['word' => config('alphabet')[$word], 'type' => 'alphabet'];
        else:
            return false;
        endif;
    }

    /**
     * Get capitalized
     *
     * @param string $word The word
     *
     * @return mixed
     */
    private function getCapitalized($word)
    {
        if (isset(config('capitalized')[$word])):
            return ['word' => config('capitalized')[$word], 'type' => 'capitalized'];
        else:
            return false;
        endif;
    }

    /**
     * Is the word a country?
     *
     * @param string $word The word
     *
     * @return mixed
     */
    private function getAcronym($word)
    {
        if (isset(config('acronyms')[$word])):
            return ['word' => config('acronyms')[$word]['uppercase'], 'type' => config('acronyms')[$word]['type']];
        else:
            return false;
        endif;
    }

    /**
     * Get brand
     *
     * @param string $word The word
     *
     * @return mixed
     */
    private function getBrand($word)
    {
        if (isset(config('brands')[$word])):
            return ['word' => config('brands')[$word], 'type' => 'brand'];
        else:
            return false;
        endif;
    }

    /**
     * Get organisation
     *
     * @param string $word The word
     *
     * @return mixed
     */
    private function getOrganisation($word)
    {
        if (isset(config('organisations')[$word])):
            return ['word' => config('organisations')[$word], 'type' => 'organisation'];
        else:
            return false;
        endif;
    }

    /**
     * Get language
     *
     * @param string $word The word
     *
     * @return mixed
     */
    private function getLanguage($word)
    {
        if (isset(config('languages')[$word])):
            return ['word' => $word, 'type' => config('languages')[$word]];
        else:
            return false;
        endif;
    }

    /**
     * Is the word made up or nonsense?
     *
     * @param string $word The word
     *
     * @return bool
     */
    private function isMadeUp($word)
    {
        return in_array($word, config('made_up'));
    }

    /**
     * Get word cloud.
     *
     * @param array $song_ids   Limit word search by song ids.
     * @param array $artist_ids Limit word search by artist ids.
     *
     * @return void
     */
    private function getWordCloud($song_ids, $artist_ids)
    {
        Log::info('Retrieving Words');
        $lyrics = $this->song->retrieveEnglishLyrics($song_ids);

        Log::info('Processing Words');
        foreach ($lyrics as $song):
            try {
                $lyric = str_replace([PHP_EOL], [' '], $song['lyrics']);
                $words = explode(' ', $lyric);
                $words[] = 'masticating';

                foreach ($words as $word):
                    $this->processWord($word, $song['id']);
                endforeach;

            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

        endforeach;

        Log::info('Storing Words');
        if ($this->store):
            // Insert words and word info into the word_cloud table.
            $this->storeWordCloud();
        else:
            $this->logWordCloud();
        endif;

        Log::info("Finished");
    }

    /**
     * Process the word and populate the word cloud.
     * 
     * @param string $word The word.
     * @param int    $id   The song id.
     * 
     * @return void
     */
    private function processWord($word, $id)
    {
        // Clean up text.
        $chars_to_replace = [',', '.', '"', ' ', '!', '?', '[', ']', '(', ')', '{', '}', '&', "''", '*', ';', '…', '~'];
        $word = str_replace(
            $chars_to_replace,
            array_fill(0, count($chars_to_replace), ''),
            $word
        );
        // Replace ticks and curly quotes..
        $word = str_replace(['`', '‘', '’'], ["'", "'", "'"], $word);
        // Strip certain characters from the start and the end of words.
        $chars_to_trim = " :-/\0\t\n\x0B\r";
        $word = trim($word, $chars_to_trim);
        if (! empty($word) && ! preg_match('/^[-]+$/', $word)):
            // 'accattone'
            if($word[0] == "'" && $word[strlen($word) - 1] == "'"):
                $word = substr($word, 1, strlen($word) - 2);
                if ($word === 'n'):
                    $word = "'n";
                endif;
            endif;
            // Retain capitilisation for countries, months, names etc
            $word_info = $this->setCaseInfo($word);
            if (! empty($word_info)):
                if (! isset($this->word_cloud[$word_info['word']])):
                    $this->word_cloud[$word_info['word']] = [
                        'word' => $word_info['word'],
                        'category' => $word_info['type'],
                        'count' => 1,
                        'song_ids' => [$id],
                    ];
                else:
                    $this->word_cloud[$word_info['word']]['count'] += 1;
                    $this->word_cloud[$word_info['word']]['song_ids'][] = $id;
                endif;
            endif;
        endif;
    }

    /**
     * Save word cloud to the database.
     *
     * @return void
     */
    private function storeWordCloud()
    {
        foreach ($this->word_cloud as $w):
            try {
                // Is this a real word?
                $is_word = $this->dictionary->isWord($w['word']);
                if (! $is_word):
                    // Check if it is possible a plural.
                    if (substr($w['word'], -1) === 's'):
                        // Try again.
                        $is_word = $this->dictionary->isWord(substr($w['word'], 0, -1));
                    endif;
                endif;
                $w['is_word'] = $is_word;
                $w['song_ids'] = array_unique($w['song_ids']);

                $this->wordCloud->dynamicStore($w);

            } catch (Exception $e) {
                Log::error($e->getMessage());
            }
        endforeach;
    }

    /**
     * Log word cloud.
     *
     * @return void
     */
    private function logWordCloud()
    {
        ksort($this->word_cloud);
        foreach ($this->word_cloud as $w => $v):
            $is_word = $this->dictionary->isWord($w);
            if (! $is_word):
                // Check if it is possible a plural.
                if (substr($w, -1) === 's'):
                    // Try again.
                    $is_word = $this->dictionary->isWord(substr($w, 0, -1));
                endif;
            endif;
            $v['is_word'] = $is_word;
             // Possible check, if false if last letter is s, strip s and try again.
            $v['song_ids'] = array_unique($v['song_ids']);
            Log::info($w);
            Log::info($v);
        endforeach;
    }

}
