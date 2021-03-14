<?php
declare(strict_types=1);
namespace Simbiat\http20;

class Sitemap
{
    #Function to generate sitemape in XML, HTML or text formats. For XML specifications refer to https://www.sitemaps.org/protocol.html
    public function sitemap(array $links, string $format = 'xml', bool $directOutput = false): string
    {
        #Sanitize format
        if (!in_array($format, ['xml', 'index', 'html', 'text', 'txt'])) {
            $format = 'xml';
        }
        #Validate links, if list is not empty. I did not find any recommendations for empty sitemaps and I do not see a technical reason to break here, because if sitemaps are generated using some kind of pagination logic and a "bad" page is server to it, that results in empty array
        $this->linksValidator($links);
        #Cache Common HTTP20 functions
        $http20 = (new \Simbiat\http20\Common);
        #Allow only 50000 links
        $links = array_slice($links, 0, 50000, true);
        #Generate the output string
        if (in_array($format, ['text', 'txt'])) {
            $output = implode("\r\n", array_column($links, 'loc'));
        } else {
            #Set initial output
            $output = match($format) {
                'xml' => '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
                'index' => '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
                default => '',
            };
            #Set initial string length
            $strlen = match($format) {
                'xml' => strlen($output) + strlen('</urlset>'),
                'index' => strlen($output) + strlen('</sitemapindex>'),
                default => 0,
            };
            foreach ($links as $key=>$link) {
                #Generate entry
                $toAdd = match($format) {
                    #Wrapping in <p> so that even if the string is sent to client directly, it would still be human-readable
                    'html' => '<p><a class="sitemaplink" id="sitemaplink_'.$key.'" href="'.$link['loc'].'" target="_blank">'.$link['name'].'</a></p>',
                    'xml' => '<url><loc>'.$link['loc'].'</loc>'.(empty($link['lastmod']) ? '' : $link['lastmod']).(empty($link['changefreq']) ? '' : $link['changefreq']).(empty($link['priority']) ? '' : $link['priority']).'</url>',
                    'index' => '<sitemap><loc>'.$link['loc'].'</loc>'.(empty($link['lastmod']) ? '' : $link['lastmod']).'</sitemap>',
                    default => '',
                };
                #Get its length
                $lenToAdd = strlen($toAdd);
                #Check, that we are not exceeding the limit of 50MB. Using limit from Google (https://developers.google.com/search/docs/advanced/sitemaps/build-sitemap) rather then from original spec (https://www.sitemaps.org/protocol.html), since we should care more about search engines' limitations
                if (($strlen + $lenToAdd) < 52428800) {
                    $output .= $toAdd;
                    $strlen += $lenToAdd;
                }
            }
            #Close tags
            $output .= match($format) {
                'xml' => '</urlset>',
                'index' => '</sitemapindex>',
                default => '',
            };
        }
        #Output directly, if flag is set to true
        if ($directOutput) {
            switch ($format) {
                case 'html':
                    header('Content-Type: text/html; charset=utf-8');
                    break;
                case 'text':
                case 'txt':
                    header('Content-Type: text/plain; charset=utf-8');
                    break;
                default:
                    header('Content-Type: text/xml; charset=utf-8');
                    break;
            }
            (new \Simbiat\http20\Common)->zEcho($output);
        } else {
            return $output;
        }
    }
    
    #Function to validate the links provided
    private function linksValidator(array &$links): bool
    {
        #Get first element of the array to use it as base for next. Need to use array_key_first, because we may get an assotiative array
        $first = @$links[array_key_first($links)];
        #Check if 'loc' is set
        if (!isset($first['loc'])) {
            throw new \UnexpectedValueException('No `loc` value provided for first link');
        }
        #Parse the URL
        $first = parse_url($first['loc']);
        #Check that scheme and host are present
        if (empty($first['scheme']) || empty($first['host'])) {
            throw new \UnexpectedValueException('Failed to determine scheme or host for provided links');
        }
        #Build base URL
        $first = $first['scheme'].'://'.(empty($first['user']) ? '' : $first['user'].(empty($first['pass']) ? '' : ':'.$first['pass']).'@').$first['host'].(empty($first['port']) ? '' : ':'.strval($first['port']));
        #Get counts of `loc` values
        $valueCounts = array_count_values(array_column($links, 'loc'));
        #Get max value of lastmod
        $maxdate = array_map('intval', array_column($links, 'lastmod'));
        if (!empty($maxdate)) {
            $maxdate = max($maxdate);
        } else {
            $maxdate = 0;
        }
        #Send Last-Modified header and stop further processing if client already has a fresh enough copy
        (new \Simbiat\http20\Headers)->lastModified($maxdate, true);
        #Cache Common HTTP20 functions
        $http20 = (new \Simbiat\http20\Common);
        #Check that all links start from
        foreach ($links as $key=>$link) {
            #Check if 'loc' is set
            if (!isset($link['loc'])) {
                throw new \UnexpectedValueException('No `loc` value provided for link `'.$key.'`');
            }
            #Check if `loc` has same base URL
            if (strripos($link['loc'], $first) !== 0) {
                throw new \UnexpectedValueException('Link `'.$key.'` has different base URL');
            }
            #Check for duplicates
            if ($valueCounts[$link['loc']] > 1) {
                #Remove duplicate
                unset($links[$key]);
                #Reduce count
                $valueCounts[$link['loc']]--;
            }
            #Sanitize values
            $links[$key]['loc'] = $http20->htmlToRFC3986($link['loc'], true);
            #Sanitize name (used only for HTML format
            if (isset($link['name'])) {
                $links[$key]['name'] = htmlspecialchars($link['name']);
            } else {
                $links[$key]['name'] = htmlspecialchars($links[$key]['loc']);
            }
            #Convert lastmod
            if (isset($link['lastmod'])) {
                $links[$key]['lastmod'] = $http20->valueToTime($link['lastmod'], \DATE_ATOM);
            }
            #Unset invalid changefreq
            if (isset($link['changefreq']) && preg_match('/^(always|hourly|daily|weekly|monthly|yearly|never)$/i', $link['changefreq']) !== 1) {
                unset($links[$key]['changefreq']);
            }
            if (isset($link['priority'])) {
                if (is_numeric($link['priority'])) {
                    $link['priority'] = floatval($link['priority']);
                    if ($link['priority'] > 1.0) {
                        $links[$key]['priority'] = '1.0';
                    } elseif ($link['priority'] < 0.0) {
                        $links[$key]['priority'] = '0.0';
                    } else {
                        $links[$key]['priority'] = number_format($link['priority'], 1, '.');
                    }
                } else {
                    unset($links[$key]['priority']);
                }
            }
        }
        return true;
    }
}
?>