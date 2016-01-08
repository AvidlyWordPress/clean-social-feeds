# Clean Social Feeds
Library for retrieving pure data from social media platforms, made for WordPress.

## Why use this instead of (insert something here)?
This helper library is made out of need to get feeds from social media platforms, and have full
control of the markup and styles, without any added bloat from external libraries.

## Usage
**1. Download and activate the plugin**

**2. Setup and request access tokens for the applications**

Head into `Settings -> Clean Social Feeds` and follow the instructions there to setup the application
information.

**3. Get the feeds**
```php
$feeds     = new Clean_Social_Feeds();
$facebook  = $feeds->getFacebookPagePosts( array( 'page_id' => 'FACEBOOK_PAGE_ID_HERE' ) );
$twitter   = $feeds->getTwitterUserPosts( array( 'username' => 'TWITTER_USERNAME_HERE' ) );
$instagram = $feeds->getInstagramUserPosts();
```

**4. Inspect the returned data and create the markup.**
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
// Instagram
if ( ! is_wp_error( $instagram ) && ! empty( $instagram['data'] ) ) {
	echo '<pre>';
	print_r($instagram);
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
| Facebook   | page_id    | (int) Facebook page ID                       | null          |
| Twitter    | username   | (string) Twitter username                    | null          |
| Instagram  | user_id    | (bool&#124;string) Instagram user ID         | self          |

## Version history

**1.2** *(8.12.2016)*
* Added settings -page for saving application information
* Added Instagram support

**1.1** *(6.12.2015)*
* Converted into plugin *(thanks danielck)*

**1.0** *(30.12.2015)*
* Initial release