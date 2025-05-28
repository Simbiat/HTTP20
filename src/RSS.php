<?php
declare(strict_types = 1);

namespace Simbiat\http20;

use function is_array;

/**
 * Class to generate RSS feed
 */
class RSS
{
    /**
     * Function generates RSS 2.0 feed (based on https://www.rssboard.org/rss-specification)
     * @param string $title         Title for RSS feed.
     * @param array  $entries       Items for the feed.
     * @param string $feedLink      Link to feed. If empty current `REQUEST_URI` will be used.
     * @param array  $feed_settings Feed settings
     *
     * @return void
     * @throws \DOMException
     */
    public static function RSS(string $title, array $entries, string $feedLink = '', array $feed_settings = []): void
    {
        #Validate title
        if (empty($title)) {
            Headers::clientReturn(500, false);
            throw new \UnexpectedValueException('No `title` provided in settings for the feed');
        }
        $feed_settings['title'] = $title;
        #Check feed link
        if (empty($feedLink)) {
            $feed_settings['link'] = Common::htmlToRFC3986((isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        } elseif (IRI::isValidIri($feedLink)) {
            $feed_settings['link'] = Common::htmlToRFC3986($feedLink);
        } else {
            Headers::clientReturn(500, false);
            throw new \UnexpectedValueException('$feedLink provided is not a valid URI');
        }
        #Validate content
        if (!empty($entries)) {
            foreach ($entries as $key => $entry) {
                if (empty($entry['title']) && empty($entry['description'])) {
                    unset($entries[$key]);
                    continue;
                }
                if (!empty($entry['enclosure_url'])) {
                    if (empty($entry['enclosure_length']) || empty($entry['enclosure_type'])) {
                        unset($entries[$key]);
                        continue;
                    }
                    if (is_numeric($entry['enclosure_length'])) {
                        unset($entries[$key]);
                        continue;
                    }
                }
                #Add <source> data
                $entries[$key]['source_url'] = $feed_settings['link'];
                $entries[$key]['source_title'] = $feed_settings['title'];
            }
        }
        if (empty($feed_settings['description'])) {
            $feed_settings['description'] = $feed_settings['title'];
        }
        #Check time
        if (empty($feed_settings['pubDate'])) {
            $dates = array_column($entries, 'pubDate');
            if (empty($dates)) {
                $feed_settings['pubDate'] = Common::valueToTime(time(), \DATE_RSS);
            } else {
                $feed_settings['pubDate'] = Common::valueToTime(max($dates), \DATE_RSS);
            }
        } else {
            $feed_settings['pubDate'] = Common::valueToTime($feed_settings['pubDate'], \DATE_RSS);
        }
        if (empty($feed_settings['lastBuildDate'])) {
            $feed_settings['lastBuildDate'] = $feed_settings['pubDate'];
        } else {
            $feed_settings['lastBuildDate'] = Common::valueToTime($feed_settings['lastBuildDate'], \DATE_RSS);
        }
        #Send Last-Modified header right now, but do not exit if 304 is sent, so that proper set of Cache-Control headers is sent as well
        Headers::lastModified(max(strtotime($feed_settings['pubDate']), strtotime($feed_settings['lastBuildDate'])));
        #Check cloud
        if (!empty($feed_settings['cloud'])) {
            if (empty($feed_settings['cloud']['domain']) || empty($feed_settings['cloud']['port']) || empty($feed_settings['cloud']['path']) || empty($feed_settings['cloud']['registerProcedure']) || empty($feed_settings['cloud']['protocol'])) {
                Headers::clientReturn(500, false);
                throw new \UnexpectedValueException('One or more attributes required for `cloud` tag are missing in settings for the feed');
            }
        }
        #Check TTL
        if (!empty($feed_settings['ttl']) && !is_numeric($feed_settings['ttl'])) {
            Headers::clientReturn(500, false);
            throw new \UnexpectedValueException('`ttl` provided in settings for the feed is not numeric');
        }
        #Check image
        if (!empty($feed_settings['image'])) {
            if (empty($feed_settings['image']['url'])) {
                Headers::clientReturn(500, false);
                throw new \UnexpectedValueException('`url` property for `image` tag is missing in settings for the feed');
            }
            if (!empty($feed_settings['image']['width'])) {
                if (!is_numeric($feed_settings['image']['width'])) {
                    Headers::clientReturn(500, false);
                    throw new \UnexpectedValueException('`width` property for `image` tag is not numeric in settings for the feed');
                }
                if ((int)$feed_settings['image']['width'] > 144) {
                    Headers::clientReturn(500, false);
                    throw new \UnexpectedValueException('`width` property for `image` tag is more than 144 in settings for the feed');
                }
            }
            if (!empty($feed_settings['image']['height'])) {
                if (!is_numeric($feed_settings['image']['height'])) {
                    Headers::clientReturn(500, false);
                    throw new \UnexpectedValueException('`height` property for `image` tag is not numeric in settings for the feed');
                }
                if ((int)$feed_settings['image']['height'] > 400) {
                    Headers::clientReturn(500, false);
                    throw new \UnexpectedValueException('`height` property for `image` tag is more than 400 in settings for the feed');
                }
            }
        }
        #Check skipHours
        if (!empty($feed_settings['skipHours']) && is_array($feed_settings['skipHours'])) {
            foreach ($feed_settings['skipHours'] as $hour) {
                if (!is_numeric($hour)) {
                    Headers::clientReturn(500, false);
                    throw new \UnexpectedValueException('Hour for for `skipHours` tag is not numeric in settings for the feed');
                }
                if ((int)$hour < 0 || (int)$hour > 23) {
                    Headers::clientReturn(500, false);
                    throw new \UnexpectedValueException('Hour property for `skipHours` tag is outside of 0-23 range in settings for the feed');
                }
            }
        }
        #Check skipDays
        if (!empty($feed_settings['skipDays']) && is_array($feed_settings['skipDays'])) {
            foreach ($feed_settings['skipDays'] as $day) {
                if (!\in_array($day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])) {
                    Headers::clientReturn(500, false);
                    throw new \UnexpectedValueException('Day property for `skipDays` tag is not one of accepted values (Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday) in settings for the feed');
                }
            }
        }
        #Generating the feed. Using DomDocument for cleaner look and strings sanitization
        $feed = new \DomDocument('1.0', 'UTF-8');
        #We would prefer a pretty file, just in case
        $feed->preserveWhiteSpace = false;
        $feed->formatOutput = true;
        $feed->substituteEntities = false;
        #Add version
        $version = $feed->appendChild($feed->createElement('rss'));
        $version->setAttribute('version', '2.0');
        $version->setAttribute('xmlns:atom', 'https://www.w3.org/2005/Atom');
        #Create root element
        $root = $version->appendChild($feed->createElement('channel'));
        #Add atom:link
        $atom = $feed->createElement('atom:link');
        $atom->setAttribute('href', Common::htmlToRFC3986($feed_settings['link']));
        $atom->setAttribute('rel', 'self');
        $atom->setAttribute('type', 'application/rss+xml');
        $root->appendChild($atom);
        #Add global mandatory feed tags
        $root->appendChild($feed->createElement('title', $feed_settings['title']));
        $root->appendChild($feed->createElement('link', Common::htmlToRFC3986($feed_settings['link'])));
        $root->appendChild($feed->createElement('description', $feed_settings['description']));
        #Add optional feed tags
        $root->appendChild($feed->createElement('pubDate', $feed_settings['pubDate']));
        $root->appendChild($feed->createElement('lastBuildDate', $feed_settings['lastBuildDate']));
        if (!empty($feed_settings['language']) && Common::LangCodeCheck($feed_settings['language'])) {
            $root->appendChild($feed->createElement('language', mb_strtolower($feed_settings['language'], 'UTF-8')));
        }
        if (!empty($feed_settings['copyright'])) {
            $root->appendChild($feed->createElement('copyright', $feed_settings['copyright']));
        }
        if (!empty($feed_settings['managingEditor']) && filter_var($feed_settings['managingEditor'], FILTER_VALIDATE_EMAIL | FILTER_FLAG_EMAIL_UNICODE)) {
            $root->appendChild($feed->createElement('managingEditor', $feed_settings['managingEditor']));
        }
        if (!empty($feed_settings['webMaster']) && filter_var($feed_settings['webMaster'], FILTER_VALIDATE_EMAIL | FILTER_FLAG_EMAIL_UNICODE)) {
            $root->appendChild($feed->createElement('webMaster', $feed_settings['webMaster']));
        }
        #Add cloud details (rssCloud)
        if (!empty($feed_settings['cloud'])) {
            $cloud = $feed->createElement('cloud');
            $cloud->setAttribute('domain', $feed_settings['cloud']['domain']);
            $cloud->setAttribute('port', $feed_settings['cloud']['port']);
            $cloud->setAttribute('path', $feed_settings['cloud']['path']);
            $cloud->setAttribute('registerProcedure', $feed_settings['cloud']['registerProcedure']);
            $cloud->setAttribute('protocol', $feed_settings['cloud']['protocol']);
            $root->appendChild($cloud);
        }
        if (!empty($feed_settings['ttl'])) {
            $root->appendChild($feed->createElement('ttl', (string)(int)$feed_settings['ttl']));
        }
        #Add categories
        if (!empty($feed_settings['categories']) && is_array($feed_settings['categories'])) {
            foreach ($feed_settings['categories'] as $cat) {
                $root->appendChild($feed->createElement('category', $cat));
            }
        }
        #Add image
        if (!empty($feed_settings['image'])) {
            $image = $root->appendChild($feed->createElement('image'));
            $image->appendChild($feed->createElement('url', Common::htmlToRFC3986($feed_settings['image']['url'])));
            $image->appendChild($feed->createElement('title', $feed_settings['title']));
            $image->appendChild($feed->createElement('link', Common::htmlToRFC3986($feed_settings['link'])));
            if (!empty($feed_settings['image']['width'])) {
                $image->appendChild($feed->createElement('width', (string)(int)$feed_settings['image']['width']));
            }
            if (!empty($feed_settings['image']['height'])) {
                $image->appendChild($feed->createElement('height', (string)(int)$feed_settings['image']['height']));
            }
        }
        #Add skipDays
        if (!empty($feed_settings['skipDays']) && is_array($feed_settings['skipDays'])) {
            $skipDays = $root->appendChild($feed->createElement('skipDays'));
            foreach ($feed_settings['skipDays'] as $day) {
                $skipDays->appendChild($feed->createElement('day', $day));
            }
        }
        #Add skipHours
        if (!empty($feed_settings['skipHours']) && is_array($feed_settings['skipHours'])) {
            $skipHours = $root->appendChild($feed->createElement('skipHours'));
            foreach ($feed_settings['skipHours'] as $hour) {
                $skipHours->appendChild($feed->createElement('hour', $hour));
            }
        }
        #Add generator referencing the library itself
        $root->appendChild($feed->createElement('generator', 'Simbiat/HTTP20, https://github.com/Simbiat/HTTP20'));
        $root->appendChild($feed->createElement('docs', 'https://www.rssboard.org/rss-specification'));
        #Add actual entries in the feed
        if (!empty($entries)) {
            foreach ($entries as $entry) {
                $element = $root->appendChild($feed->createElement('item'));
                self::rssAddEntries($element, $feed, $entry);
            }
        }
        $feed->normalizeDocument();
        #Output
        if (!headers_sent()) {
            header('Content-type: application/rss+xml;charset=utf-8');
        }
        Common::zEcho($feed->saveXML(), 'hour');
    }
    
    /**
     * Helper function to add actual entries
     * @param \DOMNode     $element Node to process
     * @param \DOMDocument $feed    Main feed object
     * @param array        $entry   Element to add
     *
     * @return void
     * @throws \DOMException
     */
    private static function rssAddEntries(\DOMNode $element, \DOMDocument $feed, array $entry): void
    {
        if (!empty($entry['title'])) {
            $element->appendChild($feed->createElement('title', $entry['title']));
        }
        if (!empty($entry['link'])) {
            $entry['link'] = Common::htmlToRFC3986($entry['link']);
            $element->appendChild($feed->createElement('link', $entry['link']));
            $guid = $element->appendChild($feed->createElement('guid', $entry['link']));
            $guid->setAttribute('isPermaLink', 'true');
            $source = $element->appendChild($feed->createElement('source', $entry['title']));
            $source->setAttribute('url', $entry['link']);
        }
        if (!empty($entry['description'])) {
            $element->appendChild($feed->createElement('description', $entry['description']));
        }
        if (!empty($feed_settings['author']) && filter_var($feed_settings['author'], FILTER_VALIDATE_EMAIL | FILTER_FLAG_EMAIL_UNICODE)) {
            $element->appendChild($feed->createElement('author', $feed_settings['author']));
        }
        if (!empty($entry['category'])) {
            $element->appendChild($feed->createElement('category', $entry['category']));
        }
        if (!empty($entry['comments'])) {
            $element->appendChild($feed->createElement('comments', Common::htmlToRFC3986($entry['comments'])));
        }
        if (!empty($entry['pubDate'])) {
            $element->appendChild($feed->createElement('pubDate', Common::valueToTime($entry['pubDate'], \DATE_RSS)));
        }
        if (!empty($entry['enclosure_url'])) {
            $enclosure = $element->appendChild($feed->createElement('enclosure'));
            $enclosure->setAttribute('url', $entry['enclosure_url']);
            $enclosure->setAttribute('length', (string)(int)$entry['enclosure_length']);
            $enclosure->setAttribute('type', $entry['enclosure_type']);
        }
    }
}
