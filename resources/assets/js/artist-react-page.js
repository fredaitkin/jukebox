(function () {
  const props = window.artistViewProps || {};
  const rootElement = document.getElementById('artist-react-root');

  if (!rootElement || !window.React || !window.ReactDOM) {
    return;
  }

  const { createElement, useState } = window.React;

  function ArtistPage() {
    const artist = props.artist || {};
    const oldValues = props.old || {};
    const countries = props.countries || [];
    const albums = props.albums || [];
    const songs = props.songs || [];
    const hasArtistId = !!artist.id;

    const [isGroup, setIsGroup] = useState(!!artist.is_group);

    const foundedValue = oldValues.founded ?? artist.founded ?? '';
    const disbandedValue = oldValues.disbanded ?? artist.disbanded ?? '';

    return createElement(
      'form',
      {
        action: props.action || '/artist',
        method: 'POST',
        encType: 'multipart/form-data',
        className: 'form-horizontal',
      },
      createElement('input', { type: 'hidden', name: '_token', value: props.csrf || '' }),
      createElement('input', { type: 'hidden', name: 'redirects_to', value: props.redirects_to || '' }),
      createElement(
        'div',
        { className: 'form-group row' },
        createElement(
          'div',
          { className: 'col' },
          createElement(
            'div',
            { className: 'row pb-2' },
            createElement(
              'div',
              { className: 'col' },
              createElement('label', { htmlFor: 'artist-artist', className: 'control-label' }, 'Artist'),
              createElement('input', {
                type: 'text',
                name: 'artist',
                id: 'artist-artist',
                className: 'form-control',
                defaultValue: artist.artist || '',
              })
            ),
            createElement(
              'div',
              { className: 'col' },
              createElement('label', { htmlFor: 'country', className: 'control-label' }, 'Country'),
              createElement(
                'select',
                {
                  className: 'form-control',
                  name: 'country',
                  defaultValue: artist.country || '',
                },
                countries.map(function (country) {
                  return createElement('option', { key: country, value: country }, country);
                })
              )
            )
          ),
          createElement(
            'div',
            { className: 'row pb-2' },
            createElement(
              'div',
              { className: 'col' },
              createElement('label', { htmlFor: 'location', className: 'control-label' }, isGroup ? 'Based' : 'Born'),
              createElement('input', {
                name: 'location',
                id: 'location',
                className: 'form-control',
                defaultValue: artist.location || '',
              })
            )
          ),
          createElement(
            'div',
            { className: 'row pb-2' },
            createElement(
              'div',
              { className: 'col-sm-2' },
              createElement('label', { htmlFor: 'founded', className: 'control-label' }, isGroup ? 'Founded' : 'Active From'),
              createElement('input', {
                type: 'text',
                name: 'founded',
                id: 'founded',
                className: 'form-control',
                defaultValue: foundedValue,
              })
            ),
            createElement(
              'div',
              { className: 'col-sm-2' },
              createElement('label', { htmlFor: 'disbanded', className: 'control-label' }, isGroup ? 'Disbanded' : 'Active To'),
              createElement('input', {
                type: 'text',
                name: 'disbanded',
                id: 'disbanded',
                className: 'form-control',
                defaultValue: disbandedValue,
              })
            ),
            createElement(
              'div',
              { className: 'col-sm-2' },
              createElement('label', { htmlFor: 'song-is_group', className: 'control-label' }, 'Is Group'),
              createElement(
                'div',
                null,
                createElement('input', {
                  type: 'checkbox',
                  name: 'is_group',
                  id: 'song-is_group',
                  checked: isGroup,
                  onChange: function (event) {
                    setIsGroup(event.target.checked);
                  },
                })
              )
            )
          ),
          (!hasArtistId || isGroup)
            ? createElement(
                'div',
                { className: 'row pb-2' },
                createElement(
                  'div',
                  { className: 'col' },
                  createElement('label', { htmlFor: 'artist-group_members', className: 'control-label' }, 'Group Members'),
                  createElement('textarea', {
                    name: 'group_members',
                    id: 'artist-group_members',
                    className: 'form-control',
                    defaultValue: artist.group_members || '',
                  })
                )
              )
            : null,
          albums.length > 0
            ? createElement(
                'div',
                { className: 'row pb-2' },
                createElement(
                  'div',
                  { className: 'col' },
                  createElement('label', { htmlFor: 'album', className: 'control-label' }, 'Albums'),
                  createElement(
                    'select',
                    { className: 'form-control', name: 'album', id: 'album' },
                    albums.map(function (album, index) {
                      return createElement('option', { key: index, value: album }, album);
                    })
                  )
                )
              )
            : null,
          createElement(
            'div',
            { className: 'row pb-2' },
            createElement(
              'div',
              { className: 'col' },
              createElement('label', { htmlFor: 'artist-notes', className: 'control-label' }, 'Notes'),
              createElement('textarea', {
                name: 'notes',
                id: 'artist-notes',
                className: 'form-control',
                defaultValue: artist.notes || '',
              })
            )
          ),
          createElement(
            'div',
            { className: 'row pb-2' },
            createElement(
              'div',
              { className: 'col' },
              createElement('label', { htmlFor: 'photo', className: 'control-label' }, 'Photo'),
              createElement('input', {
                type: 'file',
                name: 'photo',
                id: 'photo',
                className: 'form-control',
              })
            )
          ),
          createElement(
            'div',
            { className: 'row pt-2' },
            hasArtistId
              ? createElement(
                  'div',
                  { className: 'col-sm-offset-3 col-sm-6' },
                  createElement('input', { type: 'hidden', name: 'id', id: 'artist-id', value: artist.id }),
                  createElement('button', { type: 'submit', className: 'btn btn-primary' }, 'Update'),
                  createElement('a', { href: props.back_url || '/', className: 'btn btn-primary ml-2' }, 'Back')
                )
              : createElement(
                  'div',
                  { className: 'col-sm-offset-3 col-sm-6' },
                  createElement('button', { type: 'submit', className: 'btn btn-primary' }, 'Add Artist')
                )
          )
        ),
        createElement(
          'div',
          { className: 'col pt-4 pl-5' },
          createElement(
            'div',
            { className: 'row pl-5' },
            createElement(
              'div',
              { className: 'col' },
              artist.photo
                ? createElement('img', {
                    src: artist.photo,
                    className: 'img-thumbnail img-fluid artist-photo',
                    alt: 'artist photo',
                  })
                : null
            )
          ),
          songs.length > 0
            ? createElement(
                'div',
                { className: 'row pt-5 pl-5' },
                createElement(
                  'div',
                  { className: 'col' },
                  'Songs',
                  createElement(
                    'ol',
                    { id: 'songs', style: { listStyleType: 'none' } },
                    songs.map(function (song) {
                      return createElement(
                        'li',
                        { key: song.id },
                        createElement('a', { href: '/song/' + song.id }, song.title)
                      );
                    })
                  )
                )
              )
            : null
        )
      )
    );
  }

  const root = window.ReactDOM.createRoot(rootElement);
  root.render(createElement(ArtistPage));
})();
