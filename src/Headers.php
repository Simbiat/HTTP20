<?php
declare(strict_types=1);
namespace http20;

class Headers
{
    #List of supported features for Feature-Policy header, in case we are validating the policy name
    private array $features = ['accelerometer', 'ambient-light-sensor', 'autoplay', 'battery', 'camera', 'display-capture', 'document-domain', 'document-write', 'encrypted-media', 'execution-while-not-rendered', 'execution-while-out-of-viewport', 'font-display-late-swap', 'fullscreen', 'geolocation', 'gyroscope', 'image-compression', 'layout-animations', 'lazyload', 'legacy-image-formats', 'magnetometer', 'maximum-downscaling-image', 'microphone', 'midi', 'navigation-override', 'oversized-images', 'payment', 'picture-in-picture', 'publickey-credentials-get', 'screen-wake-lock', 'speaker', 'sync-script', 'sync-xhr', 'unoptimized-images', 'unoptimized-lossless-images', 'unoptimized-lossy-images', 'unsized-media', 'usb', 'vertical-scroll', 'vibrate', 'vr', 'wake-lock', 'web-share', 'xr-spatial-tracking'];
    
    #separate function for authentication headers?
    
    #somve validations are suggested for https://www.moesif.com/blog/technical/cors/Authoritative-Guide-to-CORS-Cross-Origin-Resource-Sharing-for-REST-APIs/
    #allow read only by default
    #Access-Control-Allow-Methods: POST, GET, OPTIONS (should go with Allow header)
    #block all by default, allow override (frame-ancestors \'self\' to all requests)
    #header('Content-Security-Policy: default-src https:; frame-ancestors 'none'; block-all-mixed-content; form-action https:; frame-ancestors \'self\'');
    #read on Sec-Fetch-Dest: image
        #Sec-Fetch-Mode: no-cors
        #Sec-Fetch-Site: cross-site
    
    #read on Save-Data
    
    #https://www.fastly.com/blog/best-practices-using-vary-header
    #https://www.keycdn.com/blog/client-hints
    
    #consider https://github.com/bspot/phpsourcemaps for https://www.html5rocks.com/en/tutorials/developertools/sourcemaps/ https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/SourceMap
    
    #consider fucntion for server-timing:
    #https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Server-Timing
    #https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Timing-Allow-Origin
    
    #unclear what to do with https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Tk
    
    #Function sends headers, related to security
    public function security(string $strat = 'strict', array $allowOrigins = [], array $allowHeaders = [], array $exposeHeaders = [])
    {
        #HSTS and force HTTPS
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        #Set caching value for CORS
        header('Access-Control-Max-Age: 86400');
        #Allows credentials to be shared to front-end JS. By itself this should not be a security issue, but it may ease of use for 3rd-party parser in some cases if you are using cookies.
        header('Access-Control-Allow-Credentials: true');
        #Allow access from origins
        if (!empty($allowOrigins)) {
            #Vary is requried by the standard. Using `false` to prevent overwriting of other Vary headers, if any were sent
            header('Vary: Origin', false);
            foreach ($allowOrigins as $origin) {
                header('Access-Control-Allow-Origin: '.$origin, false);
            }
        }
        #Allow headers, that can change server state, but are normally restricted by CORS
        if (!empty($allowHeaders)) {
            header('Access-Control-Allow-Headers: '.implode(', ', $allowHeaders));
        }
        #Allow headers sent from server, normally restricted by CORS
        if (!empty($exposeHeaders)) {
            header('Access-Control-Expose-Headers: '.implode(', ', $exposeHeaders));
        }
        #Set CORS strategy
        switch (strtolower($strat)) {
            case 'strict':
                header('Cross-Origin-Embedder-Policy: require-corp');
                header('Cross-Origin-Opener-Policy: same-origin');
                header('Cross-Origin-Resource-Policy: same-origin');
                header('Referrer-Policy: no-referrer');
                break;
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
        }
        
        header('Link: <https://'.$_SERVER['HTTP_HOST'].str_ireplace('?onepager', '', $_SERVER['REQUEST_URI']).'>; rel=canonical;', false);
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
        $deafults = [
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
        foreach ($features as $feature=>$allowlist) {
            #Sanitize
            $feature = strtolower(trim($feature));
            $allowlist = strtolower(trim($allowlist));
            #If validation is enforced, validate the feature and value provided
            if ($forcecheck === false || ($forcecheck === true && isset($deafults[$feature]) && preg_match('/^(?<nonorigin>(?<standard>\*|\'(none|self)\')(?<setting>\(\d{1,}(\.\d{1,})?\))?)|(?<origin>(?<scheme>[a-z][a-z0-9+.-]+):\/\/(?<host>[a-z0-9.\-_~]+)(?<port>:\d+)?(?<setting_o>\(\d{1,}(\.\d{1,})?\))?(?<delimiter> )?){1,}$/i', $allowlist) === 1)) {
                #Update value
                $deafults[$feature] = $allowlist;
            }
        }
        #Generate line for header
        $headerline = '';
        foreach ($deafults as $feature=>$allowlist) {
            $headerline .= $feature.' '.$allowlist.'; ';
        }
        header('Feature-Policy: '.trim($headerline));
        return $this;
    }
    
    #Function to set Last-Modified header. This header is generally not required if you already have Cache-Control and ETag, but still may be useful in case of conditional requests. At least if you will provide it with proper modification time.
    public function lastModified(int $modtime = 0)
    {
        if ($modtime <= 0) {
            #Get freshest modification time of all PHP files used ot PHP's getlastmod time
            $modtime = max(max(array_map('filemtime', array_filter(get_included_files(), 'is_file'))), getlastmod());
        }
        #Set the flag to false for now
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
           $IfModifiedSince = strtotime(substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 5));
           if ($IfModifiedSince >= $modtime) {
                #If content has not beend modified - return 304
                header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified');
                exit;
            }
        }
        header('Last-Modified: '.gmdate(\DATE_RFC7231, $modtime));
        return $this;
    }
    
    #Function to prepare and send cache-related headers
    public function cacheControl(string $string, string $cacheStrat = '')
    {
        #Send headers related to cache based on strategy selected
        #Some of the strategies are derived from https://csswizardry.com/2019/03/cache-control-for-civilians/
        if (!empty($cacheStrat)) {
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
                case 'hour':
                    header('Cache-Control: max-age=3600, stale-while-revalidate=1800, stale-if-error=1800, no-transform');
                    break;
            }
        }
        #Set ETag
        $etag = hash('sha3-256', $string);
        #Check if we have a conditional request. While this may have a less ideal placement than lastModified(), since it will not save much resources on server, it still can improve performance on client side
        if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
            if (trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
                #If content has not beend modified - return 304
                header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified');
                exit;
            }
        }
        if (isset($_SERVER['HTTP_IF_MATCH'])) {
            if (trim($_SERVER['HTTP_IF_MATCH']) !== $etag) {
                header($_SERVER['SERVER_PROTOCOL'].' 412 Precondition Failed');
            }
        }
        #Send ETag for caching purposes
        header('ETag: '.$etag);
        return $this;
    }
}

?>