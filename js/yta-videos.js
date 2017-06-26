var ytArchive = ytArchive || {};

( function ( window, document, $, undefined ) {
	'use strict';

	ytArchive = {

		init : function () {
			ytArchive.setVars();
			ytArchive.bind();
			ytArchive.loadYouTubePlayer();
			ytArchive.updateShareLinks();
		},

		setVars : function () {
			ytArchive.player = null;
			ytArchive.videosContainer = document.getElementById('videos');
			ytArchive.videos = ytArchive.videosContainer.getElementsByTagName('div');
			ytArchive.$loadMoreLink = $('.load-more-videos');
		},

		bind : function () {

			// Listen for YT player to be ready
			window.onYouTubeIframeAPIReady = function () {
				ytArchive.onYouTubeIframeAPIReady();
				$('.videos').on( 'click', '.video-link', ytArchive.triggerVideo );
			};

			// Listen for history state change
			window.onpopstate = function ( event ) {
				ytArchive.onPopState( event );
			};

			ytArchive.$loadMoreLink.on( 'click', ytArchive.getMoreVideos );

		},

		/**
		 * Load YouTube iFrame API asynchronously.
		 */
		loadYouTubePlayer : function () {
			var tag = document.createElement('script');
			tag.src = "https://www.youtube.com/iframe_api";
			var firstScriptTag = document.getElementsByTagName('script')[0];
			firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
		},

		/**
		 * Initialize the YouTube player.
		 */
		onYouTubeIframeAPIReady : function () {
			var videoId = document.getElementById('ytplayer').attributes['data-video-id'].value;

			ytArchive.toggleActiveVideo( videoId );

			ytArchive.player = new YT.Player( 'ytplayer', {
				width: '1200',
				height: '675',
				videoId: videoId,
				modestbranding: '1',
				showinfo: '0'
			});
		},

		/**
		 * Load the clicked video, toggle active states, and scroll to video player.
		 */
		triggerVideo : function ( event ) {
			event.preventDefault();

			var videoId = this.parentElement.getElementsByTagName('a')[0].attributes['data-video-id'].value;

			ytArchive.toggleActiveVideo( videoId );
			ytArchive.loadVideo( videoId );
			ytArchive.scrollToPlayer();

			if ( ytArchive.isArchivePage() ) {
				ytArchive.setCurrentURL( videoId );
				ytArchive.updateShareLinks();
			}

		},

		/**
		 * Load the provided video ID into the YT player.
		 */
		loadVideo : function ( videoId ) {
			ytArchive.player.loadVideoById({ videoId: videoId });
		},

		/**
		 * Toggle .active class to provided video element.
		 */
		toggleActiveVideo : function ( videoId ) {
			$( '.active', '.videos' ).removeClass( 'active' );
			$( 'a[data-video-id="' + videoId + '"]', '.videos' ).closest('.video').addClass( 'active' );
		},

		/**
		 * Scroll video player into view.
		 */
		scrollToPlayer : function () {
			$( 'html, body' ).animate( { scrollTop: ( $( '#ytplayer' ).offset().top - 100 ) }, 500 );
		},

		updateShareLinks : function () {
			var baseUrl = window.location;
			$('.sd-content a').each( function () {
				var $this = $(this);
				var shareUrl = $this.attr( 'href' ).split('?');
				$this.attr( 'href', baseUrl + '?' + shareUrl[1] );
			});
		},

		/**
		 * Fetch more videos via AJAX
		 */
		getMoreVideos : function ( event ) {
			event.preventDefault();

			ytArchive.setButtonToLoading();

			$.post(
				ytaVideos.ajaxUrl,
				{
					action: 'yta-video-loop',
					pageToken: ytaVideos.next,
				})
				.done( function ( response ) {
					ytArchive.appendVideosToPage( response.data.videos );
					ytArchive.setButtonToNormal();

					ytaVideos.previous = response.data.previous;
					ytaVideos.next = response.data.next;
				});
		},

		setButtonToLoading : function () {
			ytArchive.$loadMoreLink
				.html( 'Loading... <i class="fa fa-refresh fa-spin"></i>' )
				.addClass( 'loading');
		},

		setButtonToNormal : function () {
			ytArchive.$loadMoreLink
				.html( 'Load More' )
				.removeClass( 'loading' );
		},

		appendVideosToPage : function ( videos ) {
			$( videos ).appendTo( '#videos' );
		},

		/**
		 * Push active video URL into browser history.
		 */
		setCurrentURL : function ( videoId ) {
			history.pushState( {videoId: videoId}, '', '/videos/id/' + videoId + '/' );
		},

		/**
		 * Load corresponding video to loaded history state.
		 */
		onPopState : function( event ) {

			if ( ! ytArchive.isArchivePage() ) {
				return;
			}

			var videoId = ( null !== event.state && null !== event.state.videoId )
				? event.state.videoId
				: document.getElementById('ytplayer').attributes['data-video-id'].value;

			ytArchive.loadVideo( videoId );
			ytArchive.toggleActiveVideo( videoId );
			ytArchive.scrollToPlayer();
		},

		isArchivePage : function () {
			return null !== window.location.pathname.match(/^\/?videos\//);
		}
	};

	ytArchive.init();

})( window, document, jQuery );
