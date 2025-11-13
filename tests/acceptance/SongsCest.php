<?php 

class SongsCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function trySongsPage(AcceptanceTester $I)
    {
    	$I->login($I);
		$I->amOnPage('/songs');
		$I->see('Current Songs');
	}

    public function tryViewSongEditPage(AcceptanceTester $I)
    {
        $I->login($I);
        $I->amOnPage('/songs');
        $I->click('a[name="title"]');
        $I->see('Composer');
	}

    public function tryPlayAlbumSongs(AcceptanceTester $I)
    {
        $I->login($I);
        $I->amOnPage('/songs');
        $I->click('span[name="play_album"]');
        $I->wait(1);
        $I->seeElement('audio');
    }

    public function tryAddToPlaylist(AcceptanceTester $I)
    {
        $I->login($I);
        $I->amOnPage('/songs');
        $I->click('span[name="playlist"]');
        $I->wait(1);
        $I->see('Existing Playlist');
    }
}
