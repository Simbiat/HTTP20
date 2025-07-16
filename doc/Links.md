# links

```php
links(array $links = [], string $type = 'header', bool $strict_rel = true);
```

Function to generate a set of `Link` elements either for HTTP header or HTML. If you are serving a web-page, it may be beneficial to send both HTTP header and the HTML tags to support wider variety of clients.

`$links` - is an array of arrays, where each subarray describes the `Link` element. At the least you require a `href` element, but for a fuller description, please, refer to https://developer.mozilla.org/en-US/docs/Web/HTML/Element/link. Note that, in case some attribute is missing a link can be skipped depending on values of other attributes.

`$type` - the type of `Link` elements to generate. Default is `header`, meaning, that a single HTTP header `Link:` will be generated. The other supported values are `head` and `body`, which differ only in one extra check for `body`, which checks if a link is considered `body-ok`, but works only with `$strict_rel` set to `true.

`$strict_rel` - if set to `true`, if `rel` attribute is set it will be checked against a list based on https://html.spec.whatwg.org/multipage/links.html#linkTypes and https://microformats.org/wiki/existing-rel-values#formats, meaning against `rel` values, that have to be supported by clients. If you are using something "special", set this to `false`. Personally, in such cases, I would recommend splitting the set of `Link` elements you have into 2 sets: standard and non-standard.
