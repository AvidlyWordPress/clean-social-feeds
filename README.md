# Clean Social Feeds
Library for retrieving pure data from social media platforms, made for WordPress.

## Why use this instead of (insert something here)?
This helper library is made out of need to get feeds from social media platforms, and have full
control of the markup and styles, without any added bloat from external libraries.

## Usage
1. Include this repo as submodule or download the `clean-social-feeds.php` and include it directly in your theme/plugin.
2. Create applications for social media platforms you are intending to use.
3. Get the feeds, build your own markup and styles and you're good to go!
4. In case the feeds are broken, the API's may have changed, so check if there are any updates for the library.

## Usage in-depth

### Application keys
You need to create application for the social media platforms you are indending to use, and get the
corresponsing application keys.

#### Facebook
Create your Facebook App here: https://developers.facebook.com/apps/

#### Twitter
Create your Twitter App here: https://apps.twitter.com/app/new

### Getting social media feeds

**1. Setup the main class with application keys**
```php
$feeds = new Clean_Social_Feeds( array(
	'facebook_app_id'         => 'YOUR_FACEBOOK_APP_ID_HERE',
	'facebook_app_secret'     => 'YOUR_FACEBOOK_APP_SECRET_HERE',
	'twitter_consumer_key'    => 'YOUR_TWITTER_CONSUMER_KEY_HERE',
	'twitter_consumer_secret' => 'YOUR_TWITTER_CONSUMER_SECRET_HERE',
) );
```

**2. Get the feeds**
```php
$facebook = $feeds->getFacebookPagePosts( array( 'page_id' => 'FACEBOOK_PAGE_ID_HERE' ) );
$twitter  = $feeds->getTwitterUserPosts( array( 'username' => 'TWITTER_USERNAME_HERE' ) );
```

**3. Create the markup for the feeds.**
```php
// Facebook
if ( ! is_wp_error( $facebook ) && ! empty( $facebook['posts']['data'] ) ) {
	echo '<pre>';
	print_r($facebook);
	echo '</pre>';
}
// Twitter
if ( ! is_wp_error( $twitter ) && ! empty( $twitter ) ) {
	echo '<pre>';
	print_r($twitter);
	echo '</pre>';
}
```

### Optional arguments for feeds
Currently you can pass the following arguments while retrieving the feeds from services:

| Service    | Param      | Description                                  | Default value |
| ---------- | ---------- | -------------------------------------------- | --------------|
| All        | cache      | (bool) Should we cache the result            | true          |
| All        | cache_time | (int) Time to cache the results (in seconds) | 60*60         |
| All        | limit      | (int) Number of results to get               | 10            |

## Version history

**1.0** *(30.12.2015)*
* Initial release.