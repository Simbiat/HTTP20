<?php
declare(strict_types = 1);

namespace Simbiat\http20;

use Simbiat\SandClock;

use function is_int, is_float, is_string, is_array;

/**
 * Functions, that generate useful HTML code.
 */
class HTML
{
    #Static to count breadcrumbs in case multiple ones are created
    public static int $crumbs = 0;
    #Static to count pagination elements in case multiple ones are created
    public static int $paginations = 0;
    #Static to count timelines in case multiple ones are created
    public static int $timelines = 0;
    
    /**
     * Function to generate timeline
     * @param array  $items    List of timeline items
     * @param string $format   Datetime format
     * @param bool   $asc      Whether to use ascending or descending order
     * @param int    $br_limit Maximum number of `<br>` elements between items
     *
     * @return string
     */
    public static function timeline(array $items, string $format = 'Y-m-d', bool $asc = false, int $br_limit = 0): string
    {
        if (method_exists(SandClock::class, 'seconds')) {
            $sand_clock = true;
        } else {
            $sand_clock = false;
        }
        $time = time();
        #Sanitize $items and add them to array, that will be ordered
        $to_order = [];
        $current = [];
        foreach ($items as $item) {
            #Check that at least start_time or end_time and name or position tags are present
            if ((empty($item['start_time']) && empty($item['end_time'])) || (empty($item['name']) && empty($item['position']))) {
                continue;
            }
            if (!empty($item['end_time'])) {
                #Ensure we have an integer time or something that can be converted to one
                if (is_string($item['end_time'])) {
                    #Convert string
                    $item['end_time'] = strtotime($item['end_time']);
                    if ($item['end_time'] === false) {
                        #Failed to convert, skipping item
                        continue;
                    }
                } elseif (!is_int($item['end_time']) && !is_float($item['end_time'])) {
                    #If not int or float - skip item
                    continue;
                } elseif (is_float($item['end_time'])) {
                    #Convert float to integer
                    $item['end_time'] = (int)$item['end_time'];
                }
            }
            if (!empty($item['start_time'])) {
                #Ensure we have an integer time or something that can be converted to one
                if (is_string($item['start_time'])) {
                    #Convert string
                    $item['start_time'] = strtotime($item['start_time']);
                    if ($item['start_time'] === false) {
                        #Failed to convert, skipping item
                        continue;
                    }
                } elseif (!is_int($item['end_time']) && !is_float($item['start_time'])) {
                    #If not int or float - skip item
                    continue;
                } elseif (is_float($item['start_time'])) {
                    #Convert float to integer
                    $item['start_time'] = (int)$item['start_time'];
                }
            }
            #Check if end_time is set
            if (!empty($item['end_time'])) {
                #Add columns for sorting
                $item['time'] = $item['end_time'];
                $item['start'] = 0;
                #Add to order as "end" item
                $to_order[] = $item;
            }
            #Check if start_time is set
            if (!empty($item['start_time'])) {
                #If end_time is present and its formatted version is the same as a formatted version of start_time - continue to next element
                if (!empty($item['end_time']) && date($format, $item['end_time']) === date($format, $item['start_time'])) {
                    continue;
                }
                #Add columns for sorting
                $item['time'] = $item['start_time'];
                $item['start'] = 1;
                #Add to the array of current items if end_time is empty
                if (empty($item['end_time'])) {
                    $item['ended'] = false;
                    $current[] = $item;
                } else {
                    $item['ended'] = true;
                }
                #Add to order as "start" item
                $to_order[] = $item;
            }
        }
        #Order timeline
        if ($asc) {
            usort($to_order, static function ($a, $b) {
                return [$a['time'], $a['start']] <=> [$b['time'], $b['start']];
            });
        } else {
            usort($to_order, static function ($a, $b) {
                return [$b['time'], $b['start']] <=> [$a['time'], $a['start']];
            });
        }
        #Order current events if any
        if (!empty($current)) {
            usort($current, static function ($a, $b) {
                return [$a['time'], $a['start']] <=> [$b['time'], $b['start']];
            });
        }
        #Increase the count for crumbs
        self::$timelines++;
        #Open timeline
        $output = '<time-line id="timeline_"'.self::$timelines.' role="complementary" aria-label="timeline '.self::$timelines.'">';
        #Add elements
        foreach ($to_order as $key => $item) {
            #Generate id
            $id = PrettyURL::pretty((empty($item['name']) ? '' : $item['name']).(empty($item['position']) ? '' : $item['position']).($item['start'] === 1 ? $item['start_time'] : $item['end_time']));
            $output .= '<div class="timeline_block timeline_'.($item['start'] === 1 ? 'start'.($item['ended'] === false ? ' timeline_current' : '') : 'end').'" id="'.$id.'"><div class="timeline_content"><div class="timeline_time">';
            if (!empty($item['icon']) && $item['start'] === 0) {
                $output .= '<img loading="lazy" class="timeline_icon" src="'.$item['icon'].'" alt="'.$item['name'].'">';
            }
            $output .= '<time datetime="'.($item['start'] === 1 ? date('Y-m-d H:i:s.v', $item['start_time']) : date('Y-m-d H:i:s.v', $item['end_time'])).'">'.($item['start'] === 1 ? date($format, $item['start_time']) : date($format, $item['end_time'])).'</time>';
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
            if ($sand_clock) {
                $elapsed = 0;
                if ($item['start'] === 0) {
                    if (!empty($item['start_time'])) {
                        $elapsed = $item['end_time'] - $item['start_time'];
                    }
                } elseif ($item['ended'] === false) {
                    $elapsed = $time - $item['start_time'];
                }
                if ($elapsed > 0) {
                    $output .= '<div class="timeline_elapsed"><b>Elapsed time: </b><time datetime="'.SandClock::seconds($elapsed, iso: true).'">'.SandClock::seconds($elapsed).'</time></div>';
                }
            }
            if ((!$asc && ($item['start'] === 0 || ($item['start'] === 1 && $item['ended'] === false))) || ($asc && $item['start'] === 1)) {
                #Add description
                if (!empty($item['description'])) {
                    $output .= '<div class="timeline_description">'.$item['description'].'</div>';
                }
                #List responsibilities
                if (!empty($item['responsibilities'])) {
                    if (is_string($item['responsibilities'])) {
                        $output .= '<div class="timeline_responsibilities"><b>Responsibilities: </b>'.$item['responsibilities'].'</div>';
                    } elseif (is_array($item['responsibilities'])) {
                        $output .= '<div class="timeline_responsibilities"><b>Responsibilities:</b></div><ul class="timeline_responsibilities_list">';
                        foreach ($item['responsibilities'] as $responsibility) {
                            $output .= '<li>'.$responsibility.'</li>';
                        }
                        $output .= '</ul>';
                    }
                }
                #List achievements
                if (!empty($item['achievements'])) {
                    if (is_string($item['achievements'])) {
                        $output .= '<div class="timeline_achievements"><b>Achievements: </b>'.$item['achievements'].'</div>';
                    } elseif (is_array($item['achievements'])) {
                        $output .= '<div class="timeline_achievements"><b>Achievements:</b></div><ul class="timeline_achievements_list">';
                        foreach ($item['achievements'] as $achievement) {
                            $output .= '<li>'.$achievement.'</li>';
                        }
                        $output .= '</ul>';
                    }
                }
            }
            $output .= '</div></div>';
            #Check if there is a following item
            if (!empty($to_order[$key + 1])) {
                #Calculate time difference
                if ($asc) {
                    $brs = $to_order[$key + 1]['time'] - $item['time'];
                } else {
                    $brs = $item['time'] - $to_order[$key + 1]['time'];
                }
                #Convert difference to number of months
                $brs = (int)floor($brs / 60 / 60 / 24 / 30);
                #Limit it to 12
                if ($brs > $br_limit) {
                    $brs = $br_limit;
                }
                $output .= str_repeat('<br>', $brs);
            }
        }
        #Close timeline
        $output .= '</time-line>';
        #Process current events. Doing this here, because it's less important.
        #Check if there are finished events in timeline. If there are none - do not create links to "current" ones
        if (!empty($current) && in_array(0, array_column($to_order, 'start'), true)) {
            $current_list = '<time-line-shortcut class="timeline_shortcut" role="directory" aria-label="Shortcuts for timeline '.self::$timelines.'"><b>Ongoing: </b>';
            foreach ($current as $item) {
                #Generate id
                $id = PrettyURL::pretty((empty($item['name']) ? '' : $item['name']).(empty($item['position']) ? '' : $item['position']).$item['start_time']);
                $current_list .= '<a href="#'.$id.'">'.(empty($item['icon']) ? '' : '<img loading="lazy" class="timeline_icon_current" src="'.$item['icon'].'" alt="'.($item['name'] ?? $item['position']).'">').($item['position'] ?? $item['name']).'</a>';
            }
            $current_list .= '</time-line-shortcut>';
            $output = $current_list.$output;
        }
        return $output;
    }
    
