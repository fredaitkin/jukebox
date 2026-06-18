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
  $(document).on('click', "td[name='play_album']", async function() {
    try {
      const songId = extractIdFromElement($(this).find('span'), JUKEBOX_CONFIG.PLAY_ALBUM_PREFIX);
      const data = await fetchSongs({ id: songId, album: 'true' });
      if (data.length > 0) {
        display_jukebox(data[0].album, data, deviceType);
      }
    } catch (error) {
      console.error('Error playing album:', error);
    }
  });

  // Play single song handler
  $(document).on('click', "td[name='play']", async function() {
    try {
      const songId = extractIdFromElement($(this).find('span'), JUKEBOX_CONFIG.PLAY_PREFIX);
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

  const $dialog = $(dialogElement);
  const $audio = $dialog.find('audio')[0];
  const $nextBtn = $dialog.find('button.next')[0];
  const $prevBtn = $dialog.find('button.previous')[0];
  const $restartBtn = $dialog.find('button.restart')[0];
  const $albumBtn = $dialog.find('button.album')[0];

  // Set initial styling
  $(`#song-${previousSongId}`).addClass('font-weight-bold');
  $("span.ui-dialog-title").html(escapeHtml(songs[0].title));

  // AirPods / media-remote controls
  const firstSong   = songs[0];
  const firstArtist = firstSong.artists?.[0]?.artist ?? '';
  initializeAirPodsControls(
    $audio,
    () => handleSongChange(1),
    () => handleSongChange(-1),
    { title: firstSong.title, artist: firstArtist }
  );

  // Event listeners
  $audio.addEventListener('ended', () => handleSongChange(1));
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

    const song = songs[currentIndex];
    if (!song) {
      $($nextBtn).hide();
      return;
    }

    $nextBtn.disabled = false;
    $audio.src = songUrl + song.id;
    
    // Update UI
    $(`#song-${previousSongId}`).removeClass('font-weight-bold');
    previousSongId = song.id;
    $(`#song-${previousSongId}`).addClass('font-weight-bold');
    $("span.ui-dialog-title").html(escapeHtml(song.title));

    // Refresh AirPods / lock-screen Now Playing metadata
    const artist = song.artists?.[0]?.artist ?? '';
    updateAirPodsMetadata(song.title, artist);

    // Reset and play
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
 * Initialize AirPods (and other media remote) controls via the Media Session API.
 *
 * AirPods gesture → Media Session action mapping:
 *   Single click  → play / pause  (toggles playback)
 *   Double click  → nexttrack     (advance to next song)
 *   Triple click  → previoustrack (go back to previous song)
 *
 * @param {HTMLAudioElement} audioEl   - The active <audio> element.
 * @param {Function}         onNext    - Callback to advance to the next track.
 * @param {Function}         onPrev    - Callback to go back to the previous track.
 * @param {Object}           songMeta  - { title, artist } for the lock-screen display.
 */
function initializeAirPodsControls(audioEl, onNext, onPrev, songMeta = {}) {
  if (!('mediaSession' in navigator)) {
    return; // Media Session API not supported in this browser
  }

  // Populate the OS / lock-screen "Now Playing" card when metadata is provided
  if (songMeta.title) {
    navigator.mediaSession.metadata = new MediaMetadata({
      title:  songMeta.title  || '',
      artist: songMeta.artist || '',
    });
  }

  // Single click: play
  navigator.mediaSession.setActionHandler('play', () => {
    audioEl.play().catch(err => console.warn('AirPods play blocked:', err));
  });

  // Single click: pause
  navigator.mediaSession.setActionHandler('pause', () => {
    audioEl.pause();
  });

  // Double click (right AirPod / forward gesture): next track
  navigator.mediaSession.setActionHandler('nexttrack', () => {
    onNext();
  });

  // Triple click / back gesture: previous track
  navigator.mediaSession.setActionHandler('previoustrack', () => {
    onPrev();
  });
}

/**
 * Update the Media Session "Now Playing" metadata displayed on the lock screen /
 * notification centre while AirPods are in use.
 *
 * @param {string} title
 * @param {string} artist
 */
function updateAirPodsMetadata(title, artist) {
  if (!('mediaSession' in navigator)) {
    return;
  }
  navigator.mediaSession.metadata = new MediaMetadata({
    title:  title  || '',
    artist: artist || '',
  });
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