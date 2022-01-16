<?php
declare(strict_types=1);
namespace Simbiat\HTTP20;

class Atom
{
    #Object to cache some common functions
    private Common $HTTP20;

    public function __construct()
    {
        #Caching common functions
        $this->HTTP20 = (new Common);
    }

    #Function generates Atom feed (based on https://validator.w3.org/feed/docs/atom.html)
    public function Atom(string $title, array $entries, string $id = '', string $textType = 'text', array $feed_settings = []): void
    {
        #Validate title
        if (empty($title)) {
            (new Headers)->clientReturn('500', false);
            throw new \UnexpectedValueException('No `title` provided in settings for the feed');
        } else {
            $feed_settings['title'] = $title;
        }
        #validate text type
        if (!in_array(strtolower($textType), ['text', 'html', 'xhtml'])) {
            (new Headers)->clientReturn('500', false);
            throw new \UnexpectedValueException('Unsupported text type provided for Atom feed');
        }
        #Validate content
        if (!empty($entries)) {
            $this->atomElementValidator($entries, 'entry', 'link');
        }
        #Check id
        if (empty($id)) {
            $feed_settings['id'] = $this->HTTP20->htmlToRFC3986((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        } else {
            if ($this->HTTP20->uriValidator($id)) {
                $feed_settings['id'] = $this->HTTP20->htmlToRFC3986($id);
            } else {
                (new Headers)->clientReturn('500', false);
                throw new \UnexpectedValueException('$id provided is not a valid URI');
            }
        }
        #Check time
        if (empty($feed_settings['updated'])) {
            $dates = array_merge(array_column($entries, 'updated'), array_column($entries, 'published'));
            if (empty($dates)) {
                $feed_settings['updated'] = $this->HTTP20->valueToTime(time(), \DATE_ATOM);
            } else {
                $feed_settings['updated'] = $this->HTTP20->valueToTime(max($dates), \DATE_ATOM);
            }
        } else {
            $feed_settings['updated'] = $this->HTTP20->valueToTime($feed_settings['updated'], \DATE_ATOM);
        }
        #Send Last-Modified header right now, but do not exit if 304 is sent, so that proper set of Cache-Control headers is sent as well
        (new Headers)->lastModified(strtotime($feed_settings['updated']));
        #Validate authors
        if (!empty($feed_settings['authors'])) {
            $this->atomElementValidator($feed_settings['authors']);
        }
        #Validate contributors
        if (!empty($feed_settings['contributors'])) {
            $this->atomElementValidator($feed_settings['contributors'], 'contributor');
        }
        #Validate links
        if (!empty($feed_settings['links'])) {
            $this->atomElementValidator($feed_settings['links'], 'link', 'href');
        }
        #Validate categories
        if (!empty($feed_settings['categories'])) {
            $this->atomElementValidator($feed_settings['categories'], 'category', 'term');
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
        $title = $root->appendChild($feed->createElement('title', $feed_settings['title']));
        $title->setAttribute('type', $textType);
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
                $this->atomAddAttributes($linkElem, $link, ['href', 'rel', 'type', 'hreflang', 'title', 'length']);
            }
        }
        #Add persons
        if (!empty($feed_settings['authors'])) {
            foreach ($feed_settings['authors'] as $person) {
                $author = $root->appendChild($feed->createElement('author'));
                $this->atomAddSubElements($author, $feed, $person);
            }
        }
        if (!empty($feed_settings['contributors'])) {
            foreach ($feed_settings['contributors'] as $person) {
                $contributor = $root->appendChild($feed->createElement('contributor'));
                $this->atomAddSubElements($contributor, $feed, $person);
            }
        }
        #Add optional feed tags
        if (!empty($feed_settings['subtitle'])) {
            $subtitle = $root->appendChild($feed->createElement('subtitle', $feed_settings['subtitle']));
            $subtitle->setAttribute('type', $textType);
        }
        if (!empty($feed_settings['icon'])) {
            $root->appendChild($feed->createElement('icon', $this->HTTP20->htmlToRFC3986($feed_settings['icon'])));
        }
        if (!empty($feed_settings['logo'])) {
            $root->appendChild($feed->createElement('logo', $this->HTTP20->htmlToRFC3986($feed_settings['logo'])));
        }
        if (!empty($feed_settings['rights'])) {
            $rights = $root->appendChild($feed->createElement('rights', $feed_settings['rights']));
            $rights->setAttribute('type', $textType);
        }
        if (!empty($feed_settings['categories'])) {
            foreach ($feed_settings['categories'] as $cat) {
                $category = $root->appendChild($feed->createElement('category'));
                $this->atomAddAttributes($category, $cat, ['term', 'scheme', 'label']);
            }
        }
        #Add generator referencing the library itself
        $generator = $root->appendChild($feed->createElement('generator', 'Simbiat/HTTP20'));
        $generator->setAttribute('uri', 'https://github.com/Simbiat/HTTP20');
        #Add actual entries in the feed
        if (!empty($entries)) {
            foreach ($entries as $entry) {
                $element = $root->appendChild($feed->createElement('entry'));
                $this->atomAddEntries($element, $feed, $entry, $textType);
            }
        }
        $feed->normalizeDocument();
        #Output
        @header('Content-type: application/atom+xml;charset=utf-8');
        $this->HTTP20->zEcho($feed->saveXML(), 'hour');
    }

    #Helper function to validate some elements
    private function atomElementValidator(array &$elements, string $type = 'author', string $element = 'name'): void
    {
        foreach ($elements as $key=> $elementToVal) {
            if (!is_array($elementToVal)) {
                unset($elements[$key]);continue;
            }
            if (empty($elementToVal[$element])) {
                unset($elements[$key]);continue;
            } else {
                if (!is_string($elementToVal[$element])) {
                    unset($elements[$key]);continue;
                }
            }
            if ($type === 'link') {
                if (!$this->HTTP20->uriValidator($elementToVal['href'])) {
                    unset($elements[$key]);continue;
                }
                if (!empty($elementToVal['rel'])) {
                    if (!in_array($elementToVal['rel'], ['alternate', 'self', 'enclosure', 'related', 'via'])) {
                        unset($elements[$key]);continue;
                    }
                }
            }
            if ($type === 'entry') {
                if (empty($elementToVal['title'])) {
                    unset($elements[$key]);continue;
                }
                if (empty($elementToVal['updated'])) {
                    unset($elements[$key]);continue;
                }
                if (!$this->HTTP20->uriValidator($elementToVal['link'])) {
                    unset($elements[$key]);continue;
                }
            }
        }
    }

    #Helper function to add some elements
    private function atomAddSubElements(\DOMNode $element, \DOMDocument $feed, array $topTag): void
    {
        foreach (['name', 'email', 'uri'] as $subNode) {
            if (!empty($topTag[$subNode])) {
                if ($subNode === 'uri') {
                    $element->appendChild($feed->createElement($subNode, $this->HTTP20->htmlToRFC3986( $topTag[$subNode])));
                } else {
                    $element->appendChild($feed->createElement($subNode, $topTag[$subNode]));
                }
            }
        }
    }

    #Helper function to add some elements
    private function atomAddAttributes(\DOMElement $element, array $topTag, array $attributes): void
    {
        if (empty($attributes)) {
            (new Headers)->clientReturn('500', false);
            throw new \UnexpectedValueException('Empty list of attributes provided for `atomAddAttributes` function');
        }
        foreach ($attributes as $attribute) {
            if (!empty($topTag[$attribute])) {
                if ($attribute === 'href') {
                    $element->setAttribute($attribute, $this->HTTP20->htmlToRFC3986($topTag[$attribute]));
                } else {
                    $element->setAttribute($attribute, $topTag[$attribute]);
                }
            }
        }
    }

    #Helper function to add actual entries
    private function atomAddEntries(\DOMNode $element, \DOMDocument $feed, array $entry, string $textType): void
    {
        #Adding mandatory tags
        if (empty($entry['id'])) {
            $element->appendChild($feed->createElement('id', $this->HTTP20->atomIDGen($entry['link'])));
        } else {
            $element->appendChild($feed->createElement('id', $entry['id']));
        }
        $title = $element->appendChild($feed->createElement('title', $entry['title']));
        $title->setAttribute('type', $textType);
        $element->appendChild($feed->createElement('updated', $this->HTTP20->valueToTime($entry['updated'], \DATE_ATOM)));
        #Add link as alternate
        $link = $element->appendChild($feed->createElement('link'));
        $link->setAttribute('rel', 'alternate');
        $link->setAttribute('href', $this->HTTP20->htmlToRFC3986($entry['link']));
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
                $contributor->appendChild($feed->createElement('uri', $this->HTTP20->htmlToRFC3986($entry['contributor_uri'])));
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
            $element->appendChild($feed->createElement('published', $this->HTTP20->valueToTime($entry['published'], \DATE_ATOM)));
        } else {
            $element->appendChild($feed->createElement('updated', $this->HTTP20->valueToTime($entry['updated'], \DATE_ATOM)));
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
                $source->appendChild($feed->createElement('updated', $this->HTTP20->valueToTime($entry['source_updated'], \DATE_ATOM)));
            }
        }
    }
}
