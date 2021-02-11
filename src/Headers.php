<?php
declare(strict_types=1);
namespace http20;

class Headers
{    
    #separate function for authentication headers?
    
    #read on Save-Data
    
    #https://www.fastly.com/blog/best-practices-using-vary-header
    #https://www.keycdn.com/blog/client-hints
    
    #consider https://github.com/bspot/phpsourcemaps for https://www.html5rocks.com/en/tutorials/developertools/sourcemaps/ https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/SourceMap
    
    #consider fucntion for server-timing:
    #https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Server-Timing
    #https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Timing-Allow-Origin
    
    #unclear what to do with https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Tk
    
    #Regex to validate Origins (essentially, an URI in https://examplecom:443 format)
    public const originRegex = '(?<scheme>[a-zA-Z][a-zA-Z0-9+.-]+):\/\/(?<host>[a-zA-Z0-9.\-_~]+)(?<port>:\d+)?';
    #Regex for MIME type
    public const mimeRegex = '[-\w.]+\/[-+\w.]+';
    #Safe HTTP methods which can, generally, be allowed for processing
    public const safeMethods = ['GET', 'HEAD', 'POST'];
    #Full list of HTTP methods
    public const allMethods = ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'TRACE', 'PATCH'];
    #List of headers we allow to expose by default
    public const exposedHeaders = [
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
    #Default values for CSP directives set to mostly restrictive values
    public const secureDirectives = [
        #Fetch Directives
        'default-src' => '\'self\'',
        'child-src' => '\'self\'',
        'connect-src' => '\'self\'',
        'font-src' => '\'self\'',
        'frame-src' => '\'self\'',
        #Blocking images, because images can be used to inject scripts:
        #https://www.secjuice.com/hiding-javascript-in-png-csp-bypass/
        #https://portswigger.net/research/bypassing-csp-using-polyglot-jpegs
        'img-src' => '\'none\'',
        'manifest-src' => '\'self\'',
        'media-src' => '\'self\'',
        'object-src' => '\'none\'',
        'prefetch-src' => '\'self\'',
        'script-src' => '\'none\'',
        'script-src-elem' => '\'none\'',
        'script-src-attr' => '\'none\'',
        'style-src' => '\'none\'',
        'style-src-elem' => '\'none\'',
        'style-src-attr' => '\'none\'',
        'worker-src' => '\'self\'',
        #Document directives
        'base-uri' => '\'self\'',
        'plugin-types' => '',
        'sandbox' => '',
        #Navigate directives
        'form-action' => '\'self\'',
        'frame-ancestors' => '\'self\'',
        'navigate-to' => '\'self\'',
        #Other directives
        'require-trusted-types-for' => '\'script\'',
        'trusted-types' => '',
        'report-to' => '',
    ];
    #Default values for Feature-Policy, essentially disabling most of them
    public const secureFeatures = [
        #Disable access to sensors
        'accelerometer' => '\'none\'',
        'ambient-light-sensor' => '\'none\'',
        'gyroscope' => '\'none\'',
        'magnetometer' => '\'none\'',
        'vibrate' => '\'none\'',
        #Disable access to devices
        'camera' => '\'none\'',
        'microphone' => '\'none\'',
        'midi' => '\'none\'',
        'battery' => '\'none\'',
        'usb' => '\'none\'',
        'speaker' => '\'none\'',
        #Changing document.domain can allow some cross-origin access and is discouraged, due to existence of other (better) mechanisms
        'document-domain' => '\'none\'',
        #document-write (.write, .writeln, .open and .close) is aslo discouraged because it dynamically rewrites your HTML markup and blocks parsing of the document. While this may not be exactly a security concern, if there is a stray script, that uses it, we have little control (if any) regarding what exactly it modifies.
        'document-write' => '\'none\'',
        #Allowing use of DRM and Web Authentication API, but only on our site and its own frames
        'encrypted-media' => '\'self\'',
        'publickey-credentials-get' => '\'self\'',
        #Disable geolocation, XR tracking, payment and screen capture APIs
        'geolocation' => '\'none\'',
        'xr-spatial-tracking' => '\'none\'',
        'payment' => '\'none\'',
        'display-capture' => '\'none\'',
        #Disable wake-locks
        'wake-lock' => '\'none\'',
        'screen-wake-lock' => '\'none\'',
        #Disable Web Share API. It's recommended to enable it explicitely for pages, where sharing will not expose potentially sensetive materials
        'web-share' => '\'none\'',
        #Disable synchronous XMLHttpRequests (that were technically deprecated)
        'sync-xhr' => '\'none\'',
        #Disable synchronous parsing blocking scripts (inline without defer/asycn attribute)
        'sync-script' => '\'none\'',
        #Disable WebVR API (halted standard, replaced by WebXR)
        'vr' => '\'none\'',
        #Images optimizations as per https://github.com/w3c/webappsec-permissions-policy/blob/master/policies/optimized-images.md
        'oversized-images' => '*(2.0)',
        'unoptimized-images' => '*(0.5)',
        'unoptimized-lossy-images' => '*(0.5)',
        'unoptimized-lossless-images' => '*(1.0)',
        'legacy-image-formats' => '\'none\'',
        'unsized-media' => '\'none\'',
        'image-compression' => '\'none\'',
        'maximum-downscaling-image' => '\'none\'',
        #Disable lazyload. Do not apply it to everything. While it can improve performacne somewhat, if it's applied to everything it can provide a reversed effect. Apply it strategically with lazyload attribute.
        'lazyload' => '\'none\'',
        #Disable autoplay, font swapping, fullscreen and picture-in-picture (if triggered in some automatic mode, can really annoy users)
        'autoplay' => '\'none\'',
        'fullscreen' => '\'none\'',
        'picture-in-picture' => '\'none\'',
        #Turn off font swapping and CSS animations for any property that triggers a re-layout (e.g. top, width, max-height)
        'font-display-late-swap' => '\'none\'',
        'layout-animations' => '\'none\'',
        #Disable execution of scripts/task in elements, that are not rendered or visible
        'execution-while-not-rendered' => '\'none\'',
        'execution-while-out-of-viewport' => '\'none\'',
        #Disabling APIs for modification of spatial navgiation and scrolling, since you need them only for specific cases
        'navigation-override' => '\'none\'',
        'vertical-scroll' => '\'none\'',
    ];
    #Values supported by Sandbox in CSP
    public const sandboxValues = ['allow-downloads-without-user-activation', 'allow-forms', 'allow-modals', 'allow-orientation-lock', 'allow-pointer-lock', 'allow-popups', 'allow-popups-to-escape-sandbox', 'allow-presentation', 'allow-same-origin', 'allow-scripts', 'allow-storage-access-by-user-activation', 'allow-top-navigation', 'allow-top-navigation-by-user-activation'];
    #Supported values for Sec-Fetch-* headers
    public const fetchSite = ['cross-site', 'same-origin', 'same-site', 'none'];
    public const fetchMode = ['same-origin', 'cors', 'navigate', 'nested-navigate', 'websocket', 'no-cors'];
    public const fetchUser = ['?0', '?1'];
    public const fetchDest = ['audio', 'audioworklet', 'document', 'embed', 'empty', 'font', 'image', 'manifest', 'object', 'paintworklet', 'report', 'script', 'serviceworker', 'sharedworker', 'style', 'track', 'video', 'worker', 'xslt', 'nested-document'];
    #List of Set-Fetch-Destinations that are considered "script-like", that is they are, most likely, triggered by a script (<script> or similar object)
    public const scriptLike = ['audioworklet', 'paintworklet', 'script', 'serviceworker', 'sharedworker', 'worker'];
    
    #Function sends headers, related to security
    public function security(string $strat = 'strict', array $allowOrigins = [], array $exposeHeaders = [], array $allowHeaders = [], array $allowMethods = [], array $cspDirectives = [], bool $reportonly = false)
    {
        #Default list of allowed methods, limited to only "simple" ones
        $defaultMethods = self::safeMethods;
        #Sanitize the custom methods
        foreach ($allowMethods as $key=>$method) {
            if (!in_array($method, self::allMethods)) {
                unset($allowMethods[$key]);
            }
        }
        #If we end up with empty list of custom methods - use default one
        if (empty($allowMethods)) {
            $allowMethods = $defaultMethods;
        }
        #Send the header. More on methods - https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods
        header('Access-Control-Allow-Methods: '.implode(', ', $allowMethods));
        #Handle wrong type of method from client
        if ((isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) && !in_array($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'], $allowMethods)) || (isset($_SERVER['REQUEST_METHOD']) && !in_array($_SERVER['REQUEST_METHOD'], $allowMethods))) {
            header($_SERVER['SERVER_PROTOCOL'].' 405 Method Not Allowed');
            exit;
        }
        #Sanitize Origins list
        foreach ($allowOrigins as $key=>$origin) {
            if (preg_match('/'.self::originRegex.'/i', $origin) !== 1) {
                unset($allowOrigins[$key]);
            }
        }
        #Check that list is still not empty, otherwise, we assume, that access from all origins is allowed (akin to *)
        if (!empty($allowOrigins)) {
            if (isset($_SERVER['HTTP_ORIGIN']) && preg_match('/'.self::originRegex.'/i', $_SERVER['HTTP_ORIGIN']) === 1 && in_array($_SERVER['HTTP_ORIGIN'], $allowOrigins)) {
                #Vary is requried by the standard. Using `false` to prevent overwriting of other Vary headers, if any were sent
                header('Vary: Origin', false);
                #Send actual header
                header('Access-Control-Allow-Origin: '.$allowOrigins);
            } else {
                #Send proper header denying access and stop processing
                header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
                exit;
            }
        } else {
            #Vary is requried by the standard. Using `false` to prevent overwriting of other Vary headers, if any were sent
            header('Vary: Origin', false);
            #Send actual header
            header('Access-Control-Allow-Origin: *');
        }
        #HSTS and force HTTPS
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        #Set caching value for CORS
        header('Access-Control-Max-Age: 86400');
        #Allows credentials to be shared to front-end JS. By itself this should not be a security issue, but it may ease of use for 3rd-party parser in some cases if you are using cookies.
        header('Access-Control-Allow-Credentials: true');
        #Allow headers sent from server, normally restricted by CORS
        #Keep a default list, that includes those originally allowed by CORS and those present in this class
        $defaultHeaders = self::exposedHeaders;
        #Merge with custom ones
        $exposeHeaders = array_merge($defaultHeaders, $exposeHeaders);
        #Send list
        header('Access-Control-Expose-Headers: '.implode(', ', $exposeHeaders));
        #Allow headers, that can change server state, but are normally restricted by CORS
        if (!empty($allowHeaders)) {
            header('Access-Control-Allow-Headers: '.implode(', ', $allowHeaders));
        }
        #Set CORS strategy
        switch (strtolower($strat)) {
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
            #Make 'strict' default value, but also allow explicit specification
            case 'strict':
            default:
                header('Cross-Origin-Embedder-Policy: require-corp');
                header('Cross-Origin-Opener-Policy: same-origin');
                header('Cross-Origin-Resource-Policy: same-origin');
                header('Referrer-Policy: no-referrer');
                break;
        }
        #Set defaults directives for CSP
        $defaultDirectives = self::secureDirectives;
        #Apply custom directives
        foreach ($cspDirectives as $directive=>$value) {
            #If value is empty, assume, that we want to remove the directive entirely
            if (empty($value)) {
                unset($defaultDirectives[$directive]);
            } else {
                switch ($directive) {
                    case 'sandbox':
                        #Validate the value we have
                        if (in_array($value, self::sandboxValues)) {
                            $defaultDirectives['sandbox'] = $value;
                        } else {
                            #Ignore the value entirely
                            unset($defaultDirectives['sandbox']);
                        }
                        break;
                    case 'trusted-types':
                        #Validate the value we have
                        if (preg_match('/^\'none\'|((([a-z0-9-#=_\/@.%]+) ?){1,}( ?\'allow-duplicates\')?)$/i', $value) === 1) {
                            $defaultDirectives['trusted-types'] = $value;
                        } else {
                            #Ignore the value entirely
                            unset($defaultDirectives['trusted-types']);
                        }
                        break;
                    case 'plugin-types':
                        #Validate the value we have
                        if (preg_match('/^(('.self::mimeRegex.') ?){1,}$/i', $value) === 1) {
                            $defaultDirectives['plugin-types'] = $value;
                        } else {
                            #Ignore the value entirely
                            unset($defaultDirectives['plugin-types']);
                        }
                        break;
                    case 'report-to':
                        if (!empty($value)) {
                            $defaultDirectives['report-to'] = $value;
                            $defaultDirectives['report-uri'] = $value;
                        } else {
                            #Ignore the value entirely
                            unset($defaultDirectives['report-to']);
                        }
                        break;
                    default:
                        #Validate the value
                        if (isset($defaultDirectives[$directive]) && preg_match('/^(?<nonorigin>(?<standard>\'(none|\*)\'))|(\'self\' ?)?(\'strict-dynamic\' ?)?(\'report-sample\' ?)?(((?<origin>'.self::originRegex.')|(?<nonce>\'nonce-(?<base64>(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{4}))\')|(?<hash>\'sha(256|384|512)-(?<base64_2>(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{4}))\')|((?<justscheme>[a-zA-Z][a-zA-Z0-9+.-]+):))(?<delimiter> )?){1,}$/i', $value) === 1) {
                            #Check if it's script or style source
                            if (in_array($directive, ['script-src', 'script-src-elem', 'script-src-attr', 'style-src', 'style-src-elem', 'style-src-attr'])) {
                                #If it's not 'none' - add 'report-sample'
                                if ($value !== '\'none\'') {
                                    $defaultDirectives[$directive] = '\'report-sample\' '.$value;
                                } else {
                                    $defaultDirectives[$directive] = $value;
                                }
                            } else {
                                $defaultDirectives[$directive] = $value;
                            }
                        }
                        break;
                }
            }
        }
        #plugin-types is not used if all objects are blocked
        if ($defaultDirectives['object-src'] === '\'none\'') {
            unset($defaultDirectives['plugin-types']);
        } else {
            #If empty provides inconsitent behaviour depending on browser
            if (empty($defaultDirectives['plugin-types'])) {
                unset($defaultDirectives['plugin-types']);
            }
        }
        #Sandbox is ignored if we use Report-Only
        if (!empty($cspReportURI)) {
            unset($defaultDirectives['sandbox']);
        }
        #Generate line for CSP
        $cspLine = '';
        foreach ($defaultDirectives as $directive=>$value) {
            if (!empty($value)) {
                $cspLine .= $directive.' '.$value.'; ';
            }
        }
        #If report is set also send Content-Security-Policy-Report-Only header
        if ($reportonly === false) {
            header('Content-Security-Policy: upgrade-insecure-requests; '.trim($cspLine));
        } else {
            if (!empty($defaultDirectives['report-to'])) {
                header('Content-Security-Policy-Report-Only: '.trim($cspLine));
            }
        }
        
        header('Link: <https://'.$_SERVER['HTTP_HOST'].str_ireplace('?onepager', '', $_SERVER['REQUEST_URI']).'>; rel=canonical;', false);
        return $this;
    }
    
    #Function to process Sec-Fetch headers. Arrays are set to empty ones by default for ease of use (sending empty array is a bit easier than copying values).
    #$strict allows to enforce compliance with suported values only. Current W3C allows ignoring headers, if not sent or have unsupported values, but we may want to be stricter by setting this option to true
    #Below amterials were used in preparation
    #https://www.w3.org/TR/fetch-metadata/
    #https://fetch.spec.whatwg.org/
    #https://web.dev/fetch-metadata/
    public function secFetch(array $site = [], array $mode = [], array $user = [], array $dest = [], bool $strict = false)
    {
        #Set flag for processing
        $badRequest = false;
        #Check if Sec-Fetch was passed at all (older browsers will not use it). Process it only if it's present.
        if (isset($_SERVER['HTTP_SEC_FETCH_SITE'])) {
            #Check if support values are sent in headers
            if (
                in_array($_SERVER['HTTP_SEC_FETCH_SITE'], self::fetchSite) &&
                in_array($_SERVER['HTTP_SEC_FETCH_MODE'], self::fetchMode) &&
                (
                    empty($_SERVER['HTTP_SEC_FETCH_USER']) ||
                    in_array($_SERVER['HTTP_SEC_FETCH_USER'], self::fetchUser)
                ) &&
                (
                    empty($_SERVER['HTTP_SEC_FETCH_DEST']) ||
                    in_array($_SERVER['HTTP_SEC_FETCH_DEST'], self::fetchDest)
                )
            ) {
                #Setting defaults
                $site = array_intersect($site, self::fetchSite);
                if (empty($site)) {
                    #Alloes only same-origin (site and subdomain) or top-navigation
                    $site = ['same-origin', 'none'];
                }
                $mode = array_intersect($mode, self::fetchMode);
                if (empty($mode)) {
                    #Allow all modes
                    $mode = self::fetchMode;
                }
                $user = array_intersect($user, self::fetchUser);
                if (empty($user)) {
                    #Allow only actions triggered by user activation
                    $user = ['?1'];
                }
                $dest = array_intersect($dest, self::fetchDest);
                if (empty($dest)) {
                    $dest = [
                        #Allow navigation (including from frames)
                        'document', 'embed', 'frame', 'iframe',
                        #Allow common elements
                        'audio', 'font', 'image', 'style', 'video', 'track', 'manifest',
                        #Allow empty
                        'empty',
                    ];
                }
                #Actual validation
                if (
                    !in_array($_SERVER['HTTP_SEC_FETCH_SITE'], $site) ||
                    !in_array($_SERVER['HTTP_SEC_FETCH_MODE'], $mode) ||
                    (
                        !empty($_SERVER['HTTP_SEC_FETCH_USER']) &&
                        !in_array($_SERVER['HTTP_SEC_FETCH_USER'], $user)
                    ) ||
                    (
                        !empty($_SERVER['HTTP_SEC_FETCH_DEST']) &&
                        !in_array($_SERVER['HTTP_SEC_FETCH_DEST'], $dest)
                    )
                ) {
                    $badRequest = true;
                } else {
                    #There is also a recomendation to check wheter a script-like is requesting certain MIME types
                    #Normally this should be done by browser, but we can do that as well and be independent from their logic
                    if (!empty($_SERVER['HTTP_SEC_FETCH_DEST']) && in_array($_SERVER['HTTP_SEC_FETCH_DEST'], self::scriptLike)) {
                        #Attempt to get content-type headers
                        $contenttype = '';
                        #This header may be present in some cases
                        if (isset($_SERVER['HTTP_CONTENT_TYPE'])) {
                            $contenttype = $_SERVER['HTTP_CONTENT_TYPE'];
                        } else {
                            #This is a standard header that should be present in PHP. Usually in case of POST method
                            if (isset($_SERVER['CONTENT_TYPE']) || isset($_SERVER['HTTP_CONTENT_TYPE'])) {
                                $contenttype = $_SERVER['CONTENT_TYPE'];
                            }
                        }
                        #Check if we have already sent our own content-type header
                        foreach (headers_list() as $header) {
                            if (preg_match('/^Content-type:/', $header) === 1) {
                                #Get MIME
                                $contenttype = preg_replace('/^(Content-type:\s*)('.self::mimeRegex.')(;.*?)$/', '$2', $header);
                                break;
                            }
                        }
                        #If MIME is found and it amtches CSV, audio, image or video - reject
                        if (!empty($contenttype) && preg_match('/(text\/csv)|((audio|image|video)\/[-+\w.]+)/', $contenttype) === 1) {
                            $badRequest = true;
                        }
                    }
                }
            } else {
                #Reject if we want to be stricter than W3C
                if ($strict) {
                    $badRequest = true;
                }
            }
        } else {
            #Reject if we want to be stricter than W3C
            if ($strict) {
                $badRequest = true;
            }
        }
        if ($badRequest) {
            #Send proper header denying access and stop processing
            header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
            exit;
        }
        return $this;
    }
    
    #Function to send headers, that may improve performance on client side
    public function performance(int $keepalive = 0)
    {
        #Prevent content type sniffing (determening file type by content, not by extension or header)
        header('X-Content-Type-Options: nosniff');
        #Allow DNS prefetch for some performance improvement on client side
        header('X-DNS-Prefetch-Control: on');
        #Keep-alive connection if not using HTTP2.0 (which prohibits it). Setting maximum number of connection as timeout power 1000. If a human is opening the pages, it's unlike he will be opening more than 1 page per second and it's unlikely that any page will have more than 1000 files linked to same server. If it does - some optimization may be required.
        if ($keepalive > 0 && $_SERVER['SERVER_PROTOCOL'] !== 'HTTP/2.0') {
            header('Connection: Keep-Alive');
            header('Keep-Alive: timeout='.$keepalive.', max='.($keepalive*1000));
        }
        return $this;
    }
    
    #Function to manage Feature-Policy to control differnet features. By default most features are disabled for security and performance
    #https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy
    #https://feature-policy-demos.appspot.com/
    #https://featurepolicy.info/
    public function features(array $features = [], bool $forcecheck = true)
    {
        $defaults = self::secureFeatures;
        foreach ($features as $feature=>$allowlist) {
            #Sanitize
            $feature = strtolower(trim($feature));
            $allowlist = strtolower(trim($allowlist));
            #If validation is enforced, validate the feature and value provided
            if ($forcecheck === false || ($forcecheck === true && isset($defaults[$feature]) && preg_match('/^(?<nonorigin>(?<standard>\*|\'none\')(?<setting>\(\d{1,}(\.\d{1,})?\))?)|(\'self\' ?)?(?<origin>'.self::originRegex.'(?<setting_o>\(\d{1,}(\.\d{1,})?\))?(?<delimiter> )?){1,}$/i', $allowlist) === 1)) {
                #Update value
                $defaults[$feature] = $allowlist;
            }
        }
        #Generate line for header
        $headerline = '';
        foreach ($defaults as $feature=>$allowlist) {
            $headerline .= $feature.' '.$allowlist.'; ';
        }
        header('Feature-Policy: '.trim($headerline));
        return $this;
    }
    
    #Function to set Last-Modified header. This header is generally not required if you already have Cache-Control and ETag, but still may be useful in case of conditional requests. At least if you will provide it with proper modification time.
    public function lastModified(int $modtime = 0, bool $exit = false)
    {
        if ($modtime <= 0) {
            #Get freshest modification time of all PHP files used ot PHP's getlastmod time
            $modtime = max(max(array_map('filemtime', array_filter(get_included_files(), 'is_file'))), getlastmod());
        }
        #Send header
        header('Last-Modified: '.gmdate(\DATE_RFC7231, $modtime));
        #Set the flag to false for now
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
           $IfModifiedSince = strtotime(substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 5));
           if ($IfModifiedSince >= $modtime) {
                #If content has not beend modified - return 304
                header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified');
                if ($exit === true) {
                    exit;
                }
            }
        }
        return $this;
    }
    
    #Function to prepare and send cache-related headers
    public function cacheControl(string $string, string $cacheStrat = '', bool $exit = false)
    {
        #Send headers related to cache based on strategy selected
        #Some of the strategies are derived from https://csswizardry.com/2019/03/cache-control-for-civilians/
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
                header('Cache-Control: max-age=86400, must-revalidate, stale-while-revalidate=43200, stale-if-error=43200, no-transform');
                break;
            #Make 'hour' default value, but also allow explicit specification
            case 'hour':
            default:
                header('Cache-Control: max-age=3600, stale-while-revalidate=1800, stale-if-error=1800, no-transform');
                break;
        }
        #Set ETag
        $etag = hash('sha3-256', $string);
        #Send ETag for caching purposes
        header('ETag: '.$etag);
        #Check if we have a conditional request. While this may have a less ideal placement than lastModified(), since ideally you will have some text to output first, but it can still save some time on client side
        if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
            if (trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
                #If content has not beend modified - return 304
                header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified');
                if ($exit === true) {
                    exit;
                }
            }
        }
        if (isset($_SERVER['HTTP_IF_MATCH'])) {
            if (trim($_SERVER['HTTP_IF_MATCH']) !== $etag) {
                header($_SERVER['SERVER_PROTOCOL'].' 412 Precondition Failed');
                if ($exit === true) {
                    exit;
                }
            }
        }
        return $this;
    }
}

?>