@extends('layouts.app')

@section('content')

    <div class="panel-body mysound-submit-form-div">

        <h2 class="col-sm-12">{{ $title }}</h2>

        @include('common.errors')

        <form action="/song" method="POST" class="form-horizontal">
            {{ csrf_field() }}
            {{  html()->hidden('url',URL::previous())  }}

            @if ($song_exists)
            <div class="form-group">
                <div class="col-sm-3">
                    <span name="play" id="play-{{ $song->id }}">
                        <i class="fa fa-music" style="color:peru;"></i>
                        <i class="fa fa-music" style="color:peru;"></i>
                        <i class="fa fa-music" style="color:peru;"></i>
                    </span>
                </div>
            </div>
            @endif

            <div class="row">
                <div class="col w-25">

                    <label for="title" class="control-label">Title</label>
                    <div class="pb-1">
                        <input type="text" name="title" id="song-title" class="form-control" @if ( ! empty($song->title)) value="{{ $song->title }}" @endif>
                    </div>

                    <label for="album" class="control-label">Album</label>
                    <div class="pb-1">
                        <input type="text" name="album" id="song-album" class="form-control" @if ( ! empty($song->album)) value="{{ $song->album }}" @endif>
                    </div>

                    <label for="artist" class="control-label">Artist</label>
                    <div class="pb-1">
                        <select class="artists form-control" multiple="multiple" name="artists[]" id="artists"></select>
                    </div>
                    <label for="year" class="control-label">Year</label>
                    <div>
                        <input type="text" name="year" id="song-year" class="form-control" @if ( ! empty($song->year)) value="{{ $song->year }}" @endif>
                    </div>

                </div>

                <div class="col-md-5 w-75">
                    @if ($cover_art)
                        <img class="pt-4 ml-5" src="{{ $cover_art }}" class="css-class" alt="alt text" style="width:40%">
                    @endif
                </div>

            </div>

            <div class="row w-50">

                <div class="col">
                    <label for="genre" class="control-label">Genre</label>
                    <div class="pb-1">
                        <input type="text" name="genre" id="song-genre" class="form-control" @if ( ! empty($song->genre)) value="{{ $song->genre }}" @endif>
                    </div>
                </div>

            </div>

            <div class="row w-50">

                <div class="col">
                    <label for="track_no" class="control-label">Track No</label>
                    <div class="pb-1">
                        <input type="text" name="track_no" id="song-track_no" class="form-control" @if ( ! empty($song->track_no)) value="{{ $song->track_no }}" @endif>
                    </div>
                </div>

                <div class="col">
                    <label for="file_type" class="control-label">File Type</label>
                    <div class="pb-1">
                         <select class="form-control" name="file_type">
                            @foreach ($file_types as $file_type)
                                <option value="{{ $file_type }}" @if ( ! empty($song->file_type) && ($song->file_type == $file_type)) selected @endif>{{ $file_type }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col">
                    <label for="playtime" class="control-label">Playtime</label>
                    <div class="pb-1">
                        <input type="text" name="playtime" id="song-playtime" class="form-control" @if ( ! empty($song->playtime)) value="{{ $song->playtime }}" @endif>
                    </div>
                </div>
            </div>

            <div class="row w-50">

                <div class="col">
                    <label for="composer" class="control-label">Composer</label>
                    <div class="pb-1">
                        <input type="text" name="composer" id="song-composer" class="form-control" @if ( ! empty($song->composer)) value="{{ $song->composer }}" @endif>
                    </div>
                </div>

            </div>

            <div class="row w-50">

                <div class="col">
                    <label for="location" class="control-label">Location</label>
                    <div class="pb-1">
                        <input type="text" name="location" id="song-location" class="form-control" @if ( ! empty($song->location)) value="{{ $song->location }}" @endif>
                    </div>
                </div>

            </div>

            <div class="row w-75">

                <div class="col">
                    <label for="notes" class="control-label">Notes</label>
                    <div class="pb-2">
                        <textarea name="notes" id="song-notes" class="form-control" rows="1">@if (!empty($song->notes)){{ $song->notes }}@endif</textarea>
                    </div>
                </div>

            </div>

            <div class="row w-50">

                <div class="col">
                    <label for="rank" class="control-label">Rank</label>
                    <div class="pb-1">
                         <select class="form-control" name="rank">
                            <option value=""></option>
                            @for ($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" @if ( ! empty($song->rank) && ($song->rank == $i)) selected @endif>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="col">
                    <label for="last_played" class="control-label">Last Played</label>
                    <div class="pb-1">
                        <input type="text" name="last_played" id="last_played" class="form-control" @if ( ! empty($song->last_played)) value="{{ $song->last_played }}" @endif>
                    </div>
                </div>

                <div class="col">
                    <label for="played" class="control-label">Played</label>
                    <div class="pb-1">
                        <input type="text" name="played" id="played" class="form-control" @if ( ! empty($song->played)) value="{{ $song->played }}" @endif>
                    </div>
                </div>

                <div class="col">
                    <label for="file_type" class="control-label">Don't Play</label>
                    <div class="pb-1">
                        <input type="checkbox" name="do_not_play" id="do_not_play" @if (!empty($song->do_not_play) && ($song->do_not_play)) checked @endif>
                    </div>
                </div>

            </div>

            <div class="row pt-2">
                <div class="col">
                    @if ( ! empty($song->id))
                        <input type="hidden" name="id" id="song-id" value="{{ $song->id }}">
                        <input type="hidden" name="artist_json" id="artist_json" value="{{ $artists }}">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
                    @else
                        <button type="submit" class="btn btn-primary">Add Song</button>
                    @endif
                </div>
            </div>

        </form>

    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/artist.js') }}"></script>
    <script src="{{ asset('js/playlist.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
@endsection

@section('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
@endsection
