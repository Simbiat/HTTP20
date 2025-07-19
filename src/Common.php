<?php
declare(strict_types = 1);

namespace Simbiat\http20;

use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\NoReturn;
use function in_array, is_string, extension_loaded;

/**
 * Collection of useful HTTP related functions
 */
class Common
{
    /**
     * Regex for language tag as per https://tools.ietf.org/html/rfc5987 and https://tools.ietf.org/html/rfc5646#section-2.1. Uses a portion from https://stackoverflow.com/questions/7035825/regular-expression-for-a-language-tag-as-defined-by-bcp47
     * @var string
     */
    public const string LANGUAGE_ENC_REGEX = /** @lang PhpRegExp */
        '(UTF-8|ISO-8859-1|[!#$%&+\-^_`{}~a-zA-Z0-9]+)\'((?<grandfathered>(?:en-GB-oed|i-(?:ami|bnn|default|enochian|hak|klingon|lux|mingo|navajo|pwn|t(?:a[oy]|su))|sgn-(?:BE-(?:FR|NL)|CH-DE))|(?:art-lojban|cel-gaulish|no-(?:bok|nyn)|zh-(?:guoyu|hakka|min(?:-nan)?|xiang)))|(?<language>[A-Za-z]{2,3}(?:-(?<extlang>[A-Za-z]{3}(?:-[A-Za-z]{3}){0,2}))?|[A-Za-z]{4}|[A-Za-z]{5,8})(?:-(?<script>[A-Za-z]{4}))?(?:-(?<region>[A-Za-z]{2}|[0-9]{3}))?(?:-(?<variant>[A-Za-z0-9]{5,8}|[0-9][A-Za-z0-9]{3}))*(?:-(?<extension>[0-9A-WY-Za-wy-z](?:-[A-Za-z0-9]{2,8})+))*(?:-(?<privateUse>x(?:-[A-Za-z0-9]{1,8})+))?)?\'';
    /**
     * Language values as per https://www.ietf.org/rfc/bcp/bcp47.txt (essentially just part of the above value)
     * @var string
     */
    public const string LANGUAGE_TAG_REGEX = /** @lang PhpRegExp */
        '((?<grandfathered>(?:en-GB-oed|i-(?:ami|bnn|default|enochian|hak|klingon|lux|mingo|navajo|pwn|t(?:a[oy]|su))|sgn-(?:BE-(?:FR|NL)|CH-DE))|(?:art-lojban|cel-gaulish|no-(?:bok|nyn)|zh-(?:guoyu|hakka|min(?:-nan)?|xiang)))|(?<language>[A-Za-z]{2,3}(?:-(?<extlang>[A-Za-z]{3}(?:-[A-Za-z]{3}){0,2}))?|[A-Za-z]{4}|[A-Za-z]{5,8})(?:-(?<script>[A-Za-z]{4}))?(?:-(?<region>[A-Za-z]{2}|[0-9]{3}))?(?:-(?<variant>[A-Za-z0-9]{5,8}|[0-9][A-Za-z0-9]{3}))*(?:-(?<extension>[0-9A-WY-Za-wy-z](?:-[A-Za-z0-9]{2,8})+))*(?:-(?<privateUse>x(?:-[A-Za-z0-9]{1,8})+))?)';
    /**
     * Regex for MIME type
     * @var string
     */
    public const string MIME_REGEX = /** @lang PhpRegExp */
        '(?<type>application|audio|image|message|multipart|text|video|(x-[-\w.]+))\/[-+\w.]+(?<parameter> *; *[-\w.]+ *= *("*[()<>@,;:\/\\\\\[\]?="\-\w. ]+"|[-\w.]+))*';
    /**
     * Linkage of extensions to MIME types
     * @var array
     */
    public static array $extension_to_mime = [];
    
