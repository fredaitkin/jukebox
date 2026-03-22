$(function() {

  var device_type = $("meta[name='device-type']").attr("content");

  $("#album").change(function() {

    var url = '/songs?album=' + encodeURIComponent($('#album').val());

    fetch(url)
      .then(
        function(response) {
          if (response.status !== 200) {
            console.log('Looks like there was a problem. Status Code: ' + response.status);
            return;
          }
          response.json().then(function(data) {
            var ol = '<ol id="songs" style="list-style-type:none">';
            $.each(data, function(k, song) {
              ol += '<li><a href="/song/' + song.id + '">' + song.title + '</a></li>';
            });
            ol += '</ol>';
            $("#songs").replaceWith(ol);
          });
        }
      )
      .catch(function(err) {
        console.log('Fetch Error: ', err);
    });

  });

  $("a[name='shuffle_songs']").on('click', function() {
    var url = '/songs?all';

    fetch(url)
      .then(
        function(response) {
          if (response.status !== 200) {
            console.log('Looks like there was a problem. Status Code: ' + response.status);
            return;
          }
          response.json().then(function(data) {
            var songs = shuffle(data);
            display_jukebox("EVERYBODY SHUFFLIN...", songs, device_type);
          });
        }
      )
      .catch(function(err) {
          console.log('Fetch Error: ', err);
    });

  });

  $("span[name='play_album']").on('click', function() {
    let song_id = $(this).attr('id');
    song_id = song_id.replace("play-album-", "");

    var url = '/songs?id=' + song_id + '&album=true';

    fetch(url)
      .then(
          function(response) {
            if (response.status !== 200) {
              console.log('Looks like there was a problem. Status Code: ' + response.status);
              return;
            }
            response.json().then(function(data) {
              display_jukebox(data[0].album, data, device_type);
            });
          }
      )
      .catch(function(err) {
          console.log('Fetch Error: ', err);
    });

  });

  $("span[name='play']").on('click', function() {
    let song_id = $(this).attr('id');
    song_id = song_id.replace("play-", "");

    var url = '/songs?id=' + song_id;
    fetch(url)
      .then(
        function(response) {
          if (response.status !== 200) {
            console.log('Looks like there was a problem. Status Code: ' + response.status);
            return;
          }
          response.json().then(function(data) {
            display_jukebox(data[0].album, data, device_type);
          });
        }
      )
      .catch(function(err) {
        console.log('Fetch Error: ', err);
    });

  });

  $("a[name='play_genre']").on('click', function() {
    let genre = $(this).parent().prev('td').find('div').text();

    let url = '/songs?genre=' + encodeURIComponent(genre);

    fetch(url)
      .then(
        function(response) {
          if (response.status !== 200) {
            console.log('Looks like there was a problem. Status Code: ' + response.status);
            return;
          }
          response.json().then(function(data) {
            display_jukebox(genre, data, device_type);
          });
        }
      )
      .catch(function(err) {
        console.log('Fetch Error: ', err);
    });

  });

  $("#get_lyrics").off().on("click", function() {
      if ($("#artist").val().length > 0) {
        let url = '/songs?artist=' + $("#artist").val() +
          '&lyrics_empty=true' +
          '&exact_match=' + $('#exact_match').is(':checked') +
          '&exempt=' + $('#exempt').val();

        fetch(url)
          .then(
            function(response) {
              if (response.status !== 200) {
                console.log('Looks like there was a problem. Status Code: ' + response.status);
                return;
              }
              response.json().then(function(songs) {
                if (songs.length == 0) {
                  alert('No missing lyrics');
                } else {
                  for (let i = 0; i < songs.length; i++) {
                    var search = $("#artist").val() + ' ' + songs[i].title + ' lyrics';
                    window.open("https://www.google.com/search?q=" + encodeURIComponent(search), '_blank');
                    window.open("songs?lyrics=true & id=" + songs[i].id);
                    if (i == 2) {
                      break;
                    }
                  }
                }
              });
            }
          )
          .catch(function(err) {
            console.log('Fetch Error: ', err);
        });
      }

  });

  $("button[name='reset']").on(function() {
    $(this).parent().parent().find('input').val('');
    $(this).parent().parent().find('input').focus();
  });

  $("#shuffle").on('click', function() {
    var url = '/songs?all&do_not_play=true';

    fetch(url)
      .then(
        function(response) {

          if (response.status !== 200) {
            console.log('Looks like there was a problem. Status Code: ' + response.status);
            return;
          }
          response.json().then(function(data) {
            var songs = shuffle(data);
            display_jukebox("EVERYBODY SHUFFLIN...", songs, device_type, true);
          });
        }
      )
      .catch(function(err) {
          console.log('Fetch Error: ', err);
    });

  });

  $("a[name='title']").on('mousedown', function(event) {
    switch (event.which) {
      case 3:
        var title = $(this).text();
        var artist  = $(this).parent().next().find('a').text();
        var url ='http://www.google.com/search?q=' + artist + ' ' + title + ' wikipedia';
        window.open(url, title, 'toolbars=0,width=1200,height=800');
        window.location.href = $(this).attr('href');
        break;

      default:
        window.location.href = $(this).attr('href');
    }

  });

});

