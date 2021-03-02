# HTTP20
Set of classes/functions that may be universally useful for websites (or some parts of them, at least).

I hope that at some point in future `Headers` part will become quite popular. While some frameworks do seem to implement some similar functionality it seems to be generally ignored, while HTTP headers can make user experience better and more secure. If you start using `Headers` (especially `security`) it will (by default) force you into following best practices when designing your website. And if you need to loosen up your security for some reason, it is possible in a relatively convinient way. And if you find that it isn't (like you need to use `unsafe` directives, for example), most likely you are trying to save a security hole, that you should not be saving.

- [HTTP20](#http20)
  * [Atom](#atom)
  * [RSS](#rss)
  * [Sitemap](#sitemap)
  * [PrettyURL](#prettyurl)
  * [Sharing](#sharing)
    + [download](#download)
    + [upload](#upload)
    + [streamCopy](#streamcopy)
    + [speedLimit](#speedlimit)
    + [phpMemoryToInt](#phpmemorytoint)
    + [rangesValidate](#rangesvalidate)
  * [Headers](#headers)
    + [cacheControl](#cachecontrol)
    + [eTag](#etag)
    + [lastModified](#lastmodified)
    + [performance](#performance)
    + [security](#security)
    + [features](#features)
    + [secFetch](#secFetch)
    + [clientReturn](#clientreturn)
    + [links](#links)
  * [Common](#common)
    + [valueToTime](#valuetotime)
    + [atomIDGen](#atomidgen)
    + [zEcho](#zecho)
    + [emailValidator](#emailvalidator)
    + [uriValidator](#urivalidator)
    + [LangCodeCheck](#langcodecheck)
    + [htmlToRFC3986](#htmltorfc3986)
    + [reductor](#reductor)
    + [forceClose](#forceclose)

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

## Sitemap
```php
sitemap(array $links, string $format = 'xml', bool $directOutput = false)
```
Function to generate sitemape in XML, HTML or text formats. For XML specifications refer to https://www.sitemaps.org/protocol.html. Besids some useful checks (see below) it will try to output as much as possible from the list provided, but so that the size will be no more than 50MBs.

`$links` - array of assotiative arrays. Maximum for each element will look like this:
```php
[
  #Loc is mandatory for all entries. All entries wil also be checked for relation to same schema and domain. Duplicats will be removed
  'loc' => 'https://example.com',
  'name' => 'optional name used only for HTML sitemaps',
  #Lastmod will be used to calculate the freshest entry in the list and it will be sent out to [lastModified](#lastmodified) function. That will allow earlier exit. Using numeric values is recommended.
  'lastmod' => '20210302'
  'changefreq' => 'change frequency as per specification'
  'priority' => 'priority as per specification'
]
```
`$format` - selector for the format. XML, HTML and plain text (`txt` or `text`) are supported. If you need index of sitemaps, use `index`. HTML format will provide output like this:
```html
<p><a class="sitemaplink" id="sitemaplink_%id%" href="%loc%" target="_blank">%name_or_loc%</a></p>
```
`<p>` is used to provide human-readable output. Use of CSS is advisable to properly style it.

`$directOutput` - if set to `true` will send the generated string directly to client using [zEcho](#zecho) and also send appropriate `Content-Type` header.

## PrettyURL
```php
(new \http20\PrettyURL)->pretty(string $string, string $whitespace = '-', bool $urlsafe = true);
```
Function transliterates lots of characters and makes a safe and pretty URL. This is intended more as a SEO thing, rather than sanitization.

Includes majority (if not all) of diactrics (accented characters), cyrillic, hiragana, katakana, arabic, chinese characters. They are replaced by Latin characters or combinations that are representing how they are pronounced, officially romanized or the closest neighbours in terms of how a chartacer is drawn.
It also allows to replace whitespace characters with a chacarter of your choosing (hyphen as default).

If `$urlsafe` is set to `true`, some characters will be removed as well, because they can "break" the URL. Some of them are valid for an URI, but they are not good for SEO links.

## Sharing
Function that can be used in processes related to file sharing.
```php
(new \http20\Sharing)->nameOfFunction();
```
### download
```php
download(string $file, string $filename = '', string $mime = '', bool $inline = false, int $speedlimit = 10485760, bool $exit = true);
```
Function to download files (or more precisely, feed them to client). Unlike other functions, that can be found, this one can:
- Send proper headers both in positive and negative situations
- Determine MIME type of the file based on extension, yet allow overriding both file name and MIME
- Allow feeding file "inline" instead of as attachment
- Limit speed, but in a way, that will limit the chances of exceeding allocated memory on server in a smart way

`$file` - path to file.

`$filename` - optional override for file name, if you want to provide a file with a different name.

`$inline` - if set to `true` will feed the file "inline", as regular images are sent (for example). It is unlikely you will want to use it like that, but it may be useful if you want to stream a video/audio/image/other file like that and then do something once it's shown. Note, though, that some browsers may start downloading such content using `Range`.

`$speedlimit` - the maximum of bytes you want to send per second. Default is 10MBs. Note, that if it's too large it will be overriden by internal logic (`speedLimit()`).

`$exit` - if set to `false` will not automatically exit once a file/range or a "bad" header is sent to client. It then will return a `false` or `true` value, that you can utilize somehow.

While this function can return the number of bytes, that may be useful for some statistics, I'd recommend not using it as confirmation of successful file download, because it is not possible to reliably track client success on server without some scripting on client side.

### upload
```php
upload($destPath, bool $preserveNames = false, bool $overwrite = false, array $allowedMime = [], bool $intollerant = true, bool $exit = true);
```
Function to handle uploads. It's not fancy as https://tus.io/, but if you need hadnling some simple file uploads, this still can provide you useful features:
- Send proper headers both in positive and negative situations
- Support both POST and PUT methods
- Sanitize filenames if you have https://github.com/Simbiat/filename-sanitizer (PUT supports names from `Content-disposition` header)
- Do not actually save the files with those names: hash them instead
- Sanitized names are not discarded: they are returned as one of the values of the array, after the upload
- MIME type of the files is determined after upload is completed
- For POST method you can use multiple forms and they can even be uploading multiple files
- PUT method somewhat supports resumable uploads

`$destPath` is the only setting, that is mandatory and it can be a `string` or a named `array`, where each element of the array is equal to the name of the field (`<input type="file"></input>`) in your web-form (as element key) and the path to save files from that field to. Both `string` value and each value of the `array` are expected to be existing and writable directories (no auto-creation, since this may be abused). Example of the array is below:
```php
[
   'avatar' => './upload/avatar',
   'background' => './upload/background',
]
```
`$preserveNames` (only for POST) is set to false by default to replace names of the files with their hash + extension, based on actual MIME type (if we were able to grab it). This is done to avoid potential exploits, that may arise depending on how you use the fiels afterwards.

`$overwrite` (only for POST) allows to overwrite files, if set to `true`. Otherwise - they will be ignored (but still will be returned in the resulting array).

`$allowedMime` - optional array of MIME types, that you accept for upload.

`$intollerant` (only for POST) changes behaviour in case of failures during multiple files upload. If set to `true` (by default), if an error is encountered with any of the file - further processing will be aborted. If issues are encountered on checks, this will essentially discard any uploads. If it's encountered during moving of the uploaded files, list of files that were successfully processed will still be returned (or an empty array).

`$exit` - if set to `false` will not automatically exit once a file/range or a "bad" header is sent to client. It then will return a value, that you can utilize somehow.

In case a file has been uploaded, you will get an array like below. It is useful, if you are going to store the data in database: add any additional data, generate a query - write it to database. Be sure to set `$exit` to `false` though.
```php
[
  0 => 
  [
    #Name of the file, that it recieved on server
    'server_name' => 'bb345ba5253d677fe6bac0d553040ca62faa02347d316f30bc436009543c5d92.jpg',
    #Name of the file as provided by client. For PUT it will be empty, unless provided in Content-Dsiposition header
    'user_name' => 'WodFws_Awfc.jpg',
    #Size of the file. For POST - as seen on server. For PUT - as provided in Content-Length header
    'size' => 945001,
    #File MIME type
    'type' => 'image/jpeg',
    #sha3-256 hash of the file
    'hash' => 'bb345ba5253d677fe6bac0d553040ca62faa02347d316f30bc436009543c5d92',
    #Name of the field to which file was uploaded. For PUT will always be 'PUT'
    'field' => 'userfile',
  ],
]
```
If you're going to use PUT, there are some peculiarities, that you need to be aware of:
- You need to send `Content-Length` header: it's used to determine proper end of input stream
- Upload will be considered `resumable` if there is a header `Content-Disposition` with `filename` or `filename*` parameter
- `resumable` upload will utilize filename from the header to be saved, but it will not be preserved, when upload it successful
- To avoid potential collisions during multiple PUT uploads it will be up to you to send unique names
- Already saved portion of the `resumable` file will still be uploaded, but to a temp stream, that will be discarded before the stream will be continued into a previously saved file
- MIME type is checked twice: firstly using `Content-Type` header, if it's present, secondly after the upload is finished
- If MIME type check against allowed types fails after the upload file will be removed

### streamCopy
```php
streamCopy(&$input, &$output, int $totalsize = 0, int $offset = 0, int $speed = 10485760);
```
Function to copy data in small chunks (not HTTP1.1 chunks) with speed limitation from one stream to another. In essense, this is `stream_copy_to_stream`, but with said speed limitation. Example of usage is the above mentioned `download` function.

`&$input` and `&$output` - thease are resources (generally created from `fopen`), from which you will read and to which you will write.

`$totalsize` - bytes to copy.

`$offset` - where to start copying from.

`$speed` - the maximum of bytes you want to copy per second. Default is 10MBs. Note, that if it's too large it will be overriden by internal logic (`speedLimit()`).

### speedLimit
```php
speedLimit(int $speed = 0, float $percentage = 0.9);
```
Function calculates maximum number of bytes that can be allocated for streaming (or similar functions) based on memory limit and currently available memory. In case of streaming, using more bytes can result in memory exceptions, that you would want to avoid, if possible.

`$speed` - the desired "speed limit". If it's less than calculated value, it will be returned.

`$percentage` - percent of available memory, that we can use. For example, if we have 256M as memory limit and 200M available, 0.9 will allow us to use 180M. Default was experimentally derived from downloading a 1.5G file with 256M memory limit until there was no "Allowed memory size of X bytes exhausted". Actually it was 0.94, but we would prefer to have at least some headroom.

### phpMemoryToInt
```php
phpMemoryToInt(string $memory);
```
Converts PHP's memory strings (like 256M) used in some settings to integer value (bytes).

### rangesValidate
```php
rangesValidate(int $size);
```
Function to validate `Range` request header (https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Range). It is mandatory to provide it with the `$size` of the file in bytes, because one of the conditions for "bad" range is that start or end of the range is after the last byte in the file. The most important thing here is checking for overlapping ranges, which should not happen as per as per https://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html.

It will always return an array. If there was a "bad" range, though it will not be empty (which is considered a valid value), but rather `[0 => false]`, that is an array with 1 index valued as `false`. Be careful with this when validating the output.

I can't think of a case, when this can be used outside of `download` function, except for custom version of it, but keeping this separate if there is one.

## Headers
Functions that send/handle different HTTP headers.
```php
(new \http20\Headers)->nameOfFunction();
```

### cacheControl
```php
cacheControl(string $string, string $cacheStrat = '', bool $exit = false);
```
Allows you to send appropriate `Cache-Control` headers (refer to https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control for explanation on parameters):
```php
switch (strtolower($cacheStrat)) {
	case 'aggressive':
	    header('Cache-Control: max-age=31536000, immutable, no-transform');
	    break;
	case 'private':
	    header('Cache-Control: private, no-cache, no-store, no-transform');
	    break;
	case 'live':
	    header('Cache-Control: no-cache, no-transform');
	    break;
	case 'month':
	    #28 days to be more precise
	    header('Cache-Control: max-age=2419200, must-revalidate, stale-while-revalidate=86400, stale-if-error=86400, no-transform');
	    break;
	case 'week':
	    header('Cache-Control: max-age=604800, must-revalidate, stale-while-revalidate=86400, stale-if-error=86400, no-transform');
	    break;
	case 'day':
	    header('Cache-Control: max-age=86400, must-revalidate, stale-while-revalidate=86400, stale-if-error=86400, no-transform');
	    break;
	case 'hour':
	    header('Cache-Control: max-age=3600, must-revalidate, stale-while-revalidate=86400, stale-if-error=86400, no-transform');
	    break;
}
```
ETag procesing using `eTag()` will happen regardless (unless string is empty: then it will simply make no sense).

`$exit` if set to `true` will exit the script right after HTTP 304 is sent (that is we hit the cache).

### eTag
```php
eTag(string $etag)
```
Sends ETag header and handles its validation depending on requesting headers (If-Match, If-None-Match).

### lastModified
```php
lastModified(int $modtime = 0, bool $exit = false);
```
Sends Last-Modified header based on either parameter provided or the freshest date of all the script files used to generate a page. Also handles HTTP_IF_MODIFIED_SINCE header from client, if it was sent, allowing for some performance improvement if cache can be used.

`$exit` if set to `true` will exit the script right after HTTP 304 is sent (that is we hit the cache).

### performance
```php
performance(int $keepalive = 0, array $clientHints = []);
```
Sends some headers that may improve performance on client side.

`$keepalive` is used for `Keep-Alive` header governing how long the connection should stay up. Header will be sent only if server is using HTTP version other than 2.0.

`$clientHints` instructs clients, that your server supports Client Hints (https://developer.mozilla.org/en-US/docs/Glossary/Client_hints) like DPR, Width, Viewport-Width, Downlink, .etc and client should cache the output accordingly, in order to increase allow cache hitting and thus improve performance.

### security
```php
security(string $strat = 'strict', array $allowOrigins = [], array $exposeHeaders = [], array $allowHeaders = [], array $allowMethods = [], array $cspDirectives = [], bool $reportonly = false);
```
Sends headers that can improve security of your page.

`$stract` allows to select one fo 3 strategies for CORS headers (https://developer.mozilla.org/ru/docs/Web/HTTP/CORS). `strict` is default, because security is king.
```php
case 'mild':
	header('Cross-Origin-Embedder-Policy: unsafe-none');
	header('Cross-Origin-Embedder-Policy: same-origin-allow-popups');
	header('Cross-Origin-Resource-Policy: same-site');
	header('Referrer-Policy: strict-origin');
	break;
case 'loose':
	header('Cross-Origin-Embedder-Policy: unsafe-none');
	header('Cross-Origin-Opener-Policy: unsafe-none');
	header('Cross-Origin-Resource-Policy: cross-origin');
	header('Referrer-Policy: strict-origin-when-cross-origin');
	break;
case 'strict':
default:
	header('Cross-Origin-Embedder-Policy: require-corp');
	header('Cross-Origin-Opener-Policy: same-origin');
	header('Cross-Origin-Resource-Policy: same-origin');
	header('Referrer-Policy: no-referrer');
	break;
```
`$allowOrigins` (https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Origin) allows you to set a list of allowed `Origin` values, that can access your page. If empty - will allow access to all (`*`).

`Access-Control-Allow-Origin` allows only 1 values by specification, but `$allowOrigins` allows to overcome it by doing validation against the list you've provided. Then, if the origin is allowed - access will be granted, if not - 403 will be sent and code will exit.

`$exposeHeaders` (https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Expose-Headers) allows you to set a list of headers, that you are ok to expose to client. Headers, that are provided by this class, will always be exposed.

`$allowHeaders` (https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Headers) lets you add headers, that you willing to accept and use to change states in your code.

`$allowMethods` (https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Methods) allows you to restrict accepted methods. If request to page is done by a method not in the list - it will be rejected (405). By default GET, POST and HEAD are the only allowed.

`$cspDirectives` (https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy) allows you to provide a list of directives and their settings (with validation) to control CSP headers. By default, essentially eveyrthing is either disabled or allowed only from `self`, which give you a solid base in terms of restricting access.

`$reportonly` allows you to control, whether you only report (`Content-Security-Policy-Report-Only`) CPS violations or report **and** block them. Be default it's set as `false` for security enforcement. Note, that if it's set to `true`, but you do not provide `report-to` directive **no** CSP header will be sent, reducing your security. For that reason, if you do want to report, I can suggest using https://rapidsec.com/ which is free. Also note, that while `report-uri` is **temporary** added until `report-to` is supported by all browsers, `report-uri` **will be discarded** if it's provided without `report-to` to encourage the use of a modern directive.

### features
```php
features(array $features = [], bool $forcecheck = true);
```
Allows to control different features through Feature-Policy header.

`$features` expectes assotiative array, where each key is name of the policy in lower case and value - expected `allow list`. If an empty array is sent default values will be applied (most features are disabled).

`$forcecheck` is added for futureproofing, but is enabled by default. If set to `true` will check if the feature is "supported" (present in default array) and value complies with the standard. Setting it to `false` will allow you to utilize a feature or value not yet supported by the library.

### secFetch
```php
secFetch(array $site = [], array $mode = [], array $user = [], array $dest = [], bool $strict = false);
```
Allows validation of Sec-Fetch-* headers from client against the provided list of allowed values. Each of the 4 `array` values represent a list of values of respective Sec-Fetch-* header, which you allow to be processed. For more information refer https://www.w3.org/TR/fetch-metadata/

`$strict` allows to enforce compliance with suported values only. Current W3C allows ignoring headers, if not sent or have unsupported values, but we may want to be stricter by setting this option to `true`.

**Be mindful**: unlike `security`, which, essentially, attempts to be as secure as possible by default, this may be too lax for some use-cases. It is recommended, that you you call it with different parameters depending on what is calling what on your server. For example, you may want to restrict certain code getting called with `Sec-Fetch-Destination: image`, especially, if it's a `POST` request, let alone `DELETE`. Thus the best way to use this is in some `switch` or `if-elseif-else` scenario, rather than universally.

### clientReturn
```php
clientReturn(string $code = '500', bool $exit = true);
```
Returns a selected HTTP status code (defaults to 500) with option to forcibly close HTTP connection (`$exit = true`). This is mostly useful for returnring error codes, especially, when you want to close the connection, even if the client is still sending something, thus the default values are `500` and `true`.

### links
```php
links(array $links = [], string $type = 'header', bool $strictRel = true);
```
Function to generate a set of `Link` elements either for HTTP header or HTML. If you are serving a web-page, it may be beneficial to send both HTTP header and the HTML tags to support wider variety of clients.

`$links` - is an array of arrays, where each subarray describes the `Link` element. At the least you require a `href` element, but for a fuller description, please, refer to https://developer.mozilla.org/en-US/docs/Web/HTML/Element/link. Note that, in case some attribute is missing a link can be skipped depending on values of toher attributes.

`$type` - the type of `Link` elements to generate. Default is `header`, meaning, that a single HTTP header `Link:` will be generated. The other supported values are `head` and `body`, which differ only in one extra check for `body`, which checks if a link is considered `body-ok`, but works only with `$strictRel` set to `true.

`$strictRel` - if set to `true`, if `rel` attribute is set it will be checked against a list based on https://html.spec.whatwg.org/multipage/links.html#linkTypes and https://microformats.org/wiki/existing-rel-values#formats, meaning against `rel` values, that have to be supported by clients. If you are using something "special", set this to `false`. Personally, in such cases, I would recommend splitting the set of `Link` elements you have into 2 sets: standard and non-standard.

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
zEcho(string $string, string $cacheStrat = '');
```
A function for outputting data to web-browser while attempting to use compression, if available, and providing `Content-Length` header. In terms of compression, it will check whether `zlib` extension is loaded, then check if `zlib.output_compression` is `'On'`. If `zlib` is enabled, but compression is not enabled globally, it will use `ob_gzhandler` and add header, if not - just use the buffer and send the data. If `zlib` is not loaded, will not use compression, but will use buffer to provide proper header. The goal of the function is more standardization of the output, in case you have different settings on different environments for some reason.

`$cacheStrat` is an optional caching strategy to apply (as described for `cacheControl`)

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

### reductor
```php
reductor($files, string $type, bool $minify = false, string $tofile = '', string $cacheStrat = '');
```
Function to merge CSS/JS files to reduce number of connections to your server, yet allow you to keep the files separate for easier development. It also allows you to minify the result for extra size saving, but be careful with that.

Minification is based on https://gist.github.com/Rodrigo54/93169db48194d470188f

`$files` can be a string, a path to file, a path to folder or an array of filepaths.

`$type` can be anything, technically, but currently `css`, `js` or `html` are supported.

`$minify` trigger the minification if set to `true`. It's set to `false` by default, because minifcation is known to cause some issues, especially with HTML, so you need to be careful with this.

`$tofile` allows to output the data to a file, instead of to browser. Useful if you do not want to do this dynamically, but would rather prepare the files beforehand.

`$cacheStrat` is an optional caching strategy to apply (as described for `cacheControl`)

### forceClose
```php
forceClose();
```
Function to force close HTTP connection. Sounds simple, but it may actually become a problem, if the client is actively sending data to you. Trick is simple, too (just flush the buffer), but using this function will help you not bother thinking about it.