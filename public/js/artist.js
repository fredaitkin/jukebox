$(document).ready(function() {

  $("input[name='play_songs']").click(function() {
    let artist_id = $(this).attr('id');
    let id = artist_id.replace("play-songs-", "");
    let artist = $(this).closest('tr').find('div[name="artist_name"]').text();

    let url = APP_URL + '/artists?id=' + id + '&artist=' + artist + '&songs=true';

    fetch(url)
      .then(
        function(response) {
          if (response.status !== 200) {
            console.log('Looks like there was a problem. Status Code: ' + response.status);
            return;
          }
          response.json().then(function(data) {
            display_jukebox(artist, data);
          });
        }
      )
      .catch(function(err) {
        console.log('Fetch Error: ', err);
    });

  });

  if ($('.artists').length) {
    $('.artists').select2({
      placeholder: 'Please Select',
      ajax: {
        url: '/artist-autocomplete',
        dataType: 'json',
        delay: 250,
        processResults: function (data) {
          return {
            results: data
          };
        },
        cache: true
      }
    });
  }

  if ($("#artist_json").val() != undefined) {
    var artists = JSON.parse($("#artist_json").val());
    $.each(artists, function(i, artist) {
      let set_artist = $("<option selected='selected'></option>").val(artist.id).text(artist.artist);
      $(".artists").append(set_artist).trigger('change');
    });
  }

});