- [valueToTime](#valuetotime)
- [atomIDGen](#atomidgen)
- [zEcho](#zecho)
- [LangCodeCheck](#langcodecheck)
- [htmlToRFC3986](#htmltorfc3986)
- [reductor](#reductor)
- [forceClose](#forceclose)

# Common

Assortment of functions, that are used by classes inside the library, but can also be used directly. They are all called as

```php
\Simbiat\HTTP20\Common::nameOfFunction();
```

## valueToTime

```php
valueToTime($time, string $format, string $validRegex = '')
```

This is more of a wrapper for `date()`. Integrated function expects an integer, but you may have a time, that is already a string in some format. This function tries to handle that. Furthermore, it supports validation after conversion using provided Regexp. This is useful for when you provided a formatted string, because depending on the string the output may be "corrupted" (simply not what you expected), and improper format may cause issues. Validating the result here, may help avoid that. You will have to provide your own Regexp, though, since currently only the one for `'c'` (ISO 8601 format) is built in. Feel free to create a push request to add other common cases.

## atomIDGen

```php
atomIDGen(string $link, $date = NULL);
```

Function to prepare ID for Atom feed as suggested on http://web.archive.org/web/20110514113830/http://diveintomark.org/archives/2004/05/28/howto-atom-id. Doubt it will be useful anywhere outside of Atom feeds, but still.

## zEcho

```php
zEcho(string $string, string $cacheStrat = '', bool $exit = true);
```

A function for outputting data to web-browser while attempting to use compression, if available, and providing `Content-Length` header. In terms of compression, it will check whether `brotli` or `zlib` extension is loaded, then check if `zlib.output_compression` is `'On'` (for `zlib`). If `zlib` is enabled, but compression is not enabled globally, it will use `ob_gzhandler` and add header, if not - just use the buffer and send the data. If `zlib` is not loaded, will not use compression, but will use buffer to provide proper header. The goal of the function is more standardization of the output, in case you have different settings on different environments for some reason.

`$cacheStrat` is an optional caching strategy to apply (as described for [cacheControl](Headers.md#cacheControl))

`$exit` allows to cancel automatic exit of the script (default), in case you want to do some more processing even after the page is pushed to client.

## LangCodeCheck

```php
LangCodeCheck(string $string);
```

Simple boolean function to check that string is a valid language code. I was not able to find a proper list beside the one meant for RSS, though. If you know of other codes, please, feel free to create a push request to update it.

## htmlToRFC3986

```php
htmlToRFC3986(string $string, bool $full = true);
```

Function does the same as `rawurlencode` (which converts characters to strings like `%20`), but only for selected characters, that are restricted in HTML/XML. Useful for URIs that can have these characters and need to be used in HTML/XML and thus can't use `htmlentities` for escaping but break HTML/XML otherwise.

`$full` set to `true` means that all of them (`'"&<>`) will be converted (useful when text is inside a tag). If `false` only `<` and `&` are converted (useful when inside attribute value). If `false` is used - be careful with quotes inside the string you provide, because they can invalidate your HTML/XML.

## reductor

```php
reductor($files, string $type, bool $minify = false, string $toFile = '', string $cacheStrat = '');
```

Function to merge CSS/JS files to reduce number of connections to your server, yet allow you to keep the files separate for easier development. It also allows you to minify the result for extra size saving, but be careful with that.

Minification is based on https://gist.github.com/Rodrigo54/93169db48194d470188f

`$files` can be a string, a path to file, a path to folder or an array of file paths.

`$type` can be anything, technically, but currently `css`, `js` or `html` are supported.

`$minify` trigger the minification if set to `true`. It's set to `false` by default, because minification is known to cause some issues, especially with HTML, so you need to be careful with this.

`$toFile` allows to output the data to a file, instead of to browser. Useful if you do not want to do this dynamically, but would rather prepare the files beforehand.

`$cacheStrat` is an optional caching strategy to apply (as described for [cacheControl](Headers.md#cachecontrol))

`$exit` if set to `true` will exit the script, if to `false` - return an `int` representing the HTTP status code, unless text, font or some image/application MIME types is encountered: in this case `zEcho` will be used, which will exit the code regardless.

## forceClose

```php
forceClose();
```

Function to force close HTTP connection. Sounds simple, but it may actually become a problem, if the client is actively sending data to you. Trick is simple, too (just flush the buffer), but using this function will help you not bother thinking about it.
