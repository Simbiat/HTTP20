- [breadcrumbs](#breadcrumbs)
- [pagination](#pagination)

# HTML
Functions, that generate useful HTML code.

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
pagination(int $current, int $total, int $maxNumerics = 5, array $nonNumerics = ['first' => '<<', 'prev' => '<', 'next' => '>', 'last' => '>>', 'first_text' => 'First page', 'prev_text' => 'Previous page', 'next_text' => 'Next page', 'last_text' => 'Last page', 'page_text' => 'Page '], string $prefix = '', bool $links = false, bool $headers = false)
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
`$prefix` is an optional prefix for the links used in `href` attribute. Generally you will be ok with an empty string (default) and respective relative links, but in some cases, you may want to change that, for example, if your pages are using links like `#1` or `?page=1`. You can use that setting to adjust accordingly.
$links` - if set to `false`, you will get just a string of the requested pagination, but if set to `true`, this will also generate values for `rel="first prefetch"`, `rel="prev prefetch"`, `rel="next prefetch"` and `rel="last prefetch"` required for `Links()` [function](Headers.md#links), and thus function will return an array like this:
```php
[
    'pagination' => 'string_of_pagination',
    'links' => [...],
]
```
You can then manually send the `'links'` array to `Links()` function to generate respective tags or headers.
`$headers` is checked only if `$links` is `true`. If `$headers` is also `true`, then it will directly send the `Link` header(s), and the return array value of `'links'` will have pre-generated set of `<link>` tags. While neither the headers, nor the tags are required, they may assist with navigation or performance improvement for the client (due to `prefetch`).
