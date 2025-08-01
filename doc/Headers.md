- [cacheControl](#cachecontrol)
- [eTag](#etag)
- [lastModified](#lastmodified)
- [performance](#performance)
- [security](#security)
- [contentPolicy](#contentpolicy)
- [features](#features)
- [secFetch](#secFetch)
- [clientReturn](#clientreturn)
- [redirect](#redirect)
- [notAccept](#notaccept)
- [multiPartFormParse](#multiPartFormParse)

# Headers

Functions that send/handle different HTTP headers.

I hope that at some point this will become popular. While some frameworks do seem to implement some similar functionality it seems to be generally ignored, while HTTP headers can make user experience better and more secure. If you start using `Headers` (especially `security`) it will (by default) force you into following best practices when designing your website. If you need to loosen up your security for some reason, it is possible in a relatively convenient way. If you find that it isn't (like you need to use `unsafe` directives, for example), most likely you are trying to save a security hole, that you should not be saving.

```php
\Simbiat\http20\Headers::nameOfFunction();
```

## cacheControl

```php
cacheControl(string $string, string $cache_strategy = '', bool $exit = false, string $postfix ='');
```

Allows you to send appropriate `Cache-Control` headers (refer to https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control for explanation on parameters):

```php
switch (mb_strtolower($cache_strategy, 'UTF-8')) {
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

ETag processing using `eTag()` will happen regardless (unless string is empty: then it will simply make no sense).

`$exit` if set to `true` will exit the script right after HTTP 304 is sent (that is we hit the cache).

`$postfix` is an optional string to add to `eTag` string and used mainly for [zEcho](Common.md#zEcho), to comply with recommendations when using compression.

## eTag

```php
eTag(string $etag, bool $exit = false);
```

Sends ETag header and handles its validation depending on requesting headers (If-Match, If-None-Match).

`$exit` if set to `true` will exit the script right after HTTP 304 is sent (that is we hit the cache).

## lastModified

```php
lastModified(int $mod_time = 0, bool $exit = false);
```

Sends Last-Modified header based on either provided parameter, or the freshest date of all the script files used to generate a page. Also handles HTTP_IF_MODIFIED_SINCE header from client, if it was sent, allowing for some performance improvement if cache can be used.

`$exit` if set to `true` will exit the script right after HTTP 304 is sent (that is we hit the cache).

## performance

```php
performance(int $keepalive = 0, array $client_hints = []);
```

Sends some headers that may improve performance on client side.

`$keepalive` is used for `Keep-Alive` header governing how long the connection should stay up. Header will be sent only if server is using HTTP version other than 2.0.

`$client_hints` instructs clients, that your server supports Client Hints (https://developer.mozilla.org/en-US/docs/Glossary/Client_hints) like DPR, Width, Viewport-Width, Downlink, etc. and client should cache the output accordingly, in order to increase allow cache hitting and thus improve performance.

## security

```php
security(string $strat = 'strict', array $allow_origins = [], array $expose_headers = [], array $allow_headers = [], array $allow_methods = []);
```

Sends headers that can improve security of your page.

`$strat` allows to select one fo 3 strategies for CORS headers (https://developer.mozilla.org/ru/docs/Web/HTTP/CORS). `strict` is default, because security is king.

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

`$allow_origins` (https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Origin) allows you to set a list of allowed `Origin` values, that can access your page. If empty - will allow access to all (`*`).

`Access-Control-Allow-Origin` allows only 1 values by specification, but `$allow_origins` allows to overcome it by doing validation against the list you've provided. Then, if the origin is allowed - access will be granted, if not - 403 will be sent and code will exit.

`$expose_headers` (https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Expose-Headers) allows you to set a list of headers, that you are ok to expose to client. Headers, that are provided by this class, will always be exposed.

`$allow_headers` (https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Headers) lets you add headers, that you are willing to accept and use to change states in your code.

`$allow_methods` (https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Methods) allows you to restrict accepted methods. If request to page is done by a method not in the list - it will be rejected (405). By default, GET, POST and HEAD are the only allowed.

## contentPolicy

```php
contentPolicy(array $csp_directives = [], bool $report_only = false, bool $report_uri = false);
```

Sends Content-Security-Policy header, that improves your page security. It's done separately from other security stuff, because unlike the rest of the headers this is usable only for HTML.

`$csp_directives` (https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy) allows you to provide a list of directives and their settings (with validation) to control CSP headers. By default, essentially everything is either disabled or allowed only from `self`, which give you a solid base in terms of restricting access.

`$report_only` allows you to control, whether you only report (`Content-Security-Policy-Report-Only`) CPS violations or report **and** block them. Be default it's set as `false` for security enforcement. Note, that if it's set to `true`, but you do not provide `report-to` directive **no** CSP header will be sent, reducing your security. For that reason, if you do want to report, I can suggest using https://rapidsec.com/ which is free. Also note, that while `report-uri` is **temporary** added until `report-to` is supported by all browsers, `report-uri` **will be discarded** if it's provided without `report-to` to encourage the use of a modern directive.

`$report_uri` will add `report-uri` in the headers as well. It is deprecated, thus defaults to `false`, but if you want to support still - you can.

## features

```php
features(array $features = [], bool $force_check = true, bool $permissions = false);
```

Allows controlling different features through `Feature-Policy` header. It should only be used, when sending HTML.

`$features` expects associative array, where each key is name of the policy in lower case and value - expected `allow list`. If an empty array is sent, default values will be applied (most features are disabled).

`$force_check` is added for futureproofing, but is enabled by default. If set to `true` will check if the feature is "supported" (present in default array) and value complies with the standard. Setting it to `false` will allow you to utilize a feature or value not yet supported by the library.

`$permissions` is a flag to toggle `Permissions-Policy`, which is replacement for `Feature-Policy` header. Alternatively you can use `features(array $features = [], bool $force_check = true)` signature, which will call `features` internally.

## secFetch

```php
secFetch(array $site = [], array $mode = [], array $user = [], array $dest = [], bool $strict = true);
```

Allows validation of Sec-Fetch-* headers from client against the provided list of allowed values. Each of the 4 `array` values represent a list of values of respective Sec-Fetch-* header, which you allow to be processed. For more information refer https://www.w3.org/TR/fetch-metadata/

`$strict` allows enforcing compliance with supported values only. Current W3C allows ignoring headers, but we want to be stricter so setting this to `true` by default. You may want to set to `false` to in situations, when your site is parsed by certain bots (including those from search engines), since they usually do not set this value.

**Be mindful**: unlike `security`, which, essentially, attempts to be as secure as possible by default, this may be too lax for some use-cases. It is recommended, that you call it with different parameters depending on what is calling what on your server. For example, you may want to restrict certain code getting called with `Sec-Fetch-Destination: image`, especially, if it's a `POST` request, let alone `DELETE`. Thus, the best way to use this is in some `switch` or `if-elseif-else` scenario, rather than universally.

## clientReturn

```php
clientReturn(string $code = '500', bool $exit = true);
```

Returns a selected HTTP status code (defaults to 500) with option to forcibly close HTTP connection (`$exit = true`). This is mostly useful for returning error codes, especially, when you want to close the connection, even if the client is still sending something, thus the default values are `500` and `true`.

## redirect

```php
redirect(string $new_uri, bool $permanent = true, bool $preserve_method = true, bool $force_get = false)
```

Function to allow redirects.

`$new_uri` is the URL, that will be used in `Location` header. If it fails validation as URI, instead of redirect a 500 error will be sent to client.

`$permanent` governs whether this is a permanent redirect or a temporary one. Permanent redirects tell browser to always use the new address, while temporary - only this time. `true` by default.

`$preserve_method` governs whether method is allowed to be changed when redirecting. Historically 301 and 302 redirects does not restrict client in this regard, which may cause some issues sometimes. If set to `true` (default) 307 and 308 codes will be used.

`$force_get` is a flag to use 303 code. If you want client to specifically change method to GET when redirecting, you can use this one. Useful, if after POST or PUT you want to show output, that is handled by some other page.

## notaccept

```php
notAccept(array $supported = ['text/html'], bool $exit = true)
```

This is quite niche, but still may be useful in some cases. If you provide several formats of the data (for example, JSON and XML) and you want client to negotiate the format that the client will receive, you can use this function.

Client will send standard `Accept` HTTP header with list of acceptable MIME types, this function will check if any of the MIME types your backend provides is in the list and the use the one with the highest priority. That is, if client will accept both JSON and XML, but JSON will have priority 0.9 and XML - 0.8, function will return `application/json`, to let you know, that you should provide the data in JSON format.

If none of your supported MIME types match `Accept` header, `406` header will be returned to client. If header is not provided by client, function will return `true`. It will also return `true`, if client provides `*/*` MIME type. This is why it will be niche: most browsers are sending it in their `Accept` headers. If you are using some custom API - this may be useful, still.

`$supported` - array of MIME types you support.

`$exit` if set to `true` will exit the script right after HTTP 406 is sent, otherwise will return `false`.

## multiPartFormParse

```php
multiPartFormParse()
```

This function parses `multipart/form-data` data for PUT, DELETE and PATCH methods and dumps the result as associative array to respective static variables `$_PUT`, `$_DELETE` and `$_PATCH` (accessed as `\Simbiat\http20\Headers::$_PUT`).

The same logic can technically be used for POST as well, but PHP already parses it into native `$_POST`, so need to cover it.

Other HTTP verbs are not supposed to be accompanied by form data like this.