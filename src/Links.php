<?php
declare(strict_types = 1);

namespace Simbiat\http20;

use JetBrains\PhpStorm\ExpectedValues;
use function in_array;

/**
 * Functions that send/handle different HTTP headers.
 */
class Links
{
    /**
     * Regex for all valid `rel` values, based on https://html.spec.whatwg.org/multipage/links.html#linkTypes and https://microformats.org/wiki/existing-rel-values#formats (types, that NEED to be supported by clients). Also includes webmention (https://www.w3.org/TR/2017/REC-webmention-20170112/).
     * @var string
     */
    public const string validRels = '/^(?!$)(alternate( |$))?((appendix|author|canonical|chapter|child|contents|copyright|dns-prefetch|glossary|help|icon|apple-touch-icon|apple-touch-icon-precomposed|mask-icon|its-rules|license|manifest|me|modulepreload|next|pingback|preconnect|prefetch|preload|prerender|prev|previous|search|section|stylesheet|subsection|toc|transformation|up|first|last|index|home|top|webmention)( |$))*/i';
    /**
     * Regex for `rel` values, that are allowed in HTML body
     * @var string
     */
    public const string allowedInBody = '/^(alternate )?.*(dns-prefetch|modulepreload|pingback|preconnect|prefetch|preload|prerender|stylesheet).*$/i';
    /**
     * Regex for `rel` values, that are used for preload
     * @var string
     */
    public const string preloadRel = '/(dns-prefetch|modulepreload|preconnect|prefetch|preload|prerender)/i';
    /**
     * Regex for allowed `as` values
     * @var string
     */
    public const string asValues = '/^(document|object|embed|audio|font|image|script|worker|style|track|video|fetch)$/i';
    /**
     * Regex for `rel` values, that are considered external resources
     * @var string
     */
    public const string externalResources = '/^(alternate )?((dns-prefetch|icon|apple-touch-icon|apple-touch-icon-precomposed|mask-icon|manifest|modulepreload|pingback|preconnect|prefetch|preload|prerender|stylesheet)( |$))*/i';
    /**
     * Flag indicating that HTTP_SAVE_DATA is was received and is `on`
     * @var bool
     */
    private static bool $saveData = false;
    
    public function __construct()
    {
        #Check if Save-Data is on
        if (isset($_SERVER['HTTP_SAVE_DATA']) && preg_match('/^on$/i', $_SERVER['HTTP_SAVE_DATA']) === 1) {
            self::$saveData = true;
        } else {
            self::$saveData = false;
        }
    }
    
