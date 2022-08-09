<?php
declare(strict_types=1);
namespace Simbiat\HTTP20;

class HTML
{
    #Static to count breadcrumbs in case multiple ones are created
    public static int $crumbs = 0;
    #Static to count paginations in case multiple ones are created
    public static int $paginations = 0;
    #Static to count timelines in case multiple ones are created
    public static int $timelines = 0;

    #Function to generate timeline
    public static function timeline(array $items, string $format = 'Y-m-d', bool $asc = false, string $lang = 'en', int $brLimit = 0): string {
        if (method_exists('\Simbiat\SandClock','seconds')) {
            $sandClock = true;
        } else {
            $sandClock = false;
        }
        $time = time();
        #Sanitize $items and add them to array, that will be ordered
        $toOrder = [];
        $current = [];
        foreach ($items as $item) {
            #Check that at least startTime or endTime and name or position tags are present
            if ((empty($item['startTime']) && empty($item['endTime'])) || (empty($item['name']) && empty($item['position']))) {
               continue;
            }
            if (!empty($item['endTime'])) {
                #Ensure we have an integer time or something, that can be converted to one
                if (is_string($item['endTime'])) {
                    #Convert string
                    $item['endTime'] = strtotime($item['endTime']);
                    if ($item['endTime'] === false) {
                        #Failed to convert, skipping item
                        continue;
                    }
                } else if (!is_int($item['endTime']) && !is_float($item['endTime'])) {
                    #If not int or float - skip item
                    continue;
                } else if (is_float($item['endTime'])) {
                    #Convert float to integer
                    $item['endTime'] = intval($item['endTime']);
                }
            }
            if (!empty($item['startTime'])) {
                #Ensure we have an integer time or something, that can be converted to one
                if (is_string($item['startTime'])) {
                    #Convert string
                    $item['startTime'] = strtotime($item['startTime']);
                    if ($item['startTime'] === false) {
                        #Failed to convert, skipping item
                        continue;
                    }
                } else if (!is_int($item['endTime']) && !is_float($item['startTime'])) {
                    #If not int or float - skip item
                    continue;
                } else if (is_float($item['startTime'])) {
                    #Convert float to integer
                    $item['startTime'] = intval($item['startTime']);
                }
            }
            #Check if endTime is set
            if (!empty($item['endTime'])) {
                #Add columns for sorting
                $item['time'] = $item['endTime'];
                $item['start'] = 0;
                #Add to order as "end" item
                $toOrder[] = $item;
            }
            #Check if startTime is set
            if (!empty($item['startTime'])) {
                #If endTime is present and its formatted version is same as formatted version of startTime - continue to next element
                if (!empty($item['endTime']) && date($format, $item['endTime']) === date($format, $item['startTime'])) {
                    continue;
                }
                #Add columns for sorting
                $item['time'] = $item['startTime'];
                $item['start'] = 1;
                #Add to array of current items if endTime is empty
                if (empty($item['endTime'])) {
                    $item['ended'] = false;
                    $current[] = $item;
                } else {
                    $item['ended'] = true;
                }
                #Add to order as "start" item
                $toOrder[] = $item;
            }
        }
        #Order timeline
        if ($asc) {
            usort($toOrder, function($a, $b) {
                return [$a['time'], $a['start']] <=> [$b['time'], $b['start']];
            });
        } else {
            usort($toOrder, function($a, $b) {
                return [$b['time'], $b['start']] <=> [$a['time'], $a['start']];
            });
        }
        #Order current events if any
        if (!empty($current)) {
            usort($current, function($a, $b) {
                return [$a['time'], $a['start']] <=> [$b['time'], $b['start']];
            });
        }
        #Increase the count for crumbs
        self::$timelines++;
        #Open timeline
        $output = '<section id="timeline_"'.self::$timelines.' class="timeline" aria-label="timeline '.self::$timelines.'">';
        #Add elements
        foreach ($toOrder as $key=>$item) {
            #Generate id
            $id = PrettyURL::pretty((empty($item['name']) ? '' : $item['name']).(empty($item['position']) ? '' : $item['position']).($item['start'] === 1 ? $item['startTime'] : $item['endTime']));
            $output .= '<div class="timeline_block timeline_'.($item['start'] === 1 ? 'start'.($item['ended'] === false ? ' timeline_current' : '') : 'end').'" id="'.$id.'"><div class="timeline_content"><div class="timeline_time">';
            if (!empty($item['icon']) && $item['start'] === 0) {
                $output .= '<img loading="lazy" class="timeline_icon" src="'.$item['icon'].'" alt="'.$item['name'].'">';
            }
            $output .= '<time datetime="'.($item['start'] === 1 ? date('Y-m-d H:i:s.v', $item['startTime']) : date('Y-m-d H:i:s.v', $item['endTime'])).'">'.($item['start'] === 1 ? date($format, $item['startTime']) : date($format, $item['endTime'])).'</time>';
            if (!empty($item['icon']) && $item['start'] === 1) {
                $output .= '<img loading="lazy" class="timeline_icon" src="'.$item['icon'].'" alt="'.($item['name'] ?? $item['position']).'">';
            }
            $output .= '</div>';
            #Generate content
            $output .= '<h3 class="timeline_header">';
            if (!empty($item['name']) && !empty($item['position'])) {
                $output .= '<i>'.$item['position'].'</i> at '.(empty($item['href']) ? '' : '<a href="'.$item['href'].'" target="_blank">').$item['name'].(empty($item['href']) ? '' : '</a>');
            } elseif (empty($item['name']) && !empty($item['position'])) {
                $output .= (empty($item['href']) ? '' : '<a href="'.$item['href'].'" target="_blank">').'<i>'.$item['position'].'</i>'.(empty($item['href']) ? '' : '</a>');
            } elseif (!empty($item['name']) && empty($item['position'])) {
                $output .= (empty($item['href']) ? '' : '<a href="'.$item['href'].'" target="_blank">').$item['name'].(empty($item['href']) ? '' : '</a>');
            }
            $output .= '</h3>';
            #Add time elapsed
            if ($sandClock) {
                $elapsed = 0;
                if ($item['start'] === 0) {
                    if (!empty($item['startTime'])) {
                        $elapsed = $item['endTime'] - $item['startTime'];
                    }
                } else {
                    if ($item['ended'] === false) {
                        $elapsed = $time - $item['startTime'];
                    }
                }
                if ($elapsed > 0) {
                    /** @noinspection PhpFullyQualifiedNameUsageInspection */
                    $output .= '<div class="timeline_elapsed"><b>Elapsed time: </b><time datetime="' .\Simbiat\SandClock::seconds($elapsed, iso: true). '">' .\Simbiat\SandClock::seconds($elapsed, lang: $lang). '</time></div>';
                }
            }
            if (($asc === false && ($item['start'] === 0 || ($item['start'] === 1 && $item['ended'] === false))) || ($asc === true && $item['start'] === 1)) {
                #Add description
                if (!empty($item['description'])) {
                    $output .= '<div class="timeline_description">' . $item['description'] . '</div>';
                }
                #List responsibilities
                if (!empty($item['responsibilities'])) {
                    if (is_string($item['responsibilities'])) {
                        $output .= '<div class="timeline_responsibilities"><b>Responsibilities: </b>' . $item['responsibilities'] . '</div>';
                    } else if (is_array($item['responsibilities'])) {
                        $output .= '<div class="timeline_responsibilities"><b>Responsibilities:</b></div><ul class="timeline_responsibilitiesList">';
                        foreach ($item['responsibilities'] as $responsibility) {
                            $output .= '<li>' . $responsibility . '</li>';
                        }
                        $output .= '</ul>';
                    }
                }
                #List achievements
                if (!empty($item['achievements'])) {
                    if (is_string($item['achievements'])) {
                        $output .= '<div class="timeline_achievements"><b>Achievements: </b>' . $item['achievements'] . '</div>';
                    } else if (is_array($item['achievements'])) {
                        $output .= '<div class="timeline_achievements"><b>Achievements:</b></div><ul class="timeline_achievementsList">';
                        foreach ($item['achievements'] as $achievement) {
                            $output .= '<li>' . $achievement . '</li>';
                        }
                        $output .= '</ul>';
                    }
                }
            }
            $output .= '</div></div>';
            #Check if there is a following item
            if (!empty($toOrder[$key + 1])) {
                #Calculate time difference
                if ($asc) {
                    $brs = $toOrder[$key + 1]['time'] - $item['time'];
                } else {
                    $brs = $item['time'] - $toOrder[$key + 1]['time'];
                }
                #Convert difference to number of months
                $brs = intval(floor($brs / 60 / 60 / 24 / 30));
                #Limit it to 12
                if ($brs > $brLimit) {
                    $brs = $brLimit;
                }
                $output .= str_repeat('<br>', $brs);
            }
        }
        #Close timeline
        $output .= '</section>';
        #Process current events. Doing this here, because it's less important.
        if (!empty($current)) {
            #Check if there are finished events in timeline. If there are none - do not create links to "current" ones
            if (in_array(0, array_column($toOrder, 'start'))) {
                $currentList = '<div class="timeline_shortcut"><b>Ongoing: </b>';
                foreach ($current as $item) {
                    #Generate id
                    $id = PrettyURL::pretty((empty($item['name']) ? '' : $item['name']).(empty($item['position']) ? '' : $item['position']).$item['startTime']);
                    $currentList .= '<a href="#'.$id.'">'.(empty($item['icon']) ? '' : '<img loading="lazy" class="timeline_icon_current" src="'.$item['icon'].'" alt="'.($item['name'] ?? $item['position']).'">').($item['position'] ?? $item['name']).'</a>';
                }
                $currentList .= '</div>';
                $output = $currentList.$output;
            }
        }
        return $output;
    }

