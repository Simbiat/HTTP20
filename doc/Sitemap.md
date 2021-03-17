## Sitemap
```php
sitemap(array $links, string $format = 'xml', bool $directOutput = false)
```
Function to generate sitemape in XML, HTML or text formats. For XML specifications refer to https://www.sitemaps.org/protocol.html. Besids some useful checks (see below) it will try to output as much as possible from the list provided, but so that the size will be no more than 50MBs.  
`$links` - array of assotiative arrays. Maximum for each element will look like this:
```php
[
  #Loc is mandatory for all entries. All entries wil also be checked for relation to same schema and domain. Duplicats will be removed
  'loc' => 'https://example.com',
  'name' => 'optional name used only for HTML sitemaps',
  #Lastmod will be used to calculate the freshest entry in the list and it will be sent out to [lastModified](#lastmodified) function. That will allow earlier exit. Using numeric values is recommended.
  'lastmod' => '20210302'
  'changefreq' => 'change frequency as per specification'
  'priority' => 'priority as per specification'
]
```
`$format` - selector for the format. XML, HTML and plain text (`txt` or `text`) are supported. If you need index of sitemaps, use `index`. HTML format will provide output like this:
```html
<p><a class="sitemaplink" id="sitemaplink_%id%" href="%loc%" target="_blank">%name_or_loc%</a></p>
```
`<p>` is used to provide human-readable output. Use of CSS is advisable to properly style it.  
`$directOutput` - if set to `true` will send the generated string directly to client using [zEcho](#zecho) and also send appropriate `Content-Type` header.