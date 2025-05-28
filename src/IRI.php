<?php
declare(strict_types = 1);

namespace Simbiat\http20;

/**
 * IRI-related function
 */
class IRI
{
    /**
     * Characters from the `ucschar` terminal as seen in RFC 3987
     *
     * @var string
     */
    public const string ucschar = '\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}';
    /**
     * Characters from the `iprivate` terminal as seen in RFC 3987
     *
     * @var string
     */
    public const string iprivate = '\x{E000}-\x{F8FF}\x{F0000}-\x{FFFFD}\x{100000}-\x{10FFFD}';
    /**
     * Characters from the `unreserved` terminal as seen in RFC 3987
     *
     * @var string
     */
    public const string unreserved = 'a-zA-Z0-9~_.\-';
    /**
     * Characters from the `gen-delims` terminal as seen in RFC 3987
     *
     * @var string
     */
    public const string gendelims = ':\/?#\[\]@';
    /**
     * Characters from the `sub-delims` terminal as seen in RFC 3987
     *
     * @var string
     */
    public const string subdelims = '!$&\'()*+,;=';
    
    /**
     * Check if a string is a valid International Resource Identifier (IRI)
     *
     * @param string       $iri    IRI to validate
     * @param string|array $scheme Optional allowed scheme(s)
     *
     * @return bool
     */
    public static function isValidIri(string $iri, string|array $scheme = []): bool
    {
        #Validate scheme, if provided
        if (empty($scheme)) {
            if (\is_string($scheme)) {
                $scheme = [$scheme];
            }
            if (preg_match('/^('.implode('|', $scheme).'):\/\//ui', $iri) !== 1) {
                return false;
            }
        }
        #Convert to URI. If we succeeded - then it's a valid IRI
        return self::iriToUri($iri) !== null;
    }
    