    #Function to generate breadcrumbs for your website in Microdata format as per https://schema.org/BreadcrumbList
    public static function breadcrumbs(array $items, bool $links = false, bool $headers = false): string|array
    {
        #Sanitize $items
        foreach ($items as $key=>$item) {
            if (empty($item['href']) || empty($item['name'])) {
                unset($items[$key]);
            }
        }
        #Register error, by do not stop further processing
        if (empty($items)) {
            trigger_error('No valid items found for breadcrumbs');
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
        #Set initial item number (position)
        $position = 1;
        #Set depth of the item. This is, essentially, position in reverse. Useful in case you want to hide some elements in the list. Adding 1 to avoid last element getting ID of 0.
        $itemDepth = count($items);
        #Open data
        $output = '<nav role="navigation" aria-label="breadcrumb '.self::$crumbs.'"><ol name="breadcrumbs_'.self::$crumbs.'" id="ol_breadcrumbs_'.self::$crumbs.'" itemscope itemtype="https://schema.org/BreadcrumbList" numberOfItems="'.$itemDepth.'" itemListOrder="ItemListUnordered">';
        #Open links
        $linksArr = [];
        foreach ($items as $key=>$item) {
            #Add top page if links were requested and this if the first link in set. Technically, it looks like only "home" should be currently supported, but if not supported by client, the link should be silently ignored as per specification, so no worries
            if ($links && $position === 1) {
                $linksArr[] = ['href' => $item['href'], 'rel' => 'home index top begin prefetch', 'title' => $item['name']];
            }
            #Update item value to string. First element will always have its ID end with 0, because you may want to hide first element (generally home page) with CSS
            $items[$key] = '<li id="li_breadcrumbs_'.self::$crumbs.'_'.($position === 1 ? 0 : $itemDepth).'" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" '.($itemDepth === 1 ? 'aria-current="location"' : '').'><a id="a_breadcrumbs_'.self::$crumbs.'_'.($position === 1 ? 0 : $itemDepth).'" itemscope itemtype="https://schema.org/WebPage" itemprop="item" itemid="'.$item['href'].'" href="'.htmlspecialchars($item['href']).'"><span id="span_breadcrumbs_'.self::$crumbs.'_'.($position === 1 ? 0 : $itemDepth).'" itemprop="name">'.htmlspecialchars($item['name']).'</span></a><meta itemprop="position" content="'.$position.'" /></li>';
            #Update counters
            $itemDepth--;
            $position++;
            #Add page as "parent" to current one, if links were requested and this not the first (and only) link in set. Technically, "up" was dropped from specification, but if not supported by client, the link should be silently ignored as per specification, so no worries
            if ($links && $itemDepth === 1 && $position !== 1) {
                $linksArr[] = ['href' => $item['href'], 'rel' => 'up prefetch', 'title' => $item['name']];
            }
        }
        #Implode items and add them to output
        $output .= implode('', $items);
        #Close data
        $output .= '</ol></nav>';
        if ($links) {
            #Send headers, if this was requested
            if ($headers) {
                #Send headers
                Headers::links($linksArr);
                #Replace array of links with prepared strings for future use, if required
                $linksArr = Headers::links($linksArr, 'head');
            }
            #Return both breadcrumbs and links (so that they can be used later, for example, added to final HTML document)
            return ['breadcrumbs' => $output, 'links' => $linksArr];
        } else {
            return $output;
        }
    }

    #Function to generate pagination navigation
    public static function pagination(int $current, int $total, int $maxNumerics = 5, array $nonNumerics = ['first' => '<<', 'prev' => '<', 'next' => '>', 'last' => '>>', 'first_text' => 'First page', 'prev_text' => 'Previous page', 'next_text' => 'Next page', 'last_text' => 'Last page', 'page_text' => 'Page '], string $prefix = '', bool $links = false, bool $headers = false): string|array
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
            $output .= '<li id="li_pagination_'.self::$paginations.'_first" aria-label="'.$nonNumerics['first_text'].'"'.($current > (1 + $sideNumerics) ? '' : ' aria-disabled="true"').'>';
            if ($current > (1 + $sideNumerics) && $total !== $maxNumerics) {
                $output .= '<a id="a_pagination_'.self::$paginations.'_first" href="'.$prefix.'1">'.$nonNumerics['first'].'</a>';
            } else {
                $output .= '<span id="a_pagination_'.self::$paginations.'_first">'.$nonNumerics['first'].'</span>';
            }
            $output .= '</li>';
        }
        #Add link to previous page
        if (!empty($nonNumerics['prev'])) {
            $output .= '<li id="li_pagination_'.self::$paginations.'_prev" aria-label="'.$nonNumerics['prev_text'].'"'.($current !== 1 ? '' : ' aria-disabled="true"').'>';
            if ($current !== 1 && $total !== $maxNumerics) {
                $output .= '<a id="a_pagination_'.self::$paginations.'_prev" href="'.$prefix.($current -1).'">'.$nonNumerics['prev'].'</a>';
            } else {
                $output .= '<span id="a_pagination_'.self::$paginations.'_prev">'.$nonNumerics['prev'].'</span>';
            }
            $output .= '</li>';
        }
        #Generate numeric links
        for ($i = $startPage; $i <= $endPage; $i++) {
            $output .= '<li id="li_pagination_'.self::$paginations.'_'.$i.'" aria-label="'.$nonNumerics['page_text'].$i.'"'.($i === $current ? ' class="pagination_currentpage" aria-current="page"' : '').'>';
            if ($i === $current) {
                $output .= '<span id="a_pagination_'.self::$paginations.'_'.$i.'">'.$i.'</span>';
            } else {
                $output .= '<a id="a_pagination_'.self::$paginations.'_'.$i.'" href="'.$prefix.$i.'">'.$i.'</a>';
            }
            $output .= '</li>';
        }
        #Add link to next page
        if (!empty($nonNumerics['next'])) {
            $output .= '<li id="li_pagination_'.self::$paginations.'_next" aria-label="'.$nonNumerics['next_text'].'"'.($current !== $total ? '' : ' aria-disabled="true"').'>';
            if ($current !== $total && $total !== $maxNumerics) {
                $output .= '<a id="a_pagination_'.self::$paginations.'_next" href="'.$prefix.($current + 1).'">'.$nonNumerics['next'].'</a>';
            } else {
                $output .= '<span id="a_pagination_'.self::$paginations.'_next">'.$nonNumerics['next'].'</span>';
            }
            $output .= '</li>';
        }
        #Add link to last page
        if (!empty($nonNumerics['last'])) {
            $output .= '<li id="li_pagination_'.self::$paginations.'_last" aria-label="'.$nonNumerics['last_text'].'"'.($current < ($total - $sideNumerics) ? '' : ' aria-disabled="true"').'>';
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
                #Send headers
                Headers::links($linksArr);
                #Replace array of links with prepared strings for future use, if required
                $linksArr = Headers::links($linksArr, 'head');
            }
            #Return both breadcrumbs and links (so that they can be used later, for example, added to final HTML document)
            return ['pagination' => $output, 'links' => $linksArr];
        } else {
            return $output;
        }
    }


}