    /**
     * Function to generate breadcrumbs for your website in Microdata format as per https://schema.org/BreadcrumbList
     * @param array $items   List of items
     * @param bool  $links   If set to `false`, you will get just a string of the requested breadcrumbs, but if set to `true`, this will also generate values for `rel="home index top begin prefetch"` and `rel="up prefetch"` required for `Links()`.
     * @param bool  $headers If `$headers` is `true` along with `$links`, then it will directly send the `Link` header(s), and the return array value of `'links'` will have pre-generated set of `<link>` tags. While neither the headers, nor the tags are required, they may assist with navigation or performance improvement for the client (due to `prefetch`).
     *
     * @return string|array
     */
    public static function breadcrumbs(array $items, bool $links = false, bool $headers = false): string|array
    {
        #Sanitize $items
        foreach ($items as $key => $item) {
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
                }
                return ['breadcrumbs' => '', 'links' => []];
            }
            return '';
        }
        #Increase the count for crumbs
        self::$crumbs++;
        #Set initial item number (position)
        $position = 1;
        #Set depth of the item. This is, essentially, position in reverse. Useful in case you want to hide some elements in the list. Adding 1 to avoid last element getting ID of 0.
        $item_depth = count($items);
        #Open data
        $output = '<nav role="navigation" aria-label="breadcrumb '.self::$crumbs.'"><ol name="breadcrumbs_'.self::$crumbs.'" id="ol_breadcrumbs_'.self::$crumbs.'" itemscope itemtype="https://schema.org/BreadcrumbList" numberOfItems="'.$item_depth.'" itemListOrder="ItemListUnordered">';
        #Open links
        $links_arr = [];
        foreach ($items as $key => $item) {
            #Add top page if links were requested and this if the first link in set. Technically, it looks like only "home" should be currently supported, but if not supported by client, the link should be silently ignored as per specification, so no worries
            if ($links && $position === 1) {
                $links_arr[] = ['href' => $item['href'], 'rel' => 'home index top begin prefetch', 'title' => $item['name']];
            }
            #Update item value to string. First element will always have its ID end with 0, because you may want to hide first element (generally home page) with CSS
            $items[$key] = '<li id="li_breadcrumbs_'.self::$crumbs.'_'.($position === 1 ? 0 : $item_depth).'" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" '.($item_depth === 1 ? 'aria-current="location"' : '').'><a id="a_breadcrumbs_'.self::$crumbs.'_'.($position === 1 ? 0 : $item_depth).'" itemscope itemtype="https://schema.org/WebPage" itemprop="item" itemid="'.$item['href'].'" href="'.htmlspecialchars($item['href']).'"><span id="span_breadcrumbs_'.self::$crumbs.'_'.($position === 1 ? 0 : $item_depth).'" itemprop="name">'.htmlspecialchars($item['name']).'</span></a><meta itemprop="position" content="'.$position.'" /></li>';
            #Update counters
            $item_depth--;
            $position++;
            #Add page as "parent" to current one, if links were requested and this not the first (and only) link in set. Technically, "up" was dropped from specification, but if not supported by client, the link should be silently ignored as per specification, so no worries
            if ($links && $item_depth === 1 && $position !== 1) {
                $links_arr[] = ['href' => $item['href'], 'rel' => 'up prefetch', 'title' => $item['name']];
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
                Links::links($links_arr);
                #Replace array of links with prepared strings for future use, if required
                $links_arr = Links::links($links_arr, 'head');
            }
            #Return both breadcrumbs and links (so that they can be used later, for example, added to final HTML document)
            return ['breadcrumbs' => $output, 'links' => $links_arr];
        }
        return $output;
    }
    
    /**
     * Function to generate pagination navigation
     *
     * @param int    $current      Current page number
     * @param int    $total        Total page numbers
     * @param int    $max_numerics Maximum number of numeric links, that is those pages, that show actual numbers, and not 'First'/'Previous'/'Next'/'Last'. This number includes the current page.
     * @param array  $non_numerics Array of default text values to style 'First', 'Previous', 'Next' and 'Last' pages
     * @param string $prefix       Optional prefix for the links used in `href` attribute.
     * @param bool   $links        If set to `false`, you will get just a string of the requested pagination. If set to `true`, this will also generate values for `rel="first prefetch"`, `rel="prev prefetch"`, `rel="next prefetch"` and `rel="last prefetch"` required for `Links()`.
     * @param bool   $headers      If `$headers` is `true` along with `$links`, then it will directly send the `Link` header(s), and the return array value of `'links'` will have a pre-generated set of `<link>` tags. While neither the headers, nor the tags are required, they may assist with navigation or performance improvement for the client (due to `prefetch`).
     * @param string $tooltip      Attribute to use for tooltip. `title` by default.
     *
     * @return string|array
     */
    public static function pagination(int $current, int $total, int $max_numerics = 5, array $non_numerics = ['first' => '<<', 'prev' => '<', 'next' => '>', 'last' => '>>', 'first_text' => 'First page', 'prev_text' => 'Previous page ($number)', 'next_text' => 'Next page ($number)', 'last_text' => 'Last page ($number)', 'page_text' => 'Page '], string $prefix = '', bool $links = false, bool $headers = false, string $tooltip = 'title'): string|array
    {
        #Sanitize numbers
        if ($current < 1) {
            $current = 1;
        }
        if ($total < 1) {
            $total = 1;
        }
        if ($max_numerics < 1) {
            $max_numerics = 1;
        }
        #Adjust maxNumerics if it's larger than total
        if ($max_numerics > $total) {
            $max_numerics = $total;
        }
        #If we have just one page - no reason to do the whole pagination for that. Same if our current page is more than total
        if (($current === $total && $current <= 1) || $current > $total) {
            if ($links) {
                if ($headers) {
                    return ['pagination' => '', 'links' => ''];
                }
                return ['pagination' => '', 'links' => []];
            }
            return '';
        }
        #Sanitize settings for non-numeric settings
        foreach ($non_numerics as $key => $value) {
            #If not a string - revert text values to defaults
            if (!is_string($value)) {
                $value = match ($key) {
                    'first_text' => 'First page',
                    'prev_text' => 'Previous page ($number)',
                    'next_text' => 'Next page ($number)',
                    'last_text' => 'Last page ($number)',
                    'page_text' => 'Page ',
                    default => '',
                };
            }
            #If text values are empty - revert them to defaults, since aria-label and title need them
            if (empty($value)) {
                $value = match ($key) {
                    'first_text' => 'First page',
                    'prev_text' => 'Previous page ($number)',
                    'next_text' => 'Next page ($number)',
                    'last_text' => 'Last page ($number)',
                    'page_text' => 'Page ',
                    default => '',
                };
            }
            #Update value in
            $non_numerics[$key] = $value;
        }
        #Increase the count for pagination
        self::$paginations++;
        #Calculate maximum number of numeric links to left/right of the current one
        $side_numerics = (int)floor(($max_numerics - 1) / 2);
        #Calculate starting page
        $start_page = $current - $side_numerics;
        if ($start_page < 1) {
            $start_page = 1;
        }
        #Calculate ending page
        $end_page = $current + $side_numerics;
        if ($end_page > $total) {
            $end_page = $total;
        }
        #Adjust values so that we always have the same number of numeric pages
        if (($end_page - $start_page) < $max_numerics) {
            #Calculate "free" slots for links
            $correction = $max_numerics - (($end_page - $start_page) + 1);
            #If we have space on the right - add to right
            if ($end_page !== $total) {
                $end_page += $correction;
                #If not, but we have space on the left - add to left
            } elseif ($start_page !== 1) {
                $start_page -= $correction;
            }
            #Adjust edge cases
            if ($start_page < 1) {
                $start_page = 1;
            }
            if ($end_page > $total) {
                $end_page = $total;
            }
        }
        $prev_page = $current - 1;
        if ($prev_page < 1) {
            $prev_page = 1;
        }
        $next_page = $current + 1;
        if ($next_page > $total) {
            $next_page = $total;
        }
        #Open data
        $output = '<pagi-nation role="navigation" aria-label="pagination '.self::$paginations.'">';
        #Open list
        $output .= '<ol name="pagination_'.self::$paginations.'" id="pagination_ol_'.self::$paginations.'">';
        #Add a link to the first page
        if (!empty($non_numerics['first'])) {
            $output .= '<li class="pagination_li pagination_first" aria-label="'.$non_numerics['first_text'].'"'.($current > (1 + $side_numerics) ? ' '.$tooltip.'="'.$non_numerics['first_text'].'"' : ' aria-disabled="true"').'>';
            if ($current > (1 + $side_numerics) && $total !== $max_numerics) {
                $output .= '<a class="pagination_link" href="'.$prefix.'1">'.$non_numerics['first'].'</a>';
            } else {
                $output .= '<span class="pagination_span">'.$non_numerics['first'].'</span>';
            }
            $output .= '</li>';
        }
        #Add a link to the previous page
        if (!empty($non_numerics['prev'])) {
            $output .= '<li class="pagination_li pagination_prev" aria-label="'.str_replace('$number', (string)($prev_page), $non_numerics['prev_text']).'"'.($current !== 1 ? ' '.$tooltip.'="'.str_replace('$number', (string)($prev_page), $non_numerics['prev_text']).'"' : ' aria-disabled="true"').'>';
            if ($current !== 1 && $total !== $max_numerics) {
                $output .= '<a class="pagination_link" href="'.$prefix.($prev_page).'">'.$non_numerics['prev'].'</a>';
            } else {
                $output .= '<span class="pagination_span">'.$non_numerics['prev'].'</span>';
            }
            $output .= '</li>';
        }
        #Generate numeric links
        for ($i = $start_page; $i <= $end_page; $i++) {
            $output .= '<li class="pagination_li'.($i === $current ? ' pagination_current' : '').'" aria-label="'.$non_numerics['page_text'].$i.'"'.($i === $current ? ' aria-current="page"' : ' '.$tooltip.'="'.$non_numerics['page_text'].$i.'"').'>';
            if ($i === $current) {
                $output .= '<span class="pagination_span">'.$i.'</span>';
            } else {
                $output .= '<a class="pagination_link" href="'.$prefix.$i.'">'.$i.'</a>';
            }
            $output .= '</li>';
        }
        #Add a link to the next page
        if (!empty($non_numerics['next'])) {
            $output .= '<li class="pagination_li pagination_next" aria-label="'.str_replace('$number', (string)($next_page), $non_numerics['next_text']).'"'.($current !== $total ? ' '.$tooltip.'="'.str_replace('$number', (string)($next_page), $non_numerics['next_text']).'"' : ' aria-disabled="true"').'>';
            if ($current !== $total && $total !== $max_numerics) {
                $output .= '<a class="pagination_link" href="'.$prefix.($next_page).'">'.$non_numerics['next'].'</a>';
            } else {
                $output .= '<span class="pagination_span">'.$non_numerics['next'].'</span>';
            }
            $output .= '</li>';
        }
        #Add a link to the last page
        if (!empty($non_numerics['last'])) {
            $output .= '<li class="pagination_li pagination_last" aria-label="'.str_replace('$number', (string)$total, $non_numerics['last_text']).'"'.($current < ($total - $side_numerics) ? ' '.$tooltip.'="'.str_replace('$number', (string)$total, $non_numerics['last_text']).'"' : ' aria-disabled="true"').'>';
            if ($current < ($total - $side_numerics) && $total !== $max_numerics) {
                $output .= '<a class="pagination_link" href="'.$prefix.$total.'">'.$non_numerics['last'].'</a>';
            } else {
                $output .= '<span class="pagination_span">'.$non_numerics['last'].'</span>';
            }
            $output .= '</li>';
        }
        #Close list
        $output .= '</ol>';
        #Close data
        $output .= '</pagi-nation>';
        if ($links) {
            #Populate the array of links
            $links_array = [];
            if ($current !== 1) {
                $links_array[] = ['href' => $prefix.'1', 'rel' => 'first prefetch', 'title' => $non_numerics['first_text']];
                $links_array[] = ['href' => $prefix.($prev_page), 'rel' => 'prev prefetch', 'title' => str_replace('$number', (string)($prev_page), $non_numerics['prev_text'])];
            }
            if ($current !== $total) {
                $links_array[] = ['href' => $prefix.$total, 'rel' => 'last prefetch', 'title' => str_replace('$number', (string)$total, $non_numerics['last_text'])];
                $links_array[] = ['href' => $prefix.($next_page), 'rel' => 'next prefetch', 'title' => str_replace('$number', (string)($next_page), $non_numerics['next_text'])];
            }
            #Send the headers if this was requested
            if ($headers) {
                #Send headers
                Links::links($links_array);
                #Replace the array of links with prepared strings for future use, if required
                $links_array = Links::links($links_array, 'head');
            }
            #Return both breadcrumbs and links (so that they can be used later, for example, added to the final HTML document)
            return ['pagination' => $output, 'links' => $links_array];
        }
        return $output;
    }
}