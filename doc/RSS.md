## RSS

```php
\Simbiat\http20\RSS::rss(string $title, array $entries, string $feed_link = '', array $feed_settings = []);
```

Function to generate RSS feed as per https://www.rssboard.org/rss-specification specification. Function is designed similarly to Atom described above, with minor changes listed below. Otherwise - refer to feed specifications.

`id` in function call is renamed as `feed_link`, since as per specification feed does not have `id` but has `link` for similar purpose. At the same time elements also can have links, so `feed_link` name is used for clarity.

`feed_settings` at its maximum will look like this:

```php
[
  'description' => 'why are we even doing this?',
  'pubDate' => '2021-01-29',
  'lastBuildDate' => '2021-01-29',
  'language' => 'en-us',
  'copyright' => 'Copyrighted',
  'managingEditor' => 'support@simbiat.eu',
  'webMaster' => 'support@simbiat.eu',
  'cloud' =>
  [
    'domain' => 'test.com',
    'port' => '69',
    'path' => '/Life/Is/A/Street/',
    'registerProcedure' => 'register.php',
    'protocol' => 'SOAP',
  ],
  'ttl' => 60,
  'categories' =>
  [
    'php',
    'rss',
  ],
  'image' =>
  [
    'url' => '/image.png',
    'width' => 32,
    'height' => 32,
  ],
  'skipDays' =>
  [
    'Monday',
    'Sunday',
  ],
  'skipHours' =>
  [
    0,
    7,
    13,
    19,
  ],
]
```

Maximum for each element in `$entries` will look like this (either `title` or `description` should be present):

```php
[
  'title' => 'rss',
  'link' => 'gppgle.com',
  'description' => 'just some text',
  'author' => 'support@simbiat.eu',
  'category' => 'php',
  'comments' => 'gppgle.com/comments.html',
  'pubDate' => '20201-01-29',
  'enclosure_url' => 'gppgle.com/test.mp3',
  'enclosure_length' => 1000,
  'enclosure_type' => 'audio/mp3',
]
```
