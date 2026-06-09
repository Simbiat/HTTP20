<?php
declare(strict_types = 1);

namespace Simbiat\http20;

use JetBrains\PhpStorm\ExpectedValues;
use Simbiat\StringHelpers\Sanitize;

/**
 * Generate HTTP `Link` header and `link` HTML element.
 */
class Links
{
    /**
     * Regex for `rel` values that are allowed in the HTML body as per https://html.spec.whatwg.org/multipage/links.html#linkTypes
     *
     * @var string
     */
    public const string ALLOWED_IN_BODY = '/^\s*(alternate )?((^| )(dns-prefetch|modulepreload|pingback|preconnect|prefetch|preload|stylesheet)( |$))+(alternate)?\s*$/uir';
    /**
     * Regex for `rel` values that are used for preload
     * @var string
     */
    public const string PRELOAD_REL = '/((^| )(dns-prefetch|modulepreload|preconnect|prefetch|preload)( |$))+/uir';
    /**
     * Regex for allowed `as` values for `rel=preload` as per https://html.spec.whatwg.org/multipage/links.html#preload-destination
     * @var array
     */
    public const array AS_VALUES_PRELOAD = ['fetch', 'font', 'image', 'script', 'style', 'track'];
    /**
     * Regex for allowed `as` values for `rel=modulepreload` as per https://html.spec.whatwg.org/multipage/links.html#module-preload-destination and https://fetch.spec.whatwg.org/#request-destination-script-like
     * @var array
     */
    public const array AS_VALUES_MODULEPRELOAD = ['audioworklet', 'json', 'paintworklet', 'script', 'serviceworker', 'sharedworker', 'style', 'text', 'worker'];
    /**
     * Regex for `rel` values that are considered external resources as per https://html.spec.whatwg.org/multipage/links.html#linkTypes
     *
     * @var string
     */
    public const string EXTERNAL_RESOURCES = '/((^| )(dns-prefetch|icon|manifest|modulepreload|pingback|preconnect|prefetch|preload|stylesheet)( |$))+/uir';
    /**
     * Flag indicating that HTTP_SAVE_DATA was received and is `on`
     * @var bool
     */
    private static bool $save_data = false;
    /**
     * List of supported attributes
     */
    private const array ALLOWED_ATTRIBUTES = ['href', 'imagesrcset', 'title', 'rel', 'itemprop', 'hreflang', 'type', 'as', 'color', 'sizes', 'imagesizes', 'media', 'integrity', 'crossorigin', 'referrerpolicy', 'blocking', 'disabled', 'fetchpriority'];
    
    /**
     * Allowed values for `referrerpolicy`
     */
    private const array REFERRER_POLICY = ['no-referrer', 'no-referrer-when-downgrade', 'strict-origin', 'strict-origin-when-cross-origin', 'same-origin', 'origin', 'origin-when-cross-origin', 'unsafe-url'];
    
    /**
     * Allowed values for `fetchpriority`
     */
    private const array FETCH_PRIORITY = ['auto', 'low', 'high'];
    
    public function __construct()
    {
        #Check if Save-Data is on
        if (\array_key_exists('HTTP_SAVE_DATA', $_SERVER) && \preg_match('/^on$/uir', $_SERVER['HTTP_SAVE_DATA']) === 1) {
            self::$save_data = true;
        } else {
            self::$save_data = false;
        }
    }
    
