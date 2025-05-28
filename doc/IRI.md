- [isValidIri](#isvalidiri)
- [iriToUri](#iritouri)
- [restoreUri](#restoreuri)

# IRI

Functions useful for work with International Resource Identifiers (IRIs)

```php
\Simbiat\http20\IRI::nameOfFunction();
```

## isValidIri

```php
isValidIri(string $iri, array|string $scheme)
```

Checks if string is a valid IRI (by trying to convert it to a valid URL first). Also accepts optional `scheme` (as string or array) to allow rejection of prohibited of anything that does not match provided list. Returns `bool` value.

## iriToUri

```php
iriToUri(string $iri)
```

Converts IRI to URI. Returns `null` in case of failures or a string, if no obvious failures were observed.

## restoreUri

```php
restoreUri(array $parsedUri)
```

Restore array from `parse_url()` function to original URL.

## parseUri

```php
parseUri(astring $uri)
```

Same as `parse_url()` function to original URL, but UTF-8 safe (so supports IRIs), and supports IRIs without a scheme (and without `//`).

## restoreUri

```php
rawBuildQuery(array $params)
```

Alternative to `http_build_query()` but with URL encoding only reserved characters.

## restoreUri

```php
toRFC3986(string $string)
```

Same as `rawurlencode` but only for reserved characters (for IRI support).