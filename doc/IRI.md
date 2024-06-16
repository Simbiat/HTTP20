- [isValidIri](#isvalidiri)
- [iriToUri](#iritouri)
- [restoreUri](#restoreuri)

# IRI

Functions useful for work with International Resource Identifiers (IRIs)

```php
\Simbiat\HTTP20\IRI::nameOfFunction();
```

## isValidIri

```php
isValidIri(string $iri)
```

Checks if string is a valid IRI (by trying to convert it to a valid URL first). Returns `bool` value.

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