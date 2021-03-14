<?php
declare(strict_types=1);
namespace http20;

class HTML
{
    #Static to count breadcrumbs in case multiple ones are created
    public static int $crumbs = 0;
    #Static to count paginations in case multiple ones are created
    public static int $paginations = 0;
    
    #Function to generate breadcrumbs for your website in Microdata format as per https://schema.org/BreadcrumbList
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
            if ($links) {
                if ($headers) {
                    return ['breadcrumbs' => '', 'links' => ''];
                } else {
                    return ['breadcrumbs' => '', 'links' => []];
                }
            } else {
                return '';
            }
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
                $linksArr[] = ['href' => $item['href'], 'rel' => 'home index top begin prefetch', 'title' => $item['name']];
            }
            #Update item value to string. First element will always have its ID end with 0, because you may want to hide first element (generally home page) with CSS
            $items[$key] = '<li id="li_breadcrumbs_'.self::$crumbs.'_'.($position === 1 ? 0 : $itemDepth).'" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"'.($itemDepth === 1 ? 'aria-current="location"' : '').'><a id="a_breadcrumbs_'.self::$crumbs.'_'.($position === 1 ? 0 : $itemDepth).'" itemscope itemtype="https://schema.org/WebPage" itemprop="item" itemid="'.$item['href'].'" href="'.htmlspecialchars($item['href']).'"><span id="span_breadcrumbs_'.self::$crumbs.'_'.($position === 1 ? 0 : $itemDepth).'" itemprop="name">'.htmlspecialchars($item['name']).'</span></a><meta itemprop="position" content="'.$position.'" /></li>';
            #Update counters
            $itemDepth--;
            $position++;
            #Add page as "parent" to current one, if links were requested and this not the first (and only) link in set. Technically, "up" was dropped from specification, but if not supported by client, the link should be silently ignored as per specification, so no worries
            if ($links && $itemDepth === 0 && $position !== 1) {
                $linksArr[] = ['href' => $item['href'], 'rel' => 'up prefetch', 'title' => $item['name']];
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
    
    #Function to generate pagination navigation
    public function pagination(int $current, int $total, int $maxNumerics = 5, array $nonNumerics = ['first' => '<<', 'prev' => '<', 'next' => '>', 'last' => '>>', 'first_text' => 'First page', 'prev_text' => 'Previous page', 'next_text' => 'Next page', 'last_text' => 'Last page', 'page_text' => 'Page '], string $prefix = '', bool $links = false, bool $headers = false): string|array
    {
        #Sanitize numbers
        if ($current < 1) {
            $current = 1;
        }
        if ($total < 1) {
            $total = 1;
        }
        if ($maxNumerics < 1) {
            $maxNumerics = 1;
        }
        #Adjust maxNumerics if it's larger than total
        if ($maxNumerics > $total) {
            $maxNumerics = $total;
        }
        #If we have just one page - no reason to do the whole pagination for that. Same if our current page is more than total
        if (($current === $total && $current <= 1) || $current > $total) {
            if ($links) {
                if ($headers) {
                    return ['pagination' => '', 'links' => ''];
                } else {
                    return ['pagination' => '', 'links' => []];
                }
            } else {
                return '';
            }
        }
        #Sanitize settings for non-numeric settings
        foreach ($nonNumerics as $key=>$value) {
            #If not a string - revert text values to defaults
            if (!is_string($value)) {
                $value = match($key) {
                    'first_text' => 'First page',
                    'prev_text' => 'Previous page',
                    'next_text' => 'Next page',
                    'last_text' => 'Last page',
                    'page_text' => 'Page ',
                    default => '',
                };
            }
            #If text values are empty - revert them to defaults, since aria-label and title need them
            if (empty($value)) {
                $value = match($key) {
                    'first_text' => 'First page',
                    'prev_text' => 'Previous page',
                    'next_text' => 'Next page',
                    'last_text' => 'Last page',
                    'page_text' => 'Page ',
                    default => '',
                };
            }
            #Update value in
            $nonNumerics[$key] = $value;
        }
        #Increase the count for paginations
        self::$paginations++;
        #Calculate maximum number of numeric links to left/right of the current one
        $sideNumerics = intval(floor(($maxNumerics - 1)/2));
        #Calculate starting page
        $startPage = $current - $sideNumerics;
        if ($startPage < 1) {
            $startPage = 1;
        }
        #Calculate ending page
        $endPage = $current + $sideNumerics;
        if ($endPage > $total) {
            $endPage = $total;
        }
        #Adjust values so that we always have the same number of numeric pages
        if (($endPage - $startPage) < $maxNumerics) {
            #Calculate "free" slots for links
            $correction = $maxNumerics - (($endPage - $startPage) + 1);
            #If we have space on the right - add to right
            if ($endPage !== $total) {
                $endPage += $correction;
            #If not, but we have space on the left - add to left
            } elseif ($startPage !== 1) {
                $startPage -= $correction;
            }
            #Adjust edge cases
            if ($startPage < 1) {
                $startPage = 1;
            }
            if ($endPage > $total) {
                $endPage = $total;
            }
        }
        #Open data
        $output = '<nav role="navigation" aria-label="pagination '.self::$paginations.'">';
        #Open list
        $output .= '<ol name="pagination_'.self::$paginations.'" id="ol_pagination_'.self::$paginations.'">';
        #Add link to first page
        if (!empty($nonNumerics['first'])) {
            $output .= '<li id="li_pagination_'.self::$paginations.'_first" aria-label="'.$nonNumerics['first_text'].'" title="'.$nonNumerics['first_text'].'"'.($current > (1 + $sideNumerics) ? '' : ' aria-disabled="true"').'>';
            if ($current > (1 + $sideNumerics) && $total !== $maxNumerics) {
                $output .= '<a id="a_pagination_'.self::$paginations.'_first" href="'.$prefix.'1">'.$nonNumerics['first'].'</a>';
            } else {
                $output .= '<span id="a_pagination_'.self::$paginations.'_first">'.$nonNumerics['first'].'</span>';
            }
            $output .= '</li>';
        }
        #Add link to previous page
        if (!empty($nonNumerics['prev'])) {
            $output .= '<li id="li_pagination_'.self::$paginations.'_prev" aria-label="'.$nonNumerics['prev_text'].'" title="'.$nonNumerics['prev_text'].'"'.($current !== 1 ? '' : ' aria-disabled="true"').'>';
            if ($current !== 1 && $total !== $maxNumerics) {
                $output .= '<a id="a_pagination_'.self::$paginations.'_prev" href="'.$prefix.($current -1).'">'.$nonNumerics['prev'].'</a>';
            } else {
                $output .= '<span id="a_pagination_'.self::$paginations.'_prev">'.$nonNumerics['prev'].'</span>';
            }
            $output .= '</li>';
        }
        #Generate numeric links
        for ($i = $startPage; $i <= $endPage; $i++) {
            $output .= '<li id="li_pagination_'.self::$paginations.'_'.$i.'" aria-label="'.$nonNumerics['page_text'].$i.'" title="'.$nonNumerics['page_text'].$i.'"'.($i === $current ? ' class="pagination_currentpage" aria-current="page"' : '').'>';
            if ($i === $current) {
                $output .= '<span id="a_pagination_'.self::$paginations.'_'.$i.'">'.$i.'</span>';
            } else {
                $output .= '<a id="a_pagination_'.self::$paginations.'_'.$i.'" href="'.$prefix.$i.'">'.$i.'</a>';
            }
            $output .= '</li>';
        }
        #Add link to next page
        if (!empty($nonNumerics['next'])) {
            $output .= '<li id="li_pagination_'.self::$paginations.'_next" aria-label="'.$nonNumerics['next_text'].'" title="'.$nonNumerics['next_text'].'"'.($current !== $total ? '' : ' aria-disabled="true"').'>';
            if ($current !== $total && $total !== $maxNumerics) {
                $output .= '<a id="a_pagination_'.self::$paginations.'_next" href="'.$prefix.($current + 1).'">'.$nonNumerics['next'].'</a>';
            } else {
                $output .= '<span id="a_pagination_'.self::$paginations.'_next">'.$nonNumerics['next'].'</span>';
            }
            $output .= '</li>';
        }
        #Add link to last page
        if (!empty($nonNumerics['last'])) {
            $output .= '<li id="li_pagination_'.self::$paginations.'_last" aria-label="'.$nonNumerics['last_text'].'" title="'.$nonNumerics['last_text'].'"'.($current < ($total - $sideNumerics) ? '' : ' aria-disabled="true"').'>';
            if ($current < ($total - $sideNumerics) && $total !== $maxNumerics) {
                $output .= '<a id="a_pagination_'.self::$paginations.'_last" href="'.$prefix.$total.'">'.$nonNumerics['last'].'</a>';
            } else {
                $output .= '<span id="a_pagination_'.self::$paginations.'_last">'.$nonNumerics['last'].'</span>';
            }
            $output .= '</li>';
        }
        #Close list
        $output .= '</ol>';
        #Close data
        $output .= '</nav>';
        if ($links) {
            #Populate array of links
            $linksArr = [];
            if ($current !== 1) {
                $linksArr[] = ['href' => $prefix.'1', 'rel' => 'first prefetch', 'title' => $nonNumerics['first_text']];
                $linksArr[] = ['href' => $prefix.($current - 1), 'rel' => 'prev prefetch', 'title' => $nonNumerics['prev_text']];
            }
            if ($current !== $total) {
                $linksArr[] = ['href' => $prefix.$total, 'rel' => 'last prefetch', 'title' => $nonNumerics['last_text']];
                $linksArr[] = ['href' => $prefix.($current + 1), 'rel' => 'next prefetch', 'title' => $nonNumerics['next_text']];
            }
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
            return ['pagination' => $output, 'links' => $linksArr];
        } else {
            return $output;
        }
    }
}
?>