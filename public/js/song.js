// Configuration Constants
const JUKEBOX_CONFIG = {
  SONG_URL: '/song/play/',
  API_BASE: '/songs',
  SHUFFLE_TITLE: 'EVERYBODY SHUFFLIN...',
  PLAY_ALBUM_PREFIX: 'play-album-',
  PLAY_PREFIX: 'play-',
  GOOGLE_SEARCH_URL: 'https://www.google.com/search',
  WIKIPEDIA_SEARCH_URL: 'http://www.google.com/search',
};

const RIGHT_CLICK = 3;
const MAX_LYRICS_WINDOWS = 3;

function isMobileAudioDebugEnabled(deviceType) {
  if (deviceType === 'desktop') {
    return false;
  }

  const params = new URLSearchParams(window.location.search);
  return params.get('debug_audio') === '1';
}

/**
 * Utility function to fetch data with error handling
 */
async function fetchSongs(params = {}) {
  try {
    const queryString = new URLSearchParams(params).toString();
    const url = queryString ? `${JUKEBOX_CONFIG.API_BASE}?${queryString}` : JUKEBOX_CONFIG.API_BASE;
    
    const response = await fetch(url);
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    return await response.json();
  } catch (error) {
    console.error('Failed to fetch songs:', error);
    throw error;
  }
}

/**
 * Extract ID from element with prefix (e.g., "play-123" -> "123")
 */
function extractIdFromElement(element, prefix) {
  const id = $(element).attr('id');
  return id ? id.replace(prefix, '') : null;
}

$(function() {
  const deviceType = $("meta[name='device-type']").attr("content");

  // Album dropdown handler
  $("#album").on('change', async function() {
    try {
      const data = await fetchSongs({ album: this.value });
      let html = '<ol id="songs" style="list-style-type:none">';
      data.forEach(song => {
        html += `<li><a href="/song/${song.id}">${escapeHtml(song.title)}</a></li>`;
      });
      html += '</ol>';
      $("#songs").replaceWith(html);
    } catch (error) {
      console.error('Error loading album songs:', error);
    }
  });

  // Shuffle all songs handler
  $("a[name='shuffle_songs']").on('click', async function() {
    try {
      const data = await fetchSongs({ all: '' });
      const shuffled = shuffle(data);
      display_jukebox(JUKEBOX_CONFIG.SHUFFLE_TITLE, shuffled, deviceType);
    } catch (error) {
      console.error('Error shuffling songs:', error);
    }
  });

  // Play album handler
  $(document).on('click', "span[name='play_album']", async function() {
    try {
      const songId = extractIdFromElement(this, JUKEBOX_CONFIG.PLAY_ALBUM_PREFIX);
      const data = await fetchSongs({ id: songId, album: 'true' });
      if (data.length > 0) {
        display_jukebox(data[0].album, data, deviceType);
      }
    } catch (error) {
      console.error('Error playing album:', error);
    }
  });

  // Play single song handler
  $(document).on('click', "span[name='play']", async function() {
    try {
      const songId = extractIdFromElement(this, JUKEBOX_CONFIG.PLAY_PREFIX);
      const data = await fetchSongs({ id: songId });
      if (data.length > 0) {
        display_jukebox(data[0].album, data, deviceType);
      }
    } catch (error) {
      console.error('Error playing song:', error);
    }
  });

  // Play genre handler
  $(document).on('click', "a[name='play_genre']", async function() {
    try {
      const genre = $(this).parent().prev('td').find('div').text();
      const data = await fetchSongs({ genre });
      display_jukebox(genre, data, deviceType);
    } catch (error) {
      console.error('Error playing genre:', error);
    }
  });

  // Get lyrics handler
  $("#get_lyrics").off().on("click", async function() {
    const artist = $("#artist").val().trim();
    if (!artist) {
      console.warn('No artist specified');
      return;
    }

    try {
      const data = await fetchSongs({
        artist,
        lyrics_empty: 'true',
        exact_match: $('#exact_match').is(':checked'),
        exempt: $('#exempt').val()
      });

      if (data.length === 0) {
        alert('No missing lyrics');
        return;
      }

      const limit = Math.min(data.length, MAX_LYRICS_WINDOWS);
      for (let i = 0; i < limit; i++) {
        const searchQuery = `${artist} ${data[i].title} lyrics`;
        window.open(`${JUKEBOX_CONFIG.GOOGLE_SEARCH_URL}?q=${encodeURIComponent(searchQuery)}`, '_blank');
        window.open(`songs?lyrics=true&id=${data[i].id}`);
      }
    } catch (error) {
      console.error('Error getting lyrics:', error);
    }
  });

  // Reset form handler
  $(document).on('click', "button[name='reset']", function() {
    const $form = $(this).parent().parent();
    $form.find('input').val('').first().focus();
  });

  // Shuffle with playback disabled
  $("#shuffle").on('click', async function() {
    try {
      const data = await fetchSongs({ all: '', do_not_play: 'true' });
      const shuffled = shuffle(data);
      display_jukebox(JUKEBOX_CONFIG.SHUFFLE_TITLE, shuffled, deviceType, true);
    } catch (error) {
      console.error('Error shuffling:', error);
    }
  });

  // Title click handler (right-click opens Wikipedia)
  $(document).on('mousedown', "a[name='title']", function(event) {
    if (event.which === RIGHT_CLICK) {
      const title = $(this).text();
      const artist = $(this).parent().next().find('a').text();
      const searchQuery = `${artist} ${title} wikipedia`;
      window.open(`${JUKEBOX_CONFIG.WIKIPEDIA_SEARCH_URL}?q=${encodeURIComponent(searchQuery)}`, title, 'toolbars=0,width=1200,height=800');
    }
    window.location.href = $(this).attr('href');
  });

});

