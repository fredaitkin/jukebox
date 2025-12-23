$(function() {

  $("input[name='dictionary']").on('click', function() {
    let word = $(this).attr('id');
    word = word.replace("dictionary-", "");
    var url = '/dictionary?word=' + word;

    fetch(url)
      .then(
        function(response) {
          if (response.status !== 200) {
            console.log('Looks like there was a problem. Status Code: ' + response.status);
            return;
          }
          response.json().then(function(data) {
            display_dictionary_form(word, data.glossary);
          });
        }
      )
      .catch(function(err) {
          console.log('Fetch Error: ', err);
    });

  });

});

function display_dictionary_form(word, data) {
  let form = '<div>';
  $.each(data, function(i, glossary) {
      form += '<div style="width:10%;float:left">' + glossary.type + '</div>';
      form += '<div style="width:90%;float:left">' + glossary.definition + '</div>';
  });
  form += '</div>';

  $(form).dialog({
    title: word,
    close: function() {
      $(this).remove();
    },
    modal: false,
    width: "85%",
    open : function() {
      $('div.ui-dialog').addClass('ui-dialog-jukebox');
    }
  });

}