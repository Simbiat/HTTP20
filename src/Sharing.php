<?php
declare(strict_types = 1);

namespace Simbiat\http20;

use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\NoReturn;
use Simbiat\SafeFileName;

use function is_array, count, is_string, in_array, extension_loaded, is_resource, strlen, ini_get;

/**
 * Functions related to file sharing
 */
class Sharing
{
    /**
     * Function for smart resumable download with proper headers
     * @param string $file        Path to file to send it to browser.
     * @param string $filename    Optional override for file name, if you want to provide a file with a different name.
     * @param string $mime        Optional MIME type. If empty, will try to determine the type based on extension or use `application/octet-stream`.
     * @param bool   $inline      If set to `true` will feed the file "inline", as regular images are sent (for example).
     * @param int    $speed_limit Download speed limit in bytes per second.
     * @param bool   $exit        If set to `false` will not automatically exit once a file/range or a "bad" header is sent to the client. It then will return a `false` or `true` value, that you can utilize somehow.
     *
     * @return bool|int
     */
    public static function download(string $file, string $filename = '', string $mime = '', bool $inline = false, int $speed_limit = 10485760, bool $exit = true): bool|int
    {
        #Sanitize speed limit
        $speed_limit = self::speedLimit($speed_limit);
        #Some protection
        header('Access-Control-Allow-Headers: range');
        header('Access-Control-Allow-Methods: GET');
        #Download is valid only in case of GET method
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            Headers::clientReturn(405, $exit);
            return false;
        }
        #Check that path exists and is actually a file
        if (!is_file($file)) {
            if (isset($_SERVER['HTTP_RANGE'])) {
                Headers::clientReturn(410, $exit);
                return false;
            }
            Headers::clientReturn(404, $exit);
            return false;
        }
        #Check if file is readable
        if (!is_readable($file)) {
            Headers::clientReturn(409, $exit);
            return false;
        }
        #Get file information (we need extension and basename)
        $fileinfo = pathinfo($file);
        #Get file size
        $filesize = filesize($file);
        #Get MD5 to use as boundary in case we have a multipart download. It may be better to use SHA3 for hash, but it looks like MD5 for boundary works as standard for such cases.
        $boundary = hash_file('md5', $file);
        #Control caching
        header('Cache-Control: must-revalidate, no-transform');
        #If file has been cached by browser since last time it has been changed - exit before everything. Depends on browser whether this will work, though.
        Headers::lastModified(filemtime($file), true);
        Headers::eTag($boundary, true);
        #Check if MIME was provided
        #If yes, validate its format
        if (!empty($mime) && preg_match('/^(('.Common::MIME_REGEX.') ?)+$/i', $mime) !== 1) {
            #Empty invalid MIME
            $mime = '';
        }
        #Check if it's empty again (or was from the start)
        if (empty($mime)) {
            #If not, attempt to check if in the constant list based on extension
            $mime = Common::EXTENSION_TO_MIME[$fileinfo['extension']] ?? 'application/octet-stream';
        }
        #Get file name
        if (empty($filename)) {
            $filename = $fileinfo['basename'];
        }
        #Process ranges
        $ranges = self::rangesValidate($filesize);
        if (isset($ranges[0]) && $ranges[0] === false) {
            Headers::clientReturn(416, $exit);
            return false;
        }
        #Send common headers
        if ($inline) {
            header('Content-Disposition: inline; filename="'.$filename.'"');
        } else {
            header('Content-Disposition: attachment; filename="'.$filename.'"');
        }
        #Notify, that we accept ranges
        header('Accept-Ranges: bytes');
        #Generally not required for web, but in case this somehow gets into a mail - better have it
        header('Content-Transfer-Encoding: binary');
        #Open the file
        $stream = fopen($file, 'rb');
        #Check if file was opened
        if ($stream === false) {
            Headers::clientReturn(500, $exit);
            return false;
        }
        #Open output stream
        $output = fopen('php://output', 'wb');
        #Check if stream was opened
        if ($output === false) {
            Headers::clientReturn(500, $exit);
            return false;
        }
        #Disable buffering. This should help limit the memory usage. At least, in some cases.
        stream_set_read_buffer($stream, 0);
        stream_set_write_buffer($output, 0);
        if (!empty($ranges)) {
            #Send partial content headers
            header($_SERVER['SERVER_PROTOCOL'].' 206 Partial Content');
            #Checking how many ranges we have
            if (count($ranges) === 1) {
                header('Content-Type: '.$mime);
                header('Content-Range: bytes '.$ranges[0]['start'].'-'.$ranges[0]['end'].'/'.$filesize);
                #Update size to block size
                $filesize = $ranges[0]['end'] - $ranges[0]['start'] + 1;
                header('Content-Length: '.$filesize);
                #Limit speed to range length, if it's current speed limit is too large, so that it will be provided fully
                if ($speed_limit > $filesize) {
                    $speed_limit = $filesize;
                }
                $speed_limit = self::speedLimit($speed_limit);
                #Output data
                $result = self::streamCopy($stream, $output, $filesize, $ranges[0]['start'], $speed_limit);
                #Close file
                fclose($stream);
                fclose($output);
                if ($result === false) {
                    Headers::clientReturn(500, $exit);
                    return false;
                }
                if ($exit) {
                    Headers::clientReturn(200);
                    return true;
                }
                return $result;
            }
            header('Content-Type: multipart/byteranges; boundary='.$boundary);
            #Calculate size starting with the mandatory end of the feed (delimiter)
            $parts_size = strlen("\r\n--".$boundary."\r\n");
            foreach ($ranges as $range) {
                #Add content size
                $parts_size += $range['end'] - $range['start'] + 1;
                #Add size of the supportive text
                $parts_size += strlen("\r\n--".$boundary."\r\n".'Content-type: '.$mime."\r\n".'Content-Range: bytes '.$range['start'].'-'.$range['end'].'/'.$filesize."\r\n\r\n");
            }
            #Send expected size to the client
            header('Content-Length: '.$parts_size);
            #Iterrate the parts
            $sent = 0;
            foreach ($ranges as $range) {
                #Echo supportive text
                echo "\r\n--".$boundary."\r\n".'Content-type: '.$mime."\r\n".'Content-Range: bytes '.$range['start'].'-'.$range['end'].'/'.$filesize."\r\n\r\n";
                #Limit speed to range length if the current speed limit is too large, so that it will be provided fully
                if ($speed_limit > $range['end'] - $range['start'] + 1) {
                    $speed_limit_multi = $range['end'] - $range['start'] + 1;
                } else {
                    $speed_limit_multi = $speed_limit;
                }
                $speed_limit_multi = self::speedLimit($speed_limit_multi);
                #Output data
                $result = self::streamCopy($stream, $output, $range['end'] - $range['start'] + 1, $range['start'], $speed_limit_multi);
                if ($result === false) {
                    fclose($stream);
                    fclose($output);
                    Headers::clientReturn(500, $exit);
                    return false;
                }
                $sent += $result;
            }
            #Close the file
            fclose($stream);
            fclose($output);
            echo "\r\n--".$boundary."\r\n";
            if ($exit) {
                Headers::clientReturn(200);
                return true;
            }
            return $sent;
        }
        header('Content-Type: '.$mime);
        header('Content-Length: '.$filesize);
        header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
        #Output data
        $result = self::streamCopy($stream, $output, $filesize, 0, $speed_limit);
        #Close the file
        fclose($stream);
        fclose($output);
        if ($result === false) {
            Headers::clientReturn(500, $exit);
            return false;
        }
        if ($exit) {
            Headers::clientReturn(200);
            return true;
        }
        return $result;
    }
    
    /**
     * Function to handle file uploads
     * @param string|array $dest_path      Path to save a file(s) to.
     * @param bool         $preserve_names Whether to preserve name(s). If `false` will rename files to their hash + extension based on MIME type. Only for POST uploads.
     * @param bool         $overwrite      Whether to overwrite existing file(s). Only for POST uploads.
     * @param array        $allowed_mime   List of allowed MIME types to block files, that do not match it.
     * @param bool         $intolerant     Changes behaviour in case of failures during multiple files upload. If set to `true` (by default), if an error is encountered with any of the file - further processing will be aborted. If issues are encountered on checks, this will essentially discard any uploads. If it's encountered during moving of the uploaded files, list of files that were successfully processed will still be returned (or an empty array). Only for POST uploads.
     * @param bool         $exit           If set to `false` will not automatically exit once a file/range or a "bad" header is sent to a client. It then will return a value, that you can utilize somehow.
     *
     * @return int|array
     */
    public static function upload(string|array $dest_path, bool $preserve_names = false, bool $overwrite = false, array $allowed_mime = [], bool $intolerant = true, bool $exit = true): int|array
    {
        #Set upload directory
        if (is_writable(ini_get('upload_tmp_dir'))) {
            $upload_dir = ini_get('upload_tmp_dir');
        } else {
            $upload_dir = sys_get_temp_dir();
        }
        #Ensure we do not have trailing slash
        $upload_dir = preg_replace('/(.*[^\\\\\/]+)([\\\\\/]+$)/', '$1', $upload_dir);
        #Cache some PHP settings
        $max_upload = self:: phpMemoryToInt(ini_get('upload_max_filesize'));
        $max_post = self:: phpMemoryToInt(ini_get('post_max_size'));
        $max_files = (int)ini_get('max_file_uploads');
        #Check if POST or PUT
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
            return Headers::clientReturn(405, $exit);
        }
        #Check content type if we have POST method
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (empty($_SERVER['CONTENT_TYPE']) || preg_match('/^multipart\/form-data(;)?/i', $_SERVER['CONTENT_TYPE']) !== 1)) {
            return Headers::clientReturn(415, $exit);
        }
        #Sanitize provided MIME types
        if (!empty($allowed_mime)) {
            foreach ($allowed_mime as $key => $mime) {
                if (preg_match('/^'.Common::MIME_REGEX.'$/i', $mime) !== 1) {
                    unset($allowed_mime[$key]);
                }
            }
        }
        #Cache filename sanitizer
        if (method_exists(SafeFileName::class, 'sanitize')) {
            $safe_filename = true;
        } else {
            $safe_filename = false;
        }
        #Check if file upload is enabled on server
        if (!ini_get('file_uploads')) {
            return Headers::clientReturn(501, $exit);
        }
        #Check that we do have some space allocated for file uploads
        if ($max_upload === 0 || $max_post === 0 || $max_files === 0) {
            return Headers::clientReturn(507, $exit);
        }
        #Validate destination directory
        if (is_string($dest_path)) {
            $dest_path = realpath($dest_path);
            if (!is_dir($dest_path) || !is_writable($dest_path)) {
                return Headers::clientReturn(500, $exit);
            }
        } elseif (is_array($dest_path)) {
            foreach ($dest_path as $key => $path) {
                $dest_path[$key] = realpath($path);
                if (!is_dir($dest_path[$key]) || !is_writable($dest_path[$key])) {
                    return Headers::clientReturn(500, $exit);
                }
            }
        } else {
            return Headers::clientReturn(500, $exit);
        }
        #Process files based on method used
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            #Check that something was sent to us at all
            if ((isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] === 0) || empty($_FILES)) {
                return Headers::clientReturn(400, $exit);
            }
            #Standardize $_FILES and also count them
            $total_files = 0;
            foreach ($_FILES as $field => $files) {
                #Check if multiple files were uploaded to a field and process the values accordingly
                if (is_array($files['name'])) {
                    $total_files += count($files['name']);
                    foreach ($files['name'] as $key => $file) {
                        $_FILES[$field][$key]['name'] = $file;
                        $_FILES[$field][$key]['full_path'] = $files['full_path'][$key];
                        $_FILES[$field][$key]['type'] = $files['type'][$key];
                        $_FILES[$field][$key]['size'] = $files['size'][$key];
                        $_FILES[$field][$key]['tmp_name'] = $files['tmp_name'][$key];
                        $_FILES[$field][$key]['error'] = $files['error'][$key];
                    }
                } else {
                    ++$total_files;
                    $_FILES[$field][0]['name'] = $files['name'];
                    $_FILES[$field][0]['full_path'] = $files['full_path'];
                    $_FILES[$field][0]['type'] = $files['type'];
                    $_FILES[$field][0]['size'] = $files['size'];
                    $_FILES[$field][0]['tmp_name'] = $files['tmp_name'];
                    $_FILES[$field][0]['error'] = $files['error'];
                    
                }
                unset($_FILES[$field]['name'], $_FILES[$field]['type'], $_FILES[$field]['size'], $_FILES[$field]['tmp_name'], $_FILES[$field]['error'], $_FILES[$field]['full_path']);
            }
            #Check number of files
            if ($total_files > $max_files) {
                return Headers::clientReturn(413, $exit);
            }
            #Prepare array for uploaded files
            $uploaded_files = [];
            #Check for any errors in files, so that we can exit before actually processing the rest
            foreach ($_FILES as $field => $files) {
                #Check that field has a folder to copy the file to
                if (is_array($dest_path) && !isset($dest_path[$field])) {
                    if ($intolerant) {
                        return Headers::clientReturn(501, $exit);
                    }
                    #Remove the file from list
                    unset($_FILES[$field]);
                    continue;
                }
                #Set destination path
                if (is_array($dest_path)) {
                    $final_path = $dest_path[$field];
                } else {
                    $final_path = $dest_path;
                }
                foreach ($files as $key => $file) {
                    switch ($file['error']) {
                        case UPLOAD_ERR_OK:
                            #Do nothing at this time
                            break;
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            if ($intolerant) {
                                return Headers::clientReturn(413, $exit);
                            }
                            #Remove the file from list
                            unset($_FILES[$field][$key]);
                            continue 2;
                        case UPLOAD_ERR_PARTIAL:
                        case UPLOAD_ERR_NO_FILE:
                        case UPLOAD_ERR_CANT_WRITE:
                            if ($intolerant) {
                                return Headers::clientReturn(409, $exit);
                            }
                            #Remove the file from list
                            unset($_FILES[$field][$key]);
                            continue 2;
                        case UPLOAD_ERR_NO_TMP_DIR:
                        case UPLOAD_ERR_EXTENSION:
                            if ($intolerant) {
                                return Headers::clientReturn(500, $exit);
                            }
                            #Remove the file from list
                            unset($_FILES[$field][$key]);
                            continue 2;
                        default:
                            if ($intolerant) {
                                return Headers::clientReturn(418, $exit);
                            }
                            #Remove the file from the list
                            unset($_FILES[$field][$key]);
                            continue 2;
                    }
                    #Check if the file being referenced was, indeed, sent to us via POST
                    if (!is_uploaded_file($file['tmp_name'])) {
                        #Deny further processing. This is the only case, where we ignore $intolerant setting for security reasons
                        return Headers::clientReturn(403, $exit);
                    }
                    #Check file size
                    if ($file['size'] > $max_upload) {
                        if ($intolerant) {
                            return Headers::clientReturn(413, $exit);
                        }
                        #Remove the file from the list
                        unset($_FILES[$field][$key]);
                        continue;
                    }
                    if ($file['size'] === 0 || empty($file['tmp_name'])) {
                        #Check if tmp_name is set or $file size is empty
                        if ($intolerant) {
                            return Headers::clientReturn(400, $exit);
                        }
                        #Remove the file from the list
                        unset($_FILES[$field][$key]);
                        continue;
                    }
                    #Get actual MIME type
                    if (isset($_FILES[$field][$key])) {
                        if (extension_loaded('fileinfo')) {
                            $_FILES[$field][$key]['type'] = mime_content_type($file['tmp_name']);
                        }
                        #Check against allowed MIME types if any was set and fileinfo is loaded
                        #Get MIME from the file (not relying on what was sent by client)
                        if (!empty($allowed_mime) && !in_array($_FILES[$field][$key]['type'], $allowed_mime, true)) {
                            if ($intolerant) {
                                return Headers::clientReturn(415, $exit);
                            }
                            #Remove the file from the list
                            unset($_FILES[$field][$key]);
                            continue;
                        }
                    }
                    #Sanitize name
                    if (isset($_FILES[$field][$key]) && $safe_filename !== false) {
                        $_FILES[$field][$key]['name'] = basename(SafeFileName::sanitize($file['name']));
                        #If name is empty or name is too long, do not process it
                        if (empty($_FILES[$field][$key]['name']) || mb_strlen($_FILES[$field][$key]['name'], 'UTF-8') > 225) {
                            if ($intolerant) {
                                return Headers::clientReturn(400, $exit);
                            }
                            #Remove the file from list
                            unset($_FILES[$field][$key]);
                        } else {
                            #Set new name for the file. By default, we will be using hash of the file. Using sha3-512 since it has lower probability of collisions than md5, although we do lose some speed
                            #Hash is saved regardless, though, since it may be very useful
                            $_FILES[$field][$key]['hash'] = hash_file('sha3-512', $file['tmp_name']);
                            if ($preserve_names) {
                                $_FILES[$field][$key]['new_name'] = $_FILES[$field][$key]['name'];
                            } else {
                                #Get extension (if any)
                                $ext = array_search($_FILES[$field][$key]['type'], Common::EXTENSION_TO_MIME, true);
                                if ($ext) {
                                    $ext = '.'.$ext;
                                } else {
                                    $ext = pathinfo($_FILES[$field][$key]['name'], PATHINFO_EXTENSION);
                                    if (!empty($ext) && is_string($ext)) {
                                        $ext = '.'.$ext;
                                    } else {
                                        $ext = '';
                                    }
                                }
                                #Generate name from hash and extension from the original file
                                $_FILES[$field][$key]['new_name'] = $_FILES[$field][$key]['hash'].$ext;
                            }
                            #Check if the destination file already exists
                            if (is_file($final_path.'/'.$_FILES[$field][$key]['new_name'])) {
                                if ($overwrite) {
                                    #Check that it is writable
                                    if (!is_writable($final_path.'/'.$_FILES[$field][$key]['new_name'])) {
                                        if ($intolerant) {
                                            return Headers::clientReturn(409, $exit);
                                        }
                                        #Remove the file from the list
                                        unset($_FILES[$field][$key]);
                                    }
                                } else {
                                    #Add it to the list of successfully uploaded files if we are not preserving names, since that implies relative uniqueness of them, thus we are most likely seeing the same file
                                    if (!$preserve_names) {
                                        $uploaded_files[] = ['server_name' => $_FILES[$field][$key]['new_name'], 'server_path' => $final_path, 'user_name' => $_FILES[$field][$key]['name'], 'size' => $file['size'], 'type' => $_FILES[$field][$key]['type'], 'hash' => $_FILES[$field][$key]['hash'], 'field' => $field];
                                    }
                                    #Remove the file from global list
                                    unset($_FILES[$field][$key]);
                                }
                            }
                        }
                    }
                }
                #Clean up
                if (empty($_FILES[$field])) {
                    unset($_FILES[$field]);
                }
            }
            #Check if any files were left
            if (empty($_FILES)) {
                if (empty($uploaded_files)) {
                    return Headers::clientReturn(400, $exit);
                }
            } else {
                #Process files and put them into an array
                foreach ($_FILES as $field => $files) {
                    #Set destination path
                    if (is_array($dest_path)) {
                        $final_path = $dest_path[$field];
                    } else {
                        $final_path = $dest_path;
                    }
                    foreach ($files as $file) {
                        #Move file, but only if it's not already present in destination
                        if (!is_file($final_path.'/'.$file['new_name']) && move_uploaded_file($file['tmp_name'], $final_path.'/'.$file['new_name'])) {
                            $uploaded_files[] = ['server_name' => $file['new_name'], 'server_path' => $final_path, 'user_name' => $file['name'], 'size' => $file['size'], 'type' => $file['type'], 'hash' => $file['hash'], 'field' => $field];
                        } elseif ($intolerant) {
                            return $uploaded_files;
                        }
                    }
                }
            }
            #Process PUT requests
        } else {
            if (!isset($_SERVER['CONTENT_LENGTH']) || (int)$_SERVER['CONTENT_LENGTH'] === 0) {
                return Headers::clientReturn(411, $exit);
            }
            $client_size = (int)$_SERVER['CONTENT_LENGTH'];
            #Set time limit equal to the size. If load speed is <=10 kilobytes per second - that's definitely low speed session, that we do not want to keep forever
            set_time_limit((int)floor($client_size / 10240));
            if ($_SERVER['CONTENT_LENGTH'] > $max_upload) {
                return Headers::clientReturn(413, $exit);
            }
            #Check that destination is a string
            if (!is_string($dest_path)) {
                return Headers::clientReturn(500, $exit);
            }
            #Get MIME from the file (not relying on what was sent by client)
            if (!empty($allowed_mime) && isset($_SERVER['CONTENT_TYPE']) && !in_array($_SERVER['CONTENT_TYPE'], $allowed_mime, true)) {
                return Headers::clientReturn(415, $exit);
            }
            #Attempt to get name from header
            $name = '';
            if (isset($_SERVER['HTTP_CONTENT_DISPOSITION'])) {
                #filename* is preferred over filename as per https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Disposition
                #Note that this format MAY include charset
                $name = preg_replace('/^(.*filename\*='.Common::LANGUAGE_ENC_REGEX.'"?)(?<filename>[^";=*]*)((([";]).*)|($))$/i', '$12', $_SERVER['HTTP_CONTENT_DISPOSITION']);
                if (empty($name) || $name === $_SERVER['HTTP_CONTENT_DISPOSITION']) {
                    #If we are here, it means that there is no filename*
                    $name = preg_replace('/^(.*filename="?)(?<filename>[^";=*]*)((([";]).*)|($))$/i', '$2', $_SERVER['HTTP_CONTENT_DISPOSITION']);
                    if (empty($name) || $name === $_SERVER['HTTP_CONTENT_DISPOSITION']) {
                        #If we are here, it means no filename was shared
                        $name = '';
                    }
                }
            }
            #Sanitize the name
            if (!empty($name) && $safe_filename) {
                $name = basename(SafeFileName::sanitize($name));
            }
            if (empty($name)) {
                #Generate random name. Using 64 to be consistent with sha3-512 hash
                try {
                    $name = hash('sha3-512', random_bytes(64)).'.put';
                } catch (\Throwable) {
                    #Use microseconds, if we somehow failed to get random value, since it''s unlikely we get more than 1 file upload at the same microsecond
                    $name = microtime().'.put';
                }
                $resumable = false;
            } else {
                $resumable = true;
            }
            #Check if file already exists
            if ($resumable && is_file($upload_dir.'/'.$name)) {
                $offset = filesize($upload_dir.'/'.$name);
            } else {
                $offset = 0;
            }
            if ($offset !== $client_size) {
                #Open input stream
                $stream = fopen('php://input', 'rb');
                #Check if file was opened
                if ($stream === false) {
                    return Headers::clientReturn(409, $exit);
                }
                #Read input stream
                if ($offset > 0 && $offset < $client_size) {
                    #We can't fseek php://input, thus we need to read it. To improve performance we will use php://temp with a memory limit
                    $garbage = fopen('php://temp', 'wb');
                    #Check if stream was opened
                    if ($garbage === false) {
                        fclose($stream);
                        return Headers::clientReturn(500, $exit);
                    }
                    $collected = stream_copy_to_stream($stream, $garbage, $offset);
                    #Close stream
                    fclose($garbage);
                    if ($collected !== $offset) {
                        #Means we failed to read appropriate amount of bytes
                        fclose($stream);
                        return Headers::clientReturn(500, $exit);
                    }
                }
                if (feof($stream)) {
                    #Most likely our stream got interrupted during initial read
                    fclose($stream);
                    $result = false;
                } else {
                    #Open output stream
                    if ($offset < $client_size) {
                        $output = fopen($upload_dir.'/'.$name, 'ab');
                    } else {
                        #Means the file is different and we better rewrite it
                        $output = fopen($upload_dir.'/'.$name, 'wb');
                    }
                    #Check if stream was opened
                    if ($output === false) {
                        fclose($stream);
                        return Headers::clientReturn(500, $exit);
                    }
                    #Disable buffering. This should help limit the memory usage. At least, in some cases.
                    stream_set_read_buffer($stream, 0);
                    stream_set_write_buffer($output, 0);
                    #Ignore user abort to attempt to identify when client has aborted
                    #ignore_user_abort(true);
                    #Save file
                    $result = stream_copy_to_stream($stream, $output, $client_size - $offset);
                    #Close streams
                    fclose($stream);
                    fclose($output);
                    #Check that the size is the one we expect
                    if (($result + $offset) < $client_size) {
                        $result = false;
                    } else {
                        $result = true;
                    }
                }
            } else {
                #Means the file we have is complete
                $result = true;
            }
            if (!$result) {
                if (!$resumable && is_file($upload_dir.'/'.$name)) {
                    unlink($upload_dir.'/'.$name);
                }
                return Headers::clientReturn(500, $exit);
            }
            #Get file MIME type
            $filetype = $_SERVER['CONTENT_TYPE'] ?? 'application/octet-stream';
            if (extension_loaded('fileinfo')) {
                $filetype = mime_content_type($upload_dir.'/'.$name);
            }
            #Check against allowed MIME types if any was set and fileinfo is loaded
            #Get MIME from file (not relying on what was sent by client)
            if (!empty($allowed_mime) && !in_array($filetype, $allowed_mime, true)) {
                if (is_file($upload_dir.'/'.$name)) {
                    unlink($upload_dir.'/'.$name);
                }
                return Headers::clientReturn(415, $exit);
            }
            #Get extension of the file
            $ext = array_search($filetype, Common::EXTENSION_TO_MIME, true);
            if ($ext === false) {
                $ext = 'PUT';
            }
            #Get hash
            $hash = hash_file('sha3-512', $upload_dir.'/'.$name);
            #Set new name
            $new_name = $hash.'.'.$ext;
            #Attempt to move the file
            if (!rename($upload_dir.'/'.$name, $dest_path.'/'.$new_name)) {
                return Headers::clientReturn(500, $exit);
            }
            #Add to array. Using an array here for consistency with POST method. The field is reported as PUT to indicate the method. It's advisable not to use it for fields if you use POST method as well
            $uploaded_files[] = ['server_name' => $new_name, 'server_path' => $dest_path, 'user_name' => $name, 'size' => $client_size, 'type' => $filetype, 'hash' => $hash, 'field' => 'PUT'];
        }
        if (empty($uploaded_files)) {
            return Headers::clientReturn(500, $exit);
        }
        if ($exit) {
            #Inform client, that files were uploaded
            return Headers::clientReturn(200);
        }
        return $uploaded_files;
    }
    
    /**
     * Function to copy data in small chunks (not HTTP1.1 chunks) based on speed limitation
     * @param resource $input      Input stream
     * @param resource $output     Output stream
     * @param int      $total_size Total size of bytes to copy
     * @param int      $offset     Offset of bytes to copy from
     * @param int      $speed      Maximum speed of copying in bytes per second
     *
     * @return bool|int
     */
    public static function streamCopy($input, $output, int $total_size = 0, int $offset = 0, int $speed = 10485760): bool|int
    {
        #Ignore user abort to attempt to identify when the client has aborted
        ignore_user_abort(true);
        #Check that we have resources, since PHP does not have type hinting for resources
        if (!is_resource($input) || !is_resource($output)) {
            #Close session
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            return false;
        }
        #Get size if not provided
        if ($total_size <= 0) {
            $fstat = \fstat($input);
            if (is_array($fstat)) {
                /** @noinspection OffsetOperationsInspection https://github.com/kalessil/phpinspectionsea/issues/1941 */
                $total_size = $fstat['size'];
            } else {
                $total_size = 0;
            }
        }
        #Sanitize speed
        $speed = self::speedLimit($speed);
        #Set time limit equal to the size. If load speed is <=10 kilobytes per second - that's definitely low speed session, that we do not want to keep forever
        set_time_limit((int)floor($total_size / 10240));
        #Set counter for the amount of data sent
        $sent = 0;
        while ($sent < $total_size && connection_status() === CONNECTION_NORMAL) {
            #Using stream_copy_to_stream because it is able to handle much larger files even with relatively large speed limits, close to how readfile() can.
            $sent_stat = stream_copy_to_stream($input, $output, $speed, $offset);
            if ($sent_stat !== false) {
                $sent += $sent_stat;
            } else {
                #Close session
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_write_close();
                }
                return false;
            }
            $offset += $speed;
            ob_flush();
            flush();
            #Sleep to limit data rate
            sleep(1);
        }
        #Close session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        if ($sent >= $total_size && connection_status() === CONNECTION_NORMAL) {
            return $sent;
        }
        return false;
    }
    
    /**
     * Function to determine speed limit based on maximum allowed memory usage
     * @param int   $speed      Desired maximum speed
     * @param float $percentage Maximum percentage of memory allowed to use
     *
     * @return int
     */
    public static function speedLimit(int $speed = 0, float $percentage = 0.9): int
    {
        #Sanitize percentage
        if ($percentage <= 0 || $percentage > 1.0) {
            $percentage = 0.9;
        }
        #Get memory limit
        $memory = ini_get('memory_limit');
        $memory = self::phpMemoryToInt($memory);
        #Exclude memory peak usage (assume, that it's either still being used or can be used in near future)
        $memory -= memory_get_peak_usage(true);
        #When using stream there is still a certain memory overhead, so we take only percentage of the memory
        #Percentage was experimentally derived from downloading a 1.5G file with 256M memory limit until there was no "Allowed memory size of X bytes exhausted". Actually it was 0.94, but we would prefer to have at least some headroom.
        $memory = (int)floor($memory * $percentage);
        if ($speed <= 0 || $speed > $memory) {
            $speed = $memory;
        }
        return $speed;
    }
    
    /**
     * Function to convert PHP's memory strings (like 256M) used in some settings to integer value (bytes)
     * @param string $memory
     *
     * @return int
     */
    public static function phpMemoryToInt(string $memory): int
    {
        #Get suffix. Suppressing inspection, since false-positive, `mb_strlen` returns `int`
        /** @noinspection OffsetOperationsInspection */
        $suffix = mb_strtolower($memory[mb_strlen($memory, 'UTF-8') - 1], 'UTF-8');
        #Get int value
        $memory_int = (int)mb_substr($memory, 0, -1, 'UTF-8');
        $memory_int *= match ($suffix) {
            'g' => 1073741824,
            'm' => 1048576,
            'k' => 1024,
            default => 1,
        };
        return $memory_int;
    }
    
    /**
     * Function to validate HTTP header `Range` and return it as an array. If case of errors it will return array with one element (index 0) equalling false. https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Range
     * @param int $size
     *
     * @return array
     */
    public static function rangesValidate(int $size): array
    {
        if (isset($_SERVER['HTTP_RANGE'])) {
            #Validate the value
            if (preg_match('/^bytes=\d*-\d*(\s*,\s*\d*-\d*)*$/i', $_SERVER['HTTP_RANGE']) !== 1) {
                header($_SERVER['SERVER_PROTOCOL'].' 416 Range Not Satisfiable');
                return [0 => false];
            }
            #Remove bytes
            $ranges = preg_replace('/bytes=/i', '', $_SERVER['HTTP_RANGE']);
            #Split ranges
            $ranges = explode(',', $ranges);
            #Sanitize
            foreach ($ranges as $key => $range) {
                if (preg_match('/^-\d+$/', $range) === 1) {
                    $ranges[$key] = ['start' => 0, 'end' => (int)mb_ltrim($range, '-', 'UTF-8')];
                } elseif (preg_match('/^\d+-$/', $range) === 1) {
                    $ranges[$key] = ['start' => (int)mb_rtrim($range, '-', 'UTF-8'), 'end' => ($size - 1)];
                } elseif (preg_match('/^\d+-\d+$/', $range) === 1) {
                    $temp_range = explode('-', $range);
                    $ranges[$key] = ['start' => (int)$temp_range[0], 'end' => (int)$temp_range[1]];
                } else {
                    #If we get here, something went incredibly wrong, so better exit
                    return [0 => false];
                }
                #Check range is of proper value
                if ($ranges[$key]['start'] >= $ranges[$key]['end'] || $ranges[$key]['start'] >= $size || $ranges[$key]['end'] > $size || ($ranges[0]['end'] - $ranges[0]['start'] + 1) > $size) {
                    return [0 => false];
                }
            }
            #Checking for overlaps, since as per https://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html we expect non-overlapping ranges
            if (count($ranges) > 1) {
                foreach ($ranges as $key_prime => $range_prime) {
                    foreach ($ranges as $key_sec => $range_sec) {
                        #Only compare pairs after current one
                        if ($key_sec > $key_prime) {
                            #If overlap in any way - exit
                            if (
                                ($range_prime['start'] === $range_sec['start'] && $range_prime['end'] === $range_sec['end']) ||
                                ($range_sec['end'] >= $range_prime['start'] && $range_sec['end'] < $range_prime['end']) ||
                                ($range_sec['start'] > $range_prime['start'] && $range_sec['start'] <= $range_prime['end'])
                            ) {
                                return [0 => false];
                            }
                        }
                    }
                }
            }
            #If something went wrong, and we got an empty range here - return as false
            if (empty($ranges)) {
                return [0 => false];
            }
            return $ranges;
        }
        return [];
    }
    
    /**
     * Function to send a file directly to browser
     * @param string $filepath       Path to file
     * @param array  $allowed_mime   List of allowed MIME types
     * @param string $cache_strategy Cashing strategy (same as for `Headers::cacheControl`)
     * @param bool   $exit           Whether to stop execution in case of errors
     *
     * @return int
     */
    public static function fileEcho(string $filepath, array $allowed_mime = [], #[ExpectedValues(['', 'aggressive', 'private', 'none', 'live', 'month', 'week', 'day', 'hour'])] string $cache_strategy = 'month', bool $exit = true): int
    {
        #Check if file exists
        if (is_file($filepath)) {
            #Process MIME
            if (extension_loaded('fileinfo')) {
                #Get MIME from the file
                $mime_type = mime_content_type($filepath);
                if (!empty($allowed_mime)) {
                    #Sanitize provided MIME types
                    foreach ($allowed_mime as $key => $mime) {
                        if (preg_match('/^'.Common::MIME_REGEX.'$/i', $mime) !== 1) {
                            unset($allowed_mime[$key]);
                        }
                    }
                    #Check if MIME is allowed
                    if (!empty($allowed_mime) && !in_array($mime_type, $allowed_mime, true)) {
                        return Headers::clientReturn(403, $exit);
                    }
                }
            }
            #While the above checks the actual MIME type, it may be different from the one client may be expecting based on extension. For example RSS file will be recognized as application/xml (or text/xml), instead of application/rss+xml. This may be minor, but depending on client can cause unexpected behaviour. Thus, we rely on extension here, since it can provide a more appropriate MIME type
            $extension = pathinfo($filepath, PATHINFO_EXTENSION);
            #Set MIME from extension, of available
            if (!empty($extension) && is_string($extension) && !empty(Common::EXTENSION_TO_MIME[$extension])) {
                $mime_type_alt = Common::EXTENSION_TO_MIME[$extension];
            }
            #Set MIME type to stream if it's empty
            if (empty($mime_type_alt)) {
                if (empty($mime_type)) {
                    $mime_type = 'application/octet-stream';
                }
            } else {
                $mime_type = $mime_type_alt;
            }
            #Send Last Modified, eTag and Cache-Control headers
            Headers::lastModified(filemtime($filepath), true);
            Headers::eTag(hash_file('sha3-512', $filepath), true);
            Headers::cacheControl('', $cache_strategy, true);
            #Send MIME types. Add Charset to those, that are recommended to have it
            if (preg_match('/^(text\/.*)|(image\/svg\+xml)|(application\/(.*javascript|.*json|.*xml))$/i', $mime_type) === 1) {
                header('Content-Type: '.$mime_type.'; charset=utf-8');
            } else {
                header('Content-Type: '.$mime_type);
            }
            #Send content disposition
            header('Content-Disposition: inline; filename="'.basename($filepath).'"');
            #Open stream
            $stream = fopen($filepath, 'rb');
            if ($stream === false) {
                return Headers::clientReturn(500, $exit);
            }
            #Some MIME types can be zipped nicely
            if (preg_match('/^((font|text)\/.*)|(application\/(.*javascript|.*json|.*xml|vnd\.ms-fontobject|wasm|x-font-ttf))|(image\/(bmp|svg\+xml|vnd.microsoft.icon))$/i', $mime_type) === 1) {
                #Read the file
                $output = fread($stream, filesize($filepath));
                #Close stream
                fclose($stream);
                if ($output === false) {
                    return Headers::clientReturn(500, $exit);
                }
                Common::zEcho($output, $cache_strategy);
            } else {
                #Send size information
                header('Content-Length: '.filesize($filepath));
                #Exit if HEAD method was used (by this time all headers should have been sent
                if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'HEAD') {
                    #Close session
                    if (session_status() === PHP_SESSION_ACTIVE) {
                        session_write_close();
                    }
                    exit(0);
                }
                #Send data
                if (fpassthru($stream) === 0) {
                    return Headers::clientReturn(500, $exit);
                }
                #Close stream
                fclose($stream);
            }
            #Close session
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            #Either exit or return
            if ($exit) {
                exit(0);
            }
            return 200;
        }
        return Headers::clientReturn(404, $exit);
    }
    
    /**
     * Function to proxy a file from another server as a stream
     * @param string $url            URL to proxy
     * @param string $cache_strategy Cashing strategy (same as for `Headers::cacheControl`)
     *
     * @return void
     */
    #[NoReturn] public static function proxyFile(string $url, #[ExpectedValues(['', 'aggressive', 'private', 'none', 'live', 'month', 'week', 'day', 'hour'])] string $cache_strategy = ''): void
    {
        #Get headers
        $headers_data = get_headers($url, context: stream_context_create(['http' => [
            'method' => 'HEAD',
            'follow_location' => 1,
            'protocol_version' => 2.0
        ]]));
        #Check that we did get headers
        if (!is_array($headers_data)) {
            #Failed to get headers, meaning we most likely will not be able to get the content as well
            Headers::clientReturn();
        }
        #Cache-Control flag
        $cache = false;
        #Send the headers from remote server
        foreach ($headers_data as $header_value) {
            if (preg_match('/^Cache-Control:.*$/', $header_value) === 1) {
                $cache = true;
            }
            header($header_value);
        }
        #Add Cache-Control
        if (!$cache) {
            Headers::cacheControl('', $cache_strategy, true);
        }
        #Process lastModified and eTag to attempt to rely on client cache and not waste server resources
        foreach ($headers_data as $header_value) {
            if (preg_match('/^Last-Modified:.*$/', $header_value) === 1) {
                Headers::lastModified(strtotime(preg_replace('/^(Last-Modified:\s*"?)([^"]*)("?)$/', '$2', $header_value)), true);
            } elseif (preg_match('/^ETag:.*$/', $header_value) === 1) {
                Headers::eTag(preg_replace('/^(ETag:\s*"?)([^"]*)("?)$/', '$2', $header_value), true);
            }
        }
        #Open streams
        #Supress warning for $url, since connection can be refused for some reason, and it still may be "normal"
        $url_open = fopen($url, 'rb', context: stream_context_create(['http' => [
            'method' => 'GET',
            'follow_location' => 1,
            'protocol_version' => 2.0
        ]]));
        $output = fopen('php://output', 'wb');
        #Send contents
        if ($url_open !== false && $output !== false) {
            stream_copy_to_stream($url_open, $output);
            fclose($output);
            fclose($url_open);
        } else {
            Headers::clientReturn();
        }
        #Close session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        #Ensure we exit
        exit(0);
    }
}