    /**
     * Function to return a Link header (https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Link) or respective HTML set of tags
     * @param array  $links     List of links
     * @param string $type      Type of links: `header`, `head` or `body`.
     * @param bool   $strictRel If set to `true` (default), if `rel` attribute is set it will be checked against a list based on https://html.spec.whatwg.org/multipage/links.html#linkTypes and https://microformats.org/wiki/existing-rel-values#formats, meaning against `rel` values, that have to be supported by clients. If you are using something "special", set this to `false`. Personally, in such cases, I would recommend splitting the set of `Link` elements you have into 2 sets: standard and non-standard.
     *
     * @return string
     */
    public static function links(array $links = [], #[ExpectedValues(['header', 'head', 'body'])] string $type = 'header', bool $strictRel = true): string
    {
        #Validate type
        if (!in_array($type, ['header', 'head', 'body'])) {
            throw new \UnexpectedValueException('Unsupported type was provided to `links` function');
        }
        #Prepare an empty string
        $linksToSend = [];
        foreach ($links as $link) {
            #Check that element is an array;
            if (!\is_array($link)) {
                continue;
            }
            self::disablePreload($link);
            if (!self::isLinkValid($link, $type, $strictRel)) {
                continue;
            }
            #Clean up link properties
            if (!self::cleanLink($link, $type)) {
                continue;
            }
            #Process `type` property
            if (!self::processTypeProperty($link)) {
                continue;
            }
            #Generate element as string
            if ($type === 'header') {
                $linksToSend[] = self::generateHeader($link);
            } else {
                $linksToSend[] = self::generateTag($link);
            }
        }
        if (empty($linksToSend)) {
            return '';
        }
        if ($type === 'header') {
            if (!headers_sent()) {
                header('Link: '.preg_replace('/[\r\n]/i', '', implode(', ', $linksToSend)), false);
            }
            return '';
        }
        return implode("\r\n", $linksToSend);
    }
    
    /**
     * Generate `<link>` representing respective Link object
     * @param array $link Link object
     *
     * @return string
     */
    private static function generateTag(array $link): string
    {
        return '<link'.
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
    
    /**
     * Generate link for HTTP header representing respective Link object
     * @param array $link
     *
     * @return string
     */
    private static function generateHeader(array $link): string
    {
        return '<'.$link['href'].'>'.
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
            (empty($link['referrerpolicy']) ? '' : '; referrerpolicy="'.$link['referrerpolicy'].'"');
    }
    
    /**
     * Process `type` property, that needs to comply with certain rules
     * @param array $link
     *
     * @return bool
     */
    private static function processTypeProperty(array &$link): bool
    {
        #Empty MIME type if it does ont confirm with the standard
        if (isset($link['type']) && preg_match('/'.Common::mimeRegex.'/', $link['type']) !== 1) {
            $link['type'] = '';
        }
        #Set or update media type based on link. Or, at least, try to
        if (empty($link['type']) && isset($link['href'])) {
            $ext = pathinfo($link['href'], PATHINFO_EXTENSION);
            if (\is_string($ext) && isset(Common::extToMime[$ext])) {
                $link['type'] = Common::extToMime[$ext];
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
        return !(!empty($link['type']) && !empty($link['as']) && preg_match('/^(audio|image|video|font)$/i', $link['as']) === 1 && preg_match('/^'.$link['as'].'\/.*$/i', $link['type']) !== 1);
    }
    
    /**
     * Remove certain attributes, if they are invalid or excessive
     * @param array  $link
     * @param string $type
     *
     * @return bool
     */
    private static function cleanLink(array &$link, #[ExpectedValues(['header', 'head', 'body'])] string $type): bool
    {
        #referrerpolicy is allowed to have limited set of values
        if (isset($link['referrerpolicy']) && preg_match('/^(no-referrer|no-referrer-when-downgrade|strict-origin|strict-origin-when-cross-origin|same-origin|origin|origin-when-cross-origin|unsafe-url)$/i', $link['(referrerpolicy']) !== 1) {
            unset($link['referrerpolicy']);
        }
        #Remove hreflang, if it's a wrong language value
        if (isset($link['hreflang']) && preg_match(Common::langTagRegex, $link['hreflang']) !== 1) {
            unset($link['hreflang']);
        }
        #Remove sizes if wrong format
        if (isset($link['sizes']) && preg_match('/((any|[1-9]\d+[xX][1-9]\d+)( |$))+$/i', $link['sizes']) !== 1) {
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
            $link['title'] = '';
        }
        #Validate title*, which is valid only for HTTP header
        if (isset($link['title*']) && ($type !== 'header' || preg_match('/'.Common::langEncRegex.'.*/i', $link['title*']) !== 1)) {
            unset($link['title*']);
        }
        #If integrity is set, validate if it's a valid value
        if (isset($link['integrity']) && !self::processIntegrity($link)) {
            return false;
        }
        #If integrity is set, check that rel type is of proper type, otherwise remove it
        if (isset($link['integrity'], $link['rel']) && preg_match('/^(alternate )?.*(modulepreload|preload|stylesheet).*$/i', $link['rel']) !== 1) {
            unset($link['integrity']);
        }
        return true;
    }
    
    /**
     * Process `integrity` attribute of Link object
     * @param array $link
     *
     * @return bool
     */
    private static function processIntegrity(array &$link): bool
    {
        if (preg_match('/^(sha256|sha384|sha512)-(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{4})$/', $link['integrity']) === 0) {
            #If not valid, check if it's a file and generate hash
            if (is_file($link['integrity'])) {
                #Attempt to get actual MIME type while we're at it
                if (!isset($link['type']) && \extension_loaded('fileinfo')) {
                    $link['type'] = mime_content_type(realpath($link['integrity']));
                }
                #Get size of the image, if the file is an image
                if (!isset($link['sizes']) && isset($link['type']) && preg_match('/^image\/.*$/i', $link['type']) === 1 && parse_url($link['integrity'], PHP_URL_HOST) === NULL) {
                    $size = self::getImageSize($link);
                    #Set tags if we were able to get size
                    if (!empty($size)) {
                        if (isset($link['rel']) && preg_match('/^(alternate )?.*(icon|apple-touch-icon|apple-touch-icon-precomposed).*$/i', $link['rel']) === 1) {
                            $link['sizes'] = $size;
                        } elseif (preg_match('/^(alternate )?.*preload.*$/i', $link['rel']) === 1) {
                            $link['imagesizes'] = $size;
                            #Sanitize 'as' attribute
                            if (isset($link['as']) && $link['as'] !== 'image') {
                                #Assume error or malicious intent and skip
                                return false;
                            }
                            #Set 'as' attribute if rel is "preload"
                            if (isset($link['rel']) && preg_match('/^(alternate )?.*(modulepreload|preload|prefetch).*$/i', $link['rel']) === 1) {
                                $link['as'] = 'image';
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
        return true;
    }
    
    /**
     * Attempt to get image size, if integrity attribute is a file
     * @param array $link
     *
     * @return string
     */
    private static function getImageSize(array $link): string
    {
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
        return $size;
    }
    
    /**
     * Check if valid according to https://html.spec.whatwg.org/multipage/semantics.html#the-link-element
     * @param array  $link   Link element
     * @param string $type   Type of link expected
     * @param bool   $strict Whether to support only types from `validRels`
     *
     * @return bool
     */
    private static function isLinkValid(array $link, #[ExpectedValues(['header', 'head', 'body'])] string $type, bool $strict = true): bool
    {
        #Either href or imagesrcset or both need to be present. imagesrcset does not make sense in HTTP header
        if ((!isset($link['href']) && !isset($link['imagesrcset'])) || ($type === 'header' && !isset($link['href']))) {
            return false;
        }
        #Either rel or itemprop can be set at a time. itemprop does not make sense in HTTP header
        if ((!isset($link['rel']) && !isset($link['itemprop'])) || isset($link['rel'], $link['itemprop']) || ($type === 'header' && !isset($link['rel']))) {
            return false;
        }
        #Validate rel values
        if (isset($link['rel']) && !self::isRelValid($link, $type, $strict)) {
            return false;
        }
        #imagesrcset is an image candidate with width descriptor, we need imagesizes as well
        if (isset($link['imagesrcset']) && !isset($link['imagesizes']) && preg_match('/ \d+w(,|$)/', $link['imagesrcset']) === 1) {
            return false;
        }
        if (isset($link['as'])) {
            #as is allowed to have limited set of values (as per https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content).
            if ((preg_match(self::asValues, $link['as']) !== 1)) {
                return false;
            }
            #Also check that crossorigin is set, if as=fetch
            if (!isset($link['crossorigin']) && preg_match('/^fetch$/i', $link['as']) === 1) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Check validity of links with rel set
     * @param array  $link   Link element
     * @param string $type   Type of link expected
     * @param bool   $strict Whether to support only types from `validRels`
     *
     * @return bool
     */
    private static function isRelValid(array $link, #[ExpectedValues(['header', 'head', 'body'])] string $type, bool $strict = true): bool
    {
        if ($strict === true) {
            #Check that rel is valid
            if (preg_match(self::validRels, $link['rel']) !== 1) {
                return false;
            }
            #If crossorigin or referrerpolicy is set, check that rel type is an external resource
            if (
                (isset($link['crossorigin']) || isset($link['referrerpolicy'])) &&
                preg_match(self::externalResources, $link['rel']) !== 1
            ) {
                return false;
            }
        }
        #If we are using "body", check that rel is body-ok one
        if ($type === 'body' && preg_match(self::allowedInBody, $link['rel']) !== 1) {
            return false;
        }
        #imagesrcset and imagesizes are allowed only for preload with as=image
        if (
            (isset($link['imagesrcset']) || isset($link['imagesizes'])) &&
            (!isset($link['as']) || $link['as'] !== 'image' || preg_match('/^(alternate )?.*preload.*$/i', $link['rel']) !== 1)
        ) {
            return false;
        }
        #sizes attribute should be set only if rel is icon of apple-touch-icon
        if (isset($link['sizes']) && preg_match('/^(alternate )?.*(icon|apple-touch-icon|apple-touch-icon-precomposed).*$/i', $link['rel']) !== 1) {
            return false;
        }
        #as is allowed only for preload
        if (isset($link['as']) && preg_match('/^(alternate )?.*(modulepreload|preload|prefetch).*$/i', $link['rel']) !== 1) {
            return false;
        }
        #color is allowed only for mask-icon
        if (isset($link['color']) && preg_match('/^(alternate )?.*mask-icon.*$/i', $link['rel']) !== 1) {
            return false;
        }
        return true;
    }
    
    /**
     * If Save-Data is set to 'on', disable (remove respective rel) HTTP2 push logic (that is preloads and prefetches)
     * @param array $link
     *
     * @return void
     */
    private static function disablePreload(array &$link): void
    {
        if (self::$saveData === true && isset($link['rel']) && preg_match(self::preloadRel, $link['rel']) === 1) {
            $link['rel'] = preg_replace(self::preloadRel, '', $link['rel']);
            #Replace multiple whitespaces with single space and trim
            $link['rel'] = trim(preg_replace('/\s{2,}/', ' ', $link['rel']));
            #Unset rel if it's empty
            if (empty($link['rel'])) {
                unset($link['rel']);
            }
            #Unset 'imagesrcset', 'imagesizes' and 'as', since they are allowed only with preload. If we do not do this some links may get skipped by logic below.
            unset($link['imagesrcset'], $link['imagesizes'], $link['as']);
        }
    }
}
