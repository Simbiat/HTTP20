<?php
declare(strict_types=1);
namespace http20;

class HTML
{
    #Static to count breadcrumbs in case multiple ones are created
    public static int $crumbs = 0;
    
    #Function to generate breadcrumbs for your website in Microdata format
    public function breadcrumbs(array $items, string $delimiter = '>', bool $links = false, bool $directSend = true): string|array
    {
        #Sanitize $items
        foreach ($items as $key=>$item) {
            if (empty($item['href']) || empty($item['name'])) {
                unset($items[$key]);
            } else {
                $items[$key]['href'] = htmlspecialchars($item['href']);
                $items[$key]['name'] = htmlspecialchars($item['name']);
            }
        }
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
        foreach ($items as $key=>$item) {
            #Update item value to string. First element will always have its ID end with 0, because you may want to hide first element (generally home page) with CSS
            $items[$key] = '<li id="li_breadcrumbs_'.self::$crumbs.'_'.($position === 1 ? 0 : $itemDepth).'" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"'.($itemDepth === 1 ? 'aria-current="location"' : '').'><a id="a_breadcrumbs_'.self::$crumbs.'_'.($position === 1 ? 0 : $itemDepth).'" itemscope itemtype="https://schema.org/WebPage" itemprop="item" itemid="'.$item['href'].'" href="'.$item['href'].'"><span id="span_breadcrumbs_'.self::$crumbs.'_'.($position === 1 ? 0 : $itemDepth).'" itemprop="name">'.$item['name'].'</span></a><meta itemprop="position" content="'.$position.'" /></li>';
            #Update counters
            $itemDepth--;
            $position++;
        }
        #Implode items and add them to output
        $output .= implode($delimiter, $items);
        #Close data
        $output .= '</ol></nav>';
        return $output;
    }
    
    #for pagination: <nav role="navigation" aria-label="pagination X">
    #for current page: aria-current="page"
    #https://getbootstrap.com/docs/4.0/components/pagination/
    #<link rel="prev/next/first/last"?
    #rel="up" for breadcrumbs second to last page? (itemdepth = 2 or itemdepth =1 and position - 0?)
}
?>