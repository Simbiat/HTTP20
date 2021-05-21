- [download](#download)
- [upload](#upload)
- [streamCopy](#streamcopy)
- [speedLimit](#speedlimit)
- [phpMemoryToInt](#phpmemorytoint)
- [rangesValidate](#rangesvalidate)
- [fileEcho](#fileecho)
- [proxyFile](#proxyfile)

# Sharing
Function that can be used in processes related to file sharing.
```php
(new \Simbiat\HTTP20\Sharing)->nameOfFunction();
```

## download
```php
download(string $file, string $filename = '', string $mime = '', bool $inline = false, int $speedlimit = 10485760, bool $exit = true);
```
Function to download files (or more precisely, feed them to client). Unlike other functions, that can be found, this one can:
- Send proper headers both in positive and negative situations
- Determine MIME type of the file based on extension, yet allow overriding both file name and MIME
- Allow feeding file "inline" instead of as attachment
- Limit speed, but in a way, that will limit the chances of exceeding allocated memory on server in a smart way  
`$file` - path to file.  
`$filename` - optional override for file name, if you want to provide a file with a different name.  
`$inline` - if set to `true` will feed the file "inline", as regular images are sent (for example). It is unlikely you will want to use it like that, but it may be useful if you want to stream a video/audio/image/other file like that and then do something once it's shown. Note, though, that some browsers may start downloading such content using `Range`.  
`$speedlimit` - the maximum of bytes you want to send per second. Default is 10MBs. Note, that if it's too large it will be overriden by internal logic (`speedLimit()`).  
`$exit` - if set to `false` will not automatically exit once a file/range or a "bad" header is sent to client. It then will return a `false` or `true` value, that you can utilize somehow.  
While this function can return the number of bytes, that may be useful for some statistics, I'd recommend not using it as confirmation of successful file download, because it is not possible to reliably track client success on server without some scripting on client side.

## upload
```php
upload($destPath, bool $preserveNames = false, bool $overwrite = false, array $allowedMime = [], bool $intollerant = true, bool $exit = true);
```
Function to handle uploads. It's not fancy as https://tus.io/, but if you need hadnling some simple file uploads, this still can provide you useful features:
- Send proper headers both in positive and negative situations
- Support both POST and PUT methods
- Sanitize filenames if you have https://github.com/Simbiat/filename-sanitizer (PUT supports names from `Content-disposition` header)
- Do not actually save the files with those names: hash them instead
- Sanitized names are not discarded: they are returned as one of the values of the array, after the upload
- MIME type of the files is determined after upload is completed
- For POST method you can use multiple forms and they can even be uploading multiple files
- PUT method somewhat supports resumable uploads  
`$destPath` is the only setting, that is mandatory and it can be a `string` or a named `array`, where each element of the array is equal to the name of the field (`<input type="file"></input>`) in your web-form (as element key) and the path to save files from that field to. Both `string` value and each value of the `array` are expected to be existing and writable directories (no auto-creation, since this may be abused). Example of the array is below:
```php
[
   'avatar' => './upload/avatar',
   'background' => './upload/background',
]
```
`$preserveNames` (only for POST) is set to false by default to replace names of the files with their hash + extension, based on actual MIME type (if we were able to grab it). This is done to avoid potential exploits, that may arise depending on how you use the fiels afterwards.  
`$overwrite` (only for POST) allows to overwrite files, if set to `true`. Otherwise - they will be ignored (but still will be returned in the resulting array).  
`$allowedMime` - optional array of MIME types, that you accept for upload.  
`$intollerant` (only for POST) changes behaviour in case of failures during multiple files upload. If set to `true` (by default), if an error is encountered with any of the file - further processing will be aborted. If issues are encountered on checks, this will essentially discard any uploads. If it's encountered during moving of the uploaded files, list of files that were successfully processed will still be returned (or an empty array).  
`$exit` - if set to `false` will not automatically exit once a file/range or a "bad" header is sent to client. It then will return a value, that you can utilize somehow.  
In case a file has been uploaded, you will get an array like below. It is useful, if you are going to store the data in database: add any additional data, generate a query - write it to database. Be sure to set `$exit` to `false` though.
```php
[
  0 => 
  [
    #Name of the file, that it recieved on server
    'server_name' => 'bb345ba5253d677fe6bac0d553040ca62faa02347d316f30bc436009543c5d92.jpg',
    #Name of the file as provided by client. For PUT it will be empty, unless provided in Content-Dsiposition header
    'user_name' => 'WodFws_Awfc.jpg',
    #Size of the file. For POST - as seen on server. For PUT - as provided in Content-Length header
    'size' => 945001,
    #File MIME type
    'type' => 'image/jpeg',
    #sha3-256 hash of the file
    'hash' => 'bb345ba5253d677fe6bac0d553040ca62faa02347d316f30bc436009543c5d92',
    #Name of the field to which file was uploaded. For PUT will always be 'PUT'
    'field' => 'userfile',
  ],
]
```
If you're going to use PUT, there are some peculiarities, that you need to be aware of:
- You need to send `Content-Length` header: it's used to determine proper end of input stream
- Upload will be considered `resumable` if there is a header `Content-Disposition` with `filename` or `filename*` parameter
- `resumable` upload will utilize filename from the header to be saved, but it will not be preserved, when upload it successful
- To avoid potential collisions during multiple PUT uploads it will be up to you to send unique names
- Already saved portion of the `resumable` file will still be uploaded, but to a temp stream, that will be discarded before the stream will be continued into a previously saved file
- MIME type is checked twice: firstly using `Content-Type` header, if it's present, secondly after the upload is finished
- If MIME type check against allowed types fails after the upload file will be removed

## streamCopy
```php
streamCopy(&$input, &$output, int $totalsize = 0, int $offset = 0, int $speed = 10485760);
```
Function to copy data in small chunks (not HTTP1.1 chunks) with speed limitation from one stream to another. In essense, this is `stream_copy_to_stream`, but with said speed limitation. Example of usage is the above mentioned `download` function.  
`&$input` and `&$output` - thease are resources (generally created from `fopen`), from which you will read and to which you will write.  
`$totalsize` - bytes to copy.  
`$offset` - where to start copying from.  
`$speed` - the maximum of bytes you want to copy per second. Default is 10MBs. Note, that if it's too large it will be overriden by internal logic (`speedLimit()`).

## speedLimit
```php
speedLimit(int $speed = 0, float $percentage = 0.9);
```
Function calculates maximum number of bytes that can be allocated for streaming (or similar functions) based on memory limit and currently available memory. In case of streaming, using more bytes can result in memory exceptions, that you would want to avoid, if possible.  
`$speed` - the desired "speed limit". If it's less than calculated value, it will be returned.  
`$percentage` - percent of available memory, that we can use. For example, if we have 256M as memory limit and 200M available, 0.9 will allow us to use 180M. Default was experimentally derived from downloading a 1.5G file with 256M memory limit until there was no "Allowed memory size of X bytes exhausted". Actually it was 0.94, but we would prefer to have at least some headroom.

## phpMemoryToInt
```php
phpMemoryToInt(string $memory);
```
Converts PHP's memory strings (like 256M) used in some settings to integer value (bytes).

## rangesValidate
```php
rangesValidate(int $size);
```
Function to validate `Range` request header (https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Range). It is mandatory to provide it with the `$size` of the file in bytes, because one of the conditions for "bad" range is that start or end of the range is after the last byte in the file. The most important thing here is checking for overlapping ranges, which should not happen as per as per https://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html.  
It will always return an array. If there was a "bad" range, though it will not be empty (which is considered a valid value), but rather `[0 => false]`, that is an array with 1 index valued as `false`. Be careful with this when validating the output.  
I can't think of a case, when this can be used outside of `download` function, except for custom version of it, but keeping this separate if there is one.

## fileEcho
```php
fileEcho(string $filepath, array $allowedMime = [], string $cacheStrat = 'month', bool $exit = true)
```
A function, that will pass a file to client while sending appropriate headers. Note, that, while this can be used for download, I'd recommend against that: for downloads, please, use [download](doc/Sharing.md#download) function, instead. Use `fileEcho` for small files, that you want to display inline.  
`$filepath` - path to the file. If path is not a file, fucntion will return 404.  
`$allowedMime` - array of allowed MIME types, if you want to restrict the use of this function by the type. Note, that it will check the actual file MIME type, but attempt to send the MIME type based on file extension to the client. If MIME type is not allowed, will return 403.  
`$cacheStrat` is an optional caching strategy to apply (as described for [cacheControl](doc/Headers.md#cachecontrol))

## fileEcho
```php
proxyFile(string $url, string $cacheStrat = '')
```
A function, that will proxy a remote file to client while duplicating headers from that URL. It's not something you should use randomly, since it will increase load on your server, but it can be used when you need to keep your CSP rules strict, but still access resources without proper CORS support. This function will try to utilize Last-Modified and ETag headers if available and will also add Cache-Control, if it's missing in order to rely on client caching more.  
`$url` - URL to proxy.  
`$cacheStrat` is an optional caching strategy to apply (as described for [cacheControl](doc/Headers.md#cachecontrol))