    /**
     * @param string $extension Extension to get MIME for
     * @param string $mime_list Optional list extension-to-MIME map file
     *
     * @return string|null
     */
    public static function getMimeFromExtension(string $extension, string $mime_list = ''): string|null
    {
        if (\preg_match('/^\s*$/u', $mime_list) === 1 || !\is_file($mime_list)) {
            $mime_list = __DIR__.'/mime.json';
        }
        #Read the file with MIME types
        if (\count(self::$extension_to_mime) === 0) {
            try {
                self::$extension_to_mime = \json_decode(\file_get_contents($mime_list), true, 512, \JSON_THROW_ON_ERROR);
            } catch (\Throwable) {
                return null;
            }
        }
        return self::$extension_to_mime[$extension] ?? null;
    }
    
    /**
     * @param string $mime      MIME to get an extension for
     * @param string $mime_list Optional list extension-to-MIME map file
     *
     * @return false|int|string
     */
    public static function getExtensionFromMime(string $mime, string $mime_list = ''): false|int|string
    {
        if (\preg_match('/^\s*$/u', $mime_list) === 1 || !\is_file($mime_list)) {
            $mime_list = __DIR__.'/mime.json';
        }
        #Read the file with MIME types
        if (\count(self::$extension_to_mime) === 0) {
            try {
                self::$extension_to_mime = \json_decode(\file_get_contents($mime_list), true, 512, \JSON_THROW_ON_ERROR);
            } catch (\Throwable) {
                return false;
            }
        }
        return \array_search($mime, self::$extension_to_mime, true);
    }
    
    /**
     * Wrapper for date(), that handles strings and allows validation of the result
     * @param string|int|float|null $time        Time value
     * @param string                $format      Expected format
     * @param string                $valid_regex Regex to use for validation
     *
     * @return string
     */
    public static function valueToTime(string|int|float|null $time, string $format, string $valid_regex = ''): string
    {
        #If we want to use a constant, but it was sent as a string
        if (str_starts_with(mb_strtoupper($format, 'UTF-8'), 'DATE_')) {
            $format = \constant($format);
        }
        if (empty($time)) {
            $time = \date($format);
        } elseif (\is_numeric($time)) {
            #Ensure we use int
            $time = \date($format, (int)$time);
        } elseif (is_string($time)) {
            #Attempt to convert string to time
            $time = \date($format, \strtotime($time));
        } else {
            throw new \UnexpectedValueException('Time provided to `valueToTime` is neither numeric or string');
        }
        if ($format === 'c' || $format === \DATE_ATOM) {
            $valid_regex = '/^(?:[1-9]\d{3}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1\d|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[1-9]\d(?:0[48]|[2468][048]|[13579][26])|(?:[2468][048]|[13579][26])00)-02-29)T(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d(?:Z|[+-][01]\d:[0-5]\d)$/i';
        }
        if (!empty($valid_regex) && \preg_match($valid_regex, $time) !== 1) {
            throw new \UnexpectedValueException('Date provided to `valueToTime` failed to be validated against the provided regex');
        }
        return $time;
    }
    
