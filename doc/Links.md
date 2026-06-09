# links

```php
links(array $links = [], string $type = 'header', bool $strict_rel = true);
```

Function to generate a set of `Link` elements either for HTTP header or HTML. If you are serving a web-page, it may be beneficial to send both HTTP header and the HTML tags to support a wider variety of clients.

`$links` - is an array of arrays, where each subarray describes the `Link` element. At the least you require a `href` element, but for a fuller description, please, refer to https://developer.mozilla.org/en-US/docs/Web/HTML/Element/link. Note that, in case some attribute is missing, a link can be skipped depending on values of other attributes.

`$type` - the type of `Link` elements to generate. Default is `header`, meaning, that a single HTTP header `Link:` will be generated. The other supported values are `head` and `body`, which differ only in one extra check for `body`, which checks if a link is considered `body-ok`.

`$force_cross_origin` - if set to `true`, if `rel` attribute is set it will be checked against the list of external resources as per spec https://html.spec.whatwg.org/multipage/links.html#linkTypes and if matching, will force `crossorigin="anonymous"`, if attribute is missing. While this may require you to add `crossorigin` attribute to all elements on the webpage (like `img`), it may slightly improve privacy and security.
