$(function() {

  var device_type = $("meta[name='device-type']").attr("content");

  $("input[name='songs']").on('click', function() {
    let word_id = $(this).attr('id');
    word_id = word_id.replace("songs-", "");
    var url = '/word-cloud?songs=true&id=' + word_id;

    fetch(url)
      .then(
        function(response) {
          if (response.status !== 200) {
            console.log('Looks like there was a problem. Status Code: ' + response.status);
            return;
          }
          response.json().then(function(data) {
            display_song_form(data, device_type);
          });
        }
      )
      .catch(function(err) {
        console.log('Fetch Error: ', err);
    });

  });

});

function display_song_form(data, device_type) {
  let form = '<div>';
  $.each(data, function(i, song) {
    let title = (song.song.length > 20) ? song.song.substr(0, 20) + '...' : song.song; 
    form += '<div>' +
      '<div style="width:50%;float:left" id="' + song.id + '" song="' + song.song + '" class="song"><a class="songs" href="#">' + title + '</a></div>' +
      '<div style="width:50%;float:left">' + song.artist + '</div>' +
      '<div id="modal-' + song.id + '" style="display:none;white-space:pre;">' + song.lyrics + '</div>' +
      '</div>';
  });
  form += '</div>';

  $(form).dialog({
    title: 'Songs',
    close: function() {
      $(this).remove();
    },
    modal: false,
    width: device_type == 'desktop' ? 700 : 330,
    open : function() {
      $('div.ui-dialog').addClass('ui-dialog-jukebox');
    }
  });

  $('.song').on('click', function() {
      var modal_div = 'modal-' + $(this).attr('id');
      $('#' + modal_div).dialog({
        modal : true ,
        title: $(this).attr('song'),
        height :device_type == 'desktop' ? 700 : 500,
        width : device_type == 'desktop' ? 400 : 300,
        open : function() {
          $("[aria-describedby=" + modal_div + "]").addClass('ui-dialog-lyrics');
        },
      });
  });
}
