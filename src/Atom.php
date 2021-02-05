<?php
declare(strict_types=1);
namespace http20;

class Atom
{
    #Object to cache some common functions
    private \HTTP20\Common $http20;
    
    public function __construct()
    {
        #Caching common functions
        $this->http20 = (new \http20\Common);
    }
    
    #Function generates Atom feed (based on https://validator.w3.org/feed/docs/atom.html)
    public function Atom(string $title, array $entries, string $id = '', string $texttype = 'text', array $feed_settings = [])
    {
        #Validate title
        if (empty($title)) {
            throw new \UnexpectedValueException('No `title` provided in settings for the feed');
        } else {
            $feed_settings['title'] = $title;
        }
        #validate text type
        if (!in_array(strtolower($texttype), ['text', 'html', 'xhtml'])) {
            throw new \UnexpectedValueException('Unsupported text type provided for Atom feed');
        }
        #Validate content
        if (!empty($entries)) {
            $this->atomElementValidator($entries, 'entry', 'link');
        }
        #Check id
        if (empty($id)) {
            $feed_settings['id'] = $this->http20->htmlToRFC3986((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        } else {
            if ($this->http20->uriValidator($id)) {
                $feed_settings['id'] = $this->http20->htmlToRFC3986($id);
            } else {
                throw new \UnexpectedValueException('$id provided is not a valid URI');
            }
        }
        #Check time
        if (empty($feed_settings['updated'])) {
            $dates = array_merge(array_column($entries, 'updated'), array_column($entries, 'published'));
            if (empty($dates)) {
                $feed_settings['updated'] = $this->http20->valueToTime(time(), \DATE_ATOM);
            } else {
                $feed_settings['updated'] = $this->http20->valueToTime(max($dates), \DATE_ATOM);
            }
        } else {
            $feed_settings['updated'] = $this->http20->valueToTime($feed_settings['updated'], \DATE_ATOM);
        }
        #Send Last-Modified header right now, but do not exit if 304 is sent, so that proper set of Cache-Control headers is sent as well
        (new \http20\Headers)->lastModified(strtotime($feed_settings['updated']), false);
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
        $root->setAttribute('xmlns', 'http://www.w3.org/2005/Atom');
        #Add global mandatory feed tags
        $title = $root->appendChild($feed->createElement('title', $feed_settings['title']));
        $title->setAttribute('type', $texttype);
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
                $linkelem = $root->appendChild($feed->createElement('link'));
                $this->atomAddAttributes($linkelem, $feed, $link, ['href', 'rel', 'type', 'hreflang', 'title', 'length']);
            }
        }
        #Add persons
        if (!empty($feed_settings['authors'])) {
            foreach ($feed_settings['authors'] as $person) {
                $author = $root->appendChild($feed->createElement('author'));
                $this->atomAddSubElements($author, $feed, $person, ['name', 'email', 'uri']);
            }
        }
        if (!empty($feed_settings['contributors'])) {
            foreach ($feed_settings['contributors'] as $person) {
                $contributor = $root->appendChild($feed->createElement('contributor'));
                $this->atomAddSubElements($contributor, $feed, $person, ['name', 'email', 'uri']);
            }
        }
        #Add optional feed tags
        if (!empty($feed_settings['subtitle'])) {
            $subtitle = $root->appendChild($feed->createElement('subtitle', $feed_settings['subtitle']));
            $subtitle->setAttribute('type', $texttype);
        }
        if (!empty($feed_settings['icon'])) {
            $root->appendChild($feed->createElement('icon', $this->http20->htmlToRFC3986($feed_settings['icon'])));
        }
        if (!empty($feed_settings['logo'])) {
            $root->appendChild($feed->createElement('logo', $this->http20->htmlToRFC3986($feed_settings['logo'])));
        }
        if (!empty($feed_settings['rights'])) {
            $rights = $root->appendChild($feed->createElement('rights', $feed_settings['rights']));
            $rights->setAttribute('type', $texttype);
        }
        if (!empty($feed_settings['categories'])) {
            foreach ($feed_settings['categories'] as $cat) {
                $category = $root->appendChild($feed->createElement('category'));
                $this->atomAddAttributes($category, $feed, $cat, ['term', 'scheme', 'label']);
            }
        }
        #Add generator referencing the library itself
        $generator = $root->appendChild($feed->createElement('generator', 'Simbiat/HTTP20'));
        $generator->setAttribute('uri', 'https://github.com/Simbiat/HTTP20');
        #Add actual entries in the feed
        if (!empty($entries)) {
            foreach ($entries as $entry) {
                $element = $root->appendChild($feed->createElement('entry'));
                $this->atomAddEntries($element, $feed, $entry, $texttype);
            }
        }
        $feed->normalizeDocument();
        #Output
        header('Content-type: application/atom+xml;charset=utf-8');
        $this->http20->zEcho($feed->saveXML(), 'hour');
    }
    
