<?php
declare(strict_types=1);
namespace Simbiat\HTTP20;

class RSS
{
    #Object to cache some common functions
    private \Simbiat\HTTP20\Common $HTTP20;
    
    public function __construct()
    {
        #Caching common functions for some performance benefits
        $this->HTTP20 = (new \Simbiat\HTTP20\Common);
    }
    
    #Function generates RSS 2.0 feed (based on https://www.rssboard.org/rss-specification)
    public function RSS(string $title, array $entries, string $feedlink = '', array $feed_settings = []): void
    {
        #Validate title
        if (empty($title)) {
            (new \Simbiat\HTTP20\Headers)->clientReturn('500', false);
            throw new \UnexpectedValueException('No `title` provided in settings for the feed');
        } else {
            $feed_settings['title'] = $title;
        }
        #Check feed link
        if (empty($feedlink)) {
            $feed_settings['link'] = $this->HTTP20->htmlToRFC3986((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        } else {
            if ($this->HTTP20->uriValidator($feedlink)) {
                $feed_settings['link'] = $this->HTTP20->htmlToRFC3986($feedlink);
            } else {
                (new \Simbiat\HTTP20\Headers)->clientReturn('500', false);
                throw new \UnexpectedValueException('$feedlink provided is not a valid URI');
            }
        }
        #Validate content
        if (!empty($entries)) {
            foreach ($entries as $key=>$entry) {
                if (empty($entry['title']) && empty($entry['description'])) {
                    unset($entries[$key]);continue;
                }
                if (!empty($entry['enclosure_url'])) {
                    if (empty($entry['enclosure_length']) || empty($entry['enclosure_type'])) {
                        unset($entries[$key]);continue;
                    } else {
                        if (is_numeric($entry['enclosure_length'])) {
                            unset($entries[$key]);continue;
                        }
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
                $feed_settings['pubDate'] = $this->HTTP20->valueToTime(time(), \DATE_RSS);
            } else {
                $feed_settings['pubDate'] = $this->HTTP20->valueToTime(max($dates), \DATE_RSS);
            }
        } else {
            $feed_settings['pubDate'] = $this->HTTP20->valueToTime($feed_settings['pubDate'], \DATE_RSS);
        }
        if (empty($feed_settings['lastBuildDate'])) {
            $feed_settings['lastBuildDate'] = $feed_settings['pubDate'];
        } else {
            $feed_settings['lastBuildDate'] = $this->HTTP20->valueToTime($feed_settings['lastBuildDate'], \DATE_RSS);
        }
        #Send Last-Modified header right now, but do not exit if 304 is sent, so that proper set of Cache-Control headers is sent as well
        (new \Simbiat\HTTP20\Headers)->lastModified(max(strtotime($feed_settings['pubDate']), strtotime($feed_settings['lastBuildDate'])), false);
        #Check cloud
        if (!empty($feed_settings['cloud'])) {
            if (empty($feed_settings['cloud']['domain']) || empty($feed_settings['cloud']['port']) || empty($feed_settings['cloud']['path']) || empty($feed_settings['cloud']['registerProcedure']) || empty($feed_settings['cloud']['protocol'])) {
                (new \Simbiat\HTTP20\Headers)->clientReturn('500', false);
                throw new \UnexpectedValueException('One or more atributes requried for `cloud` tag are missing in settings for the feed');
            }
        }
        #Check TTL
        if (!empty($feed_settings['ttl']) && !is_numeric($feed_settings['ttl'])) {
            (new \Simbiat\HTTP20\Headers)->clientReturn('500', false);
            throw new \UnexpectedValueException('`ttl` provided in settings for the feed is not numeric');
        }
        #Check image
        if (!empty($feed_settings['image'])) {
            if (empty($feed_settings['image']['url'])) {
                (new \Simbiat\HTTP20\Headers)->clientReturn('500', false);
                throw new \UnexpectedValueException('`url` property for `image` tag is missing in settings for the feed');
            }
            if (!empty($feed_settings['image']['width'])) {
                if (!is_numeric($feed_settings['image']['width'])) {
                    (new \Simbiat\HTTP20\Headers)->clientReturn('500', false);
                    throw new \UnexpectedValueException('`width` property for `image` tag is not numeric in settings for the feed');
                }
                if (intval($feed_settings['image']['width']) > 144) {
                    (new \Simbiat\HTTP20\Headers)->clientReturn('500', false);
                    throw new \UnexpectedValueException('`width` property for `image` tag is more than 144 in settings for the feed');
                }
            }
            if (!empty($feed_settings['image']['height'])) {
                if (!is_numeric($feed_settings['image']['height'])) {
                    (new \Simbiat\HTTP20\Headers)->clientReturn('500', false);
                    throw new \UnexpectedValueException('`height` property for `image` tag is not numeric in settings for the feed');
                }
                if (intval($feed_settings['image']['height']) > 400) {
                    (new \Simbiat\HTTP20\Headers)->clientReturn('500', false);
                    throw new \UnexpectedValueException('`height` property for `image` tag is more than 400 in settings for the feed');
                }
            }
        }
        #Check skipHours
        if (!empty($feed_settings['skipHours']) && is_array($feed_settings['skipHours'])) {
            foreach ($feed_settings['skipHours'] as $hour) {
                if (!is_numeric($hour)) {
                    (new \Simbiat\HTTP20\Headers)->clientReturn('500', false);
                    throw new \UnexpectedValueException('Hour for for `skipHours` tag is not numeric in settings for the feed');
                }
                if (intval($hour) < 0 || intval($hour) > 23) {
                    (new \Simbiat\HTTP20\Headers)->clientReturn('500', false);
                    throw new \UnexpectedValueException('Hour property for `skipHours` tag is outside of 0-23 range in settings for the feed');
                }
            }
        }
        #Check skipDays
        if (!empty($feed_settings['skipDays']) && is_array($feed_settings['skipDays'])) {
            foreach ($feed_settings['skipDays'] as $day) {
                if (!in_array($day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])) {
                    (new \Simbiat\HTTP20\Headers)->clientReturn('500', false);
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
        $version->setAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
        #Create root element
        $root = $version->appendChild($feed->createElement('channel'));
        #Add atom:link
        $atom = $root->appendChild($feed->createElement('atom:link'));
        $atom->setAttribute('href', $this->HTTP20->htmlToRFC3986($feed_settings['link']));
        $atom->setAttribute('rel', 'self');
        $atom->setAttribute('type', 'application/rss+xml');
        #Add global mandatory feed tags
        $root->appendChild($feed->createElement('title', $feed_settings['title']));
        $root->appendChild($feed->createElement('link', $this->HTTP20->htmlToRFC3986($feed_settings['link'])));
        $root->appendChild($feed->createElement('description', $feed_settings['description']));
        #Add optional feed tags
        $root->appendChild($feed->createElement('pubDate', $feed_settings['pubDate']));
        $root->appendChild($feed->createElement('lastBuildDate', $feed_settings['lastBuildDate']));
        if (!empty($feed_settings['language']) && $this->HTTP20->LangCodeCheck($feed_settings['language'])) {
            $root->appendChild($feed->createElement('language', strtolower($feed_settings['language'])));
        }
        if (!empty($feed_settings['copyright'])) {
            $root->appendChild($feed->createElement('copyright', $feed_settings['copyright']));
        }
        if (!empty($feed_settings['managingEditor']) && $this->HTTP20->emailValidator($feed_settings['managingEditor'])) {
            $root->appendChild($feed->createElement('managingEditor', $feed_settings['managingEditor']));
        }
        if (!empty($feed_settings['webMaster']) && $this->HTTP20->emailValidator($feed_settings['webMaster'])) {
            $root->appendChild($feed->createElement('webMaster', $feed_settings['webMaster']));
        }
        #Add cloud details (rssCloud)
        if (!empty($feed_settings['cloud'])) {
            $cloud = $root->appendChild($feed->createElement('cloud'));
            $cloud->setAttribute('domain', $feed_settings['cloud']['domain']);
            $cloud->setAttribute('port', $feed_settings['cloud']['port']);
            $cloud->setAttribute('path', $feed_settings['cloud']['path']);
            $cloud->setAttribute('registerProcedure', $feed_settings['cloud']['registerProcedure']);
            $cloud->setAttribute('protocol', $feed_settings['cloud']['protocol']);
        }
        if (!empty($feed_settings['ttl'])) {
            $root->appendChild($feed->createElement('ttl', strval(intval($feed_settings['ttl']))));
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
            $image->appendChild($feed->createElement('url', $this->HTTP20->htmlToRFC3986($feed_settings['image']['url'])));
            $image->appendChild($feed->createElement('title', $feed_settings['title']));
            $image->appendChild($feed->createElement('link', $this->HTTP20->htmlToRFC3986($feed_settings['link'])));
            if (!empty($feed_settings['image']['width'])) {
                $image->appendChild($feed->createElement('width', strval(intval($feed_settings['image']['width']))));
            }
            if (!empty($feed_settings['image']['height'])) {
                $image->appendChild($feed->createElement('height', strval(intval($feed_settings['image']['height']))));
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
                $this->rssAddEntries($element, $feed, $entry);
            }
        }
        $feed->normalizeDocument();
        #Output
        header('Content-type: application/rss+xml;charset=utf-8');
        $this->HTTP20->zEcho($feed->saveXML(), 'hour');
    }
    
    #Helper function to add actual entries
    private function rssAddEntries(\DOMNode &$element, \DOMDocument &$feed, array $entry): void
    {
        if (!empty($entry['title'])) {
            $element->appendChild($feed->createElement('title', $entry['title']));
        }
        if (!empty($entry['link'])) {
            $entry['link'] = $this->HTTP20->htmlToRFC3986($entry['link']);
            $element->appendChild($feed->createElement('link', $entry['link']));
            $guid = $element->appendChild($feed->createElement('guid', $entry['link']));
            $guid->setAttribute('isPermaLink', 'true');
            $source = $element->appendChild($feed->createElement('source', $entry['title']));
            $source->setAttribute('url', $entry['link']);
        }
        if (!empty($entry['description'])) {
            $element->appendChild($feed->createElement('description', $entry['description']));
        }
        if (!empty($feed_settings['author']) && $this->HTTP20->emailValidator($feed_settings['author'])) {
            $root->appendChild($feed->createElement('author', $feed_settings['author']));
        }
        if (!empty($entry['category'])) {
            $element->appendChild($feed->createElement('category', $entry['category']));
        }
        if (!empty($entry['comments'])) {
            $element->appendChild($feed->createElement('comments', $this->HTTP20->htmlToRFC3986($entry['comments'])));
        }
        if (!empty($entry['pubDate'])) {
            $element->appendChild($feed->createElement('pubDate', $this->HTTP20->valueToTime($entry['pubDate'], \DATE_RSS)));
        }
        if (!empty($entry['enclosure_url'])) {
            $enclosure = $element->appendChild($feed->createElement('enclosure'));
            $enclosure->setAttribute('url', $entry['enclosure_url']);
            $enclosure->setAttribute('length', strval(intval($entry['enclosure_length'])));
            $enclosure->setAttribute('type', $entry['enclosure_type']);
        }
    }
}
?>