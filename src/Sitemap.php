<?php
declare(strict_types = 1);

namespace Simbiat\http20;

use JetBrains\PhpStorm\ExpectedValues;
use function in_array;

/**
 * Generate sitemap file
 */
class Sitemap
{
    /**
     * Function to generate sitemap in XML, HTML or text formats. For XML specifications refer to https://www.sitemaps.org/protocol.html
     * @param array  $links         List of links to process.
     * @param string $format        Format for the output. Values `xml`, `index`, `html`, `text` or `txt` are expected.
     * @param bool   $direct_output Whether to output result directly to browser.
     *
     * @return string
     */
    public static function sitemap(array $links, #[ExpectedValues(['xml', 'index', 'html', 'text', 'txt'])] string $format = 'xml', bool $direct_output = false): string
    {
        #Sanitize format
        if (!in_array($format, ['xml', 'index', 'html', 'text', 'txt'])) {
            $format = 'xml';
        }
        #Validate the links if the list is not empty. I did not find any recommendations for empty sitemaps, and I do not see a technical reason to break here. If sitemaps are generated using some kind of pagination logic and a "bad" page is server to it, that results in empty array
        self::linksValidator($links);
        #Allow only 50000 links
        $links = array_slice($links, 0, 50000, true);
        #Generate the output string
        if (in_array($format, ['text', 'txt'])) {
            $output = implode("\r\n", array_column($links, 'loc'));
        } else {
            #Set initial output
            $output = match ($format) {
                'xml' => '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">',
                'index' => '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">',
                default => '',
            };
            #Set initial string length
            $str_len = match ($format) {
                'xml' => mb_strlen($output, 'UTF-8') + mb_strlen('</urlset>', 'UTF-8'),
                'index' => mb_strlen($output, 'UTF-8') + mb_strlen('</sitemapindex>', 'UTF-8'),
                default => 0,
            };
            foreach ($links as $key => $link) {
                #Generate entry
                $to_add = match ($format) {
                    #Wrapping in <p> so that even if the string is sent to client directly, it would still be human-readable
                    'html' => '<p><a class="sitemaplink" id="sitemaplink_'.$key.'" href="'.$link['loc'].'" target="_blank">'.$link['name'].'</a></p>',
                    'xml' => '<url><loc>'.$link['loc'].'</loc>'.(empty($link['lastmod']) ? '' : '<lastmod>'.$link['lastmod'].'</lastmod>').(empty($link['changefreq']) ? '' : '<changefreq>'.$link['changefreq'].'</changefreq>').(empty($link['priority']) ? '' : '<priority>'.$link['priority'].'</priority>').'</url>',
                    'index' => '<sitemap><loc>'.$link['loc'].'</loc>'.(empty($link['lastmod']) ? '' : '<lastmod>'.$link['lastmod'].'</lastmod>').'</sitemap>',
                    default => '',
                };
                #Get its length
                $len_to_add = mb_strlen($to_add, 'UTF-8');
                #Check that we are not exceeding the limit of 50 MB. Using limit from Google (https://developers.google.com/search/docs/advanced/sitemaps/build-sitemap) rather than from original spec (https://www.sitemaps.org/protocol.html), since we should care more about search engines' limitations
                if (($str_len + $len_to_add) < 52428800) {
                    $output .= $to_add;
                    $str_len += $len_to_add;
                }
            }
            #Close tags
            $output .= match ($format) {
                'xml' => '</urlset>',
                'index' => '</sitemapindex>',
                default => '',
            };
        }
        #Output directly if the flag is set to true
        if ($direct_output) {
            if (!headers_sent()) {
                switch ($format) {
                    case 'html':
                        header('Content-Type: text/html; charset=utf-8');
                        break;
                    case 'text':
                    case 'txt':
                        header('Content-Type: text/plain; charset=utf-8');
                        break;
                    default:
                        header('Content-Type: application/xml; charset=utf-8');
                        break;
                }
            }
            Common::zEcho($output);
        }
        return $output;
    }
    
    /**
     * Function to validate the links provided
     * @param array $links
     *
     * @return void
     */
    private static function linksValidator(array &$links): void
    {
        #Get first element of the array to use it as base for next. Need to use array_key_first, because we may get an associative array
        if (empty($links)) {
            throw new \UnexpectedValueException('Empty array of links provided');
        }
        $first = $links[array_key_first($links)];
        #Check if 'loc' is set
        if (!isset($first['loc'])) {
            throw new \UnexpectedValueException('No `loc` value provided for first link');
        }
        #Parse the URL
        $first = IRI::parseUri($first['loc']);
        if (!is_array($first)) {
            throw new \UnexpectedValueException('Failed to parse `loc` element as URL');
        }
        #Check that scheme and host are present
        if (empty($first['scheme']) || empty($first['host'])) {
            throw new \UnexpectedValueException('Failed to determine scheme or host for provided links');
        }
        #Build base URL
        $first = $first['scheme'].'://'.(empty($first['user']) ? '' : $first['user'].(empty($first['pass']) ? '' : ':'.$first['pass']).'@').$first['host'].(empty($first['port']) ? '' : ':'.$first['port']);
        #Get counts of `loc` values
        $value_counts = array_count_values(array_column($links, 'loc'));
        #Get max value of lastmod
        $max_date = array_map('\intval', array_column($links, 'lastmod'));
        if (!empty($max_date)) {
            $max_date = max($max_date);
        } else {
            $max_date = 0;
        }
        #Send Last-Modified header and stop further processing if client already has a fresh enough copy
        Headers::lastModified($max_date, true);
        #Check that all links start from
        foreach ($links as $key => $link) {
            #Check if 'loc' is set
            if (!isset($link['loc'])) {
                throw new \UnexpectedValueException('No `loc` value provided for link `'.$key.'`');
            }
            #Check if `loc` has same base URL
            if (mb_strripos($link['loc'], $first, 0, 'UTF-8') !== 0) {
                throw new \UnexpectedValueException('Link `'.$key.'` has different base URL');
            }
            #Check for duplicates
            if ($value_counts[$link['loc']] > 1) {
                #Remove duplicate
                unset($links[$key]);
                #Reduce count
                $value_counts[$link['loc']]--;
            }
            #Sanitize values
            $links[$key]['loc'] = Common::htmlToRFC3986($link['loc']);
            #Sanitize name (used only for HTML format
            if (isset($link['name'])) {
                $links[$key]['name'] = htmlspecialchars($link['name'], ENT_QUOTES | ENT_SUBSTITUTE);
            } else {
                $links[$key]['name'] = htmlspecialchars($links[$key]['loc'], ENT_QUOTES | ENT_SUBSTITUTE);
            }
            #Convert lastmod
            if (isset($link['lastmod'])) {
                $links[$key]['lastmod'] = Common::valueToTime($link['lastmod'], \DATE_ATOM);
            }
            #Unset invalid changefreq
            if (isset($link['changefreq']) && preg_match('/^(always|hourly|daily|weekly|monthly|yearly|never)$/i', $link['changefreq']) !== 1) {
                unset($links[$key]['changefreq']);
            }
            if (isset($link['priority'])) {
                if (is_numeric($link['priority'])) {
                    $link['priority'] = (float)$link['priority'];
                    if ($link['priority'] > 1.0) {
                        $links[$key]['priority'] = '1.0';
                    } elseif ($link['priority'] < 0.0) {
                        $links[$key]['priority'] = '0.0';
                    } else {
                        $links[$key]['priority'] = number_format($link['priority'], 1);
                    }
                } else {
                    unset($links[$key]['priority']);
                }
            }
        }
    }
}
