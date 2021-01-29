# HTTP20
Set of classes/functions that may be universally useful for websites (or some parts of them, at least)

- [HTTP20](#http20)
  * [Atom](#atom)
  * [RSS](#rss)
  * [PrettyURL](#prettyurl)
  * [Common](#common)
    + [valueToTime](#valuetotime)
    + [atomIDGen](#atomidgen)
    + [zEcho](#zecho)
    + [emailValidator](#emailvalidator)
    + [uriValidator](#urivalidator)
    + [LangCodeCheck](#langcodecheck)
    + [htmlToRFC3986](#htmltorfc3986)

## Atom
```php
(new \http20\Atom)->Atom(string $title, array $entries, string $id = '', string $texttype = 'text', array $feed_settings = []);
```
Function to generate Atom feed as per https://validator.w3.org/feed/docs/atom.html specification.

`$title` - string that will be used for `<title>` tag in the feed.

`$id` - string, that will be used as `id`. It needs to be an URI, thus if it will be empty, will use `(isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']`, that is current address.

`$texttype` is the text type, that will be added as attribute to some of the tags as per specification. Supported types are `text`, `html`, `xhtml`.

`$feed_settings` - array with optional settings for the feed. Maximum will look like this:
```php
[
  'subtitle' => 'Latest deleted banks',
  'rights' => 'Copyrighted',
  'authors' => [
    [
      'name' => 'Dmitry Kustov',
      'email' => 'simbiat@outlook.com',
      'uri' => 'https://simbiat.ru/',
    ]
  ],
  'contributors' => [
    [
      'name' => 'Dmitry Kustov',
      'email' => 'simbiat@outlook.com',
      'uri' => 'https://simbiat.ru/',
    ]
  ],
  'icon' => '/frontend/images/favicons/simbiat.png',
  'logo' => '/frontend/images/ogimage.png',
  'categories' => [
    [
      'term' => 'BICs',
      'scheme' => '',
      'label' => '',
    ]
  ],
  'updated' => '2021-01-08 20:00',
  'links' => [
    [
      'href' => 'google.com',
      'rel' => 'alternate',
      'type' => 'text/html',
      'hreflang' => 'ru',
      'title' => 'google it',
    ]
   ],
]
```
For details on elements above (what they mean, recommendations, etc.), please, refer to Atom specification: names in `array` correspond to respective tags in feed.

`$entries` - array of elements (arrays), that will populate the feed. Mandatory value is `link`, instead of `id` as in specification, because `id` is expected to be an URI regardless (although it will be a modified one). `title` and `updated` are also mandatory. Maximum for each element will look like this:
```php
[
  'id' => '',
  'title' => 'Google it',
  'updated' => '2021-01-08 20:00',
  'link' => 'https://google.com',
  'author_name' => 'Dmitry Kustov',
  'author_email' => 'simbiat@outlook.com',
  'author_uri' => 'https://simbiat.ru/',
  'contributor_name' => 'Dmitry Kustov',
  'contributor_email' => 'simbiat@outlook.com',
  'contributor_uri' => 'https://simbiat.ru/',
  'content' => 'Just google it',
  'summary' => 'Search',
  'category' => 'Example',
  'published' => '2021-01-08 20:00',
  'rights' => 'Copyrighted',
  'source_id' => '',
  'source_title' => '',
  'source_updated' => '',
]
```
Since the code was designed with idea of entries taken from a database, only 1 `author`, `contributor` and `source` are supported and their respective values were 'flattened' (`author_name` and the like). If `published` is empty `updated` will be used instead of it. Otherwise, the parameters conform with Atom specifications.

## RSS
```php
(new \http20\RSS)->RSS(string $title, array $entries, string $feedlink = '', array $feed_settings = []);
```
Function to generate RSS feed as per https://www.rssboard.org/rss-specification specification. Function is designed similarly to Atom described above, with minor changes listed below. Otherwise - refer to feed specifications.

`id` in function call is renamed as `feedlink`, since as per specification feed does not have `id` but has `link` for similar purpose. At the same time elements also can have links, so `feedlink` name is used for clarity.

`feed_settings` at its maximum will look like this:
```php
[
  'description' => 'why are we even doing this?',
  'pubDate' => '2021-01-29',
  'lastBuildDate' => '2021-01-29',
  'language' => 'en-us',
  'copyright' => 'Copyrighted',
  'managingEditor' => 'simbiat@outlook.com',
  'webMaster' => 'simbiat@outlook.com',
  'cloud' =>
  [
    'domain' => 'test.com',
    'port' => '69',
    'path' => '/Life/Is/A/Street/',
    'registerProcedure' => 'register.php',
    'protocol' => 'SOAP',
  ],
  'ttl' => 60,
  'categories' =>
  [
    'php',
    'rss',
  ],
  'image' =>
  [
    'url' => '/image.png',
    'width' => 32,
    'height' => 32,
  ],
  'skipDays' =>
  [
    'Monday',
    'Sunday',
  ],
  'skipHours' =>
  [
    0,
    7,
    13,
    19,
  ],
]
```
Maximum for each element in `$entries` will look like this (either `title` or `description` should be present):
```php
[
  'title' => 'rss',
  'link' => 'gppgle.com',
  'description' => 'just some text',
  'author' => 'simbiat@outlook.com',
  'category' => 'php',
  'comments' => 'gppgle.com/comments.html',
  'pubDate' => '20201-01-29',
  'enclosure_url' => 'gppgle.com/test.mp3',
  'enclosure_length' => 1000,
  'enclosure_type' => 'audio/mp3',
]
```

## PrettyURL
```php
(new \http20\PrettyURL)->pretty(string $string, string $whitespace = '-', bool $urlsafe = true);
```
Function transliterates lots of characters and makes a safe and pretty URL. This is intended more as a SEO thing, rather than sanitization.

Includes majority (if not all) of diactrics (accented characters), cyrillic, hiragana, katakana, arabic, chinese characters. They are replaced by Latin characters or combinations that are representing how they are pronounced, officially romanized or the closest neighbours in terms of how a chartacer is drawn.
It also allows to replace whitespace characters with a chacarter of your choosing (hyphen as default).

If `$urlsafe` is set to `true`, some characters will be removed as well, because they can "break" the URL. Some of them are valid for an URI, but they are not good for SEO links.

## Common
Assortment of functions, that are used by classes inside the library, but can also be used directly. They are all called as
```php
(new \http20\Common)->nameOfFunction();
```

### valueToTime
```php
valueToTime($time, string $format, string $validregex = '')
```
This is more of a wrapper for `date()`. Integrated function expects an integer, but you may have a time, that is already a string in some format. This function tries to handle that. Furthermore, it supports validation after conversion using provided Regexp. This is useful for when you provided a formatted string, because depending on the string the output may be "corrupted" (simply not what you expected), and improper format may cause issues. Validating the result here, may help avoid that. You will have to provide your own Regexp, though, since currently only the one for `'c'` (ISO 8601 format) is built in. Feel free to create a push request to add other common cases.

### atomIDGen
```php
atomIDGen(string $link, $date = NULL);
```
Function to prepare ID for Atom feed as suggested on http://web.archive.org/web/20110514113830/http://diveintomark.org/archives/2004/05/28/howto-atom-id. Doubt it will be useful anywhere outside of Atom feeds, but still.

### zEcho
```php
zEcho(string $string);
```
A function for outputting data to web-browser while attempting to use compression, if available, and providing `Content-Length` header. In terms of compression, it will check whether `zlib` extension is loaded, then check if `zlib.output_compression` is `'On'`. If `zlib` is enabled, but compression is not enabled globally, it will use `ob_gzhandler` and add header, if not - just use the buffer and send the data. If `zlib` is not loaded, will not use compression, but will use buffer to provide proper header. The goal of the function is more standardization of the output, in case you have different settings on different environments for some reason.

### emailValidator
```php
uriValidator(string $string);
```
Simple boolean function to check that string is a proper e-mail address compliant with RFC 5322

### uriValidator
```php
emailValidator(string $string);
```
Simple boolean function to check that string is a proper URI compliant with RFC 3986

### LangCodeCheck
```php
LangCodeCheck(string $string);
```
Simple boolean function to check that string is a valid language code. I was not able to find a proper list beside the one meant for RSS, though. If you know of other codes, please, feel free to create a push request to update it.

### htmlToRFC3986
```php
htmlToRFC3986(string $string, bool $full = true);
```
Function does the same as `rawurlencode` (which converts characters to strings like `%20`), but only for selected characters, that are restricted in HTML/XML. Useful for URIs that can have these characters and need to be used in HTML/XML and thus can't use `htmlentities` for escaping but break HTML/XML otherwise.

`$full` set to `true` means that all of them (`'"&<>`) will be converted (useful when text is inside a tag). If `false` only `<` and `&` are converted (useful when inside attribute value). If `false` is used - be careful with quotes inside the string you provide, because they can invalidate your HTML/XML.