/**
 * Escape HTML special characters to prevent XSS
 */
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

/**
 * Fisher-Yates shuffle algorithm
 * Shuffles array in-place and returns it
 */
function shuffle(array) {
  const arr = [...array]; // Create copy to avoid mutating original
  for (let i = arr.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [arr[i], arr[j]] = [arr[j], arr[i]]; // Destructuring swap
  }
  return arr;
}

/**
 * Display a jukebox dialog with audio player and song list
 */
function display_jukebox(title, songs, deviceType, displayAlbumButton = false) {
  if (!songs || songs.length === 0) {
    console.warn('No songs provided to jukebox');
    return;
  }

  const songUrl = JUKEBOX_CONFIG.SONG_URL;
  let jukeboxHtml = `
    <div class="audio">
      <figure>
        <audio controls src="${songUrl}${songs[0].id}">
          Your browser does not support the <code>audio</code> element.
        </audio>
      </figure>
      <button class="previous btn-jukebox">Previous</button>
      <button class="next btn-jukebox">Next</button>
      <button class="restart btn-jukebox">Restart</button>
      <button class="album btn-jukebox">Album</button>
      <div id="div-jukebox">
  `;

  // Build song list
  songs.forEach(song => {
    let artist = '';
    if (song.artists && song.artists.length > 0) {
      artist = song.artists[0].artist;
      if (artist === 'Compilations' && song.notes) {
        const artistMatch = song.notes.match(/Artist=([^;]*)/);
        if (artistMatch) {
          artist = artistMatch[1];
        }
      }
      artist = ` - ${artist}`;
    }
    jukeboxHtml += `<span id="song-${song.id}">${escapeHtml(song.title)}${artist}</span><br>`;
  });

  jukeboxHtml += '</div></div>';

  $(jukeboxHtml).dialog({
    title,
    close: function() {
      $(this).remove();
    },
    modal: false,
    width: deviceType === 'desktop' ? 500 : 330,
    open: function() {
      initializeJukeboxPlayer(this, songs, songUrl, deviceType, displayAlbumButton);
    }
  });
}

/**
 * Initialize jukebox player controls and event handlers
 */
