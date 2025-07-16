## Atom

```php
\Simbiat\http20\Atom::atom(string $title, array $entries, string $id = '', string $text_type = 'text', array $feed_settings = []);
```

Function to generate Atom feed as per https://validator.w3.org/feed/docs/atom.html specification.

`$title` - string that will be used for `<title>` tag in the feed.

`$id` - string, that will be used as `id`. It needs to be a URI, thus if it will be empty, will use `(isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']`, that is current address.

`$text_type` is the text type, that will be added as attribute to some tags as per specification. Supported types are `text`, `html`, `xhtml`.

`$feed_settings` - array with optional settings for the feed. Maximum will look like this:

```php
[
  'subtitle' => 'Latest deleted banks',
  'rights' => 'Copyrighted',
  'authors' => [
    [
      'name' => 'Dmitrii Kustov',
      'email' => 'support@simbiat.dev',
      'uri' => 'https://www.simbiat.dev/',
    ]
  ],
  'contributors' => [
    [
      'name' => 'Dmitrii Kustov',
      'email' => 'support@simbiat.dev',
      'uri' => 'https://www.simbiat.dev/',
    ]
  ],
  'icon' => '/frontend/images/favicons/simbiat.png',
  'logo' => '/frontend/images/favicons/ogimage.png',
  'categories' => [
    [
      'term' => 'BICs',
      'scheme' => '',
      'label' => '',
    ]
  ],
  'updated' => '2021-01-08 20:00',
  'links' => [
    [
      'href' => 'google.com',
      'rel' => 'alternate',
      'type' => 'text/html',
      'hreflang' => 'ru',
      'title' => 'google it',
    ]
   ],
]
```

For details on elements above (what they mean, recommendations, etc.), please, refer to Atom specification: names in `array` correspond to respective tags in feed.

`$entries` - array of elements (arrays), that will populate the feed. Mandatory value is `link`, instead of `id` as in specification, because `id` is expected to be a URI regardless (although it will be a modified one). `title` and `updated` are also mandatory. Maximum for each element will look like this:

```php
[
  'id' => '',
  'title' => 'Google it',
  'updated' => '2021-01-08 20:00',
  'link' => 'https://google.com',
  'author_name' => 'Dmitrii Kustov',
  'author_email' => 'support@simbiat.dev',
  'author_uri' => 'https://www.simbiat.dev/',
  'contributor_name' => 'Dmitrii Kustov',
  'contributor_email' => 'support@simbiat.dev',
  'contributor_uri' => 'https://www.simbiat.dev/',
  'content' => 'Just google it',
  'summary' => 'Search',
  'category' => 'Example',
  'published' => '2021-01-08 20:00',
  'rights' => 'Copyrighted',
  'source_id' => '',
  'source_title' => '',
  'source_updated' => '',
]
```

Since the code was designed with idea of entries taken from a database, only 1 `author`, `contributor` and `source` are supported, and their respective values were 'flattened' (`author_name` and the like). If `published` is empty `updated` will be used instead of it. Otherwise, the parameters conform with Atom specifications.
