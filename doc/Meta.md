- [twitter](#twitter)
- [facebook](#facebook)
- [msTile](#mstile)

## Meta
Functions, that generate sets of meta-tags, that may be useful for your website.

### twitter
```php
twitter(array $general, array $playerApp = [], bool $pretty = false);
```
Function creates a set of `<meta>` tags required for Twitter Cards (https://developer.twitter.com/en/docs/twitter-for-websites/cards). It does some validation of the values you provide to reduce chances of botching the card.

`$general` is an array of general settings used by cards:
```php
[
  #Mandatory type of the card. Supported values are summary, summary_large_image, app, player
  'card' => 'summary',
  #Mandatory title
  'title' => 'title',
  #Twitter handle for your website. Mandatory for app and player cards
  'site' => '@simbiat199',
  #Twitter ID for your website
  'site:id' => '3049604752',
  #Twitter handle for creator of the content
  'creator' => '@simbiat199',
  #Twitter ID for creator of the content
  'creator:id' => '3049604752',
  #Description of the page
  'description' => 'Twitter card',
  #Link to the image. Needs to be an absolute HTTPS URL. Mandatory for player cards
  'image' => 'https://simbiat.ru/frontend/images/favicons/simbiat.png',
  #Description for the image
  'image:alt' => 'Just an image',
]
```
`$playerApp` is an array of values used for cards with types 'app' or 'player'.

For player cards (https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/player-card) the array will look like this:
```php
  #Mandatory URL to a frame of the player
  'player' => 'https://simbiat.ru/iframe',
  #Mandatory width of a player in pixel
  'width' => '100',
  #Mandatory height of a player in pixel
  'height' => '100',
  #Optional URL to raw stream of the audio/video
  'stream' => 'https://simbiat.ru/iframe/mp3.mp3',
]
```
For app cards (https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/app-card) the array will look like this:
```php
  #Mandatory app ID for iPad
  'id:ipad' => '101',
  #Mandatory app ID for iPhone
  'id:iphone' => '100',
  #Mandatory app ID for Google Play
  'id:googleplay' => '102',
  #Optional custom schema for iPad
  'url:ipad' => 'app://',
  #Optional custom schema for iPhone
  'url:iphone' => 'app://',
  #Optional custom schema for Google Play
  'url:googleplay' => 'app://',
  #Optional 2-characters country code if the app is not available in US (for Apple products)
  'country' => 'RU',
]
```
`$pretty` if set to `true` will add new line to the end of each `meta` tag. May be useful if you prefer a bit cleaner and human-readable look.

