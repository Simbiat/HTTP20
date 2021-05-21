## PrettyURL
```php
(new \Simbiat\HTTP20\PrettyURL)->pretty(string $string, string $whitespace = '-', bool $urlsafe = true);
```
Function transliterates lots of characters and makes a safe and pretty URL. This is intended more as a SEO thing, rather than sanitization.  
Includes majority (if not all) of diactrics (accented characters), cyrillic, hiragana, katakana, arabic, chinese characters. They are replaced by Latin characters or combinations that are representing how they are pronounced, officially romanized or the closest neighbours in terms of how a chartacer is drawn.
It also allows to replace whitespace characters with a chacarter of your choosing (hyphen as default).  
If `$urlsafe` is set to `true`, some characters will be removed as well, because they can "break" the URL. Some of them are valid for an URI, but they are not good for SEO links.