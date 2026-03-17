@extends('layouts.app')

@section('content')
    @php
        $artistViewProps = [
            'title' => $title,
            'action' => url('/artist'),
            'redirects_to' => URL::previous(),
            'back_url' => url()->previous(),
            'csrf' => csrf_token(),
            'countries' => array_values($countries ?? []),
            'albums' => array_values($albums ?? []),
            'songs' => array_map(static function ($song) {
                return [
                    'id' => $song->id,
                    'title' => $song->title,
                ];
            }, isset($songs) ? $songs->all() : []),
            'artist' => [
                'id' => $artist->id ?? null,
                'artist' => $artist->artist ?? '',
                'country' => $artist->country ?? '',
                'location' => $artist->location ?? '',
                'founded' => $artist->founded ?? '',
                'disbanded' => $artist->disbanded ?? '',
                'is_group' => (bool) ($artist->is_group ?? false),
                'group_members' => $artist->group_members ?? '',
                'notes' => $artist->notes ?? '',
                'photo' => $artist->photo ?? '',
            ],
            'old' => [
                'founded' => old('founded'),
                'disbanded' => old('disbanded'),
            ],
        ];
    @endphp

    <div class="panel-body mysound-submit-form-div">
        <h2 class="col-sm-6 green">{{ $title }}</h2>
        @include('common.errors')
        <div id="artist-react-root"></div>
    </div>

    <script type="text/javascript">
        window.artistViewProps = @json($artistViewProps);
    </script>

@endsection

@section('scripts')
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="{{ asset('js/artist-react-page.js') }}"></script>
@endsection