function shuffle(array) {
  var currentIndex = array.length, temporaryValue, randomIndex;

  // While there remain elements to shuffle
  while(0 !== currentIndex) {
    // Pick a remaining element...
    randomIndex = Math.floor(Math.random() * currentIndex);
    currentIndex -= 1;

    // And swap it with the current element
    temporaryValue = array[currentIndex];
    array[currentIndex] = array[randomIndex];
    array[randomIndex] = temporaryValue;
  }

  return array;
}

function display_jukebox(title, songs, device_type, display_album_button=false) {
  let song_url = '/song/play/';
  let jukebox_form = '<div class="audio">';
  jukebox_form += '<figure>';
  jukebox_form += '<audio controls src="' + song_url + songs[0].id + '">Your browser does not support the<code>audio</code> element.</audio>';
  jukebox_form += '</figure>';
  jukebox_form += '<button class="previous btn-jukebox">Previous</button><button class="next btn-jukebox">Next</button><button class="restart btn-jukebox">Restart</button><button class="album btn-jukebox">Album</button>';

  jukebox_form += '<div id=div-jukebox>';
  for (let i = 0; i < songs.length; i++) {
    var artist = '';
      if (songs[i].artists) {
        artist = songs[i].artists[0].artist;
        if (artist == 'Compilations' && songs[i].notes) {
          if (songs[i].notes.indexOf('Artist=') > -1) {
            artist = songs[i].notes;
            artist = artist.replace('Artist=', '');
            var idx = artist.indexOf(';');
            if (idx > -1) {
              artist = artist.substring(0,idx);
            }
          }
        }
        artist = ' - ' + artist;
      }
      jukebox_form += '<span id="song-' + songs[i].id + '">' + songs[i].title + artist + '</span><br>';
  }
  jukebox_form += '</div>';
  jukebox_form += '</div>';

  $(jukebox_form).dialog({
    title: title,
    close: function() {
      $(this).remove();
    },
    modal: false,
    width: device_type == 'desktop' ? 500 : 330,
    open : function() {

      $('div.ui-dialog').addClass('ui-dialog-jukebox');

      var idx = 0;
      var song = songs[0];

      // Add css styling
      let previous_id = song.id;
      $("#song-" + previous_id).addClass('font-weight-bold');
      $("span.ui-dialog-title").html(song.title);

      let audio = $(this).find('audio').get(0);
      let next = $(this).find('button.next').get(0);
      let previous = $(this).find('button.previous').get(0);
      let restart = $(this).find('button.restart').get(0);
      let album = $(this).find('button.album').get(0);

      audio.addEventListener('ended',function(e) {
        idx += 1;
        previous_id = next_song(e, audio, previous_id, idx);
      });

      previous.addEventListener('click', function(e) {
        idx -= 1;
        previous_id = next_song(e, audio, previous_id, idx);
      });

      next.addEventListener('click', function(e) {
        idx += 1;
        previous_id = next_song(e, audio, previous_id, idx);
      });

      restart.addEventListener('click', function(e) {
        idx = 0;
        previous_id = next_song(e, audio, previous_id, idx);
      });

      album.addEventListener('click', function() {
        play_album(previous_id, device_type);
        $('button.ui-dialog-titlebar-close').trigger('click');
      });

      if (!display_album_button) {
        $('button.album').hide();
      }

      if (songs.length == 0) {
        $('button.next').hide();
        $('button.restart').hide();
      }

      play(null, audio);

      function play(event, audio) {

        if (idx == 0) {
          $('button.previous').hide();
          $('button.restart').hide();
        } else {
          $('button.previous').show();
          $('button.restart').show();
        }
        var playPromise = audio.play();
          playPromise.then(function() {
            // Automatic playback started!
          }).catch(function() {
            if (event.target.className == 'previous btn-jukebox') {
              $('button.previous').click();
            } else {
             $('button.next').click();
            }
          });
      }

      function next_song(event, audio, previous_id, idx) {
        $('button.next').disabled = false;
        song = songs[idx];
        if (song !== undefined) {
          audio.src = song_url + song.id;
          $("#current-song").text(song.title);
          $("#song-" + previous_id).removeClass('font-weight-bold');
          previous_id = song.id;
          $("#song-" + previous_id).addClass('font-weight-bold');
          $("span.ui-dialog-title").html(song.title);
          audio.pause();
          audio.load();
          play(event, audio);
          return previous_id;
        } else {
           $('button.next').disabled = true;
        } 
      }

      $("#div-jukebox").on('click', function() {
        $('button.next').trigger('click');
      });

    }

  });
}

function play_album(song_id, device_type) {
  var url ='/songs?id=' + song_id + '&album=true';

  fetch(url)
    .then(
        function(response) {
          if (response.status !== 200) {
            console.log('Looks like there was a problem. Status Code: ' + response.status);
            return;
          }
          response.json().then(function(data) {
            display_jukebox(data[0].album, data, device_type);
          });
        }
    )
    .catch(function(err) {
        console.log('Fetch Error: ', err);
  });
}