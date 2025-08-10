<?php
declare(strict_types = 1);

namespace Simbiat\http20;

use JetBrains\PhpStorm\ExpectedValues;
use function in_array;

/**
 * Functions that send/handle different HTTP headers.
 */
class Headers
{
    /**
     * Same as `$_POST`, but for PUT
     *
     * @var array
     * @noinspection PhpPropertyNamingConventionInspection
     */
    public static array $_PUT = [];
    /**
     * Same as `$_POST`, but for DELETE
     * @var array
     * @noinspection PhpPropertyNamingConventionInspection
     */
    public static array $_DELETE = [];
    /**
     * Same as `$_POST`, but for PATCH
     * @var array
     * @noinspection PhpPropertyNamingConventionInspection
     */
    public static array $_PATCH = [];
    /**
     * Same as `$_FILES`, but gotten from PUT, PATCH or DELETE requests
     * @var array
     * @noinspection PhpPropertyNamingConventionInspection
     */
    public static array $_FILES = [];
    
    /**
     * Regex to validate Origins (essentially, a URI in https://examplecom:443 format)
     * @var string
     */
    public const string ORIGIN_REGEX = '(?<scheme>[a-zA-Z][a-zA-Z0-9+.-]+):\/\/(?<host>[a-zA-Z0-9.\-_~]+)(?<port>:\d+)?';
    /**
     * Safe HTTP methods which can, generally, be allowed for processing
     * @var array
     */
    public const array SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];
    /**
     * Full list of HTTP methods
     * @var array
     */
    public const array ALL_METHODS = ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'TRACE', 'PATCH'];
    /**
     * List of headers we allow exposing by default
     * @var array
     */
    public const array EXPOSED_HEADERS = [
        #CORS allowed ones, except for Pragma and Expires, as those two are discouraged to be used (Cache-Control is far better)
        'Cache-Control', 'Content-Language', 'Content-Type', 'Last-Modified',
        #Security headers
        'Strict-Transport-Security', 'Access-Control-Max-Age', 'Access-Control-Allow-Credentials',
        'Vary', 'Access-Control-Allow-Origin',
        'Access-Control-Expose-Headers', 'Access-Control-Allow-Headers', 'Access-Control-Allow-Methods',
        'Cross-Origin-Embedder-Policy', 'Cross-Origin-Opener-Policy', 'Cross-Origin-Resource-Policy', 'Referrer-Policy', 'Content-Security-Policy', 'Content-Security-Policy-Report-Only',
        #Performance headers
        'X-Content-Type-Options', 'X-DNS-Prefetch-Control', 'Connection', 'Keep-Alive',
        #Other
        'Feature-Policy', 'ETag', 'Link',
    ];
    /**
     * Default values for CSP directives set to mostly restrictive values
     * @var array
     */
    public const array SECURE_DIRECTIVES = [
        #Fetch Directives
        'default-src' => '\'self\'', 'child-src' => '\'self\'', 'connect-src' => '\'self\'', 'font-src' => '\'self\'', 'frame-src' => '\'self\'',
        #Blocking images, because images can be used to inject scripts:
        #https://www.secjuice.com/hiding-javascript-in-png-csp-bypass/
        #https://portswigger.net/research/bypassing-csp-using-polyglot-jpegs
        'img-src' => '\'none\'', 'manifest-src' => '\'self\'', 'media-src' => '\'self\'', 'object-src' => '\'none\'', 'script-src' => '\'none\'', 'script-src-elem' => '\'none\'', 'script-src-attr' => '\'none\'', 'style-src' => '\'none\'', 'style-src-elem' => '\'none\'', 'style-src-attr' => '\'none\'', 'worker-src' => '\'self\'',
        #Document directives
        'base-uri' => '\'self\'', 'plugin-types' => '', 'sandbox' => '',
        #Navigate directives
        'form-action' => '\'self\'', 'frame-ancestors' => '\'self\'',
        #Other directives
        'require-trusted-types-for' => '\'script\'', 'trusted-types' => '', 'report-to' => '',
    ];
    /**
     * Default values for Feature-Policy, essentially disabling most of them
     * @var array
     */
    public const array SECURE_FEATURES = [
        #Disable access to sensors
        'accelerometer' => '\'none\'', 'ambient-light-sensor' => '\'none\'', 'gyroscope' => '\'none\'', 'magnetometer' => '\'none\'', 'vibrate' => '\'none\'',
        #Disable access to devices
        'camera' => '\'none\'', 'microphone' => '\'none\'', 'midi' => '\'none\'', 'usb' => '\'none\'', 'speaker' => '\'none\'',
        #document-write (.write, .writeln, .open and .close) is also discouraged because it dynamically rewrites your HTML markup and blocks parsing of the document. While this may not be exactly a security concern, if there is a stray script, that uses it, we have little control (if any) regarding what exactly it modifies.
        'document-write' => '\'none\'',
        #Allowing use of DRM and Web Authentication API, but only on our site and its own frames
        'encrypted-media' => '\'self\'', 'publickey-credentials-get' => '\'self\'',
        #Disable geolocation, XR tracking, payment and screen capture APIs
        'geolocation' => '\'none\'', 'xr-spatial-tracking' => '\'none\'', 'payment' => '\'none\'', 'display-capture' => '\'none\'',
        #Disable wake-locks
        'wake-lock' => '\'none\'', 'screen-wake-lock' => '\'none\'',
        #Disable Web Share API. It's recommended to enable it explicitly for pages, where sharing will not expose potentially sensitive materials
        'web-share' => '\'none\'',
        #Disable synchronous XMLHttpRequests (that were technically deprecated)
        'sync-xhr' => '\'none\'',
        #Disable WebVR API (halted standard, replaced by WebXR)
        'vr' => '\'none\'',
        #Images optimizations as per https://github.com/w3c/webappsec-permissions-policy/blob/master/policies/optimized-images.md
        'oversized-images' => '*(2.0)', 'unoptimized-images' => '*(0.5)', 'unoptimized-lossy-images' => '*(0.5)', 'unoptimized-lossless-images' => '*(1.0)', 'legacy-image-formats' => '\'none\'', 'unsized-media' => '\'none\'', 'image-compression' => '\'none\'', 'maximum-downscaling-image' => '\'none\'',
        #Disable lazyload. Do not apply it to everything. While it can improve performance somewhat, if it's applied to everything it can provide a reversed effect. Apply it strategically with lazyload attribute.
        'lazyload' => '\'none\'',
        #Disable autoplay, font swapping, fullscreen and picture-in-picture (if triggered in some automatic mode, can really annoy users)
        'autoplay' => '\'none\'', 'fullscreen' => '\'none\'', 'picture-in-picture' => '\'none\'',
        #Turn off font swapping and CSS animations for any property that triggers a re-layout (e.g., top, width, max-height)
        'font-display-late-swap' => '\'none\'', 'layout-animations' => '\'none\'',
    ];
    /**
     * Default values for Permissions-Policy, essentially disabling most of them. It is different from SECURE_FEATURES, because of slightly different values and different list of policies
     * @var array
     */
    public const array PERMISSIONS_DEFAULT = [
        #Disable access to sensors
        'accelerometer' => '', 'ambient-light-sensor' => '', 'gyroscope' => '', 'magnetometer' => '',
        #Disable access to devices
        'camera' => '', 'keyboard-map' => '', 'microphone' => '', 'midi' => '', 'usb' => '', 'gamepad' => '', 'speaker-selection' => '', 'hid' => '', 'serial' => '',
        #Changing document.domain can allow some cross-origin access and is discouraged, due to existence of other (better) mechanisms
        'document-domain' => '',
        #Allowing use of DRM and Web Authentication API, but only on our site and its own frames
        'encrypted-media' => 'self', 'publickey-credentials-get' => 'self',
        #Disable geolocation, XR tracking, payment and screen capture APIs
        'geolocation' => '', 'xr-spatial-tracking' => '', 'payment' => '', 'display-capture' => '',
        #Disable wake-locks
        'screen-wake-lock' => '', 'idle-detection' => '',
        #Disable Web Share API. It's recommended to enable it explicitly for pages, where sharing will not expose potentially sensitive materials
        'web-share' => '',
        #Disable synchronous XMLHttpRequests (that were technically deprecated)
        'sync-xhr' => '',
        #Disable autoplay, font swapping, fullscreen and picture-in-picture (if triggered in some automatic mode, can really annoy users)
        'autoplay' => '', 'fullscreen' => '', 'picture-in-picture' => '',
        #Clipboard access. Enable only if you are going to manipulate clipboard on client side
        'clipboard-read' => '', 'clipboard-write' => '',
        #User tracking stuff
        'cross-origin-isolated' => '', 'interest-cohort' => '',
    ];
    /**
     * Values supported by Sandbox in CSP
     * @var array
     */
    public const array SANDBOX_VALUES = ['allow-downloads-without-user-activation', 'allow-forms', 'allow-modals', 'allow-orientation-lock', 'allow-pointer-lock', 'allow-popups', 'allow-popups-to-escape-sandbox', 'allow-presentation', 'allow-same-origin', 'allow-scripts', 'allow-storage-access-by-user-activation', 'allow-top-navigation', 'allow-top-navigation-by-user-activation'];
    /**
     * ist of standard values for `Set-Fetch-Site`
     * @var array
     */
    public const array FETCH_SITE = ['cross-site', 'same-origin', 'same-site', 'none'];
    /**
     * List of standard values for `Set-Fetch-Mode`
     * @var array
     */
    public const array FETCH_MODE = ['same-origin', 'cors', 'navigate', 'nested-navigate', 'websocket', 'no-cors'];
    /**
     * List of values for `Set-Fetch-User`
     * @var array
     */
    public const array FETCH_USER = ['?0', '?1'];
    /**
     * List of standard Set-Fetch-Destinations besides "script-like"
     * @var array
     */
    public const array FETCH_DESTINATIONS = ['audio', 'audioworklet', 'document', 'embed', 'empty', 'font', 'image', 'manifest', 'object', 'paintworklet', 'report', 'script', 'serviceworker', 'sharedworker', 'style', 'track', 'video', 'worker', 'xslt', 'nested-document'];
    /**
     * List of standard Set-Fetch-Destinations that are considered "script-like", that is, they are, most likely, triggered by a script (`<script>` or similar object)
     * @var array
     */
    public const array SCRIPT_LIKE = ['audioworklet', 'paintworklet', 'script', 'serviceworker', 'sharedworker', 'worker'];
    /**
     * List of standard HTTP status codes
     * @var array
     */
    public const array HTTP_CODES = [
        100 => 'Continue', 101 => 'Switching Protocols', 102 => 'Processing', 103 => 'Early Hints',
        200 => 'OK', 201 => 'Created', 202 => 'Accepted', 203 => 'Non-Authoritative Information', 204 => 'No Content', 205 => 'Reset Content', 206 => 'Partial Content', 207 => 'Multi-Status', 208 => 'Already Reported', 226 => 'IM Used',
        300 => 'Multiple Choices', 301 => 'Moved Permanently', 302 => 'Found', 303 => 'See Other', 304 => 'Not Modified', 305 => 'Use Proxy', 306 => 'Switch Proxy', 307 => 'Temporary Redirect', 308 => 'Permanent Redirect',
        400 => 'Bad Request', 401 => 'Unauthorized', 402 => 'Payment Required', 403 => 'Forbidden', 404 => 'Not Found', 405 => 'Method Not Allowed', 406 => 'Not Acceptable', 407 => 'Proxy Authentication Required', 408 => 'Request Timeout', 409 => 'Conflict', 410 => 'Gone', 411 => 'Length Required', 412 => 'Precondition Failed', 413 => 'Payload Too Large', 414 => 'URI Too Long', 415 => 'Unsupported Media Type', 416 => 'Range Not Satisfiable', 417 => 'Expectation Failed', 418 => 'I\'m a teapot', 421 => 'Misdirected Request', 422 => 'Unprocessable Entity', 423 => 'Locked', 424 => 'Failed Dependency', 425 => 'Too Early', 426 => 'Upgrade Required', 428 => 'Precondition Required', 429 => 'Too Many Requests', 431 => 'Request Header Fields Too Large', 451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error', 501 => 'Not Implemented', 502 => 'Bad Gateway', 503 => 'Service Unavailable', 504 => 'Gateway Timeout', 505 => 'HTTP Version Not Supported', 506 => 'Variant Also Negotiates', 507 => 'Insufficient Storage', 508 => 'Loop Detected', 510 => 'Not Extended', 511 => 'Network Authentication Required',
    ];
    
    /**
     * Function sends headers, related to security
     * @param string $strat          Security strategy to apply: `strict` (default), `mild` or `loose`
     * @param array  $allow_origins  List of allowed origins. https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Origin
     * @param array  $expose_headers List of exposed headers. https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Expose-Headers
     * @param array  $allow_headers  List of allowed headers. https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Headers
     * @param array  $allow_methods  List of allowed methods. https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Methods
     *
     * @return void
     */
    public static function security(#[ExpectedValues(['strict', 'mild', 'loose'])] string $strat = 'strict', array $allow_origins = [], array $expose_headers = [], array $allow_headers = [], array $allow_methods = []): void
    {
        if (!\headers_sent()) {
            #Default list of allowed methods, limited to only "simple" ones
            $default_methods = self::SAFE_METHODS;
            #Sanitize the custom methods
            foreach ($allow_methods as $key => $method) {
                if (!in_array($method, self::ALL_METHODS, true)) {
                    unset($allow_methods[$key]);
                }
            }
            #If we end up with an empty list of custom methods - use the default one
            if (empty($allow_methods)) {
                $allow_methods = $default_methods;
            }
            #Send the header. More on methods - https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods
            \header('Access-Control-Allow-Methods: '.\implode(', ', $allow_methods));
            \header('Allow: '.\implode(', ', $allow_methods));
            #Handle the wrong type of method from the client
            if ((isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) && !in_array($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'], $allow_methods, true)) || (isset($_SERVER['REQUEST_METHOD']) && !in_array($_SERVER['REQUEST_METHOD'], $allow_methods, true))) {
                self::clientReturn(405);
            }
            #Sanitize Origins list
            foreach ($allow_origins as $key => $origin) {
                if (\preg_match('/'.self::ORIGIN_REGEX.'/i', $origin) !== 1) {
                    unset($allow_origins[$key]);
                }
            }
            #Check that list is still not empty; otherwise, we assume that access from all origins is allowed (akin to *)
            if (!empty($allow_origins)) {
                if (isset($_SERVER['HTTP_ORIGIN']) && \preg_match('/'.self::ORIGIN_REGEX.'/i', $_SERVER['HTTP_ORIGIN']) === 1 && in_array($_SERVER['HTTP_ORIGIN'], $allow_origins, true)) {
                    #Vary is required by the standard. Using `false` to prevent overwriting of other Vary headers if any were sent
                    \header('Vary: Origin', false);
                    #Send actual headers
                    \header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
                    \header('Timing-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
                } else {
                    #Send proper header denying access and stop processing
                    self::clientReturn(403);
                }
            } else {
                #Vary is required by the standard. Using `false` to prevent overwriting of other Vary headers, if any were sent
                \header('Vary: Origin', false);
                #Send actual headers
                \header('Access-Control-Allow-Origin: *');
                \header('Timing-Allow-Origin: '.(isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT']);
            }
            #HSTS and force HTTPS
            \header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
            #Set caching value for CORS
            \header('Access-Control-Max-Age: 86400');
            #Allows credentials to be shared to front-end JS. By itself this should not be a security issue, but it may ease of use for 3rd-party parser in some cases if you are using cookies.
            \header('Access-Control-Allow-Credentials: true');
            #Allow headers sent from server, normally restricted by CORS
            #Keep a default list, that includes those originally allowed by CORS and those present in this class as self::EXPOSED_HEADERS
            #Send the list
            \header('Access-Control-Expose-Headers: '.\implode(', ', \array_unique(\array_merge(self::EXPOSED_HEADERS, $expose_headers))));
            #Allow headers, that can change server state, but are normally restricted by CORS
            if (!empty($allow_headers)) {
                \header('Access-Control-Allow-Headers: '.\implode(', ', \array_unique(\array_merge(['Accept', 'Accept-Language', 'Content-Language', 'Content-Type'], $allow_headers))));
            }
            #Set CORS strategy
            switch (mb_strtolower($strat, 'UTF-8')) {
                case 'mild':
                    \header('Cross-Origin-Embedder-Policy: unsafe-none');
                    \header('Cross-Origin-Embedder-Policy: same-origin-allow-popups');
                    \header('Cross-Origin-Resource-Policy: same-site');
                    \header('Referrer-Policy: strict-origin');
                    break;
                case 'loose':
                    \header('Cross-Origin-Embedder-Policy: unsafe-none');
                    \header('Cross-Origin-Opener-Policy: unsafe-none');
                    \header('Cross-Origin-Resource-Policy: cross-origin');
                    \header('Referrer-Policy: strict-origin-when-cross-origin');
                    break;
                #Make 'strict' default value, but also allow explicit specification
                case 'strict':
                default:
                    \header('Cross-Origin-Embedder-Policy: require-corp');
                    \header('Cross-Origin-Opener-Policy: same-origin');
                    \header('Cross-Origin-Resource-Policy: same-origin');
                    \header('Referrer-Policy: no-referrer');
                    break;
            }
        }
    }
    
    /**
     * Function to process CSP header
     * @param array $csp_directives List of CSP directives. https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy
     * @param bool  $report_only    Whether to report violations only or both report and block them
     * @param bool  $report_uri     Whether to add `report-uri` in headers, which is officially deprecated, but maybe required for compatibility
     *
     * @return void
     */
    public static function contentPolicy(array $csp_directives = [], bool $report_only = false, bool $report_uri = false): void
    {
        if (!\headers_sent()) {
            #Set defaults directives for CSP
            $default_directives = self::SECURE_DIRECTIVES;
            #Apply custom directives
            foreach ($csp_directives as $directive => $value) {
                #If value is empty, assume, that we want to remove the directive entirely
                if (empty($value)) {
                    unset($default_directives[$directive]);
                } else {
                    switch ($directive) {
                        case 'sandbox':
                            #Validate the value we have
                            if (in_array($value, self::SANDBOX_VALUES, true)) {
                                $default_directives['sandbox'] = $value;
                            } else {
                                #Ignore the value entirely
                                unset($default_directives['sandbox']);
                            }
                            break;
                        case 'trusted-types':
                            #Validate the value we have
                            if (\preg_match('/^\'none\'|((([a-z0-9-#=_\/@.%]+) ?)+( ?\'allow-duplicates\')?)$/i', $value) === 1) {
                                $default_directives['trusted-types'] = $value;
                            } else {
                                #Ignore the value entirely
                                unset($default_directives['trusted-types']);
                            }
                            break;
                        case 'plugin-types':
                            #Validate the value we have
                            if (\preg_match('/^(('.Common::MIME_REGEX.') ?)+$/i', $value) === 1) {
                                $default_directives['plugin-types'] = $value;
                            } else {
                                #Ignore the value entirely
                                unset($default_directives['plugin-types']);
                            }
                            break;
                        case 'report-to':
                            $default_directives['report-to'] = $value;
                            #This is only for legacy purposes, since report-uri is deprecated
                            if ($report_uri) {
                                $default_directives['report-uri'] = $value;
                            }
                            break;
                        case 'report-uri':
                            #Ensure that we do not use report-uri, unless there is a report-to, since report-uri is deprecated
                            unset($default_directives['report-uri']);
                            break;
                        default:
                            #Validate the value
                            if (isset($default_directives[$directive]) && \preg_match('/^(?<nonorigin>(?<standard>\'(none|self|\*)\'))|(\'self\' ?)?(\'strict-dynamic\' ?)?(\'report-sample\' ?)?(((?<origin>'.self::ORIGIN_REGEX.')|(?<nonce>\'nonce-(?<base64>(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{4}))\')|(?<hash>\'sha(256|384|512)-(?<base64_2>(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{4}))\')|((?<justscheme>[a-zA-Z][a-zA-Z0-9+.-]+):))(?<delimiter> )?)+$/i', $value) === 1) {
                                #Check if it's script or style source
                                #If it's not 'none' - add 'report-sample'
                                if ($value !== '\'none\'' && in_array($directive, ['script-src', 'script-src-elem', 'script-src-attr', 'style-src', 'style-src-elem', 'style-src-attr'])) {
                                    $default_directives[$directive] = '\'report-sample\' '.$value;
                                } else {
                                    $default_directives[$directive] = $value;
                                }
                            }
                            break;
                    }
                }
            }
            #plugin-types is not used if all objects are blocked
            if ($default_directives['object-src'] === '\'none\'') {
                unset($default_directives['plugin-types']);
            } elseif (empty($default_directives['plugin-types'])) {
                unset($default_directives['plugin-types']);
            }
            #Sandbox is ignored if we use Report-Only
            if ($report_only) {
                unset($default_directives['sandbox']);
            }
            #Generate line for CSP
            $csp_line = '';
            foreach ($default_directives as $directive => $value) {
                if (!empty($value)) {
                    $csp_line .= $directive.' '.$value.'; ';
                }
            }
            #If the report is set also send Content-Security-Policy-Report-Only header
            if (!$report_only) {
                \header('Content-Security-Policy: upgrade-insecure-requests; '.mb_trim($csp_line, null, 'UTF-8'));
            } elseif (!empty($default_directives['report-to'])) {
                \header('Content-Security-Policy-Report-Only: '.mb_trim($csp_line, null, 'UTF-8'));
            }
        }
    }
    
    /**
     * Function to process Sec-Fetch headers. Arrays are set to empty ones by default for ease of use (sending empty array is a bit easier than copying values). Below materials were used in preparation
     * https://www.w3.org/TR/fetch-metadata/
     * https://fetch.spec.whatwg.org/
     * https://web.dev/fetch-metadata/
     *
     * @param array $site   List of allowed values for Sec-Fetch-Site
     * @param array $mode   List of allowed values for Sec-Fetch-Mode
     * @param array $user   List of allowed values for Sec-Fetch-User
     * @param array $dest   List of allowed values for Sec-Fetch-Dest
     * @param bool  $strict Whether to enforce compliance with supported values only. Current W3C allows ignoring headers, if not sent or have unsupported values, but we may want to be stricter by setting this option to true
     *
     * @return void
     */
    public static function secFetch(array $site = [], array $mode = [], array $user = [], array $dest = [], bool $strict = true): void
    {
        #Set flag for processing
        $bad_request = false;
        #Check if Sec-Fetch was passed at all (older browsers or bots may not use it). Process it only if it's present.
        if (isset($_SERVER['HTTP_SEC_FETCH_SITE']) && in_array($_SERVER['HTTP_SEC_FETCH_SITE'], self::FETCH_SITE, true)) {
            #Setting defaults
            $site = \array_intersect($site, self::FETCH_SITE);
            if (empty($site)) {
                #Allow everything
                $site = self::FETCH_SITE;
            }
            $mode = \array_intersect($mode, self::FETCH_MODE);
            if (empty($mode)) {
                #Allow all modes
                $mode = self::FETCH_MODE;
            }
            $user = \array_intersect($user, self::FETCH_USER);
            if (empty($user)) {
                #Allow only actions triggered by user activation
                $user = ['?1'];
            }
            $dest = \array_intersect($dest, self::FETCH_DESTINATIONS);
            if (empty($dest)) {
                $dest = [
                    #Allow navigation (including from frames)
                    'document', 'embed', 'frame', 'iframe',
                    #Allow common elements
                    'audio', 'font', 'image', 'style', 'video', 'track', 'manifest',
                    #Allow empty
                    'empty',
                ];
                #If we have only 'same-origin' and/or 'none', allow script as well, because otherwise default settings will prevent access to JS files hosted on same domain
                if (in_array($site, [['same-origin', 'none'], ['same-origin'], ['none']], true)) {
                    $dest[] = 'script';
                }
            }
            #Actual validation
            if (
                !in_array($_SERVER['HTTP_SEC_FETCH_SITE'], $site, true) ||
                (
                    #Mode should be ignored by default if it's any value outside the spec, which implies it can be empty
                    !empty($_SERVER['HTTP_SEC_FETCH_MODE']) && $strict &&
                    !in_array($_SERVER['HTTP_SEC_FETCH_MODE'], $mode, true)
                ) ||
                (
                    #User is allowed to be missing by the spec
                    !empty($_SERVER['HTTP_SEC_FETCH_USER']) && $strict &&
                    !in_array($_SERVER['HTTP_SEC_FETCH_USER'], $user, true)
                ) ||
                (
                    #Dest should be ignored by default if it's any value outside the spec, which implies it can be empty
                    !empty($_SERVER['HTTP_SEC_FETCH_DEST']) && $strict &&
                    !in_array($_SERVER['HTTP_SEC_FETCH_DEST'], $dest, true)
                )
            ) {
                $bad_request = true;
            } elseif (!empty($_SERVER['HTTP_SEC_FETCH_DEST']) && in_array($_SERVER['HTTP_SEC_FETCH_DEST'], self::SCRIPT_LIKE, true)) {
                #Attempt to get content-type headers
                $content_type = '';
                #This header may be present in some cases
                if (isset($_SERVER['HTTP_CONTENT_TYPE'])) {
                    $content_type = $_SERVER['HTTP_CONTENT_TYPE'];
                } elseif (isset($_SERVER['CONTENT_TYPE'])) {
                    $content_type = $_SERVER['CONTENT_TYPE'];
                }
                #Check if we have already sent our own content-type header
                foreach (\headers_list() as $header) {
                    if (str_starts_with($header, 'Content-type:') === true) {
                        #Get MIME
                        $content_type = \preg_replace('/^(Content-type:\s*)('.Common::MIME_REGEX.')$/', '$2', $header);
                        break;
                    }
                }
                #If MIME is found, and it matches CSV, audio, image or video - reject
                if (!empty($content_type) && \preg_match('/(text\/csv)|((audio|image|video)\/[-+\w.]+)/', $content_type) === 1) {
                    $bad_request = true;
                }
            }
        } elseif ($strict) {
            $bad_request = true;
        }
        if ($bad_request) {
            #Send proper header denying access and stop processing
            self::clientReturn(403);
        }
    }
    
    /**
     * Function to send headers, that may improve performance on client side
     *
     * @param int   $keepalive    Time for `Keep-Alive` header (only if protocol is below HTTP 2.0). https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Keep-Alive
     * @param array $client_hints List of supported client hints. https://developer.mozilla.org/en-US/docs/Glossary/Client_hints
     *
     * @return void
     */
    public static function performance(int $keepalive = 0, array $client_hints = []): void
    {
        if (!\headers_sent()) {
            #Prevent content type sniffing (determining the file type by content, not by extension or header)
            \header('X-Content-Type-Options: nosniff');
            #Allow DNS prefetch for some performance improvement on the client side
            \header('X-DNS-Prefetch-Control: on');
            #Keep-alive connection if not using HTTP2.0 (which prohibits it). Setting the maximum number of connections as timeout power 1000. If a human is opening the pages, it's unlike they will be opening more than 1 page per second, and it's unlikely that any page will have more than 1000 files linked to same server. If it does - some optimization may be required.
            if ($keepalive > 0 && (str_starts_with($_SERVER['SERVER_PROTOCOL'], 'HTTP/1') || str_starts_with($_SERVER['SERVER_PROTOCOL'], 'HTTP/0'))) {
                \header('Connection: Keep-Alive');
                \header('Keep-Alive: timeout='.$keepalive.', max='.($keepalive * 1000));
            }
            if (!empty($client_hints)) {
                #Implode client hints
                $client_hints_new = \implode(', ', $client_hints);
                #Notify, that we support Client Hints: https://developer.mozilla.org/en-US/docs/Glossary/Client_hints
                #Logic for processing them should be done outside this function, though
                \header('Accept-CH: '.$client_hints_new);
                #Instruct cache to vary depending on client hints (and Origin to help with CORS https://portswigger.net/research/exploiting-cors-misconfigurations-for-bitcoins-and-bounties)
                \header('Vary: Origin, '.$client_hints_new, false);
            }
        }
    }
    
    /**
     * A wrapper for `features` with `permissions = true` just for convenience of access. https://www.w3.org/TR/permissions-policy-1/ is replacement for Feature-Policy
     *
     * @param array $features    List of features to enable
     * @param bool  $force_check If set to `true` will check if the feature is "supported" (present in default array) and value complies with the standard. Setting it to `false` will allow you to utilize a feature or value not yet supported by the library.
     *
     * @return void
     */
    public static function permissions(array $features = [], bool $force_check = true): void
    {
        self::features($features, $force_check, true);
    }
    
    /**
     * Function to manage `Feature-Policy` to control different features. By default, most features are disabled for security and performance.
     * #https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy
     * #https://feature-policy-demos.appspot.com/
     * #https://featurepolicy.info/
     *
     * @param array $features    List of features to enable
     * @param bool  $force_check If set to `true` will check if the feature is "supported" (present in default array) and value complies with the standard. Setting it to `false` will allow you to utilize a feature or value not yet supported by the library.
     * @param bool  $permissions If `true` - use `Permissions-Policy` instead. https://developer.mozilla.org/en-US/docs/Web/HTTP/Permissions_Policy
     *
     * @return void
     */
    public static function features(array $features = [], bool $force_check = true, bool $permissions = false): void
    {
        if (!\headers_sent()) {
            if ($permissions) {
                $defaults = self::PERMISSIONS_DEFAULT;
            } else {
                $defaults = self::SECURE_FEATURES;
            }
            foreach ($features as $feature => $allow_list) {
                #Sanitize
                $feature = mb_strtolower(mb_trim($feature, null, 'UTF-8'), 'UTF-8');
                $allow_list = mb_strtolower(mb_trim($allow_list, null, 'UTF-8'), 'UTF-8');
                #If validation is enforced, validate the feature and value provided
                /** @noinspection OffsetOperationsInspection */
                if (!$force_check || (isset($defaults[$feature]) && \preg_match('/^(?<nonorigin>(?<standard>\*|\'none\')(?<setting>\(\d+(\.\d+)?\))?)|(\'self\' ?)?(?<origin>'.self::ORIGIN_REGEX.'(?<setting_o>\(\d+(\.\d+)?\))?(?<delimiter> )?)+$/i', $allow_list) === 1)) {
                    #Update value
                    /** @noinspection OffsetOperationsInspection */
                    $defaults[$feature] = $allow_list;
                }
            }
            #Generate line for header
            $header_line = '';
            foreach ($defaults as $feature => $allow_list) {
                if ($permissions) {
                    $header_line .= $feature.'=('.$allow_list.'), ';
                } else {
                    $header_line .= $feature.' '.$allow_list.'; ';
                }
            }
            if ($permissions) {
                \header('Permissions-Policy: '.mb_rtrim(mb_trim($header_line, null, 'UTF-8'), ',', 'UTF-8'));
            } else {
                \header('Feature-Policy: '.mb_trim($header_line, null, 'UTF-8'));
            }
        }
    }
    
    /**
     * Function to set Last-Modified header. This header is generally not required if you already have Cache-Control and ETag, but still may be useful in case of conditional requests. At least if you will provide it with proper modification time.
     * @param int|string|float $mod_time Modification time
     * @param bool             $exit     Whether to stop execution in case if HTTP 304 conditions are met (content has not been modified since last client request)
     *
     * @return void
     */
    public static function lastModified(int|string|float $mod_time = 0, bool $exit = false): void
    {
        if (!\headers_sent()) {
            #In case it's not numeric, replace with 0
            if (\is_numeric($mod_time)) {
                $mod_time = (int)$mod_time;
            } else {
                $mod_time = 0;
            }
            if ($mod_time <= 0) {
                #Get the freshest modification time of all PHP files using PHP's getlastmod time
                $mod_time = \max(\max(\array_map('\filemtime', \array_filter(\get_included_files(), '\is_file')), \getlastmod()));
            }
            #Send header
            \header('Last-Modified: '.\gmdate(\DATE_RFC7231, $mod_time));
            #Set the flag to false for now
            if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && \strtotime(mb_substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 5, null, 'UTF-8')) >= $mod_time) {
                #If content has not been modified - return 304
                self::clientReturn(304, $exit);
            }
        }
    }
    
    /**
     * Function to prepare and send cache-related headers
     *
     * @param string $string         String to be used to calculate eTag.
     * @param string $cache_strategy Caching strategy. Allowed values are `aggressive`, `private`, `live`, `month`, `week`, `day`, `hour`.
     * @param bool   $exit           Whether to stop execution in case of HTTP 304 or 412 (based on ETag).
     * @param string $postfix        Optional postfix for ETag string. Expected to be used by `zEcho` function only to comply with recommendations for compression.
     *
     * @return void
     */
    public static function cacheControl(#[ExpectedValues(['', 'aggressive', 'private', 'none', 'live', 'month', 'week', 'day', 'hour'])] string $string = 'hour', string $cache_strategy = '', bool $exit = false, string $postfix = ''): void
    {
        if (!\headers_sent()) {
            #Send headers related to cache based on strategy selected
            #Some strategies are derived from https://csswizardry.com/2019/03/cache-control-for-civilians/
            switch (mb_strtolower($cache_strategy, 'UTF-8')) {
                case 'aggressive':
                    \header('Cache-Control: max-age=31536000, immutable, no-transform');
                    break;
                case 'private':
                    \header('Cache-Control: private, no-cache, no-transform');
                    break;
                case 'none':
                    \header('Cache-Control: no-cache, no-store, no-transform');
                    break;
                case 'live':
                    \header('Cache-Control: no-cache, no-transform');
                    break;
                case 'month':
                    #28 days to be more precise
                    \header('Cache-Control: max-age=2419200, must-revalidate, stale-while-revalidate=86400, stale-if-error=86400, no-transform');
                    break;
                case 'week':
                    \header('Cache-Control: max-age=604800, must-revalidate, stale-while-revalidate=86400, stale-if-error=86400, no-transform');
                    break;
                case 'day':
                    \header('Cache-Control: max-age=86400, must-revalidate, stale-while-revalidate=43200, stale-if-error=43200, no-transform');
                    break;
                #Make 'hour' default value, but also allow explicit specification
                case 'hour':
                default:
                    \header('Cache-Control: max-age=3600, stale-while-revalidate=1800, stale-if-error=1800, no-transform');
                    break;
            }
            #Ensure that caching works properly in case the client did not support compression, but now does or vice-versa and in case data-saving mode was requested by client at any point.
            #Origin is added to help with CORS https://portswigger.net/research/exploiting-cors-misconfigurations-for-bitcoins-and-bounties
            \header('Vary: Origin, Save-Data, Accept-Encoding', false);
            #Set ETag
            if (!empty($string)) {
                self::eTag(\hash('sha3-512', $string).$postfix, $exit);
            }
        }
    }
    
    /**
     * Handle ETag header and its validation depending on request headers. https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/ETag
     * @param string $etag String to use for ETag generation
     * @param bool   $exit Whether to stop execution in case of HTTP 304 or 412
     *
     * @return void
     */
    public static function eTag(string $etag, bool $exit = false): void
    {
        if (!\headers_sent()) {
            #Send ETag for caching purposes
            \header('ETag: '.$etag);
            #Check if we have a conditional request. While this may have a less ideal placement than lastModified(), since ideally you will have some text to output first, but it can still save some time on client side
            if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && mb_trim($_SERVER['HTTP_IF_NONE_MATCH'], null, 'UTF-8') === $etag) {
                #If content has not been modified - return 304
                self::clientReturn(304, $exit);
            }
            #Return error if If-Match was sent, and it's different from our etag
            if (isset($_SERVER['HTTP_IF_MATCH']) && mb_trim($_SERVER['HTTP_IF_MATCH'], null, 'UTF-8') !== $etag) {
                self::clientReturn(412, $exit);
            }
        }
    }
    
    /**
     * Function to return to client and optionally force-close connection
     * @param string|int $code Numeric HTTP code
     * @param bool       $exit Whether to force-close connection
     *
     * @return int
     */
    public static function clientReturn(string|int $code = 500, bool $exit = true): int
    {
        #Generate response
        if (\is_numeric($code)) {
            #Enforce string for convenience
            $code = (int)$code;
            if (isset(self::HTTP_CODES[$code])) {
                $response = $code.' '.self::HTTP_CODES[$code];
            } else {
                #Non-standard code without text, not compliant with the standard
                $response = '500 '.self::HTTP_CODES[500];
            }
        } else {
            $response = $code;
        }
        #If response does not comply with HTTP standard - replace it with 500
        if (\preg_match('/^([12345]\d{2})( .+)$/', $response) !== 1) {
            $response = '500 Internal Server Error';
        }
        $numeric_code = (int)\preg_replace('/^([123]\d{2})( .+)$/', '$1', $response);
        #Send response header
        if (!\headers_sent()) {
            \header($_SERVER['SERVER_PROTOCOL'].' '.$response);
        }
        if ($exit) {
            Common::forceClose();
        }
        return $numeric_code;
    }
    
    /**
     * Function to handle redirects
     *
     * @param string $new_uri         New URI to redirect to. If it's not a valid URI, code 500 will be sent instead of redirect.
     * @param bool   $permanent       Whether it is a permanent redirect.
     * @param bool   $preserve_method Whether to preserve HTTP Method. If `false` 301 or 302 code will be used, if `true` - 307 or 308.
     * @param bool   $force_get       If `true` will use `303` code.
     *
     * @return void
     */
    public static function redirect(string $new_uri, bool $permanent = true, bool $preserve_method = true, bool $force_get = false): void
    {
        #Set default as precaution
        $code = 500;
        #If we want to enforce GET method, we can use 303: it tells client to retrieve a different page using GET method, even if original was not GET
        if ($force_get) {
            $code = 303;
            #Permanent redirect without change of method
        } elseif ($permanent && $preserve_method) {
            $code = 308;
            #Temporary redirect without change of method
        } elseif (!$permanent && $preserve_method) {
            $code = 307;
            #Permanent redirect allowing change of method
        } elseif ($permanent && !$preserve_method) {
            $code = 301;
            #Temporary redirect allowing change of method
        } elseif (!$permanent && !$preserve_method) {
            $code = 302;
        }
        #Validate URI. Not checking the scheme since it may be a valid use-case to redirect to something besides HTTPS
        if (IRI::isValidIri($new_uri)) {
            #Send Location header, indicating new URL to be used
            if (!\headers_sent()) {
                \header('Location: '.$new_uri);
            }
        } else {
            #Update code to 500, since something must have gone wrong
            $code = 500;
        }
        #Send code and enforce connection closure
        self::clientReturn($code);
    }
    
    /**
     * Function to handle Accept request header
     * @param array $supported List of supported MIME types
     * @param bool  $exit      Whether to stop processing in case of HTTP 406
     *
     * @return bool|string
     */
    public static function notAccept(array $supported = ['text/html'], bool $exit = true): bool|string
    {
        #Check if header is set, and we do have a limit on supported MIME types
        if (isset($_SERVER['HTTP_ACCEPT']) && !empty($supported)) {
            #Generate list of acceptable values
            $acceptable = [];
            foreach ($supported as $mime) {
                #Split MIME
                $mime = \explode('/', $mime);
                #Attempt to get priority for supported MIME type (with optional subtype)
                if (\preg_match('/.*('.$mime[0].'\/('.$mime[1].'|\*))(;q=((0\.[0-9])|[0-1])(?>\s*(,|$)))?.*/m', $_SERVER['HTTP_ACCEPT'], $matches) === 1) {
                    #Add to array
                    if (!isset($matches[4]) || $matches[4] === '') {
                        $acceptable[$mime[0].'/'.$mime[1]] = 1.0;
                    } else {
                        $acceptable[$mime[0].'/'.$mime[1]] = (float)$matches[4];
                    }
                }
            }
            #Check if any of the supported types are acceptable
            if (empty($acceptable)) {
                #If not - check if */* is supported
                if (\preg_match('/\*\/\*/', $_SERVER['HTTP_ACCEPT']) === 1) {
                    #Consider as no limitation
                    return true;
                }
                #Send 406 Not Acceptable
                self::clientReturn(406, $exit);
                return false;
            }
            #Get the one with the highest priority and return its value
            return \array_keys($acceptable, \max($acceptable))[0];
        }
        #Consider as no limitation
        return true;
    }
    
    /**
     * Function to parse multipart/form-data for PUT/DELETE/PATCH methods
     */
    public static function multiPartFormParse(): void
    {
        #Get method
        $method = $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] ?? $_SERVER['REQUEST_METHOD'] ?? null;
        #Get Content-Type
        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
        #Exit if not one of the supported methods or wrong content-type
        if (!in_array($method, ['PUT', 'DELETE', 'PATCH']) || \preg_match('/(^application\/x-www-form-urlencoded$)|(^multipart\/form-data; boundary=.*$)/ui', $content_type) !== 1) {
            return;
        }
        $parsed_data = \request_parse_body();
        self::${'_'.mb_strtoupper($method, 'UTF-8')} = $parsed_data[0];
        self::$_FILES = $parsed_data[1];
    }
}
