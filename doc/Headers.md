- [cacheControl](#cachecontrol)
- [eTag](#etag)
- [lastModified](#lastmodified)
- [performance](#performance)
- [security](#security)
- [contentPolicy](#contentpolicy)
- [features](#features)
- [secFetch](#secFetch)
- [clientReturn](#clientreturn)
- [links](#links)
- [redirect](#redirect)
- [notAccept](#notaccept)

# Headers
Functions that send/handle different HTTP headers.  
I hope that at some point this will become popular. While some frameworks do seem to implement some similar functionality it seems to be generally ignored, while HTTP headers can make user experience better and more secure. If you start using `Headers` (especially `security`) it will (by default) force you into following best practices when designing your website. And if you need to loosen up your security for some reason, it is possible in a relatively convinient way. And if you find that it isn't (like you need to use `unsafe` directives, for example), most likely you are trying to save a security hole, that you should not be saving.

```php
(new \Simbiat\http20\Headers)->nameOfFunction();
```

## cacheControl
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

## eTag
```php
eTag(string $etag, bool $exit = false);
```
Sends ETag header and handles its validation depending on requesting headers (If-Match, If-None-Match).  
`$exit` if set to `true` will exit the script right after HTTP 304 is sent (that is we hit the cache).

## lastModified
```php
lastModified(int $modtime = 0, bool $exit = false);
```
Sends Last-Modified header based on either parameter provided or the freshest date of all the script files used to generate a page. Also handles HTTP_IF_MODIFIED_SINCE header from client, if it was sent, allowing for some performance improvement if cache can be used.  
`$exit` if set to `true` will exit the script right after HTTP 304 is sent (that is we hit the cache).

## performance
```php
performance(int $keepalive = 0, array $clientHints = []);
```
Sends some headers that may improve performance on client side.  
`$keepalive` is used for `Keep-Alive` header governing how long the connection should stay up. Header will be sent only if server is using HTTP version other than 2.0.  
`$clientHints` instructs clients, that your server supports Client Hints (https://developer.mozilla.org/en-US/docs/Glossary/Client_hints) like DPR, Width, Viewport-Width, Downlink, .etc and client should cache the output accordingly, in order to increase allow cache hitting and thus improve performance.

## security
```php
security(string $strat = 'strict', array $allowOrigins = [], array $exposeHeaders = [], array $allowHeaders = [], array $allowMethods = []);
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

## contentPolicy
```php
contentPolicy(array $cspDirectives = [], bool $reportonly = false);
```
Sends Content-Security-Policy header, that improves your page security. It's done separately from other security stuff, because unlike the rest of the headers this is usable only for HTML.  
`$cspDirectives` (https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy) allows you to provide a list of directives and their settings (with validation) to control CSP headers. By default, essentially eveyrthing is either disabled or allowed only from `self`, which give you a solid base in terms of restricting access.  
`$reportonly` allows you to control, whether you only report (`Content-Security-Policy-Report-Only`) CPS violations or report **and** block them. Be default it's set as `false` for security enforcement. Note, that if it's set to `true`, but you do not provide `report-to` directive **no** CSP header will be sent, reducing your security. For that reason, if you do want to report, I can suggest using https://rapidsec.com/ which is free. Also note, that while `report-uri` is **temporary** added until `report-to` is supported by all browsers, `report-uri` **will be discarded** if it's provided without `report-to` to encourage the use of a modern directive.

## features
```php
features(array $features = [], bool $forcecheck = true);
```
Allows to control different features through Feature-Policy header. It should only be used, when sending HTML.  
`$features` expectes assotiative array, where each key is name of the policy in lower case and value - expected `allow list`. If an empty array is sent default values will be applied (most features are disabled).  
`$forcecheck` is added for futureproofing, but is enabled by default. If set to `true` will check if the feature is "supported" (present in default array) and value complies with the standard. Setting it to `false` will allow you to utilize a feature or value not yet supported by the library.

## secFetch
```php
secFetch(array $site = [], array $mode = [], array $user = [], array $dest = [], bool $strict = false);
```
Allows validation of Sec-Fetch-* headers from client against the provided list of allowed values. Each of the 4 `array` values represent a list of values of respective Sec-Fetch-* header, which you allow to be processed. For more information refer https://www.w3.org/TR/fetch-metadata/  
`$strict` allows to enforce compliance with suported values only. Current W3C allows ignoring headers, if not sent or have unsupported values, but we may want to be stricter by setting this option to `true`.  
**Be mindful**: unlike `security`, which, essentially, attempts to be as secure as possible by default, this may be too lax for some use-cases. It is recommended, that you you call it with different parameters depending on what is calling what on your server. For example, you may want to restrict certain code getting called with `Sec-Fetch-Destination: image`, especially, if it's a `POST` request, let alone `DELETE`. Thus the best way to use this is in some `switch` or `if-elseif-else` scenario, rather than universally.

## clientReturn
```php
clientReturn(string $code = '500', bool $exit = true);
```
Returns a selected HTTP status code (defaults to 500) with option to forcibly close HTTP connection (`$exit = true`). This is mostly useful for returnring error codes, especially, when you want to close the connection, even if the client is still sending something, thus the default values are `500` and `true`.

## links
```php
links(array $links = [], string $type = 'header', bool $strictRel = true);
```
Function to generate a set of `Link` elements either for HTTP header or HTML. If you are serving a web-page, it may be beneficial to send both HTTP header and the HTML tags to support wider variety of clients.  
`$links` - is an array of arrays, where each subarray describes the `Link` element. At the least you require a `href` element, but for a fuller description, please, refer to https://developer.mozilla.org/en-US/docs/Web/HTML/Element/link. Note that, in case some attribute is missing a link can be skipped depending on values of toher attributes.  
`$type` - the type of `Link` elements to generate. Default is `header`, meaning, that a single HTTP header `Link:` will be generated. The other supported values are `head` and `body`, which differ only in one extra check for `body`, which checks if a link is considered `body-ok`, but works only with `$strictRel` set to `true.  
`$strictRel` - if set to `true`, if `rel` attribute is set it will be checked against a list based on https://html.spec.whatwg.org/multipage/links.html#linkTypes and https://microformats.org/wiki/existing-rel-values#formats, meaning against `rel` values, that have to be supported by clients. If you are using something "special", set this to `false`. Personally, in such cases, I would recommend splitting the set of `Link` elements you have into 2 sets: standard and non-standard.

## redirect
```php
redirect(string $newURI, bool $permanent = true, bool $preserveMethod = true, bool $forceGET = false)
```
Function to allow redirects.  
`$newURI` is the URL, that will be used in `Location` header. If it fails validation as URI, instead of redirect a 500 error will be sent to client.  
`$permanent` governs whether this is a permanent redirect or a temporary one. Permanent redirects tell browser to always use the new address, while temporary - only this time. `true` by default.  
`$preserveMethod` governs whether method is allowed to be changed when redirecting. Historically 301 and 302 redirects does no restrict client in this regard, which may cause some issues sometimes. If set to `true` (default) 307 and 308 codes will be used.  
`$forceGET` is a flag to use 303 code. If you want client to specifically change method to GET when redirecting, you can use this one. Useful, if after POST or PUT you want to show output, that is handled by some other page.

## notaccept
```php
notAccept(array $supported = ['text/html'], bool $exit = true)
```
This is quite niche, but still may be useful in some cases. If you provide several formats of the data (for example, JSON and XML) and you want client to negotiate the format that the client will recieve, you can use this function.  
Client will send standard `Accept` HTTP header with list of acceptable MIME types, this function will check if any of the MIME types your backend provides is in the list and the use the one with the highest priority. That is, if client will accept both JSON and XML, but JSON will have priority 0.9 and XML - 0.8, function will return `application/json`, to let you know, that you should provide the data in JSON format.  
If none of your supported MIME types match `Accept` header, `406` header will be returned to client. If header is not provided by client, function will return `true`. It will also return `true`, if client provides `*/*` MIME type. And this is why it will be niche: most browsers are sending it in their `Accept` headers. But, if you are using some custom API - this may be useful, still.  
`$supported` - array of MIME types you support.  
`$exit` if set to `true` will exit the script right after HTTP 406 is sent, otherwise will return `false`.