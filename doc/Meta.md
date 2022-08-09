- [twitter](#twitter)
- [msTile](#mstile)
- [facebook](#facebook)

# Meta
Functions, that generate sets of meta-tags, that may be useful for your website.

## twitter
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

## msTile
```php
msTile(array $general, array $tasks = [], array $notifications = [], bool $xml = false, bool $prettyDirect = true);
```
#Function to generate either set of meta tags for Microsoft [Live] Tile (for pinned sites) or XML file for appropriate config file. Based on following specification:
- Meta specification: https://docs.microsoft.com/en-us/previous-versions/windows/internet-explorer/ie-developer/platform-apis/dn255024(v=vs.85)
- browserconfig.xml specification: https://docs.microsoft.com/en-us/previous-versions/windows/internet-explorer/ie-developer/platform-apis/dn320426(v=vs.85)
`$general` - array of basic settings:
```php
[
  #Name for the tile. Not used by XML config. Not mandatory by Meta specification, but mandatory in this function to provide you with proper control (other wise tile takes tile of current page)
  'name' => 'Simbiat Software',
  #Optional tooltip. If not set will be set to the name. Not used by XML config.
  'tooltip' => 'Simbiat Software',
  #Starting URL. Generally should by your home page. If not set will use values of the address the value is being requested from. Not used by XML config.
  'starturl' => 'https://simbiat.ru',
  #Size of the window. If not set or invalid will be set to minimum values of 800x600. Not used by XML config.
  'window' => 'width=800;height=600',
  #Two values that allow "tasks" using subdomains of your main domain (starturl). Unclear what the difference is. Defaults to true. Not used by XML config.
  'allowDomainApiCalls' => 'true',
  'allowDomainMetaTags' => 'true',
  #Badge details. Link should lead to XML file formatted as per https://docs.microsoft.com/en-us/uwp/schemas/tiles/badgeschema/schema-root
  'badge' => 'frequency=30;polling-uri=https://simbiat.ru/mstilebadge.xml',
  #Optional color for navigation buttons (used only by IE). Not used by XML config.
  'navbutton-color' => '#000000',
  #Optional tile color. There have been reports that after some update Win10 disregards it.
  'TileColor' => '#000000',
  #Paths to images of various sizes. It looks like Win10 may not be using them, instead relying on other icons referenced in you your code (`<link>` elements, `webmanifest` file) or there may be some condition to utilize them.
  'square150x150logo' => 'https://local.simbiat.ru/frontend/images/favicons/mstile-150x150.png',
  'square310x310logo' => 'https://local.simbiat.ru/frontend/images/favicons/mstile-310x310.png',
  'square70x70logo' => 'https://local.simbiat.ru/frontend/images/favicons/mstile-70x70.png',
  'wide310x150logo' => 'https://local.simbiat.ru/frontend/images/favicons/mstile-310x150.png',
  #Image for the tile. Unclear what is the difference from the ones above, especially, since specification states, that 150x150 is recommended. Yet somewhere long ago I had encountered a different recommendation for this image: 144x144.
  'TileImage' => 'https://local.simbiat.ru/frontend/images/favicons/mstile-144x144.png',
]
```
`$tasks` - array of so-called "tasks", that appear as pinned links, if pinned from IE. Does not seem to be used by Edge. Not used by XML config.
```php
[
  #Array for an actual task
  '1st' => [
    #Name to show on the menu
    'name' => 'FFXIV Tracker',
    #URL to the page referenced by this "task"
    'action-uri' => 'https://simbiat.ru/fftracker/',
    #It's icon. Seems to be mandatory.
    'icon-uri' => 'https://simbiat.ru/frontend/images/service/fftracker_icon.png',
    #Optional type of window to open in (tab, self or window). Defaults to tab.
    'window-type' => 'tab',
  ],
  #Optional separator (will draw a line in the menu). Just use 'separator' string.
  1 => 'separator',
  #Another task
  'nth' => [
    'name' => 'BICs Tracker',
    'action-uri' => 'https://simbiat.ru/bic/',
    'icon-uri' => 'https://simbiat.ru/frontend/images/service/bic_icon.png',
    'window-type' => 'tab',
  ],
]
```
`$notifications` - array of settings for notifications, URLs referencing XML files formatted as per https://docs.microsoft.com/en-us/uwp/schemas/tiles/tilesschema/schema-root
```php
[
  #List of up to 5 links
  'https://simbiat.ru/notification.xml',
  'https://simbiat.ru/notification2.xml',
  #Optional frequency for refreshing the data. Defaults to 1440
  'frequency' => '30',
  #Optional cycle type (as per specification). Defaults to 0 or 1 based on number of links
  'cycle' => 1,
]
```
`$xml` indicates whether you want to generate XML config file. `False` by default.

`$prettyDirect` if `$xml` is `false` this setting will govern whether a new line is added after each `<meta>` tag. If `$xml` is `true`, this setting will govern whether function will return a string or output the XML directly to browser.

## facebook
```php
facebook(int $appId, array $admins = []);
```
Simple function to prepare a string of Facebook meta tags.

`$appId` is mandatory application ID, that you want to link to the page.

`$admins` is an optional list of admin IDs. All non-numeric values will be removed and numeric ones will be converted to integers.