    #Helper function to validate some elements
    private function atomElementValidator(array $elstoval, string $type = 'author', string $element = 'name'): void
    {
        foreach ($elstoval as $key=>$eltoval) {
            if (!is_array($eltoval)) {
                throw new \UnexpectedValueException('Element `'.$key.'` in `'.$type.'` provided is not an array');
            }
            if (empty($eltoval[$element])) {
                throw new \UnexpectedValueException('`'.$element.'` for element `'.$key.'` in `'.$type.'` is not provided');
            } else {
                if (!is_string($eltoval[$element])) {
                    throw new \UnexpectedValueException('`'.$element.'` for element `'.$key.'` in `'.$type.'` is not a string');
                }
            }
            if ($type === 'link') {
                if (!$this->http20->uriValidator($eltoval['href'])) {
                    throw new \UnexpectedValueException('`href` for element `'.$key.'` in `'.$type.'` is not a valid URI');
                }
                if (!empty($eltoval['rel'])) {
                    if (!in_array($eltoval['rel'], ['alternate', 'self', 'ecnlosure', 'related', 'via'])) {
                        throw new \UnexpectedValueException('Unsupported `rel` value ('.$eltoval['rel'] .') provided for element `'.$key.'` in `'.$type.'s');
                    }
                }
            }
            if ($type === 'entry') {
                if (empty($eltoval['title'])) {
                    throw new \UnexpectedValueException('`Title` for element `'.$key.'` in `'.$type.'` is not provided');
                }
                if (empty($eltoval['updated'])) {
                    throw new \UnexpectedValueException('`Updated` for element `'.$key.'` in `'.$type.'` is not provided');
                }
                if (!$this->http20->uriValidator($eltoval['link'])) {
                    throw new \UnexpectedValueException('`link` ('.$eltoval['link'].') for element `'.$key.'` in `'.$type.'` is not a valid URI');
                }
            }
        }
    }
    
    #Helper function to add some elements
    private function atomAddSubElements(\DOMNode &$element, \DOMDocument &$feed, array $toptag, array $subnodes)
    {
        if (empty($subnodes)) {
            throw new \UnexpectedValueException('Empty list of subnodes provided for `atomAddSubElements` function');
        }
        foreach ($subnodes as $subnode) {
            if (!empty($toptag[$subnode])) {
                if ($subnode === 'uri') {
                    $element->appendChild($feed->createElement($subnode, $this->http20->htmlToRFC3986( $toptag[$subnode])));
                } else {
                    $element->appendChild($feed->createElement($subnode, $toptag[$subnode]));
                }
            }
        }
        return $element;
    }
    
    #Helper function to add some elements
    private function atomAddAttributes(\DOMNode &$element, \DOMDocument &$feed, array $toptag, array $attributes)
    {
        if (empty($attributes)) {
            throw new \UnexpectedValueException('Empty list of atrributes provided for `atomAddAttributes` function');
        }
        foreach ($attributes as $attribute) {
            if (!empty($toptag[$attribute])) {
                if ($attribute === 'href') {
                    $element->setAttribute($attribute, $this->http20->htmlToRFC3986($toptag[$attribute]));
                } else {
                    $element->setAttribute($attribute, $toptag[$attribute]);
                }
            }
        }
        return $element;
    }
    
    #Helper function to add actual entries
    private function atomAddEntries(\DOMNode &$element, \DOMDocument &$feed, array $entry, string $texttype)
    {
        #Adding mandatory tags
        if (empty($entry['id'])) {
            $element->appendChild($feed->createElement('id', $this->http20->atomIDGen($entry['link'])));
        } else {
            $element->appendChild($feed->createElement('id', $entry['id']));
        }
        $title = $element->appendChild($feed->createElement('title', $entry['title']));
        $title->setAttribute('type', $texttype);
        $element->appendChild($feed->createElement('updated', $this->http20->valueToTime($entry['updated'], \DATE_ATOM)));
        #Add link as alternate
        $link = $element->appendChild($feed->createElement('link'));
        $link->setAttribute('rel', 'alternate');
        $link->setAttribute('href', $this->http20->htmlToRFC3986($entry['link']));
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
                $contributor->appendChild($feed->createElement('uri', $this->http20->htmlToRFC3986($entry['contributor_uri'])));
            }
        }
        if (!empty($entry['content'])) {
            $element->appendChild($feed->createElement('content', $entry['content']));
        }
        if (!empty($entry['summary'])) {
            $summary = $element->appendChild($feed->createElement('summary', $entry['summary']));
            $summary->setAttribute('type', $texttype);
        }
        #Add optional tags
        if (!empty($entry['category'])) {
            $category = $element->appendChild($feed->createElement('category'));
            $category->setAttribute('term', $entry['category']);
        }
        if (!empty($entry['published'])) {
            $element->appendChild($feed->createElement('published', $this->http20->valueToTime($entry['published'], \DATE_ATOM)));
        } else {
            $element->appendChild($feed->createElement('updated', $this->http20->valueToTime($entry['updated'], \DATE_ATOM)));
        }
        if (!empty($entry['rights'])) {
            $rights = $element->appendChild($feed->createElement('rights', $entry['rights']));
            $rights->setAttribute('type', $texttype);
        }
        #Add source
        if (!empty($entry['source_id']) || !empty($entry['source_title']) || !empty($entry['source_updated'])) {
            $source = $element->appendChild($feed->createElement('source'));
            if (!empty($entry['source_id'])) {
                $source->appendChild($feed->createElement('id', $entry['source_id']));
            }
            if (!empty($entry['source_title'])) {
                $source_title = $source->appendChild($feed->createElement('title', $entry['source_title']));
                $source_title->setAttribute('type', $texttype);
            }
            if (!empty($entry['source_updated'])) {
                $source->appendChild($feed->createElement('updated', $this->http20->valueToTime($entry['source_updated'], \DATE_ATOM)));
            }
        }
    }
}
?>