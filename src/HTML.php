<?php
declare(strict_types=1);
namespace http20;

class HTML
{
    #Static to count breadcrumbs in case multiple ones are created
    public static int $crumbs = 0;
    
    #Function to generate breadcrumbs for your website in Microdata format
    public function breadcrumbs(array $items, string $delimiter = '>', bool $links = false, bool $headers = false): string|array
    {
        #Sanitize $items
        foreach ($items as $key=>$item) {
            if (empty($item['href']) || empty($item['name'])) {
                unset($items[$key]);
            }
        }
        #Register error, by do not stop further processing
        if (empty($items)) {
            trigger_error('No valid items found for breadcrumbs', E_USER_NOTICE);
            return '';
        }
        #Increase the count for crumbs
        self::$crumbs++;
        #Sanitise delimiter and wrap it in a span
        $delimiter = '<span class="delimiter_breadcrumbs_'.self::$crumbs.'">'.htmlspecialchars($delimiter).'</span>';
        #Set initial item number (position)
        $position = 1;
        #Set depth of the item. This is, essentially, position in reverse. Useful in case you want to hide some of the elements in the list. Adding 1 to avoid last element getting ID of 0.
        $itemDepth = count($items);
        #Open data
        $output = '<nav role="navigation" aria-label="breadcrumb '.self::$crumbs.'"><ol name="breadcrumbs_'.self::$crumbs.'" id="ol_breadcrumbs_'.self::$crumbs.'" itemscope itemtype="http://schema.org/BreadcrumbList" numberOfItems="'.$itemDepth.'" itemListOrder="ItemListUnordered">';
        #Open links
        if ($links) {
            $linksArr = [];
        }
        foreach ($items as $key=>$item) {
            #Add top page if links were requested and this if the first link in set. Technically, it looks like only "home" should be currently supported, but if not supported by client, the link should be silently ignored as per specification, so no worries
            if ($links && $position === 1) {
                $linksArr[] = ['href' => $item['href'], 'rel' => 'home index top begin prefetch'];
            }
            #Update item value to string. First element will always have its ID end with 0, because you may want to hide first element (generally home page) with CSS
            $items[$key] = '<li id="li_breadcrumbs_'.self::$crumbs.'_'.($position === 1 ? 0 : $itemDepth).'" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"'.($itemDepth === 1 ? 'aria-current="location"' : '').'><a id="a_breadcrumbs_'.self::$crumbs.'_'.($position === 1 ? 0 : $itemDepth).'" itemscope itemtype="https://schema.org/WebPage" itemprop="item" itemid="'.$item['href'].'" href="'.htmlspecialchars($item['href']).'"><span id="span_breadcrumbs_'.self::$crumbs.'_'.($position === 1 ? 0 : $itemDepth).'" itemprop="name">'.htmlspecialchars($item['name']).'</span></a><meta itemprop="position" content="'.$position.'" /></li>';
            #Update counters
            $itemDepth--;
            $position++;
            #Add page as "parent" to current one, if links were requested and this not the first (and only) link in set. Technically, "up" was dropped from specification, but if not supported by client, the link should be silently ignored as per specification, so no worries
            if ($links && $itemDepth === 0 && $position !== 1) {
                $linksArr[] = ['href' => $item['href'], 'rel' => 'up prefetch'];
            }
        }
        #Implode items and add them to output
        $output .= implode($delimiter, $items);
        #Close data
        $output .= '</ol></nav>';
        if ($links) {
            #Send headers, if this was requested
            if ($headers) {
                #Create object, since we will need it twice
                $headersObj = (new \http20\Headers);
                #Send headers
                $headersObj->links($linksArr, 'header');
                #Replace array of links with prepared strings for future use, if requried
                $linksArr = $headersObj->links($linksArr, 'head');
            }
            #Return both breadcrumbs and links (so that they can be used later, for example, added to final HTML document)
            return ['breadcrumbs' => $output, 'links' => $linksArr];
        } else {
            return $output;
        }
    }
    
    #for pagination: <nav role="navigation" aria-label="pagination X">
    #for current page: aria-current="page"
    #https://getbootstrap.com/docs/4.0/components/pagination/
    #<link rel="prev/next/first/last"
}
?>