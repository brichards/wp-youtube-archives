# YouTube Archives

- Contributors: rzen
- Donate link: http://WPSessions.com
- Tags: youtube
- Requires at least: 4.2.0
- Tested up to: 4.7.0
- Stable tag: 1.0.0
- License: GPLv2 or later
- License URI: http://www.gnu.org/licenses/gpl-2.0.html

# Description

Creates a fully interactive YouTube Archive page on the current site.

# Installation

Install YouTube Archives in the same way you would install any other WordPress plugin.

1. Upload `yta-archives.zip` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Create a custom page template in your theme for displaying your video archive (e.g. https://gist.github.com/brichards/5d41e75e2b23f9c0cf11c314adf4f197).
1. Within the custom page template, use the following functions for loading and rendering YouTube content:
  - `yta_embed_video_player( get_query_var( 'id' ) );` to embed a YouTube player
  - `$videos = yta_get_videos();` to get the most recent videos
  - `yta_video_loop( $videos );` to output the loop of `$videos`
  - `<a href="#" class="load-more-videos button">Load More</a>` to trigger more videos to load

# Changelog

- 1.0.0
  - Initial release.
