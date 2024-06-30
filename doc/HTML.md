- [timeline](#timeline)
- [breadcrumbs](#breadcrumbs)
- [pagination](#pagination)

# HTML

Functions, that generate useful HTML code.

## timeline

```php
timeline(array $items, string $format = 'Y-m-d', bool $asc = false, int $brLimit = 0);
```

Generates a timeline, sample of which (using [sample CSS](/src/timeline_sample.css)) can be seen on [video](https://youtu.be/_cSezN3JxUs).
`items` is an array of items you want to show on timeline. Here is sample of what it can look like:

```php
[
    #Start and end time of the event. Either or both need to be present and be convertable to date (float, int, valid datetime string)
    'startTime' => '2009-06-04',
    'endTime' => '2011-05-20',
    #Name of the event. In this example timeline is used for resume to show job experience, thus it's a name of the company.
    'name' => 'IBS Datafort',
    #Position if even is expected to be a job. Either or both name and position need to be present. If event is not a job, I recommend using 'name'.
    'position' => 'Engineer',
    #Path to optional icon that will be shown for the event near the timeline center. Needs to be a valid path from perspective of the web page.
    'icon' => '/img/icons/IBS.svg',
    #Optional link to wrap the 'name' in.
    'href' => 'https://www.datafort.ru/',
    #Optional free format description of the event
    'description' => 'Outsourced job for Citi Russia as evening operator.',
    #Optional list of responsibilities, that can be used for job events. Can be either a string or an array of strings.
    'responsibilities' => [
        'Initiate operations related to End of Day processing',
        'Monitor continuous night processes',
        'Level 1 support of subset of regional applications',
        'Level 1 or level 2 support of local applications'
    ],
    #Optional list of achievements, that can be used for job or education or similar events. Can be either a string or an array of strings.
    'achievements' => [
        'Promoted to day-time operator after approximately 1 year',
        'Transferred a paper-based checklist used by operators to Excel featuring several automated functions to improve traceability of work',
    ],
],
```

`format` - how to format time of each event, which is shown near the center delimiter. Needs to be a valid string, that can be parsed by `date()`.

`asc` - whether to sort the elements in ascending (`true`) or descending (`false`, default) order.

`brLimit` - option to allow up to the number of `<br>` elements between events. 1 `<br>` equals 1 month. Can be used to spread out the elements along the line to provide visual representation of time between events. `0` disables the feature.

Some clarifications about logic:

1. Elements with just `startTime` are considered "ongoing". Such elements will have extra class `timeline_current`, so that you can give them some extra style. If there are any "finished" events in the timeline, the "ongoing" ones will have shortcuts in beginning of the timeline.
2. Elements with just `endTime` or with identical `startTime` and `endTime` can be considered as "onetime" and will appear only on the right side with `timeline_right` class.
3. Elements with both `startTime` and `endTime` will be split into starting event shown on left of the timeline with `timeline_left` class and on ending event on the right.
4. Description, responsibilities, achievements and elapsed time elements will be shown either on ending events or on starting events, if an event is "ongoing".
5. Elapsed time will be calculated only if SandClock library is available.

## breadcrumbs

```php
breadcrumbs(array $items, bool $links = false, bool $headers = false);
```

Generates breadcrumbs for your website in Microdata format (as per https://schema.org/BreadcrumbList) wrapping it in proper `<nav>` tag with `id` attributes for `<li>`, `<a>`, `<span>` tags, as well as useful `aria` attributes, where applicable. `id` values are structures in a way, that allows you to style items depending on their "level" (for example always hide first element, since it's supposed to be your home page): first item will always have an `id` ending with `0` and the last one - with `1`.

`$items` is an array of the items (arrays) you plan to present as breadcrumbs. Each array needs to have `href` and `name` elements, otherwise it will be skipped.

`$links` - if set to `false`, you will get just a string of the requested breadcrumbs, but if set to `true`, this will also generate values for `rel="home index top begin prefetch"` and `rel="up prefetch"` required for `Links()` [function](Headers.md#links), and thus function will return an array like this:

```php
[
    'breadcrumbs' => 'string_of_breadcrumbs',
    'links' => [...],
]
```

You can then manually send the `'links'` array to `Links()` function to generate respective tags or headers.

`$headers` is checked only if `$links` is `true`. If `$headers` is also `true`, then it will directly send the `Link` header(s), and the return array value of `'links'` will have pre-generated set of `<link>` tags. While neither the headers, nor the tags are required, they may assist with navigation or performance improvement for the client (due to `prefetch`).

## pagination

```php
pagination(int $current, int $total, int $maxNumerics = 5, array $nonNumerics = ['first' => '<<', 'prev' => '<', 'next' => '>', 'last' => '>>', 'first_text' => 'First page', 'prev_text' => 'Previous page ($number)', 'next_text' => 'Next page ($number)', 'last_text' => 'Last page ($number)', 'page_text' => 'Page '], string $prefix = '', bool $links = false, bool $headers = false, string $tooltip = 'title')
```

Generates pagination as `<ol>` list wrapped in `<nav>` with proper `id` and `aria` attributes.

`$current` - current page number.

`$total` - total number of pages.

`$maxNumerics` - maximum number of numeric links, that is those pages, that show actual numbers, and not 'First'/'Previous'/'Next'/'Last'. This number includes the current page.

`$nonNumerics` is an array of default text values to style 'First', 'Previous', 'Next' and 'Last' pages (with some default values):

```php
[
    #Visible text for First page. If empty, element will be skipped in HTML (will still be present in Links).
    'first' => '<<',
    #Visible text for Previous page. If empty, element will be skipped in HTML (will still be present in Links).
    'prev' => '<',
    #Visible text for Next page. If empty, element will be skipped in HTML (will still be present in Links).
    'next' => '>',
    #Visible text for Las page. If empty, element will be skipped in HTML (will still be present in Links).
    'last' => '>>',
    #Text for aria-label and title attributes for First page. Cannot be empty.
    'first_text' => 'First page',
    #Text for aria-label and title attributes for Previous page. Cannot be empty.
    'prev_text' => 'Previous page',
    #Text for aria-label and title attributes for Next page. Cannot be empty.
    'next_text' => 'Next page',
    #Text for aria-label and title attributes for Last page. Cannot be empty.
    'last_text' => 'Last page',
    #Prefix text for aria-label and title attributes for numeric pages. Cannot be empty.
    'page_text' => 'Page ',
]
```

Text for previous, next and last pages support `$number` value, that will be replaced by respective page number.

`$prefix` is an optional prefix for the links used in `href` attribute. Generally you will be ok with an empty string (default) and respective relative links, but in some cases, you may want to change that, for example, if your pages are using links like `#1` or `?page=1`. You can use that setting to adjust accordingly.

`$links` - if set to `false`, you will get just a string of the requested pagination, but if set to `true`, this will also generate values for `rel="first prefetch"`, `rel="prev prefetch"`, `rel="next prefetch"` and `rel="last prefetch"` required for `Links()` [function](Headers.md#links), and thus function will return an array like this:

```php
[
    'pagination' => 'string_of_pagination',
    'links' => [...],
]
```

You can then manually send the `'links'` array to `Links()` function to generate respective tags or headers.

`$headers` is checked only if `$links` is `true`. If `$headers` is also `true`, then it will directly send the `Link` header(s), and the return array value of `'links'` will have pre-generated set of `<link>` tags. While neither the headers, nor the tags are required, they may assist with navigation or performance improvement for the client (due to `prefetch`).

`$tooltip` - attribute to use for tooltip. `title` by default.