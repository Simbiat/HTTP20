<?php
declare(strict_types = 1);

namespace Simbiat\http20;

/**
 * IRI-related function
 */
class IRI
{
    /**
     * Check if string is a valid International Resource Identifier (IRI)
     * @param string $iri
     *
     * @return bool
     */
    public static function isValidIri(string $iri): bool
    {
        #Convert to URI
        $uri = self::iriToUri($iri);
        return $uri !== null;
    }
    
    /**
     * Convert International Resource Identifier (IRI) to Universal Resource Identifier (URI)
     * @param string $iri IRI string
     *
     * @return string|null
     */
    public static function iriToUri(string $iri): ?string
    {
        $iri = mb_scrub($iri, 'UTF-8');
        #Try parsing the IRI first
        $parsed_iri = parse_url($iri);
        if (!\is_array($parsed_iri)) {
            return null;
        }
        #If there is no scheme, then it may be a relative path, and thus in some cases it's not possible to determine if first part (before first slash) is a domain or actual part of a path
        if (empty($parsed_iri['scheme'])) {
            return null;
        }
        #If we have scheme, but somehow lack host - that's also abnormal
        if (empty($parsed_iri['host'])) {
            return null;
        }
        #Convert host to ASCII
        $ascii_host = idn_to_ascii($parsed_iri['host']);
        #If it's false - return
        if ($ascii_host === false) {
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
            #Need to use explode/implode to preserve `/` symbols. Doing rawurlencode and then restoring them, may restore symbols that were encoded in original IRI
            $parsed_iri['path'] = implode('/', array_map('rawurlencode', explode('/', $parsed_iri['path'])));
        }
        #Process query, if present
        if (!empty($parsed_iri['query'])) {
            #Need to do thins in a somewhat complex way, because we need to preserve `=` and `&` signs (similar to path)
            $parsed_iri['query'] = implode('&', array_map(static function ($subPath) {
                return implode('=', array_map('rawurlencode', explode('=', $subPath)));
            }, explode('&', $parsed_iri['query'])));
        }
        #Process fragment, if present
        if (!empty($parsed_iri['fragment'])) {
            $parsed_iri['fragment'] = rawurlencode($parsed_iri['fragment']);
        }
        #Update host. For some reason IDE complains about other updates, if I do this earlier
        $parsed_iri['host'] = $ascii_host;
        return self::restoreUri($parsed_iri);
    }
    
    /**
     * Restore array from `parse_url()` function to original URL
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
            .(!empty($parsed_iri['user']) ? $parsed_iri['user'].(!empty($parsedUri['pass']) ? ':'.$parsedUri['pass'] : '').'@' : '')
            .($parsedUri['host'] ?? '')
            .(!empty($parsedUri['port']) ? ':'.$parsedUri['port'] : '')
            .($parsedUri['path'] ?? '')
            .(!empty($parsedUri['query']) ? '?'.$parsedUri['query'] : '')
            .(!empty($parsedUri['fragment']) ? '#'.$parsedUri['fragment'] : '');
    }
}
