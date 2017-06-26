<?php

class YTA_Tests extends WP_UnitTestCase {

	function testHasAPIKey() {
		$key = yta_get_api_key();
		$this->assertNotEmpty( $key );
		$this->assertInternalType( 'string', $key );
	}

	function testHasPlaylistID() {
		$id = yta_get_playlist_id();
		$this->assertNotEmpty( $id );
		$this->assertInternalType( 'string', $id );
	}

	function testCanGetVideos() {
		$videos = yta_get_videos();
		$this->assertNotEmpty( $videos );
		$this->assertInternalType( 'array', $videos );
		$this->assertNotEmpty( $videos['videos'] );
	}
}