    /**
     * Convert International Resource Identifier (IRI) to Universal Resource Identifier (URI)
     *
     * @param string $iri IRI string
     *
     * @return string|null
     *
     */
    public static function iriToUri(string $iri): ?string
    {
        #Check that UTF-8 is used
        if (!mb_check_encoding($iri, 'UTF-8')) {
            return null;
        }
        #Early check for characters unsupported as per RFC 8820 and 3987. Using respective groups for maintainability, plus adding a `%` sign (for URL encoding)
        if (preg_match('/^[%'.self::ucschar.self::iprivate.self::gendelims.self::subdelims.self::unreserved.']+$/u', $iri) !== 1) {
            return null;
        }
        #Ensure only valid UTF-8 characters are present
        $iri = mb_scrub($iri, 'UTF-8');
        #Try parsing the IRI first
        $parsed_iri = self::parseUri($iri);
        if (!\is_array($parsed_iri)) {
            return null;
        }
        #If there is no scheme, then it may be a relative path, and thus in some cases it's not possible to determine if first part (before first slash) is a domain or actual part of a path
        if (empty($parsed_iri['scheme'])) {
            return null;
        }
        $parsed_iri['scheme'] = mb_strtolower($parsed_iri['scheme'], 'UTF-8');
        #If we have a scheme but somehow lack a host - that's also abnormal
        if (empty($parsed_iri['host'])) {
            return null;
        }
        #Convert host to ASCII
        $ascii_host = idn_to_ascii($parsed_iri['host']);
        #If it's false - return
        if ($ascii_host === false) {
            return null;
        }
        $parsed_iri['host'] = mb_strtolower($ascii_host, 'UTF-8');
        #Check if a valid domain name or IP
        if (!filter_var($ascii_host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) &&
            !filter_var($ascii_host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
            return null;
        }
        #Pross user and pass if present
        if (!empty($parsed_iri['user'])) {
            $parsed_iri['user'] = rawurlencode($parsed_iri['user']);
        }
        if (!empty($parsed_iri['pass'])) {
            $parsed_iri['pass'] = rawurlencode($parsed_iri['pass']);
        }
        #Process path, if present
        if (!empty($parsed_iri['path'])) {
            #Need to use explode/implode to preserve `/` symbols. Doing rawurlencode and then restoring them may restore symbols encoded in the original IRI
            $parsed_iri['path'] = implode('/', array_map('rawurlencode', explode('/', $parsed_iri['path'])));
        }
        #Process query, if present
        if (!empty($parsed_iri['query'])) {
            #"Explode" the query into an array using parse_str
            parse_str($parsed_iri['query'], $explodedQuery);
            #Rebuild the query to ensure we URL encode properly
            $parsed_iri['query'] = http_build_query($explodedQuery, encoding_type: PHP_QUERY_RFC3986);
        }
        #Process fragment, if present
        if (!empty($parsed_iri['fragment'])) {
            $parsed_iri['fragment'] = rawurlencode($parsed_iri['fragment']);
        }
        #Validate that all components besides `query` do *not* contain characters from `iprivate` terminal
        if (array_any($parsed_iri, static fn($value, $component) => $component !== 'query' && preg_match('/['.self::iprivate.']/u', $value) === 1)) {
            return null;
        }
        return self::restoreUri($parsed_iri);
    }
    
    /**
     * Restore the array from `parse_url()` function to original URL
     * @param array $parsedUri Array from `parse_url()`
     *
     * @return string|null
     */
    public static function restoreUri(array $parsedUri): ?string
    {
        if (empty($parsedUri)) {
            return null;
        }
        return (!empty($parsedUri['scheme']) ? $parsedUri['scheme'].'://' : '')
            .(!empty($parsedUri['user']) ? $parsedUri['user'].(!empty($parsedUri['pass']) ? ':'.$parsedUri['pass'] : '').'@' : '')
            .($parsedUri['host'] ?? '')
            .(!empty($parsedUri['port']) ? ':'.$parsedUri['port'] : '')
            .($parsedUri['path'] ?? '')
            .(!empty($parsedUri['query']) ? '?'.$parsedUri['query'] : '')
            .(!empty($parsedUri['fragment']) ? '#'.$parsedUri['fragment'] : '');
    }
    
    /**
     * Alternative to `parse_url()`. UTF-8 safe, supports URIs without a scheme (and without `//`)
     * @param string $uri
     *
     * @return array|false
     */
    public static function parseUri(string $uri): array|false
    {
        #Trim first
        $uri = mb_trim($uri, null, 'UTF-8');
        $result = preg_match('/^(?:(?<scheme>[^:\/?#]+):)?(?:\/\/(?:(?<user>[^:@\/]+)(?::(?<pass>[^:@\/]+))?@)?(?<host>[^\/?#:]*)(?::(?<port>\d+))?)?(?<path>[^?#]*)(?:\?(?<query>[^#]*))?(?:#(?<fragment>.*))?$/ui', $uri, $matches);
        #If the match failed somehow or if the array is empty - return false
        if ($result !== 1 || empty($matches)) {
            return false;
        }
        #Remove the numeric keys
        foreach ($matches as $key => $value) {
            if (\is_int($key)) {
                unset($matches[$key]);
            }
        }
        return $matches;
    }
    
    /**
     * Alternative to `http_build_query()` but with URL encoding only reserved characters
     *
     * @param array $params
     *
     * @return string
     */
    public static function rawBuildQuery(array $params): string
    {
        $pairs = [];
        foreach ($params as $key => $value) {
            #Keep key and value as-is, no encoding
            $pairs[] = self::toRFC3986($key).'='.self::toRFC3986($value);
        }
        #Supressing inspection, since we are intentionally avoiding URL encoding, which `http_build_query()` does
        /** @noinspection ImplodeMissUseInspection */
        return implode('&', $pairs);
    }
    
    /**
     * Same as `rawurlencode` but only for reserved characters
     * @param string $string String to encode
     *
     * @return string
     */
    public static function toRFC3986(string $string): string
    {
        #It is important to have the `%` symbol first in the list, because otherwise the `%` in all replacements will also be replaced, and the result will require double decoding
        return str_replace(['%', ':', '/', '?', '#', '[', ']', '@', '!', '$', '&', '\'', '(', ')', '*', '+', ',', ';', '='], ['%25', '%3A', '%2F', '%3F', '%23', '%5B', '%5D', '%40', '%21', '%24', '%26', '%27', '%28', '%29', '%2A', '%2B', '%2C', '%3B', '%3D'], $string);
    }
}
