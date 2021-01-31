<?php
declare(strict_types=1);
namespace HTTP20;

class Headers
{
    #separate function for authentication headers?
    
    #Security headers:
    #separate function for Access-Control-Allow-Origin?
    #separate function for Feature-Policy?
        #autoplay: Allow or disallow autoplaying video.
        #sync-script: Allow or disallow synchronous script tags.
        #document-write: Allow or disallow document.write.
        #lazyload: Force all images and iframe to load lazily (as if all had lazyload="on"
        #image-compression: restrict images to have a byte size no more than 10x bigger than their pixel count.
        #maximum-downscaling-image: restrict images to be downscaled by not more than 2x.
        #unsized-media: requires images to have a width & height specified, otherwise defaults to 300 x 150.
        #layout-animations: turns off CSS animation for any property that triggers a re-layout (e.g. top, width, max-height)
    #allow cookies by default?
    #Access-Control-Allow-Credentials: true
    #somve validations are suggested for https://www.moesif.com/blog/technical/cors/Authoritative-Guide-to-CORS-Cross-Origin-Resource-Sharing-for-REST-APIs/
    #allow read only by default
    #Access-Control-Allow-Methods: POST, GET, OPTIONS (should go with Allow header)
    #block all by default, allow override (frame-ancestors \'self\' to all requests)
    #header('Content-Security-Policy: default-src https:; frame-ancestors 'none'; block-all-mixed-content; form-action https:; frame-ancestors \'self\'');
    #read on Sec-Fetch-Dest: image
        #Sec-Fetch-Mode: no-cors
        #Sec-Fetch-Site: cross-site
    
    #read on Save-Data
    #read on Server-Timing
    #https://www.fastly.com/blog/best-practices-using-vary-header
    #https://www.keycdn.com/blog/client-hints
    
    #consider https://github.com/bspot/phpsourcemaps for https://www.html5rocks.com/en/tutorials/developertools/sourcemaps/ https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/SourceMap
    
    #consider fucntion for server-timing:
    #https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Server-Timing
    #https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Timing-Allow-Origin
    
    #unclear what to do with https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Tk
    
    #Function sends headers, related to security
    public function security(string $strat = 'strict', array $allowHeaders = [], array $exposeHeaders = [])
    {
        #HSTS and force HTTPS
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        #Set caching value for CORS
        header('Access-Control-Max-Age: 86400');
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