<?php
declare(strict_types=1);
namespace Simbiat\http20;

class Headers
{    
    #Regex to validate Origins (essentially, an URI in https://examplecom:443 format)
    public const originRegex = '(?<scheme>[a-zA-Z][a-zA-Z0-9+.-]+):\/\/(?<host>[a-zA-Z0-9.\-_~]+)(?<port>:\d+)?';
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
        'default-src' => '\'self\'', 'child-src' => '\'self\'', 'connect-src' => '\'self\'', 'font-src' => '\'self\'', 'frame-src' => '\'self\'',
        #Blocking images, because images can be used to inject scripts:
        #https://www.secjuice.com/hiding-javascript-in-png-csp-bypass/
        #https://portswigger.net/research/bypassing-csp-using-polyglot-jpegs
        'img-src' => '\'none\'', 'manifest-src' => '\'self\'', 'media-src' => '\'self\'', 'object-src' => '\'none\'', 'prefetch-src' => '\'self\'', 'script-src' => '\'none\'', 'script-src-elem' => '\'none\'', 'script-src-attr' => '\'none\'', 'style-src' => '\'none\'', 'style-src-elem' => '\'none\'', 'style-src-attr' => '\'none\'', 'worker-src' => '\'self\'',
        #Document directives
        'base-uri' => '\'self\'', 'plugin-types' => '', 'sandbox' => '',
        #Navigate directives
        'form-action' => '\'self\'', 'frame-ancestors' => '\'self\'', 'navigate-to' => '\'self\'',
        #Other directives
        'require-trusted-types-for' => '\'script\'', 'trusted-types' => '', 'report-to' => '',
    ];
    #Default values for Feature-Policy, essentially disabling most of them
    public const secureFeatures = [
        #Disable access to sensors
        'accelerometer' => '\'none\'', 'ambient-light-sensor' => '\'none\'', 'gyroscope' => '\'none\'', 'magnetometer' => '\'none\'', 'vibrate' => '\'none\'',
        #Disable access to devices
        'camera' => '\'none\'', 'microphone' => '\'none\'', 'midi' => '\'none\'', 'battery' => '\'none\'', 'usb' => '\'none\'', 'speaker' => '\'none\'',
        #Changing document.domain can allow some cross-origin access and is discouraged, due to existence of other (better) mechanisms
        'document-domain' => '\'none\'',
        #document-write (.write, .writeln, .open and .close) is aslo discouraged because it dynamically rewrites your HTML markup and blocks parsing of the document. While this may not be exactly a security concern, if there is a stray script, that uses it, we have little control (if any) regarding what exactly it modifies.
        'document-write' => '\'none\'',
        #Allowing use of DRM and Web Authentication API, but only on our site and its own frames
        'encrypted-media' => '\'self\'', 'publickey-credentials-get' => '\'self\'',
        #Disable geolocation, XR tracking, payment and screen capture APIs
        'geolocation' => '\'none\'', 'xr-spatial-tracking' => '\'none\'', 'payment' => '\'none\'', 'display-capture' => '\'none\'',
        #Disable wake-locks
        'wake-lock' => '\'none\'', 'screen-wake-lock' => '\'none\'',
        #Disable Web Share API. It's recommended to enable it explicitely for pages, where sharing will not expose potentially sensetive materials
        'web-share' => '\'none\'',
        #Disable synchronous XMLHttpRequests (that were technically deprecated)
        'sync-xhr' => '\'none\'',
        #Disable synchronous parsing blocking scripts (inline without defer/asycn attribute)
        'sync-script' => '\'none\'',
        #Disable WebVR API (halted standard, replaced by WebXR)
        'vr' => '\'none\'',
        #Images optimizations as per https://github.com/w3c/webappsec-permissions-policy/blob/master/policies/optimized-images.md
        'oversized-images' => '*(2.0)', 'unoptimized-images' => '*(0.5)', 'unoptimized-lossy-images' => '*(0.5)', 'unoptimized-lossless-images' => '*(1.0)', 'legacy-image-formats' => '\'none\'', 'unsized-media' => '\'none\'', 'image-compression' => '\'none\'', 'maximum-downscaling-image' => '\'none\'',
        #Disable lazyload. Do not apply it to everything. While it can improve performacne somewhat, if it's applied to everything it can provide a reversed effect. Apply it strategically with lazyload attribute.
        'lazyload' => '\'none\'',
        #Disable autoplay, font swapping, fullscreen and picture-in-picture (if triggered in some automatic mode, can really annoy users)
        'autoplay' => '\'none\'', 'fullscreen' => '\'none\'', 'picture-in-picture' => '\'none\'',
        #Turn off font swapping and CSS animations for any property that triggers a re-layout (e.g. top, width, max-height)
        'font-display-late-swap' => '\'none\'', 'layout-animations' => '\'none\'',
        #Disable execution of scripts/task in elements, that are not rendered or visible
        'execution-while-not-rendered' => '\'none\'', 'execution-while-out-of-viewport' => '\'none\'',
        #Disabling APIs for modification of spatial navgiation and scrolling, since you need them only for specific cases
        'navigation-override' => '\'none\'', 'vertical-scroll' => '\'none\'',
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
    #List of standard HTTP status codes
    public const HTTPCodes = [
        '100' => 'Continue', '101' => 'Switching Protocols', '102' => 'Processing', '103' => 'Early Hints',
        '200' => 'OK', '201' => 'Created', '202' => 'Accepted', '203' => 'Non-Authoritative Information', '204' => 'No Content', '205' => 'Reset Content', '206' => 'Partial Content', '207' => 'Multi-Status', '208' => 'Already Reported', '226' => 'IM Used',
        '300' => 'Multiple Choices', '301' => 'Moved Permanently', '302' => 'Found', '303' => 'See Other', '304' => 'Not Modified', '305' => 'Use Proxy', '306' => 'Switch Proxy', '307' => 'Temporary Redirect', '308' => 'Permanent Redirect',
        '400' => 'Bad Request', '401' => 'Unauthorized', '402' => 'Payment Required', '403' => 'Forbidden', '404' => 'Not Found', '405' => 'Method Not Allowed', '406' => 'Not Acceptable', '407' => 'Proxy Authentication Required', '408' => 'Request Timeout', '409' => 'Conflict', '410' => 'Gone', '411' => 'Length Required', '412' => 'Precondition Failed', '413' => 'Payload Too Large', '414' => 'URI Too Long', '415' => 'Unsupported Media Type', '416' => 'Range Not Satisfiable', '417' => 'Expectation Failed', '418' => 'I\'m a teapot', '421' => 'Misdirected Request', '422' => 'Unprocessable Entity', '423' => 'Locked', '424' => 'Failed Dependency', '425' => 'Too Early', '426' => 'Upgrade Required', '428' => 'Precondition Required', '429' => 'Too Many Requests', '431' => 'Request Header Fields Too Large', '451' => 'Unavailable For Legal Reasons',
        '500' => 'Internal Server Error', '501' => 'Not Implemented', '502' => 'Bad Gateway', '503' => 'Service Unavailable', '504' => 'Gateway Timeout', '505' => 'HTTP Version Not Supported', '506' => 'Variant Also Negotiates', '507' => 'Insufficient Storage', '508' => 'Loop Detected', '510' => 'Not Extended', '511' => 'Network Authentication Required', 
    ];
    
    #Function sends headers, related to security
    public function security(string $strat = 'strict', array $allowOrigins = [], array $exposeHeaders = [], array $allowHeaders = [], array $allowMethods = []): self
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
            $this->clientReturn('405', true);
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
                #Send actual headers
                header('Access-Control-Allow-Origin: '.$allowOrigins);
                header('Timing-Allow-Origin: '.$allowOrigins);
            } else {
                #Send proper header denying access and stop processing
                $this->clientReturn('403', true);
            }
        } else {
            #Vary is requried by the standard. Using `false` to prevent overwriting of other Vary headers, if any were sent
            header('Vary: Origin', false);
            #Send actual headers
            header('Access-Control-Allow-Origin: *');
            header('Timing-Allow-Origin: '.(isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://'.$_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT']);
        }
        #HSTS and force HTTPS
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        #Set caching value for CORS
        header('Access-Control-Max-Age: 86400');
        #Allows credentials to be shared to front-end JS. By itself this should not be a security issue, but it may ease of use for 3rd-party parser in some cases if you are using cookies.
        header('Access-Control-Allow-Credentials: true');
        #Allow headers sent from server, normally restricted by CORS
        #Keep a default list, that includes those originally allowed by CORS and those present in this class as self::exposedHeaders
        #Send list
        header('Access-Control-Expose-Headers: '.implode(', ', array_merge(self::exposedHeaders, $exposeHeaders)));
        #Allow headers, that can change server state, but are normally restricted by CORS
        if (!empty($allowHeaders)) {
            header('Access-Control-Allow-Headers: '.implode(', ', array_merge(['Accept', 'Accept-Language', 'Content-Language', 'Content-Type'], $allowHeaders)));
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
        return $this;
    }
    
    #Function to process CSP header
    public function contentPolicy(array $cspDirectives = [], bool $reportonly = false): self
    {
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
                        if (preg_match('/^(('.(new \Simbiat\http20\Common)::mimeRegex.') ?){1,}$/i', $value) === 1) {
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
                            unset($defaultDirectives['report-uri']);
                        }
                        break;
                    case 'report-uri':
                        #Ensure that we do not use report-uri, unless there is a report-to, since report-uri is deprecated
                        unset($defaultDirectives['report-uri']);
                    default:
                        #Validate the value
                        if (isset($defaultDirectives[$directive]) && preg_match('/^(?<nonorigin>(?<standard>\'(none|self|\*)\'))|(\'self\' ?)?(\'strict-dynamic\' ?)?(\'report-sample\' ?)?(((?<origin>'.self::originRegex.')|(?<nonce>\'nonce-(?<base64>(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{4}))\')|(?<hash>\'sha(256|384|512)-(?<base64_2>(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{4}))\')|((?<justscheme>[a-zA-Z][a-zA-Z0-9+.-]+):))(?<delimiter> )?){1,}$/i', $value) === 1) {
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
        return $this;
    }
    
    #Function to process Sec-Fetch headers. Arrays are set to empty ones by default for ease of use (sending empty array is a bit easier than copying values).
    #$strict allows to enforce compliance with suported values only. Current W3C allows ignoring headers, if not sent or have unsupported values, but we may want to be stricter by setting this option to true
    #Below amterials were used in preparation
    #https://www.w3.org/TR/fetch-metadata/
    #https://fetch.spec.whatwg.org/
    #https://web.dev/fetch-metadata/
    public function secFetch(array $site = [], array $mode = [], array $user = [], array $dest = [], bool $strict = false): self
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
                    #Allows only same-origin (site and subdomain) or top-navigation
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
                    #If we have only 'same-origin' and/or 'none', allow script as well, because otherwise default settings will prevent access to JS files hosted on same domain
                    if (in_array($site, [['same-origin', 'none'], ['same-origin'], ['none']])) {
                        $dest[] = 'script';
                    }
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
                        #Cache mimeRegex
                        $mimeRegex = (new \Simbiat\http20\Common)::mimeRegex;
                        #Check if we have already sent our own content-type header
                        foreach (headers_list() as $header) {
                            if (preg_match('/^Content-type:/', $header) === 1) {
                                #Get MIME
                                $contenttype = preg_replace('/^(Content-type:\s*)('.$mimeRegex.')$/', '$2', $header);
                                break;
                            }
                        }
                        #If MIME is found and it matches CSV, audio, image or video - reject
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
            $this->clientReturn('403', true);
        }
        return $this;
    }
    
    #Function to send headers, that may improve performance on client side
    public function performance(int $keepalive = 0, array $clientHints = []): self
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
        if (!empty($clientHints)) {
            #Implode client hints
            $clientHints = implode(', ', $clientHints);
            #Notify, that we support Client Hints: https://developer.mozilla.org/en-US/docs/Glossary/Client_hints
            #Logic for processing them should be done outside this function, though
            header('Accept-CH: '.$clientHints);
            #Instruct to vary cache depending on client hints
            header('Vary: '.$clientHints, false);
        }
        return $this;
    }
    
    #Function to manage Feature-Policy to control differnet features. By default most features are disabled for security and performance
    #https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy
    #https://feature-policy-demos.appspot.com/
    #https://featurepolicy.info/
    public function features(array $features = [], bool $forcecheck = true):self 
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
    public function lastModified(int|string $modtime = 0, bool $exit = false): self
    {
        #In case it's not numeric, replace with 0
        if (!is_numeric($modtime)) {
            $modtime = 0;
        } else {
            $modtime = intval($modtime);
        }
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
                $this->clientReturn('304', $exit);
            }
        }
        return $this;
    }
    
    #Function to prepare and send cache-related headers
    public function cacheControl(string $string = '', string $cacheStrat = '', bool $exit = false): self
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
        #Ensure that caching works properly in case client did not support compresion, but now does or vice-versa and in case data-saving mode was requested by client at any point.
        header('Vary: Save-Data, Accept-Encoding', false);
        #Set ETag
        if (!empty($string)) {
            $this->eTag(hash('sha3-256', $string), $exit);
        }
        return $this;
    }
    
    #Handle Etag header and its validation depending on request headers
    public function eTag(string $etag, bool $exit = false): self
    {
        #Send ETag for caching purposes
        header('ETag: '.$etag);
        #Check if we have a conditional request. While this may have a less ideal placement than lastModified(), since ideally you will have some text to output first, but it can still save some time on client side
        if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
            if (trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
                #If content has not beend modified - return 304
                $this->clientReturn('304', $exit);
            }
        }
        #Return error if If-Match was sent and it's different from our etag
        if (isset($_SERVER['HTTP_IF_MATCH'])) {
            if (trim($_SERVER['HTTP_IF_MATCH']) !== $etag) {
                $this->clientReturn('412', $exit);
            }
        }
        return $this;
    }
    
    #Function to return to client and optionally force-close connection
    public function clientReturn(string $code = '500 Internal Server Error', bool $exit = true): bool
    {
        #Generate response
        if (is_numeric($code)) {
            if (isset(self::HTTPCodes[$code])) {
                $response = $code.' '.self::HTTPCodes[$code];
            } else {
                #Non standard code without text, not compliant with the standard
                $response = '500 Internal Server Error';
            }
        } else {
            $response = $code;
        }
        #If response doe snot comply with HTTP standard - replace it with 500
        if (preg_match('/^([12345]\d{2})( .{1,})$/', $response) !== 1) {
            $response = '500 Internal Server Error';
        }
        if (preg_match('/^([123]\d{2})( .{1,})$/', $response) === 0) {
            $positive = true;
        } else {
            $positive = false;
        }
        #Send response header
        @header($_SERVER['SERVER_PROTOCOL'].' '.$response);
        if ($exit) {
            (new \Simbiat\http20\Common)->forceClose();
        } else {
            return $positive;
        }
    }
    
    #Function to handle redirects
    public function redirect(string $newURI, bool $permanent = true, bool $preserveMethod = true, bool $forceGET = false): void
    {
        #If we want to enforce GET method, we can use 303: it tells client to retrieve a different page using GET method, even if original was not GET
        if ($forceGET) {
            $code = '303 See Other';
        } else {
            #Permanent redirect without change of method
            if ($permanent && $preserveMethod) {
                $code = '308 Permanent Redirect';
            #Temporary redirect without change of method
            } elseif (!$permanent && $preserveMethod) {
                $code = '307 Temporary Redirect';
            #Permanent redirect allowing change of method
            } elseif ($permanent && !$preserveMethod) {
                $code = '301 Moved Permanently';
            #Temporary redirect allowing change of method
            } elseif (!$permanent && !$preserveMethod) {
                $code = '302 Found';
            }
        }
        #Validate URI
        if ((new \Simbiat\http20\Common)->uriValidator($newURI) === true) {
            #Send Location header, indicating new URL to be used
            header('Location: '.$newURI);
        } else {
            #Update code to 500, since sometihng must have gone wrong
            $code = '500 Internal Server Error';
        }
        #Send code and enforce connection closure
        $this->clientReturn($code, true);
    }
    
    #Function to return a Link header (https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Link) or respective HTML set of tags
    public function links(array $links = [], string $type = 'header', bool $strictRel = true): self|string
    {
        #Validate type
        if (!in_array($type, ['header', 'head', 'body'])) {
            throw new \UnexpectedValueException('Unsupported type was provided to `links` function');
        }
        #Check if Save-Data is on
        if (isset($_SERVER['HTTP_SAVE_DATA']) && preg_match('/^on$/i', $_SERVER['HTTP_SAVE_DATA']) === 1) {
            $savedata = true;
        } else {
            $savedata = false;
        }
        #Cache (new \Simbiat\http20\Common)
        $common = (new \Simbiat\http20\Common);
        #Cache langTagRegex
        $langTagRegex = $common::langTagRegex;
        #Cache langEncRegex
        $langEncRegex = $common::langEncRegex;
        #Cache extToMime
        $extToMime = $common::extToMime;
        #Cache mimeRegex
        $mimeRegex = $common::mimeRegex;
        #Destroy $common since we do not need it anymore
        unset($common);
        #Prepare an empty string
        $linksToSend = [];
        foreach ($links as $key=>$link) {
            #Check that element is an array;
            if (!is_array($link)) {
                continue;
            }
            #If Save-Data is set to 'on', disable (remove respective rel) HTTP2 push logic (that is preloads and prefetches)
            if ($savedata === true && isset($link['rel']) && preg_match('/(dns-prefetch|modulepreload|preconnect|prefetch|preload|prerender)/i', $link['rel']) === 1) {
                $link['rel'] = preg_replace('/(dns-prefetch|modulepreload|preconnect|prefetch|preload|prerender)/i', '', $link['rel']);
                #Replace multiple whitespaces with single space and trim
                $link['rel'] = trim(preg_replace('/\s{2,}/', ' ', $link['rel']));
                #Unset rel if it's empty
                if (empty($link['rel'])) {
                    unset($link['rel']);
                }
                #Unset 'imagesrcset', 'imagesizes' and 'as', since they are allowed only with preload. If we do not do this some links may get skipped by logic below.
                unset($link['imagesrcset'], $link['imagesizes'], $link['as']);
            }
            #Sanitize links based on https://html.spec.whatwg.org/multipage/semantics.html#the-link-element
            if (
                #Either href or imagesrcset or both need to be present. imagesrcset does not make sense in HTTP header
                ((!isset($link['href'])) && !isset($link['imagesrcset']) || ($type === 'header' && !isset($link['href']))) ||
                #Either rel or itemprop can be set at a time. itemprop does not make sense in HTTP header
                ((!isset($link['rel']) && !isset($link['itemprop'])) || (isset($link['rel']) && isset($link['itemprop'])) || ($type === 'header' && !isset($link['rel']))) ||
                #Validate rel values
                (isset($link['rel']) &&
                    #If strictRel is true, only support types from https://html.spec.whatwg.org/multipage/links.html#linkTypes and https://microformats.org/wiki/existing-rel-values#formats (types, that NEED to be supported by clients)
                    ($strictRel === true && (
                        #Check that rel is valid
                        (preg_match('/^(?!$)(alternate( |$))?((appendix|author|canonical|chapter|child|contents|copyright|dns-prefetch|glossary|help|icon|apple-touch-icon|apple-touch-icon-precomposed|mask-icon|its-rules|license|manifest|me|modulepreload|next|pingback|preconnect|prefetch|preload|prerender|prev|previous|search|section|stylesheet|subsection|toc|transformation|up|first|last|index|home|top)( |$))*/i', $link['rel']) !== 1) ||
                        #If crossorigin or referrerpolicy is set, check that rel type is an external resource
                        ((isset($link['crossorigin']) || isset($link['referrerpolicy'])) && preg_match('/^(alternate )?((dns-prefetch|icon|apple-touch-icon|apple-touch-icon-precomposed|mask-icon|manifest|modulepreload|pingback|preconnect|prefetch|preload|prerender|stylesheet)( |$))*/i', $link['rel']) !== 1)
                    )) ||
                    #If we are using "body", check that rel is body-ok one
                    ($type === 'body' && preg_match('/^(alternate )?.*(dns-prefetch|modulepreload|pingback|preconnect|prefetch|preload|prerender|stylesheet).*$/i', $link['rel']) !== 1) ||
                    #imagesrcset and imagesizes are allowed only for preload with as=image
                    ((isset($link['imagesrcset']) || isset($link['imagesizes'])) && (preg_match('/^(alternate )?.*preload.*$/i', $link['rel']) !== 1 || !isset($link['as']) || $link['as'] !== 'image')) ||
                    #sizes attribute should be set only if rel is icon of apple-touch-icon
                    (isset($link['sizes']) && preg_match('/^(alternate )?.*(icon|apple-touch-icon|apple-touch-icon-precomposed).*$/i', $link['rel']) !== 1) ||
                    #as is allowed only for preload
                    (isset($link['as']) && preg_match('/^(alternate )?.*(modulepreload|preload|prefetch).*$/i', $link['rel']) !== 1) ||
                    #color is allowed only for mask-icon
                    (isset($link['color']) && preg_match('/^(alternate )?.*mask-icon.*$/i', $link['rel']) !== 1)
                ) ||
                #imagesrcset is an image candidate with width descriptor, we need imagesizes as well
                (isset($link['imagesrcset']) && preg_match('/ \d{1,}w(,|$)/', $link['imagesrcset']) === 1 && !isset($link['imagesizes'])) ||
                #as is allowed to have limited set of values (as per https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content). Also check that crossorigin is set, if as=fetch
                (isset($link['as']) && (preg_match('/^(document|object|embed|audio|font|image|script|worker|style|track|video|fetch)$/i', $link['as']) !== 1 || (preg_match('/^fetch$/i', $link['as']) === 1 && !isset($link['crossorigin']))))
            ) {
                #Skip the element, since it does not confirm with the standard
                continue;
            }
            #referrerpolicy is allowed to have limited set of values
            if (isset($link['referrerpolicy']) && preg_match('/^no-referrer|no-referrer-when-downgrade|strict-origin|strict-origin-when-cross-origin|same-origin|origin|origin-when-cross-origin|unsafe-url)$/i', $link['(referrerpolicy']) !== 1) {
                unset($link['referrerpolicy']);
            }
            #Remove hreflang, if it's a wrong language value
            if (isset($link['hreflang']) && preg_match($langTagRegex, $link['hreflang']) !== 1) {
                unset($link['hreflang']);
            }
            #Remove sizez if wrong format
            if (isset($link['sizes']) && preg_match('/((any|[1-9]\d{1,}[xX][1-9]\d{1,})( |$)){1,}$/i', $link['sizes']) !== 1) {
                unset($link['sizes']);
            }
            #Sanitize crossorigin, if set
            if (isset($link['crossorigin']) && (empty($link['crossorigin']) || !in_array($link['crossorigin'], ['anonymous', 'use-credentials']))) {
                $link['crossorigin'] = 'anonymous';
            }
            #Sanitize title if set
            if (isset($link['title'])) {
                $link['title'] = urldecode(htmlspecialchars($link['title']));
            } else {
                $link['title'] = urldecode(htmlspecialchars(basename($link['href'])));
            }
            #Validate title*, which is valid only for HTTP header
            if (isset($link['title*']) && ($type !== 'header' || preg_match('/'.$langEncRegex.'.*/i', $link['title*']) !== 1)) {
                unset($link['title*']);
            }
            #If integrity is set, validate if it's a valid value
            if (isset($link['integrity'])) {
                if (preg_match('/^(sha256|sha384|sha512)-(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{4})$/', $link['integrity']) === 0) {
                    #If not valid, check if it's a file and generate hash
                    if (is_file($link['integrity'])) {
                        #Attempt to get actual MIME type while we're at it
                        if (!isset($link['type']) && extension_loaded('fileinfo')) {
                            $link['type'] = mime_content_type(realpath($link['integrity']));
                        }
                        #Get size of the image, if the file is an image
                        if (!isset($link['sizes']) && isset($link['type']) && preg_match('/^image\/.*$/i', $link['type']) === 1 && parse_url($link['integrity'], PHP_URL_HOST) === NULL) {
                            #Set to 'any' if it's SVG
                            if (preg_match('/^image\/svg+xml$/i', $link['type']) === 1) {
                                $size = 'any';
                            } else {
                                $size = getimagesize(realpath($link['integrity']));
                                if ($size !== false) {
                                    $size = $size[0].'x'.$size[1];
                                    #Unset it if it's empty
                                    if ($size === '0x0') {
                                        $size = '';
                                    }
                                } else {
                                    $size = '';
                                }
                            }
                            #Set tags if we were able to get size
                            if (!empty($size)) {
                                if (isset($link['rel']) && preg_match('/^(alternate )?.*(icon|apple-touch-icon|apple-touch-icon-precomposed).*$/i', $link['rel']) === 1) {
                                    $link['sizes'] = $size;
                                } else {
                                    if (preg_match('/^(alternate )?.*preload.*$/i', $link['rel']) === 1) {
                                        $link['imagesizes'] = $size;
                                        #Sanitize 'as' attribute
                                        if (isset($link['as']) && $link['as'] !== 'image') {
                                            #Assume error or malicoious intent and skip
                                            continue;
                                        } else {
                                            #Set 'as' attribute if rel is preload
                                            if (isset($link['rel']) && preg_match('/^(alternate )?.*(modulepreload|preload|prefetch).*$/i', $link['rel']) === 1) {
                                                $link['as'] = 'image';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        #Get hash if we have a script or style
                        if (isset($link['type']) && preg_match('/^(application\/javascript|text\/css)$/i', $link['type']) === 1 && parse_url($link['integrity'], PHP_URL_HOST) === NULL) {
                            $link['integrity'] = 'sha512-'.base64_encode(hash_file('sha512', realpath($link['integrity'])));
                        } else {
                            unset($link['integrity']);
                        }
                    } else {
                        unset($link['integrity']);
                    }
                }
            }
            #If integrity is set, check that rel type is of proper type, otherwise remove it
            if (isset($link['integrity']) && isset($link['rel']) && preg_match('/^(alternate )?.*(modulepreload|preload|stylesheet).*$/i', $link['rel']) !== 1) {
                unset($link['integrity']);
            }
            #Empty MIME type if it does ont confirm with the standard
            if (isset($link['type']) && preg_match('/'.$mimeRegex.'/', $link['type']) !== 1) {
                $link['type'] = '';
            }
            #Set or update media type based on link. Or, at least, try to
            if (empty($link['type']) && isset($link['href'])) {
                $ext = pathinfo($link['href'], PATHINFO_EXTENSION);
                if (isset($extToMime[$ext])) {
                    $link['type'] = $extToMime[$ext];
                } else {
                    $link['type'] = '';
                }
            }
            if (preg_match('/^(alternate )?.*(modulepreload|preload|prefetch).*$/i', $link['rel']) === 1) {
                #Force 'as' for stylesheet
                if ((!empty($link['type']) && preg_match('/^text\/css(;.*)?$/i', $link['type']) === 1) || (!empty($link['rel']) && preg_match('/^.*(stylesheet).*$/i', $link['rel']) === 1)) {
                    $link['as'] = 'style';
                }
                #Force 'as' for JS
                if ((!empty($link['type']) && preg_match('/^application\/javascript(;.*)?$/i', $link['type']) === 1)) {
                    $link['as'] = 'script';
                }
            }
            #If type is defined, check it corresponds to 'as'. If not - do not process, assume error or malicious intent
            if (!empty($link['type']) && !empty($link['as']) && preg_match('/^(audio|image|video|font)$/i', $link['as']) === 1 && preg_match('/^'.$link['as'].'\/.*$/i', $link['type']) !== 1) {
                continue;
            }
            #Generate element as string
            if ($type === 'header') {
                $linksToSend[] = '<'.$link['href'].'>'.
                    (empty($link['title']) ? '' : '; title="'.$link['title'].'"').
                    (empty($link['title*']) ? '' : '; title*="'.$link['title*'].'"').
                    (empty($link['rel']) ? '' : '; rel="'.$link['rel'].'"').
                    (empty($link['hreflang']) ? '' : '; hreflang="'.$link['hreflang'].'"').
                    (empty($link['type']) ? '' : '; type="'.$link['type'].'"').
                    (empty($link['as']) ? '' : '; as="'.$link['as'].'"').
                    (empty($link['sizes']) ? '' : '; sizes="'.$link['sizes'].'"').
                    (empty($link['imagesizes']) ? '' : '; imagesizes="'.$link['imagesizes'].'"').
                    (empty($link['media']) ? '' : '; media="'.$link['media'].'"').
                    (empty($link['integrity']) ? '' : '; integrity="'.$link['integrity'].'"').
                    (empty($link['crossorigin']) ? '' : '; crossorigin="'.$link['crossorigin'].'"').
                    (empty($link['referrerpolicy']) ? '' : '; referrerpolicy="'.$link['referrerpolicy'].'"')
                ;
            } else {
                $linksToSend[] = '<link'.
                    (empty($link['href']) ? '' : ' href="'.$link['href'].'"').
                    (empty($link['imagesrcset']) ? '' : ' imagesrcset="'.$link['imagesrcset'].'"').
                    (empty($link['title']) ? '' : ' title="'.$link['title'].'"').
                    (empty($link['rel']) ? '' : ' rel="'.$link['rel'].'"').
                    (empty($link['itemprop']) ? '' : ' itemprop="'.$link['itemprop'].'"').
                    (empty($link['hreflang']) ? '' : ' hreflang="'.$link['hreflang'].'"').
                    (empty($link['type']) ? '' : ' type="'.$link['type'].'"').
                    (empty($link['as']) ? '' : ' as="'.$link['as'].'"').
                    (empty($link['color']) ? '' : ' color="'.$link['color'].'"').
                    (empty($link['sizes']) ? '' : ' sizes="'.$link['sizes'].'"').
                    (empty($link['imagesizes']) ? '' : ' imagesizes="'.$link['imagesizes'].'"').
                    (empty($link['media']) ? '' : ' media="'.$link['media'].'"').
                    (empty($link['integrity']) ? '' : ' integrity="'.$link['integrity'].'"').
                    (empty($link['crossorigin']) ? '' : ' crossorigin="'.$link['crossorigin'].'"').
                    (empty($link['referrerpolicy']) ? '' : ' referrerpolicy="'.$link['referrerpolicy'].'"').
                '>';
            }
        }
        if (empty($linksToSend)) {
            if ($type === 'header') {
                return $this;
            } else {
                return '';
            }
        } else {
            if ($type === 'header') {
                header('Link: '.preg_replace('/[\r\n]/i', '', implode(', ', $linksToSend)), false);
                return $this;
            } else {
                return implode("\r\n", $linksToSend);
            }
        }
    }
    
    #Function to handle Accept request header
    public function notAccept(array $supported = ['text/html'], bool $exit = true): bool|string
    {
        #Check if header is set and we do have a limit on supported MIME types
        if (isset($_SERVER['HTTP_ACCEPT']) && !empty($supported)) {
            #text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9
            #Generate list of acceptable values
            $acceptable = [];
            foreach ($supported as $mime) {
                #Split MIME
                $mime = explode('/', $mime);
                #Attempt to get priority for supported MIME type (with optional subtype)
                if (preg_match('/.*('.$mime[0].'\/('.$mime[1].'|\*))(;q=((0\.[0-9])|[0-1])(?>\s*(,|$)))?.*/m', $_SERVER['HTTP_ACCEPT'], $matches) === 1) {
                    #Add to array
                    if (!isset($matches[4]) || $matches[4] === '') {
                        $acceptable[$mime[0].'/'.$mime[1]] = floatval(1);
                    } else {
                        $acceptable[$mime[0].'/'.$mime[1]] = floatval($matches[4]);
                    }
                }
            }
            #Check if any of the supported types are acceptable
            if (empty($acceptable)) {
                #If not - check if */* is supported
                if (preg_match('/\*\/\*/', $_SERVER['HTTP_ACCEPT']) === 1) {
                    #Consider as no limitation
                    return true;
                } else {
                    #Send 406 Not Acceptable
                    return $this->clientReturn('406', $exit);
                }
            } else {
                #Get the one with highest priority and return its value
                return array_keys($acceptable, max($acceptable))[0];
            }
            exit;
        } else {
            #Consider as no limitation
            return true;
        }
    }
}

?>