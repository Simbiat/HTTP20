## Atom
```php
(new \http20\Atom)->Atom(string $title, array $entries, string $id = '', string $texttype = 'text', array $feed_settings = []);
```
Function to generate Atom feed as per https://validator.w3.org/feed/docs/atom.html specification.

`$title` - string that will be used for `<title>` tag in the feed.

`$id` - string, that will be used as `id`. It needs to be an URI, thus if it will be empty, will use `(isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']`, that is current address.

`$texttype` is the text type, that will be added as attribute to some of the tags as per specification. Supported types are `text`, `html`, `xhtml`.

`$feed_settings` - array with optional settings for the feed. Maximum will look like this:
```php
[
  'subtitle' => 'Latest deleted banks',
  'rights' => 'Copyrighted',
  'authors' => [
    [
      'name' => 'Dmitry Kustov',
      'email' => 'simbiat@outlook.com',
      'uri' => 'https://simbiat.ru/',
    ]
  ],
  'contributors' => [
    [
      'name' => 'Dmitry Kustov',
      'email' => 'simbiat@outlook.com',
      'uri' => 'https://simbiat.ru/',
    ]
  ],
  'icon' => '/frontend/images/favicons/simbiat.png',
  'logo' => '/frontend/images/ogimage.png',
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

`$entries` - array of elements (arrays), that will populate the feed. Mandatory value is `link`, instead of `id` as in specification, because `id` is expected to be an URI regardless (although it will be a modified one). `title` and `updated` are also mandatory. Maximum for each element will look like this:
```php
[
  'id' => '',
  'title' => 'Google it',
  'updated' => '2021-01-08 20:00',
  'link' => 'https://google.com',
  'author_name' => 'Dmitry Kustov',
  'author_email' => 'simbiat@outlook.com',
  'author_uri' => 'https://simbiat.ru/',
  'contributor_name' => 'Dmitry Kustov',
  'contributor_email' => 'simbiat@outlook.com',
  'contributor_uri' => 'https://simbiat.ru/',
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
Since the code was designed with idea of entries taken from a database, only 1 `author`, `contributor` and `source` are supported and their respective values were 'flattened' (`author_name` and the like). If `published` is empty `updated` will be used instead of it. Otherwise, the parameters conform with Atom specifications.