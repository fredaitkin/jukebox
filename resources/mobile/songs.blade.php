@extends('layouts.app')

@section('content')


        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                Please fix the following errors
            </div>
        @endif

        @if (isset($message))
            <div class="alert alert-warning ml-3" role="alert">
                {{ $message }}
            </div>
        @endif

        <div id="success_message" class="d-none alert alert-success alert-dismissible fade show"></div>

        <div class="panel-body">
            <form action="/songs/search" method="POST" role="search">
                {{ csrf_field() }}
                <div class="input-group col-sm-6 pb-2">
                    <input type="text" class="form-control" name="q" placeholder="Search"  @if (!empty($q)) value="{{ $q }}" @endif>
                    <span class="input-group-btn pl-1">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-search"></i>
                        </button>
                    </span>
                    <span class="input-group-btn pl-1">
                        <button type="button" class="btn btn-primary" name="reset">
                            Reset
                        </button>
                    </span>
                    <span class="input-group-btn pl-1">
                        <button type="button" class="btn btn-primary" id="shuffle">
                            Shuffle
                        </button>
                    </span>
                </div>
            </form>
        </div>

        @if (isset($songs) && $songs->count() > 0)
                <table class="table table-striped mysounds-table mobile-table">

                    <thead>
                        <th>Title</th>
                        <th>Artist</th>
                        <th>Play</th>
                        <th>Play Album</th>
                        <th>+ Playlist</th>
                        <th>Lyrics</th>
                    </thead>

                    <tbody>
                        @foreach ($songs as $song)
                            <tr class="mobile-mysounds-tr">
                                <td>
                                    {{ csrf_field() }}
                                    <a href="/song/{{ $song->id }}">{{ $song->title }}</a>
                                </td>
                                <td class="table-text">
                                    <div>
                                        <a href="/artist/{{ $song->artists[0]->id }}">{{ $song->artists[0]->artist }} @if($song->notes_artist) - {{ $song->notes_artist}} @endif</a>
                                    </div>
                                </td>
                                <td name="play">
                                    <span id="play-{{ $song->id }}"><i class="fa fa-play"></i></span>
                                </td>
                                <td name="play_album">
                                    <span id="play-album-{{ $song->id }}"><i class="fa fa-play"></i></span>
                                </td>
                                <td>
                                   <span name="playlist" id="playlist-{{ $song->id }}" data-title="{{ $song->title }}"><i class="fa fa-list"></i></span>
                                </td>
                                <td>
                                    <a target="_blank" href="/songs?lyrics=true&id={{ $song->id }}"><i class="fa fa-book"></i></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class='mobile ml-2'>
                    {{ $songs->onEachSide(1)->links() }}
                </div>

            <form method="GET">
                <div class="d-flex ml-2 mt-1 w-50">
                <input type="text" class="form-control" id="page" name="page" size=10>
                <input type="submit" class="btn btn-primary" id="go" value="Go">
                </div>
            </form>
        @endif

@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.0.0/crypto-js.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/utf8/3.0.0/utf8.min.js"></script>
    <script src="{{ asset('js/playlist.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>

@endsection

@section('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" rel="stylesheet" />
@endsection