    /**
     * Function to return a Link header (https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Link) or respective HTML set of tags
     * @param array  $links              List of links
     * @param string $type               Type of links: `header`, `head` or `body`.
     * @param bool   $force_cross_origin If set to `true`, if `rel` attribute is set it will be checked against a list of External Resources as per spec https://html.spec.whatwg.org/multipage/links.html#linkTypes and will force `crossorigin="anonymous"`, if the attribute is missing.
     *
     * @return string
     */
    public static function links(array $links = [], #[ExpectedValues(['header', 'head', 'body'])] string $type = 'header', bool $force_cross_origin = false): string
    {
        #Validate type
        if (!\in_array($type, ['header', 'head', 'body'], true)) {
            throw new \UnexpectedValueException('Unsupported type was provided to `links` function');
        }
        #Prepare an empty string
        $links_to_send = [];
        foreach ($links as $link) {
            self::disablePreload($link);
            #Replace multiple whitespaces with single space and trim
            $link['rel'] = mb_trim(\preg_replace('/\s{2,}/u', ' ', $link['rel'] ?? ''), null, 'UTF-8');
            #Unset rel if it's empty
            if (Sanitize::whiteString($link['rel'])) {
                unset($link['rel']);
            }
            if (\array_key_exists('rel', $link)) {
                #`shortcut icon` is legacy and `icon` shout be used instead
                $link['rel'] = \preg_replace('/shortcut icon/uir', 'icon', $link['rel']);
            }
            #Check that element is an array;
            if (!\is_array($link)) {
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
            if (\array_key_exists('rel', $link) && \preg_match(self::EXTERNAL_RESOURCES, $link['rel']) === 1) {
                if (!\array_key_exists('referrerpolicy', $link)) {
                    $link['referrerpolicy'] = 'strict-origin-when-cross-origin';
                }
                if (!\array_key_exists('fetchpriority', $link)) {
                    $link['fetchpriority'] = 'auto';
                }
                if ($force_cross_origin && !\array_key_exists('crossorigin', $link)) {
                    $link['crossorigin'] = 'anonymous';
                }
            }
            if (!self::isLinkValid($link, $type)) {
                continue;
            }
            #Generate element as string
            if ($type === 'header') {
                $links_to_send[] = self::generateHeader($link);
            } else {
                $links_to_send[] = self::generateTag($link);
            }
        }
        if (\count($links_to_send) === 0) {
            return '';
        }
        if ($type === 'header') {
            if (!\headers_sent()) {
                \header('Link: '.\preg_replace('/[\r\n]/uir', '', \implode(', ', $links_to_send)), false);
            }
            return '';
        }
        return \implode("\r\n", $links_to_send);
    }
    
    /**
     * Generate `<link>` representing the respective `Link` object
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
            (empty($link['blocking']) ? '' : ' blocking="'.$link['blocking'].'"').
            (\array_key_exists('disabled', $link) ? ' disabled' : '').
            (empty($link['fetchpriority']) ? '' : ' fetchpriority="'.$link['fetchpriority'].'"').
            '>';
    }
    
    /**
     * Generate a link for the HTTP header representing the respective `Link` object
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
            (empty($link['referrerpolicy']) ? '' : '; referrerpolicy="'.$link['referrerpolicy'].'"').
            (empty($link['fetchpriority']) ? '' : '; fetchpriority="'.$link['fetchpriority'].'"');
    }
    
    /**
     * Process `type` property that needs to comply with certain rules
     * @param array $link
     *
     * @return bool
     */
    private static function processTypeProperty(array &$link): bool
    {
        #Empty MIME type if it does ont confirm with the standard
        if (\array_key_exists('type', $link) && \preg_match('/'.Common::MIME_REGEX.'/u', $link['type']) !== 1) {
            $link['type'] = '';
        }
        #Try to set or update media type based on link
        if (empty($link['type']) && \array_key_exists('href', $link)) {
            $ext = \pathinfo($link['href'], \PATHINFO_EXTENSION);
            if (\is_string($ext) && Common::getMimeFromExtension($ext) !== false) {
                $link['type'] = Common::getMimeFromExtension($ext);
            } else {
                $link['type'] = '';
            }
        }
        if (\array_key_exists('rel', $link) && \preg_match('/((^| )(modulepreload|preload)( |$))+/uir', $link['rel']) === 1) {
            #Force 'as' for stylesheet
            if ((!empty($link['type']) && \preg_match('/^text\/css(;.*)?$/uir', $link['type']) === 1) || (!empty($link['rel']) && \preg_match('/((^| )(stylesheet)( |$))+/uir', $link['rel']) === 1)) {
                $link['as'] = 'style';
            }
            #Force 'as' for JS
            if ((!empty($link['type']) && \preg_match('/^application\/javascript(;.*)?$/uir', $link['type']) === 1)) {
                $link['as'] = 'script';
            }
            #Force 'as' for images
            if ((!empty($link['type']) && \preg_match('/^image\/.*$/uir', $link['type']) === 1)) {
                $link['as'] = 'image';
            }
            #Force 'as' for fonts
            if ((!empty($link['type']) && \preg_match('/^application\/.*(font|opentype).*$/uir', $link['type']) === 1)) {
                $link['as'] = 'font';
            }
            #Force 'as' for `track`
            if ((!empty($link['type']) && \preg_match('/^text\/vtt(;.*)?$/uir', $link['type']) === 1)) {
                $link['as'] = 'track';
            }
            #Force `as` for modulepreload to be explicit (current spec treats empty `as` for `modulepreload` as `script`)
            if (!\array_key_exists('as', $link) && \preg_match('/((^| )(modulepreload)( |$))+/uir', $link['rel']) === 1) {
                $link['as'] = 'script';
            }
        }
        #If a type is defined, check it corresponds to 'as'. If not, then do not process, assume error or malicious intent
        return !(!empty($link['type']) && !empty($link['as']) && \preg_match('/^(image|font)$/uir', $link['as']) === 1 && \preg_match('/^'.$link['as'].'\/.*$/uir', $link['type']) !== 1);
    }
    
    /**
     * Remove certain attributes if they are invalid or excessive
     * @param array  $link
     * @param string $type
     *
     * @return bool
     */
    private static function cleanLink(array &$link, #[ExpectedValues(['header', 'head', 'body'])] string $type): bool
    {
        #referrerpolicy is allowed to have limited set of values with `strict-origin-when-cross-origin` being default
        if (\array_key_exists('referrerpolicy', $link) && !\in_array(mb_strtolower($link['referrerpolicy'], 'UTF-8'), self::REFERRER_POLICY, true)) {
            $link['referrerpolicy'] = 'strict-origin-when-cross-origin';
        }
        #`fetchpriority` is allowed to have limited set of values with `auto` being default
        if (\array_key_exists('fetchpriority', $link) && !\in_array(mb_strtolower($link['fetchpriority'], 'UTF-8'), self::FETCH_PRIORITY, true)) {
            $link['fetchpriority'] = 'auto';
        }
        #Remove `hreflang`, if it's a wrong language value
        if (\array_key_exists('hreflang', $link) && \preg_match(Common::LANGUAGE_TAG_REGEX, $link['hreflang']) !== 1) {
            unset($link['hreflang']);
        }
        #Remove `sizes` if wrong format
        if (\array_key_exists('sizes', $link) && \preg_match('/((any|[1-9]\d+[xX][1-9]\d+)( |$))+$/uir', $link['sizes']) !== 1) {
            unset($link['sizes']);
        }
        #Sanitize `crossorigin`, if set
        if (\array_key_exists('crossorigin', $link) && (empty($link['crossorigin']) || !\in_array(mb_strtolower($link['crossorigin'], 'UTF-8'), ['anonymous', 'use-credentials'], true))) {
            $link['crossorigin'] = 'anonymous';
        }
        #Sanitize `title` if it's set
        if (\array_key_exists('title', $link)) {
            $link['title'] = \urldecode(\htmlspecialchars($link['title'], \ENT_QUOTES | \ENT_SUBSTITUTE));
        } else {
            $link['title'] = '';
        }
        #Validate `title*`, which is valid only for HTTP header
        if (\array_key_exists('title*', $link) && ($type !== 'header' || \preg_match('/'.Common::LANGUAGE_ENC_REGEX.'.*/uir', $link['title*']) !== 1)) {
            unset($link['title*']);
        }
        #If integrity is set, validate if it's a valid value
        if (\array_key_exists('integrity', $link) && !self::processIntegrity($link)) {
            return false;
        }
        #If integrity is set, check that rel type is of proper type, otherwise remove it
        if (isset($link['integrity'], $link['rel']) && \preg_match('/((^| )(modulepreload|preload|stylesheet)( |$))+/uir', $link['rel']) !== 1) {
            unset($link['integrity']);
        }
        #`crossorigin` and `referrerpolicy` are for external resources only
        if (\array_key_exists('rel', $link) && \preg_match(self::EXTERNAL_RESOURCES, $link['rel']) !== 1) {
            unset($link['crossorigin'], $link['referrerpolicy'], $link['fetchpriority']);
        }
        #`blocking` is allowed only for `expect` and `stylesheet`
        if (
            \array_key_exists('blocking', $link) &&
            (
                !\array_key_exists('rel', $link) ||
                \preg_match('/((^| )(expect|stylesheet)( |$))+/uir', $link['rel']) !== 1
            )
        ) {
            unset($link['blocking']);
        }
        #`disabled` is allowed only for stylesheets
        if (\array_key_exists('disabled', $link) &&
            (
                !\array_key_exists('rel', $link) ||
                \preg_match('/((^| )(stylesheet)( |$))+/uir', $link['rel']) !== 1 ||
                !$link['disabled']
            )
        ) {
            unset($link['disabled']);
        }
        #Remove unsupported attributes
        foreach ($link as $attribute => $value) {
            if (!\in_array(mb_strtolower($attribute, 'UTF-8'), self::ALLOWED_ATTRIBUTES, true)) {
                unset($link[$attribute]);
            }
        }
        return true;
    }
    
    /**
     * Process `integrity` attribute of the Link object
     * @param array $link
     *
     * @return bool
     */
    private static function processIntegrity(array &$link): bool
    {
        if (\preg_match('/^(sha256|sha384|sha512)-(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{4})$/u', $link['integrity']) === 0) {
            $potential_iri = IRI::parseUri($link['integrity']);
            /** @noinspection OffsetOperationsInspection https://github.com/kalessil/phpinspectionsea/issues/1941 */
            if (\is_array($potential_iri) && !empty($potential_iri['host'])) {
                #It looks like we have an absolute link. Assume error or malicious intent
                return false;
            }
            #If not valid, check if it's a file and generate hash
            if (\is_file($link['integrity'])) {
                #Attempt to get the actual MIME type while we're at it
                if (!\array_key_exists('type', $link) && \extension_loaded('fileinfo')) {
                    $link['type'] = \mime_content_type(\realpath($link['integrity']));
                }
                #Get the size of the image if the file is an image
                if (!\array_key_exists('sizes', $link) && \array_key_exists('type', $link) && \preg_match('/^image\/.*$/uir', $link['type']) === 1) {
                    $size = self::getImageSize($link);
                    #Set tags if we were able to get size
                    if (Sanitize::whiteString($size)) {
                        if (\array_key_exists('rel', $link) && \preg_match('/((^| )(icon|apple-touch-icon|apple-touch-icon-precomposed)( |$))+/uir', $link['rel']) === 1) {
                            $link['sizes'] = $size;
                        } elseif (\preg_match('/((^| )(preload)( |$))+/uir', $link['rel'] ?? '') === 1) {
                            $link['imagesizes'] = $size;
                            #Sanitize 'as' attribute
                            if (\array_key_exists('as', $link) && $link['as'] !== 'image') {
                                #Assume error or malicious intent and skip
                                return false;
                            }
                            #Set 'as' attribute if rel is "preload"
                            if (\array_key_exists('rel', $link) && \preg_match('/((^| )(modulepreload|preload)( |$))+/uir', $link['rel']) === 1) {
                                $link['as'] = 'image';
                            }
                        }
                    }
                }
                #Get hash if we have a script or style
                if (\array_key_exists('type', $link) && \preg_match('/^(application\/javascript|text\/css)$/uir', $link['type']) === 1) {
                    $hash = $link['integrity']
                            |> (static fn($x) => \realpath($x))
                            |> (static fn($x) => \hash_file('sha512', $x))
                            |> (static fn($x) => \base64_encode($x));
                    $link['integrity'] = 'sha512-'.$hash;
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
     * Attempt to get image size if the integrity attribute is a file
     * @param array $link
     *
     * @return string
     */
    private static function getImageSize(array $link): string
    {
        #Set to 'any' if it's SVG
        if (\preg_match('/^image\/svg\+xml(;.*)?$/uir', $link['type']) === 1) {
            $size = 'any';
        } else {
            $size = \getimagesize(\realpath($link['integrity']));
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
     * Check if valid, according to https://html.spec.whatwg.org/multipage/semantics.html#the-link-element
     * @param array  $link Link element
     * @param string $type Type of link expected
     *
     * @return bool
     */
    private static function isLinkValid(array $link, #[ExpectedValues(['header', 'head', 'body'])] string $type): bool
    {
        #Either href or imagesrcset or both need to be present. imagesrcset does not make sense in the HTTP header
        if ((!\array_key_exists('href', $link) && !\array_key_exists('imagesrcset', $link)) || ($type === 'header' && !\array_key_exists('href', $link))) {
            return false;
        }
        #Either `rel` or `itemprop` can be set at a time. itemprop does not make sense in the HTTP header
        if ((!\array_key_exists('rel', $link) && !\array_key_exists('itemprop', $link)) || isset($link['rel'], $link['itemprop']) || ($type === 'header' && !\array_key_exists('rel', $link))) {
            return false;
        }
        #Validate rel values
        if (\array_key_exists('rel', $link) && !self::isRelValid($link, $type)) {
            return false;
        }
        #Validate `blocking` value
        if (isset($link['rel'], $link['blocking']) && mb_strtolower($link['blocking'], 'UTF-8') !== 'render') {
            return false;
        }
        #`imagesrcset` is an image candidate with width descriptor, we need imagesizes as well
        if (\array_key_exists('imagesrcset', $link) && !\array_key_exists('imagesizes', $link) && \preg_match('/ \d+w(,|$)/u', $link['imagesrcset']) === 1) {
            return false;
        }
        if (\array_key_exists('as', $link)) {
            #`as` is allowed to have limited set of values and only used for `preload` and `modulepreload`.
            if (!\array_key_exists('rel', $link)) {
                return false;
            }
            if ((\preg_match('/((^| )(modulepreload|preload)( |$))+/uir', $link['rel']) !== 1)) {
                return false;
            }
            if ((\preg_match('/((^| )(preload)( |$))+/uir', $link['rel']) === 1) && !\in_array(mb_strtolower($link['as'], 'UTF-8'), self::AS_VALUES_PRELOAD, true)) {
                return false;
            }
            if ((\preg_match('/((^| )(modulepreload)( |$))+/uir', $link['rel']) === 1) && !\in_array(mb_strtolower($link['as'], 'UTF-8'), self::AS_VALUES_MODULEPRELOAD, true)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Check the validity of links with `rel` set
     * @param array  $link Link element
     * @param string $type Type of link expected
     *
     * @return bool
     */
    private static function isRelValid(array $link, #[ExpectedValues(['header', 'head', 'body'])] string $type): bool
    {
        #If we are using `body`, check that `rel` is body-ok one
        if ($type === 'body' && \preg_match(self::ALLOWED_IN_BODY, $link['rel']) !== 1) {
            return false;
        }
        #imagesrcset and imagesizes are allowed only for preload with as=image
        if (
            (\array_key_exists('imagesrcset', $link) || \array_key_exists('imagesizes', $link)) &&
            (!\array_key_exists('as', $link) || $link['as'] !== 'image' || \preg_match('/((^| )(preload)( |$))+/iur', $link['rel']) !== 1)
        ) {
            return false;
        }
        #`sizes` attribute should be set only if rel is icon of apple-touch-icon
        if (\array_key_exists('sizes', $link) && \preg_match('/((^| )(icon|apple-touch-icon|apple-touch-icon-precomposed)( |$))+/iur', $link['rel']) !== 1) {
            return false;
        }
        #as is allowed only for preload
        if (\array_key_exists('as', $link) && \preg_match('/((^| )(modulepreload|preload)( |$))+/iur', $link['rel']) !== 1) {
            return false;
        }
        #color is allowed only for mask-icon
        if (\array_key_exists('color', $link) && \preg_match('/((^| )(mask-icon)( |$))+/uir', $link['rel']) !== 1) {
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
        if (self::$save_data && \array_key_exists('rel', $link)) {
            $link['rel'] = \preg_replace(self::PRELOAD_REL, '', $link['rel']);
            #Unset 'imagesrcset', 'imagesizes' and 'as', since they are allowed only with preload. If we do not do this, some links may get skipped by the logic below.
            unset($link['imagesrcset'], $link['imagesizes'], $link['as']);
        }
    }
}