    /**
     * Function uses ob functions to attempt compressing output sent to browser and also provide browser with length of the output and some caching-related headers
     *
     * @param string $string         String to echo
     * @param string $cache_strategy Cache strategy (same as for `cacheControl` function)
     * @param bool   $exit           Whether to stop execution after echoing or not
     *
     * @return void
     */
    public static function zEcho(string $string, #[ExpectedValues(['', 'aggressive', 'private', 'none', 'live', 'month', 'week', 'day', 'hour'])] string $cache_strategy = '', bool $exit = true): void
    {
        #Close session
        if (\session_status() === \PHP_SESSION_ACTIVE) {
            \session_write_close();
        }
        $postfix = '';
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            #Attempt brotli compression, if available and client supports it
            if (extension_loaded('brotli') && str_contains($_SERVER['HTTP_ACCEPT_ENCODING'], 'br')) {
                #Compress string
                $string = \brotli_compress($string, 11, \BROTLI_TEXT);
                #Send header with format
                if (!\headers_sent()) {
                    \header('Content-Encoding: br');
                }
                $postfix = '-br';
                #Check that zlib is loaded and client supports GZip. We are ignoring Deflate because of known inconsistencies with how it is handled by browsers depending on whether it is wrapped in Zlib or not.
            } elseif (extension_loaded('zlib') && str_contains($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
                #It is recommended to use ob_gzhandler or zlib.output_compression, but I am getting inconsistent results with headers when using them, thus this "direct" approach.
                #GZipping the string
                $string = \gzcompress($string, 9, \FORCE_GZIP);
                #Send header with format
                if (!\headers_sent()) {
                    \header('Content-Encoding: gzip');
                }
                $postfix = '-gzip';
            }
        }
        Headers::cacheControl($string, $cache_strategy, true, $postfix);
        #Send header with length
        if (!\headers_sent()) {
            \header('Content-Length: '.\strlen($string));
        }
        #Some HTTP methods do not support body, thus we need to ensure it's not sent.
        $method = $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] ?? $_SERVER['REQUEST_METHOD'] ?? null;
        if (in_array($method, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'])) {
            #Send the output
            echo $string;
        }
        if ($exit) {
            exit(0);
        }
    }
    
    /**
     * Function to check if string is a valid language code
     * @param string $string
     *
     * @return bool
     */
    public static function langCodeCheck(string $string): bool
    {
        return in_array(mb_strtolower($string, 'UTF-8'),
            ['af', 'sq', 'eu', 'be', 'bg', 'ca', 'zh-cn', 'zh-tw', 'hr', 'cs', 'da', 'nl', 'nl-be', 'nl-nl', 'en', 'en-au', 'en-bz', 'en-ca', 'en-ie', 'en-jm', 'en-nz', 'en-ph', 'en-za', 'en-tt', 'en-gb', 'en-us', 'en-zw', 'et', 'fo', 'fi', 'fr', 'fr-be', 'fr-ca', 'fr-fr', 'fr-lu', 'fr-mc', 'fr-ch', 'gl', 'gd', 'de', 'de-at', 'de-de', 'de-li', 'de-lu', 'de-ch', 'el', 'haw', 'hu', 'is', 'in', 'ga', 'it', 'it-it', 'it-ch', 'ja', 'ko', 'mk', 'no', 'pl', 'pt', 'pt-br', 'pt-pt', 'ro', 'ro-mo', 'ro-ro', 'ru', 'ru-mo', 'ru-ru', 'sr', 'sk', 'sl', 'es', 'es-ar', 'es-bo', 'es-cl', 'es-co', 'es-cr', 'es-do', 'es-ec', 'es-sv', 'es-gt', 'es-hn', 'es-mx', 'es-ni', 'es-pa', 'es-py', 'es-pe', 'es-pr', 'es-es', 'es-uy', 'es-ve', 'sv', 'sv-fi', 'sv-se', 'tr', 'uk']
        );
    }
    
    /**
     * Function does the same as `rawurlencode`, but only for selected characters, that are restricted in HTML/XML. Useful for URIs that can have these characters and need to be used in HTML/XML and thus can't use `htmlentities`, but otherwise break HTML/XML
     * @param string $string String to encode
     * @param bool   $full   Means that all characters will be converted (useful when text inside a tag). If `false` only `<` and `&` are converted (useful when inside attribute). If `false` is used - be careful with quotes inside the string you provide, because they can invalidate your HTML/XML
     *
     * @return string
     */
    public static function htmlToRFC3986(string $string, bool $full = true): string
    {
        if ($full) {
            return \str_replace(['\'', '"', '&', '<', '>'], ['%27', '%22', '%26', '%3C', '%3E'], $string);
        }
        return \str_replace(['&', '<'], ['%26', '%3C'], $string);
    }
    
    /**
     * Function to merge CSS/JS files to reduce the number of connections to your server, yet allow you to keep the files separate for easier development. It also allows you to minify the result for extra size saving, but be careful with that. #Minification is based on https://gist.github.com/Rodrigo54/93169db48194d470188f
     *
     * @param string|array $files          File(s) to process
     * @param string       $type           File(s) type (`css`, `js` or `html`)
     * @param bool         $minify         Whether to minify the output
     * @param string       $to_file        Optional path to a file, if you want to save the result
     * @param string       $cache_strategy Cache strategy (same as `cacheStrategy` function)
     *
     * @return void
     */
    public static function reductor(string|array $files, #[ExpectedValues('css', 'js', 'html')] string $type, bool $minify = false, string $to_file = '', string $cache_strategy = ''): void
    {
        #Set content to empty string as precaution
        $content = '';
        #Check if empty value was sent
        if (empty($files)) {
            throw new \UnexpectedValueException('Empty set of files provided to `reductor` function');
        }
        #Check if a string
        if (is_string($files)) {
            #Convert to array
            $files = [$files];
        }
        #Prepare the array of dates
        $dates = [];
        #Iterate array
        foreach ($files as $file) {
            #Check if string is a file
            if (\is_file($file)) {
                #Check extension
                if (\strcasecmp(\pathinfo($file, \PATHINFO_EXTENSION), $type) === 0) {
                    #Add date to list
                    $dates[] = \filemtime($file);
                    #Add contents
                    $content .= \file_get_contents($file);
                }
            } elseif (\is_dir($file)) {
                $file_list = (new \RecursiveIteratorIterator((new \RecursiveDirectoryIterator($file, \FilesystemIterator::FOLLOW_SYMLINKS | \FilesystemIterator::SKIP_DOTS)), \RecursiveIteratorIterator::SELF_FIRST));
                foreach ($file_list as $sub_file) {
                    if (\strcasecmp($sub_file->getExtension(), $type) === 0) {
                        #Add date to list
                        $dates[] = $sub_file->getMTime();
                        #Add contents
                        $content .= \file_get_contents($sub_file->getRealPath());
                    }
                }
            }
        }
        #Get date if we are directly outputting the data
        if (empty($to_file)) {
            #Send Last-Modified header and exit if we hit browser cache
            Headers::lastModified(\max($dates), true);
        }
        #Minify
        if ($minify) {
            switch (mb_strtolower($type, 'UTF-8')) {
                case 'js':
                    $content = \preg_replace(
                        [
                            // Remove comment(s)
                            '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*/\*(?!!|@cc_on)(?>[\s\S]*?\*/)\s*|\s*(?<![:=])//.*(?=[\n\r]|$)|^\s*|\s*$#',
                            // Remove white-space(s) outside the string and regex
                            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|/\*(?>.*?\*/)|/(?!/)[^\n\r]*?/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*()\-=+\[\]{}|;:,.<>?/])\s*#s',
                            // Remove the last semicolon
                            '#;+}#',
                            // Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
                            '#([{,])(\')(\d+|[a-z_][a-z0-9_]*)\2(?=:)#i',
                            // --ibid. From `foo['bar']` to `foo.bar`
                            '#([a-z0-9_)\]])\[([\'"])([a-z_][a-z0-9_]*)\2]#i'
                        ],
                        [
                            '$1',
                            '$1$2',
                            '}',
                            '$1$3',
                            '$1.$3'
                        ],
                        $content);
                    break;
                case 'css':
                    $content = \preg_replace(
                        [
                            // Remove comment(s)
                            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|/\*(?!!)(?>.*?\*/)|^\s*|\s*$#s',
                            // Remove unused white-space(s)
                            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|/\*(?>.*?\*/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~]|\s(?![0-9.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
                            // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
                            '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#i',
                            // Replace `:0 0 0 0` with `:0`
                            '#:(0\s+0|0\s+0\s+0\s+0)(?=[;}]|!important)#i',
                            // Replace `background-position:0` with `background-position:0 0`
                            '#(background-position):0(?=[;}])#i',
                            // Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
                            '#(?<=[\s:,\-])0+\.(\d+)#',
                            // Minify string value
                            '#(/\*(?>.*?\*/))|(?<!content:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s{}\];,])#si',
                            '#(/\*(?>.*?\*/))|(\burl\()([\'"])(\S+?)\3(\))#si',
                            // Minify HEX color code
                            '#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
                            // Replace `(border|outline):none` with `(border|outline):0`
                            '#(?<=[{;])(border|outline):none(?=[;}!])#',
                            // Remove empty selector(s)
                            '#(/\*(?>.*?\*/))|(^|[{}])[^\s{}]+{}#s'
                        ],
                        [
                            '$1',
                            '$1$2$3$4$5$6$7',
                            '$1',
                            ':0',
                            '$1:0 0',
                            '.$1',
                            '$1$3',
                            '$1$2$4$5',
                            '$1$2$3',
                            '$1:0',
                            '$1$2'
                        ],
                        $content);
                    break;
                case 'html':
                    $content = \preg_replace_callback('#<([^/\s<>!]+)(?:\s+([^<>]*?)\s*|\s*)(/?)>#',
                        static function ($matches) {
                            return '<'.$matches[1].\preg_replace('#([^\s=]+)(=([\'"]?)(.*?)\3)?(\s+|$)#s', ' $1$2', $matches[2]).$matches[3].'>';
                        }, \str_replace("\r", '', $content));
                    $content = \preg_replace(
                        [
                            // t = text
                            // o = tag open
                            // c = tag close
                            // Keep important white-space(s) after self-closing HTML tag(s)
                            '#<(img|input)(>| .*?>)#s',
                            // Remove a line break and two or more white-space(s) between tag(s)
                            '#(<!--.*?-->)|(>)(?:\n*|\s{2,})(<)|^\s*|\s*$#s',
                            '#(<!--.*?-->)|(?<!>)\s+(</.*?>)|(<[^/]*?>)\s+(?!<)#s', // t+c || o+t
                            '#(<!--.*?-->)|(<[^/]*?>)\s+(<[^/]*?>)|(</.*?>)\s+(</.*?>)#s', // o+o || c+c
                            '#(<!--.*?-->)|(</.*?>)\s+(\s)(?!<)|(?<!>)\s+(\s)(<[^/]*?/?>)|(<[^/]*?/?>)\s+(\s)(?!<)#s', // c+t || t+o || o+t -- separated by long white-space(s)
                            '#(<!--.*?-->)|(<[^/]*?>)\s+(</.*?>)#s', // empty tag
                            '#<(img|input)(>| .*?>)</\1>#s', // reset previous fix
                            '#(&nbsp;)&nbsp;(?![<\s])#', // clean up ...
                            '#(?<=>)(&nbsp;)(?=<)#', // --ibid
                            // Remove HTML comment(s) except IE comment(s)
                            '#\s*<!--(?!\[if\s).*?-->\s*|(?<!>)\n+(?=<[^!])#s'
                        ],
                        [
                            '<$1$2</$1>',
                            '$1$2$3',
                            '$1$2$3',
                            '$1$2$3$4$5',
                            '$1$2$3$4$5$6$7',
                            '$1$2$3',
                            '<$1$2',
                            '$1 ',
                            '$1',
                            ''
                        ],
                        $content);
                    break;
            }
        }
        if (empty($to_file)) {
            #Send the appropriate header
            switch (mb_strtolower($type, 'UTF-8')) {
                case 'js':
                    if (!\headers_list()) {
                        \header('Content-Type: application/javascript; charset=utf-8');
                    }
                    break;
                case 'css':
                    if (!\headers_list()) {
                        \header('Content-Type: text/css; charset=utf-8');
                    }
                    break;
                default:
                    if (!\headers_list()) {
                        \header('Content-Type: text/html; charset=utf-8');
                    }
                    break;
            }
            #Send data to browser
            self::zEcho($content, $cache_strategy);
        } else {
            \file_put_contents($to_file, $content);
        }
    }
    
    /**
     * Function to force close HTTP connection. Possible notices from `ob_end_clean` and `flush` are suppressed, since I do not see a good alternative to this, when closing connection, which may be closed in a non-planned way.
     *
     * @return void
     * @noinspection PhpUsageOfSilenceOperatorInspection
     */
    #[NoReturn] public static function forceClose(): void
    {
        #Close session
        if (\session_status() === \PHP_SESSION_ACTIVE) {
            \session_write_close();
        }
        #Send header to notify, that connection was closed
        if (!\headers_sent()) {
            \header('Connection: close');
        }
        #Clean output buffer and close it
        @\ob_end_clean();
        #Clean system buffer
        @\flush();
        exit(0);
    }
}
