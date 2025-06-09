<?php
declare(strict_types = 1);

namespace Simbiat\http20;

use JetBrains\PhpStorm\ExpectedValues;

use function in_array, is_array;

/**
 * Generate Atom feed
 */
class Atom
{
    /**
     * Function generates Atom feed (based on https://validator.w3.org/feed/docs/atom.html)
     * @param string $title         Text to be used in `<title>` element
     * @param array  $entries       Items for the feed
     * @param string $id            Text, that will be used as `id`. It needs to be a URI. If empty `$_SERVER['REQUEST_URI']` will be used.
     * @param string $textType      Text type, that will be added as attribute to some tags as per specification. Supported types are `text`, `html`, `xhtml`.
     * @param array  $feed_settings Array with optional settings for the feed. Check Atom.md for details.
     *
     * @return void
     * @throws \DOMException
     */
    public static function atom(string $title, array $entries, string $id = '', #[ExpectedValues(['text', 'html', 'xhtml'])] string $textType = 'text', array $feed_settings = []): void
    {
        #Validate title
        if (empty($title)) {
            Headers::clientReturn(500, false);
            throw new \UnexpectedValueException('No `title` provided in settings for the feed');
        }
        $feed_settings['title'] = $title;
        #validate text type
        if (!in_array(mb_strtolower($textType, 'UTF-8'), ['text', 'html', 'xhtml'])) {
            Headers::clientReturn(500, false);
            throw new \UnexpectedValueException('Unsupported text type provided for Atom feed');
        }
        #Validate content
        if (!empty($entries)) {
            self::atomElementValidator($entries, 'entry', 'link');
        }
        #Check id
        if (empty($id)) {
            $feed_settings['id'] = Common::htmlToRFC3986((isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        } elseif (IRI::isValidIri($id, 'https')) {
            $feed_settings['id'] = Common::htmlToRFC3986($id);
        } else {
            Headers::clientReturn(500, false);
            throw new \UnexpectedValueException('$id provided is not a valid URI');
        }
        #Check time
        if (empty($feed_settings['updated'])) {
            $dates = array_merge(array_column($entries, 'updated'), array_column($entries, 'published'));
            if (empty($dates)) {
                $feed_settings['updated'] = Common::valueToTime(time(), \DATE_ATOM);
            } else {
                $feed_settings['updated'] = Common::valueToTime(max($dates), \DATE_ATOM);
            }
        } else {
            $feed_settings['updated'] = Common::valueToTime($feed_settings['updated'], \DATE_ATOM);
        }
        #Send Last-Modified header right now, but do not exit if 304 is sent, so that proper set of Cache-Control headers is sent as well
        Headers::lastModified(strtotime($feed_settings['updated']));
        #Validate authors
        if (!empty($feed_settings['authors'])) {
            self::atomElementValidator($feed_settings['authors']);
        }
        #Validate contributors
        if (!empty($feed_settings['contributors'])) {
            self::atomElementValidator($feed_settings['contributors'], 'contributor');
        }
        #Validate links
        if (!empty($feed_settings['links'])) {
            self::atomElementValidator($feed_settings['links'], 'link', 'href');
        }
        #Validate categories
        if (!empty($feed_settings['categories'])) {
            self::atomElementValidator($feed_settings['categories'], 'category', 'term');
        }
        #Generating the feed. Using DomDocument for cleaner look and strings sanitization
        $feed = new \DomDocument('1.0', 'UTF-8');
        #We would prefer a pretty file, just in case
        $feed->preserveWhiteSpace = false;
        $feed->formatOutput = true;
        $feed->substituteEntities = false;
        #Create root element
        $root = $feed->appendChild($feed->createElement('feed'));
        $root->setAttribute('xmlns', 'https://www.w3.org/2005/Atom');
        #Add global mandatory feed tags
        $titleDOM = $root->appendChild($feed->createElement('title', $feed_settings['title']));
        $titleDOM->setAttribute('type', $textType);
        $root->appendChild($feed->createElement('updated', $feed_settings['updated']));
        $root->appendChild($feed->createElement('id', $feed_settings['id']));
        #Add recommended feed tags
        #Add link tag
        $link = $root->appendChild($feed->createElement('link'));
        $link->setAttribute('rel', 'self');
        $link->setAttribute('href', $feed_settings['id']);
        #Add any extra links
        if (!empty($feed_settings['links'])) {
            foreach ($feed_settings['links'] as $link) {
                $linkElem = $root->appendChild($feed->createElement('link'));
                self::atomAddAttributes($linkElem, $link, ['href', 'rel', 'type', 'hreflang', 'title', 'length']);
            }
        }
        #Add persons
        if (!empty($feed_settings['authors'])) {
            foreach ($feed_settings['authors'] as $person) {
                $author = $root->appendChild($feed->createElement('author'));
                self::atomAddSubElements($author, $feed, $person);
            }
        }
        if (!empty($feed_settings['contributors'])) {
            foreach ($feed_settings['contributors'] as $person) {
                $contributor = $root->appendChild($feed->createElement('contributor'));
                self::atomAddSubElements($contributor, $feed, $person);
            }
        }
        #Add optional feed tags
        if (!empty($feed_settings['subtitle'])) {
            $subtitle = $root->appendChild($feed->createElement('subtitle', $feed_settings['subtitle']));
            $subtitle->setAttribute('type', $textType);
        }
        if (!empty($feed_settings['icon'])) {
            $root->appendChild($feed->createElement('icon', Common::htmlToRFC3986($feed_settings['icon'])));
        }
        if (!empty($feed_settings['logo'])) {
            $root->appendChild($feed->createElement('logo', Common::htmlToRFC3986($feed_settings['logo'])));
        }
        if (!empty($feed_settings['rights'])) {
            $rights = $root->appendChild($feed->createElement('rights', $feed_settings['rights']));
            $rights->setAttribute('type', $textType);
        }
        if (!empty($feed_settings['categories'])) {
            foreach ($feed_settings['categories'] as $cat) {
                $category = $root->appendChild($feed->createElement('category'));
                self::atomAddAttributes($category, $cat, ['term', 'scheme', 'label']);
            }
        }
        #Add generator referencing the library itself
        $generator = $root->appendChild($feed->createElement('generator', 'Simbiat/http20'));
        $generator->setAttribute('uri', 'https://github.com/Simbiat/http20');
        #Add actual entries in the feed
        if (!empty($entries)) {
            foreach ($entries as $entry) {
                $element = $root->appendChild($feed->createElement('entry'));
                self::atomAddEntries($element, $feed, $entry, $textType);
            }
        }
        $feed->normalizeDocument();
        #Output
        if (!headers_sent()) {
            header('Content-type: application/atom+xml;charset=utf-8');
        }
        Common::zEcho($feed->saveXML(), 'hour');
    }
    
    /**
     * Helper function to validate some elements
     * @param array  $elements    Array of elements to validate
     * @param string $type        Optional type of the element
     * @param string $elementName Name of the element
     *
     * @return void
     */
    private static function atomElementValidator(array &$elements, string $type = 'author', string $elementName = 'name'): void
    {
        foreach ($elements as $key => $elementToVal) {
            if (!is_array($elementToVal)) {
                unset($elements[$key]);
                continue;
            }
            if (empty($elementToVal[$elementName])) {
                unset($elements[$key]);
                continue;
            }
            if (!\is_string($elementToVal[$elementName])) {
                unset($elements[$key]);
                continue;
            }
            if ($type === 'link') {
                if (!IRI::isValidIri($elementToVal['href'], 'https')) {
                    unset($elements[$key]);
                    continue;
                }
                if (!empty($elementToVal['rel']) && !in_array($elementToVal['rel'], ['alternate', 'self', 'enclosure', 'related', 'via'])) {
                    unset($elements[$key]);
                    continue;
                }
            }
            if ($type === 'entry') {
                if (empty($elementToVal['title'])) {
                    unset($elements[$key]);
                    continue;
                }
                if (empty($elementToVal['updated'])) {
                    unset($elements[$key]);
                    continue;
                }
                if (!IRI::isValidIri($elementToVal['link'], 'https')) {
                    unset($elements[$key]);
                }
            }
        }
    }
    
    /**
     * Helper function to add sub elements
     * @param \DOMNode     $element Node to process
     * @param \DOMDocument $feed    Main feed object
     * @param array        $topTag  Tag name
     *
     * @return void
     * @throws \DOMException
     */
    private static function atomAddSubElements(\DOMNode $element, \DOMDocument $feed, array $topTag): void
    {
        foreach (['name', 'email', 'uri'] as $subNode) {
            if (!empty($topTag[$subNode])) {
                if ($subNode === 'uri') {
                    $element->appendChild($feed->createElement($subNode, Common::htmlToRFC3986($topTag[$subNode])));
                } else {
                    $element->appendChild($feed->createElement($subNode, $topTag[$subNode]));
                }
            }
        }
    }
    
    /**
     * Helper function to add attributes
     * @param \DOMElement $element    Node to process
     * @param array       $topTag     Tag name
     * @param array       $attributes Attributes to add
     *
     * @return void
     */
    private static function atomAddAttributes(\DOMElement $element, array $topTag, array $attributes): void
    {
        if (empty($attributes)) {
            Headers::clientReturn(500, false);
            throw new \UnexpectedValueException('Empty list of attributes provided for `atomAddAttributes` function');
        }
        foreach ($attributes as $attribute) {
            if (!empty($topTag[$attribute])) {
                if ($attribute === 'href') {
                    $element->setAttribute($attribute, Common::htmlToRFC3986($topTag[$attribute]));
                } else {
                    $element->setAttribute($attribute, $topTag[$attribute]);
                }
            }
        }
    }
    
    /**
     * Helper function to add actual entries
     * @param \DOMNode     $element  Node to process
     * @param \DOMDocument $feed     Main feed object
     * @param array        $entry    List of elements to add
     * @param string       $textType Text type
     *
     * @return void
     * @throws \DOMException
     */
    private static function atomAddEntries(\DOMNode $element, \DOMDocument $feed, array $entry, string $textType): void
    {
        #Adding mandatory tags
        if (empty($entry['id'])) {
            $element->appendChild($feed->createElement('id', self::atomIDGen($entry['link'])));
        } else {
            $element->appendChild($feed->createElement('id', $entry['id']));
        }
        $title = $element->appendChild($feed->createElement('title', $entry['title']));
        $title->setAttribute('type', $textType);
        $element->appendChild($feed->createElement('updated', Common::valueToTime($entry['updated'], \DATE_ATOM)));
        #Add link as alternate
        $link = $element->appendChild($feed->createElement('link'));
        $link->setAttribute('rel', 'alternate');
        $link->setAttribute('href', Common::htmlToRFC3986($entry['link']));
        #Adding recommended tags
        #Add persons
        if (!empty($entry['author_name']) || !empty($entry['author_email']) || !empty($entry['author_uri'])) {
            $author = $element->appendChild($feed->createElement('author'));
            if (!empty($entry['author_name'])) {
                $author->appendChild($feed->createElement('name', $entry['author_name']));
            }
            if (!empty($entry['author_email'])) {
                $author->appendChild($feed->createElement('email', $entry['author_email']));
            }
            if (!empty($entry['author_uri'])) {
                $author->appendChild($feed->createElement('uri', $entry['author_uri']));
            }
        }
        if (!empty($entry['contributor_name']) || !empty($entry['contributor_email']) || !empty($entry['contributor_uri'])) {
            $contributor = $element->appendChild($feed->createElement('contributor'));
            if (!empty($entry['contributor_name'])) {
                $contributor->appendChild($feed->createElement('name', $entry['contributor_name']));
            }
            if (!empty($entry['contributor_email'])) {
                $contributor->appendChild($feed->createElement('email', $entry['contributor_email']));
            }
            if (!empty($entry['contributor_uri'])) {
                $contributor->appendChild($feed->createElement('uri', Common::htmlToRFC3986($entry['contributor_uri'])));
            }
        }
        if (!empty($entry['content'])) {
            $element->appendChild($feed->createElement('content', $entry['content']));
        }
        if (!empty($entry['summary'])) {
            $summary = $element->appendChild($feed->createElement('summary', $entry['summary']));
            $summary->setAttribute('type', $textType);
        }
        #Add optional tags
        if (!empty($entry['category'])) {
            $category = $element->appendChild($feed->createElement('category'));
            $category->setAttribute('term', $entry['category']);
        }
        if (!empty($entry['published'])) {
            $element->appendChild($feed->createElement('published', Common::valueToTime($entry['published'], \DATE_ATOM)));
        } else {
            $element->appendChild($feed->createElement('updated', Common::valueToTime($entry['updated'], \DATE_ATOM)));
        }
        if (!empty($entry['rights'])) {
            $rights = $element->appendChild($feed->createElement('rights', $entry['rights']));
            $rights->setAttribute('type', $textType);
        }
        #Add source
        if (!empty($entry['source_id']) || !empty($entry['source_title']) || !empty($entry['source_updated'])) {
            $source = $element->appendChild($feed->createElement('source'));
            if (!empty($entry['source_id'])) {
                $source->appendChild($feed->createElement('id', $entry['source_id']));
            }
            if (!empty($entry['source_title'])) {
                $source_title = $source->appendChild($feed->createElement('title', $entry['source_title']));
                $source_title->setAttribute('type', $textType);
            }
            if (!empty($entry['source_updated'])) {
                $source->appendChild($feed->createElement('updated', Common::valueToTime($entry['source_updated'], \DATE_ATOM)));
            }
        }
    }
    
    /**
     * Function to prepare ID for Atom feed as suggested on http://web.archive.org/web/20110514113830/http://diveintomark.org/archives/2004/05/28/howto-atom-id
     * @param string $link
     *
     * @return string
     */
    private static function atomIDGen(string $link): string
    {
        $date = Common::valueToTime(null, 'Y-m-d', '/^\d{4}-\d{2}-\d{2}$/i');
        #Remove URI protocol (if any)
        $link = preg_replace('/^(?:[a-zA-Z]+?:\/\/)?/im', '', Common::htmlToRFC3986($link));
        #Replace any # with /
        $link = preg_replace('/#/m', '/', $link);
        #Remove HTML/XML reserved characters as precaution.
        #Using \x{5C} instead if \ directly due false-positive hit from PHPStorm https://youtrack.jetbrains.com/issue/IDEA-298082
        $link = preg_replace('/[\x{5C}\'"<>&]/im', '', $link);
        #Add 'tag:' to beginning and ',Y-m-d:' after domain name
        return preg_replace('/(?<domain>^(?:www\.)?([^:\/\n?]+))(?<rest>.*)/im', 'tag:$1,'.$date.':$3', $link);
    }
}