function initializeJukeboxPlayer(dialogElement, songs, songUrl, deviceType, displayAlbumButton) {
  $('div.ui-dialog').addClass('ui-dialog-jukebox');

  let currentIndex = 0;
  let previousSongId = songs[0].id;
  let songAdvanced = false;
  let audioEndCheckIntervalId = null;
  const debugAudio = isMobileAudioDebugEnabled(deviceType);

  const $dialog = $(dialogElement);
  const $audio = $dialog.find('audio')[0];
  const $nextBtn = $dialog.find('button.next')[0];
  const $prevBtn = $dialog.find('button.previous')[0];
  const $restartBtn = $dialog.find('button.restart')[0];
  const $albumBtn = $dialog.find('button.album')[0];

  // Set initial styling
  $(`#song-${previousSongId}`).addClass('font-weight-bold');
  $("span.ui-dialog-title").html(escapeHtml(songs[0].title));

  function logAudioDebug(message, details = {}) {
    if (!debugAudio) {
      return;
    }

    console.log('[jukebox-audio-debug]', message, {
      currentIndex,
      songId: previousSongId,
      currentTime: $audio?.currentTime,
      duration: $audio?.duration,
      ended: $audio?.ended,
      paused: $audio?.paused,
      visibilityState: document.visibilityState,
      ...details,
    });
  }

  // Some mobile browsers can miss `ended` while the device is locked/backgrounded.
  // These fallbacks ensure we still advance when playback reaches the end.
  const advanceToNextSong = (source = 'unknown') => {
    if (songAdvanced) {
      logAudioDebug('advance-blocked-song-already-advanced', { source });
      return;
    }
    logAudioDebug('advance-to-next-song', { source });
    songAdvanced = true;
    handleSongChange(1);
  };

  function hasTrackReachedEnd() {
    if (!$audio || !Number.isFinite($audio.duration) || $audio.duration <= 0) {
      return false;
    }

    return $audio.ended || ($audio.currentTime >= ($audio.duration - 0.35));
  }

  function resetEndDetection() {
    songAdvanced = false;
    logAudioDebug('reset-end-detection');
  }

  function checkTrackEnd(source = 'check') {
    if (hasTrackReachedEnd()) {
      advanceToNextSong(source);
    }
  }

  $audio.addEventListener('ended', () => advanceToNextSong('ended'));
  $audio.addEventListener('pause', () => {
    logAudioDebug('pause-event');
    if (hasTrackReachedEnd()) {
      advanceToNextSong('pause');
    }
  });
  $audio.addEventListener('timeupdate', () => checkTrackEnd('timeupdate'));

  const onVisibilityChange = () => {
    logAudioDebug('visibilitychange-event');
    checkTrackEnd('visibilitychange');
  };

  const onWindowFocus = () => {
    logAudioDebug('focus-event');
    checkTrackEnd('focus');
  };

  const onPageShow = () => {
    logAudioDebug('pageshow-event');
    checkTrackEnd('pageshow');
  };

  document.addEventListener('visibilitychange', onVisibilityChange);
  window.addEventListener('focus', onWindowFocus);
  window.addEventListener('pageshow', onPageShow);

  audioEndCheckIntervalId = window.setInterval(() => checkTrackEnd('interval'), 1000);

  $dialog.on('dialogclose', () => {
    if (audioEndCheckIntervalId !== null) {
      clearInterval(audioEndCheckIntervalId);
    }
    document.removeEventListener('visibilitychange', onVisibilityChange);
    window.removeEventListener('focus', onWindowFocus);
    window.removeEventListener('pageshow', onPageShow);
    logAudioDebug('dialog-closed-cleanup');
  });

  // Button event listeners
  $prevBtn.addEventListener('click', () => handleSongChange(-1));
  $nextBtn.addEventListener('click', () => handleSongChange(1));
  $restartBtn.addEventListener('click', () => handleSongChange(-currentIndex));
  $albumBtn.addEventListener('click', () => {
    playAlbum(previousSongId, deviceType);
    $dialog.closest('.ui-dialog').find('.ui-dialog-titlebar-close').trigger('click');
  });

  // Control visibility
  if (!displayAlbumButton) {
    $($albumBtn).hide();
  }

  if (songs.length === 0) {
    $($nextBtn).hide();
    $($restartBtn).hide();
  }

  playSong(null);

  // Event delegation for song list clicks
  $dialog.find('#div-jukebox').off('click').on('click', () => {
    $nextBtn.click();
  });

  /**
   * Handle song change (next/previous/restart)
   */
  function handleSongChange(offset) {
    currentIndex += offset;
    updateSong();
  }

  /**
   * Update song display and playback
   */
  function updateSong() {
    if (currentIndex < 0) currentIndex = 0;
    if (currentIndex >= songs.length) currentIndex = songs.length - 1;

    const song = songs[currentIndex];
    if (!song) {
      $nextBtn.disabled = true;
      return;
    }

    $nextBtn.disabled = false;
    $audio.src = songUrl + song.id;
    
    // Update UI
    $(`#song-${previousSongId}`).removeClass('font-weight-bold');
    previousSongId = song.id;
    $(`#song-${previousSongId}`).addClass('font-weight-bold');
    $("span.ui-dialog-title").html(escapeHtml(song.title));

    // Reset and play
    resetEndDetection();
    $audio.pause();
    $audio.load();
    playSong();
  }

  /**
   * Play song with navigation button visibility
   */
  function playSong(event = null) {
    // Update button visibility
    if (currentIndex === 0) {
      $($prevBtn).hide();
      $($restartBtn).hide();
    } else {
      $($prevBtn).show();
      $($restartBtn).show();
    }

    const playPromise = $audio.play();
    if (playPromise) {
      playPromise.catch(() => {
        // Autoplay prevented, move to next song
        if (event?.target?.className === 'previous btn-jukebox') {
          $prevBtn.click();
        } else {
          $nextBtn.click();
        }
      });
    }
  }
}

/**
 * Fetch and play an album
 */
async function playAlbum(songId, deviceType) {
  try {
    const data = await fetchSongs({ id: songId, album: 'true' });
    if (data.length > 0) {
      display_jukebox(data[0].album, data, deviceType);
    }
  } catch (error) {
    console.error('Error playing album:', error);
  }
}