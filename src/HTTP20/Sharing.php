<?php
declare(strict_types=1);
namespace Simbiat\HTTP20;

class Sharing
{
    private array $extToMime = [];
    private string $mimeRegex = '';
    private ?string $uploaddir = NULL;
    
    public function __construct()
    {
        #Cache (new \Simbiat\HTTP20\Common)
        $common = (new \Simbiat\HTTP20\Common);
        #Cache MIMEs list
        $this->extToMime = $common::extToMime;
        #Cache mimeRegex
        $this->mimeRegex = $common::mimeRegex;
        unset($common);
        #Set upload directory
        if (is_writable(ini_get('upload_tmp_dir')) === true) {
            $this->uploaddir = ini_get('upload_tmp_dir');
        } else {
            $this->uploaddir = sys_get_temp_dir();
        }
        #Ensure we do not have trailing slash
        $this->uploaddir = preg_replace('/(.*[^\\\\\/]{1,})([\\\\\/]{1,}$)/', '$1', $this->uploaddir);
    }
    
    #Function for smart resumable download with proper headers
    public function download(string $file, string $filename = '', string $mime = '', bool $inline = false, int $speedlimit = 10485760, bool $exit = true): bool|int
    {
        #Sanitize speedlimit
        $speedlimit = $this->speedLimit($speedlimit);
        #Some protection
        header('Access-Control-Allow-Headers: range');
        header('Access-Control-Allow-Methods: GET');
        #Download is valid only in case of GET method
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return (new \Simbiat\HTTP20\Headers)->clientReturn('405', $exit);
        }
        #Check that path exists and is actually a file
        if (!is_file($file)) {
            if (isset($_SERVER['HTTP_RANGE'])) {
                return (new \Simbiat\HTTP20\Headers)->clientReturn('410', $exit);
            } else {
                return (new \Simbiat\HTTP20\Headers)->clientReturn('404', $exit);
            }
        }
        #Check if file is readable
        if (!is_readable($file)) {
            return (new \Simbiat\HTTP20\Headers)->clientReturn('409', $exit);
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
        (new \Simbiat\HTTP20\Headers)->lastModified(filemtime($file), true)->eTag($boundary, true);
        #Check if MIME was provided
        if (!empty($mime)) {
            #If yes, validate its format
            if (preg_match('/^(('.$this->mimeRegex.') ?){1,}$/i', $value) !== 1) {
                #Empty invalid MIME
                $mime = '';
            }
        }
        #Check if it's empty again (or was from the start)
        if (empty($mime)) {
            #If not, attempt to check if in the constant list based on extesnion
            if (isset($this->extToMime[$fileinfo['extension']])) {
                $mime = $this->extToMime[$fileinfo['extension']];
            } else {
                #Replace MIME
                $mime = 'application/octet-stream';
            }
        }
        #Get file name
        if (empty($filename)) {
            $filename = $fileinfo['basename'];
        }
        #Process ranges
        $ranges = $this->rangesValidate($filesize);
        if (isset($ranges[0]) && $ranges[0] === false) {
            return (new \Simbiat\HTTP20\Headers)->clientReturn('416', $exit);
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
            return (new \Simbiat\HTTP20\Headers)->clientReturn('500', $exit);
        }
        #Open output stream
        $output = fopen('php://output', 'wb');
        #Check if stream was opened
        if ($output === false) {
            return (new \Simbiat\HTTP20\Headers)->clientReturn('500', $exit);
        }
        #Disable buffering. This should help limiting the memory usage. At least, in some cases.
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
                if ($speedlimit > $filesize) {
                    $speedlimit = $filesize;
                }
                $speedlimit = $this->speedLimit($speedlimit);
                #Output data
                $result = $this->streamCopy($stream, $output, $filesize, $ranges[0]['start'], $speedlimit);
                #Close file
                fclose($stream);
                fclose($output);
                if ($result === false) {
                    return (new \Simbiat\HTTP20\Headers)->clientReturn('500', $exit);
                } else {
                    if ($exit) {
                        return (new \Simbiat\HTTP20\Headers)->clientReturn('200', true);
                    } else {
                        return $result;
                    }
                }
            } else {
                header('Content-Type: multipart/byteranges; boundary='.$boundary);
                #Calculate size starting with the mandatory end of the feed (delimiter)
                $partsSize = strlen("\r\n--".$boundary."\r\n");
                foreach ($ranges as $range) {
                    #Add content size
                    $partsSize += $range['end'] - $range['start'] + 1;
                    #Add size of supportive text
                    $partsSize += strlen("\r\n--".$boundary."\r\n".'Content-type: '.$mime."\r\n".'Content-Range: bytes '.$range['start'].'-'.$range['end'].'/'.$filesize."\r\n\r\n");
                }
                #Send expected size to client
                header('Content-Length: '.$partsSize);
                #Iterrate the parts
                $result = false;
                $sent = 0;
                foreach ($ranges as $range) {
                    #Echo supportive text
                    echo "\r\n--".$boundary."\r\n".'Content-type: '.$mime."\r\n".'Content-Range: bytes '.$range['start'].'-'.$range['end'].'/'.$filesize."\r\n\r\n";
                    #Limit speed to range length, if current speed limit is too large, so that it will be provided fully
                    if ($speedlimit > $range['end'] - $range['start'] + 1) {
                        $speedlimit_multi = $range['end'] - $range['start'] + 1;
                    } else {
                        $speedlimit_multi = $speedlimit;
                    }
                    $speedlimit_multi = $this->speedLimit($speedlimit_multi);
                    #Output data
                    $result = $this->streamCopy($stream, $output, $range['end'] - $range['start'] + 1, $range['start'], $speedlimit_multi);
                    if ($result === false) {
                        fclose($stream);
                        fclose($output);
                        return (new \Simbiat\HTTP20\Headers)->clientReturn('500', $exit);
                    } else {
                        $sent += $result;
                    }
                }
                #Close the file
                fclose($stream);
                fclose($output);
                echo "\r\n--".$boundary."\r\n";
                if ($exit) {
                    return (new \Simbiat\HTTP20\Headers)->clientReturn('200', true);
                } else {
                    return $sent;
                }
            }
        } else {
            header('Content-Type: '.$mime);
            header('Content-Length: '.$filesize);
            header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
            #Output data
            $result = $this->streamCopy($stream, $output, $filesize, 0, $speedlimit);
            #Close the file
            fclose($stream);
            fclose($output);
            if ($result === false) {
                return (new \Simbiat\HTTP20\Headers)->clientReturn('500', $exit);
            } else {
                if ($exit) {
                    return (new \Simbiat\HTTP20\Headers)->clientReturn('200', true);
                } else {
                    return $result;
                }
            }
        }
    }
    
    #Function to handle file uploads
    public function upload(string|array $destPath, bool $preserveNames = false, bool $overwrite = false, array $allowedMime = [], bool $intollerant = true, bool $exit = true): bool|array
    {
        #Cache some PHP settings
        $maxUpload = $this-> phpMemoryToInt(ini_get('upload_max_filesize'));
        $maxPost = $this-> phpMemoryToInt(ini_get('post_max_size'));
        $maxFiles = ini_get('max_file_uploads');
        #Check if POST or PUT
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
            return (new \Simbiat\HTTP20\Headers)->clientReturn('405', $exit);
        }
        #Check content type if we have POST method
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (empty($_SERVER['CONTENT_TYPE']) || preg_match('/^multipart\/form-data(;)?.*/i', $_SERVER['CONTENT_TYPE']) !== 1)) {
            return (new \Simbiat\HTTP20\Headers)->clientReturn('415', $exit);
        }
        #Sanitize provided MIME types
        if (!empty($allowedMime)) {
            foreach ($allowedMime as $key=>$mime) {
                if (preg_match('/^'.$this->mimeRegex.'$/i', $mime) !== 1) {
                    unset($allowedMime[$key]);
                }
            }
        }
        #Cache filename sanitizer
        if (method_exists('\Simbiat\SafeFileName','sanitize')) {
            $SafeFileName = (new \Simbiat\SafeFileName);
        } else {
            $SafeFileName = false;
        }
        #Check if file upload is enabled on server
        if (ini_get('file_uploads') == false) {
            return (new \Simbiat\HTTP20\Headers)->clientReturn('501', $exit);
        }
        #Check that we do have some space allocated for file uploads
        if ($maxUpload === 0 || $maxPost === 0 || $maxFiles === 0) {
            return (new \Simbiat\HTTP20\Headers)->clientReturn('507', $exit);
        }
        #Validate destination directory
        if (is_string($destPath)) {
            $destPath = realpath($destPath);
            if (!is_dir($destPath) || !is_writable($destPath)) {
                return (new \Simbiat\HTTP20\Headers)->clientReturn('500', $exit);
            }
        } elseif (is_array($destPath)) {
            foreach ($destPath as $key=>$path) {
                $destPath[$key] = realpath($path);
                if (!is_dir($destPath[$key]) || !is_writable($destPath[$key])) {
                    return (new \Simbiat\HTTP20\Headers)->clientReturn('500', $exit);
                }
            }
        } else {
            return (new \Simbiat\HTTP20\Headers)->clientReturn('500', $exit);
        }
        #Process files based on method used
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            #Check that something was sent to us at all
            if ((isset($_SERVER['CONTENT_LENGTH']) && intval($_SERVER['CONTENT_LENGTH']) === 0) || empty($_FILES) || empty($_POST)) {
                return (new \Simbiat\HTTP20\Headers)->clientReturn('400', $exit);
            }
            #Standardize $_FILES and also count them
            $totalfiles = 0;
            foreach ($_FILES as $field=>$files) {
                #Check if multiple files were uploaded to a field and process the values accordingly
                if (is_array($files['name'])) {
                    $totalfiles += count($files['name']);
                    foreach ($files['name'] as $key=>$file) {
                        $_FILES[$field][$key]['name'] = $file;
                        $_FILES[$field][$key]['type'] = $files['type'][$key];
                        $_FILES[$field][$key]['size'] = $files['size'][$key];
                        $_FILES[$field][$key]['tmp_name'] = $files['tmp_name'][$key];
                        $_FILES[$field][$key]['error'] = $files['error'][$key];
                    }
                } else {
                    $totalfiles += 1;
                    $_FILES[$field][0]['name'] = $files['name'];
                    $_FILES[$field][0]['type'] = $files['type'];
                    $_FILES[$field][0]['size'] = $files['size'];
                    $_FILES[$field][0]['tmp_name'] = $files['tmp_name'];
                    $_FILES[$field][0]['error'] = $files['error'];
                }
                unset($_FILES[$field]['name'], $_FILES[$field]['type'], $_FILES[$field]['size'], $_FILES[$field]['tmp_name'], $_FILES[$field]['error']);
            }
            #Check number of files
            if ($totalfiles > $maxFiles) {
                return (new \Simbiat\HTTP20\Headers)->clientReturn('413', $exit);
            }
            #Prepare array for uploaded files
            $uploadedFiles = [];
            #Check for any errors in files, so that we can exit before actually processing the rest
            foreach ($_FILES as $field=>$files) {
                #Check that field has a folder to copy file to
                if (is_array($destPath) && !isset($destPath[$field])) {
                    if ($intollerant) {
                        return (new \Simbiat\HTTP20\Headers)->clientReturn('501', $exit);
                    } else {
                        #Remove the file from list
                        unset($_FILES[$field]);
                        continue;
                    }
                }
                #Set destination path
                if (is_string($destPath)) {
                    $finalPath = $destPath;
                } else {
                    $finalPath = $destPath[$field];
                }
                foreach ($files as $key=>$file) {
                    switch ($file['error'])
                    {
                        case UPLOAD_ERR_OK:
                            #Do nothing at this time
                            break;
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            if ($intollerant) {
                                return (new \Simbiat\HTTP20\Headers)->clientReturn('413', $exit);
                            } else {
                                #Remove the file from list
                                unset($_FILES[$field][$key]);
                                continue 2;
                            }
                            break;
                        case UPLOAD_ERR_PARTIAL:
                        case UPLOAD_ERR_NO_FILE:
                        case UPLOAD_ERR_CANT_WRITE:
                            if ($intollerant) {
                                return (new \Simbiat\HTTP20\Headers)->clientReturn('409', $exit);
                            } else {
                                #Remove the file from list
                                unset($_FILES[$field][$key]);
                                continue 2;
                            }
                            break;
                        case UPLOAD_ERR_NO_TMP_DIR:
                        case UPLOAD_ERR_EXTENSION:
                            if ($intollerant) {
                                return (new \Simbiat\HTTP20\Headers)->clientReturn('500', $exit);
                            } else {
                                #Remove the file from list
                                unset($_FILES[$field][$key]);
                                continue 2;
                            }
                            break;
                        default:
                            if ($intollerant) {
                                return (new \Simbiat\HTTP20\Headers)->clientReturn('418', $exit);
                            } else {
                                #Remove the file from list
                                unset($_FILES[$field][$key]);
                                continue 2;
                            }
                            break;
                    }
                    #Check if file being referenced was, indeed, sent to us via POST
                    if (is_uploaded_file($file['tmp_name']) === false) {
                        #Deny further processing. This is the only case, where we ignore $intollerant setting for security reasons
                        return (new \Simbiat\HTTP20\Headers)->clientReturn('403', $exit);
                    }
                    #Check file size
                    if ($file['size'] > $maxUpload) {
                        if ($intollerant) {
                            return (new \Simbiat\HTTP20\Headers)->clientReturn('413', $exit);
                        } else {
                            #Remove the file from list
                            unset($_FILES[$field][$key]);
                            continue;
                        }
                    } elseif ($file['size'] === 0 || empty($file['tmp_name'])) {
                        #Check if tmp_name is set or $file size is empty
                        if ($intollerant) {
                            return (new \Simbiat\HTTP20\Headers)->clientReturn('400', $exit);
                        } else {
                            #Remove the file from list
                            unset($_FILES[$field][$key]);
                            continue;
                        }
                    }
                    #Get actual MIME type
                    if (isset($_FILES[$field][$key])) {
                        if (extension_loaded('fileinfo')) {
                            $_FILES[$field][$key]['type'] = mime_content_type($file['tmp_name']);
                        }
                        #Check against allowed MIME types if any was set and fileinfo is loaded
                        if (!empty($allowedMime)) {
                            #Get MIME from file (not relying on what was sent by client)
                            if (!in_array($_FILES[$field][$key]['type'], $allowedMime)) {
                                if ($intollerant) {
                                    return (new \Simbiat\HTTP20\Headers)->clientReturn('415', $exit);
                                } else {
                                    #Remove the file from list
                                    unset($_FILES[$field][$key]);
                                    continue;
                                }
                            }
                        }
                    }
                    #Sanitize name
                    if (isset($_FILES[$field][$key])) {
                        if ($SafeFileName !== false) {
                            $_FILES[$field][$key]['name'] = basename($SafeFileName->sanitize($file['name']));
                            #If name is empty or name is too long, do not process it
                            if (empty($_FILES[$field][$key]['name']) || mb_strlen($_FILES[$field][$key]['name'], 'UTF-8') > 225) {
                                if ($intollerant) {
                                    return (new \Simbiat\HTTP20\Headers)->clientReturn('400', $exit);
                                } else {
                                    #Remove the file from list
                                    unset($_FILES[$field][$key]);
                                    continue;
                                }
                            } else {
                                #Set new name for the file. By default we will be using hash of the file. Using sha3-256 since it has lower probability of collissions than md5, although we do lose some speed
                                #Hash is saved regardless, though, since it may be very useful
                                $_FILES[$field][$key]['hash'] = hash_file('sha3-256', $file['tmp_name']);
                                if ($preserveNames) {
                                    $_FILES[$field][$key]['new_name'] = $_FILES[$field][$key]['name'];
                                } else {
                                    #Get extension (if any)
                                    $ext = array_search($_FILES[$field][$key]['type'], $this->extToMime);
                                    if ($ext === false) {
                                        $ext = strval(pathinfo($_FILES[$field][$key]['name'])['extension']);
                                        if (!empty($ext)) {
                                            $ext = '.'.$ext;
                                        } else {
                                            $ext = '';
                                        }
                                    } else {
                                        $ext = '.'.$ext;
                                    }
                                    #Generate name from hash and extension from original file
                                    $_FILES[$field][$key]['new_name'] = $_FILES[$field][$key]['hash'].$ext;
                                }
                                #Check if destinatino file already exists
                                if (is_file($finalPath.'/'.$_FILES[$field][$key]['new_name'])) {
                                    if ($overwrite) {
                                        #Check that it is writable
                                        if (!is_writable($finalPath.'/'.$_FILES[$field][$key]['new_name'])) {
                                            if ($intollerant) {
                                                return (new \Simbiat\HTTP20\Headers)->clientReturn('409', $exit);
                                            } else {
                                                #Remove the file from list
                                                unset($_FILES[$field][$key]);
                                                continue;
                                            }
                                        }
                                    } else {
                                        #Add it to the list of successfully uploaded files if we are not preserving names, since that implies relative uniqueness of them, thus we are most likely seeing the same file
                                        if ($preserveNames === false) {
                                            $uploadedFiles[] = ['server_name' => $_FILES[$field][$key]['new_name'], 'user_name' => $_FILES[$field][$key]['name'], 'size' => $file['size'], 'type' => $_FILES[$field][$key]['type'], 'hash' => $_FILES[$field][$key]['hash'], 'field' => $field];
                                        }
                                        #Remove the file from global list
                                        unset($_FILES[$field][$key]);
                                        continue;
                                    }
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
                if (empty($uploadedFiles)) {
                    return (new \Simbiat\HTTP20\Headers)->clientReturn('400', $exit);
                }
            } else {
                #Process files and put them into an array
                foreach ($_FILES as $field=>$files) {
                    #Set destination path
                    if (is_string($destPath)) {
                        $finalPath = $destPath;
                    } else {
                        $finalPath = $destPath[$field];
                    }
                    foreach ($files as $key=>$file) {
                        #Move file, but only if it's not already present in destination
                        if (!is_file($finalPath.'/'.$file['new_name']) && move_uploaded_file($file['tmp_name'], $finalPath.'/'.$file['new_name']) === true) {
                            $uploadedFiles[] = ['server_name' => $file['new_name'], 'user_name' => $file['name'], 'size' => $file['size'], 'type' => $file['type'], 'hash' => $file['hash'], 'field' => $field];
                        } else {
                            if ($intollerant) {
                                return $uploadedFiles;
                            }
                        }
                    }
                }
            }
        #Process PUT requests
        } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            if (!isset($_SERVER['CONTENT_LENGTH']) || intval($_SERVER['CONTENT_LENGTH']) === 0) {
                return (new \Simbiat\HTTP20\Headers)->clientReturn('411', $exit);
            }
            $client_size = intval($_SERVER['CONTENT_LENGTH']);
            #Set time limit equal to the size. If load speed is <=10 kilobytes per second - that's definitely low speed session, that we do not want to keep forever
            set_time_limit(intval(floor($client_size/10240)));
            if ($_SERVER['CONTENT_LENGTH'] > $maxUpload) {
                return (new \Simbiat\HTTP20\Headers)->clientReturn('413', $exit);
            }
            #Check that destination is a string
            if (!is_string($destPath)) {
                return (new \Simbiat\HTTP20\Headers)->clientReturn('500', $exit);
            }
            if (!empty($allowedMime) && isset($_SERVER['CONTENT_TYPE'])) {
                #Get MIME from file (not relying on what was sent by client)
                if (!in_array($_SERVER['CONTENT_TYPE'], $allowedMime)) {
                    return (new \Simbiat\HTTP20\Headers)->clientReturn('415', $exit);
                }
            }
            #Attempt to get name from header
            $name = '';
            if (isset($_SERVER['HTTP_CONTENT_DISPOSITION'])) {
                #filename* is preferred over filename as per https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Disposition
                #Note that this format MAY include charset
                $name = preg_replace('/^(.*filename\*='.(new \Simbiat\HTTP20\Common)::langEncRegex.'"?)(?<filename>[^";=\*]*)((("|;).*)|($))$/i', '$12', $_SERVER['HTTP_CONTENT_DISPOSITION']);
                if (empty($name) || $name === $_SERVER['HTTP_CONTENT_DISPOSITION']) {
                    #If we are here, it means that there is no filename*
                    $name = preg_replace('/^(.*filename="?)(?<filename>[^";=\*]*)((("|;).*)|($))$/i', '$2', $_SERVER['HTTP_CONTENT_DISPOSITION']);
                    if (empty($name) || $name === $_SERVER['HTTP_CONTENT_DISPOSITION']) {
                        #If we are here, it means no filename was shared
                        $name = '';
                    }
                }
            }
            #Sanitize the name
            if (!empty($name) && $SafeFileName !== false) {
                $name = basename($SafeFileName->sanitize($name));
            }
            if (empty($name)) {
                #Generate random name. Using 64 to be consistent with sha3-256 hash
                $name = hash('sha3-256', random_bytes(64)).'.put';
                $resumable = false;
            } else {
                $resumable = true;
            }
            #Check if file already exists
            if ($resumable && is_file($this->uploaddir.'/'.$name)) {
                $offset = filesize($this->uploaddir.'/'.$name);
            } else {
                $offset = 0;
            }
            if ($offset !== $client_size) {
                #Open input stream
                $stream = fopen('php://input', 'rb');
                #Check if file was opened
                if ($stream === false) {
                    return (new \Simbiat\HTTP20\Headers)->clientReturn('409', $exit);
                }
                #Read input stream
                if ($offset > 0 && $offset < $client_size) {
                    #We can't fseek php://input, thus we need to read it. To improve peformance we will use php://temp with a memorylimit
                    $garbage = fopen('php://temp', 'wb');
                    #Check if stream was opened
                    if ($garbage === false) {
                        fclose($stream);
                        return (new \Simbiat\HTTP20\Headers)->clientReturn('500', $exit);
                    }
                    $collected = stream_copy_to_stream($stream, $garbage, $offset);
                    #Close stream
                    fclose($garbage);
                    if ($collected != $offset) {
                        #Means we failed to read appropriate amount of bytes
                        fclose($stream);
                        return (new \Simbiat\HTTP20\Headers)->clientReturn('500', $exit);
                    }
                }
                if (feof($stream) === false) {
                    #Open output stream
                    if ($offset < $client_size) {
                        $output = fopen($this->uploaddir.'/'.$name, 'ab');
                    } else {
                        #Means the file is different and we better rewrite it
                        $output = fopen($this->uploaddir.'/'.$name, 'wb');
                    }
                    #Check if stream was opened
                    if ($output === false) {
                        fclose($stream);
                        return (new \Simbiat\HTTP20\Headers)->clientReturn('500', $exit);
                    }
                    #Disable buffering. This should help limiting the memory usage. At least, in some cases.
                    stream_set_read_buffer($stream, 0);
                    stream_set_write_buffer($output, 0);
                    #Ignore user abort to attempt identify when client has aborted
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
                } else {
                    #Most likely our stream got interrupted during initial read
                    fclose($stream);
                    $result = false;
                }
            } else {
                #Means the file we have is complete
                $result = true;
            }
            if ($result === false) {
                if (!$resumable) {
                    @unlink($this->uploaddir.'/'.$name);
                }
                return (new \Simbiat\HTTP20\Headers)->clientReturn('500', $exit);
            } else {
                #Get file MIME type
                if (isset($_SERVER['CONTENT_TYPE'])) {
                    $filetype = $_SERVER['CONTENT_TYPE'];
                } else {
                    $filetype = 'application/octet-stream';
                }
                if (extension_loaded('fileinfo')) {
                    $filetype = mime_content_type($this->uploaddir.'/'.$name);
                }
                #Check against allowed MIME types if any was set and fileinfo is loaded
                if (!empty($allowedMime)) {
                    #Get MIME from file (not relying on what was sent by client)
                    if (!in_array($filetype, $allowedMime)) {
                        @unlink($this->uploaddir.'/'.$name);
                        return (new \Simbiat\HTTP20\Headers)->clientReturn('415', $exit);
                    }
                }
                #Get extension of the file
                $ext = array_search($filetype, $this->extToMime);
                if ($ext === false) {
                    $ext = 'PUT';
                }
                #Get hash
                $hash = hash_file('sha3-256', $this->uploaddir.'/'.$name);
                #Set new name
                $newName = $hash.'.'.$ext;
                #Attempt to move the file
                if (rename($this->uploaddir.'/'.$name, $destPath.'/'.$newName) === false) {
                    return (new \Simbiat\HTTP20\Headers)->clientReturn('500', $exit);
                }
                #Add to array. Using array here for consistency with POST method. Field is reported as PUT to indicate the method. It's advisable not to use it for fields if you use POST method as well
                $uploadedFiles[] = ['server_name' => $newName, 'user_name' => $name, 'size' => $client_size, 'type' => $filetype, 'hash' => $hash, 'field' => 'PUT'];
            }
        }
        if (empty($uploadedFiles)) {
            return (new \Simbiat\HTTP20\Headers)->clientReturn('500', $exit);
        } else {
            if ($exit) {
                #Inform client, that files were uploaded
                return (new \Simbiat\HTTP20\Headers)->clientReturn('200', true);
            } else {
                return $uploadedFiles;
            }
        }
    }
    
    #Function to copy data in small chunks (not HTTP1.1 chunks) based on speed limitation
    public function streamCopy(&$input, &$output, int $totalsize = 0, int $offset = 0, int $speed = 10485760): bool|int
    {
        #Ignore user abort to attempt identify when client has aborted
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
        if ($totalsize <= 0) {
            $totalsize = fstat($input)['size'];
        }
        #Sanitize speed
        $speed = $this->speedLimit($speed);
        #Set time limit equal to the size. If load speed is <=10 kilobytes per second - that's definitely low speed session, that we do not want to keep forever
        set_time_limit(intval(floor($totalsize/10240)));
        #Set counter for amount of data sent
        $sent = 0;
        while ($sent < $totalsize && connection_status() === CONNECTION_NORMAL) {
            #Using stream_copy_to_stream because it is able to handle much larger files even with relatively large speed limits, close to how readfile() can.
            $sentStat = stream_copy_to_stream($input, $output, $speed, $offset);
            if ($sentStat !== false) {
                $sent += $sentStat;
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
        if (connection_status() === CONNECTION_NORMAL && $sent >= $totalsize) {
            return $sent;
        } else {
            return false;
        }
    }
    
    #Function to determine speed limit based on maximum allowed memory usage
    public function speedLimit(int $speed = 0, float $percentage = 0.9): int
    {
        #Sanitize percentage
        if ($percentage <= 0 || $percentage > 1.0) {
            $percentage = 0.9;
        }
        #Get memory limit
        $memory = ini_get('memory_limit');
        $memory = $this->phpMemoryToInt($memory);
        #Exclude memory peak usage (assume, that it's either still being used or can be used in near future)
        $memory = $memory - memory_get_peak_usage(true);
        #When using stream there is still a certain memory overhead, so we take only percentage of the memory
        #Percentage was experimentally derived from downloading a 1.5G file with 256M memory limit until there was no "Allowed memory size of X bytes exhausted". Actually it was 0.94, but we would prefer to have at least some headroom.
        $memory = intval(floor($memory * 0.9));
        if ($speed <= 0 || $speed > $memory) {
            $speed = $memory;
        }
        return $speed;
    }
    
    #Function to convert PHP's memory strings (like 256M) used in some settings to integer value (bytes)
    public function phpMemoryToInt(string $memory): int
    {
        #Get suffix
        $suffix = strtolower($memory[strlen($memory)-1]);
        #Get int value
        $memory = intval(substr($memory, 0, -1));
        $memory *= match($suffix) {
            'g' => 1073741824,
            'm' => 1048576,
            'k' => 1024,
            default => 1,
        };
        return $memory;
    }
    
    #Function to validate HTTP header "Range" and return it as an array. If case of errors it will return array with one element (index 0) equallling false.
    public function rangesValidate(int $size): array
    {
        if (isset($_SERVER['HTTP_RANGE'])) {
            #Validate the value
            if (preg_match('/^bytes=\d*-\d*(\s*,\s*\d*-\d*)*$/i', $_SERVER['HTTP_RANGE']) !== 1) {
                header($_SERVER['SERVER_PROTOCOL'].' 416 Range Not Satisfiable');
                return [0 => false];
            } else {
                #Remove bytes=
                $ranges = preg_replace('/bytes=/i', '', $_SERVER['HTTP_RANGE']);
                #Split ranges
                $ranges = explode(',', $ranges);
                #Sanitize
                foreach ($ranges as $key=>$range) {
                    if (preg_match('/^-\d{1,}$/', $range) === 1) {
                        $ranges[$key] = ['start' => 0, 'end' => intval(ltrim($range, '-'))];
                    } elseif (preg_match('/^\d{1,}-$/', $range) === 1) {
                        $ranges[$key] = ['start' => intval(rtrim($range, '-')), 'end' => ($size - 1)];
                    } elseif (preg_match('/^\d{1,}-\d{1,}$/', $range) === 1) {
                        $temprange = explode('-', $range);
                        $ranges[$key] = ['start' => intval($temprange[0]), 'end' => intval($temprange[1])];
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
                    foreach ($ranges as $keyPrime=>$rangePrime) {
                        foreach ($ranges as $keySec=>$rangeSec) {
                            #Only compare pairs after current one
                            if ($keySec > $keyPrime) {
                                #If overlap in any way - exit
                                if (
                                    ($rangePrime['start'] === $rangeSec['start'] && $rangePrime['end'] === $rangeSec['end']) ||
                                    ($rangeSec['end'] >= $rangePrime['start'] && $rangeSec['end'] < $rangePrime['end']) ||
                                    ($rangeSec['start'] > $rangePrime['start'] && $rangeSec['start'] <= $rangePrime['end'])
                                ) {
                                    return [0 => false];
                                }
                            }
                        }
                    }
                }
                #If something went wrong and we got an empty range here - return as false
                if (empty($ranges)) {
                    return [0 => false];
                } else {
                    return $ranges;
                }
            }
        } else {
            return [];
        }
    }
    
    #Function to send a file to browser
    public function fileEcho(string $filepath, array $allowedMime = [], string $cacheStrat = 'month', bool $exit = true): int
    {
        #Check if file exists
        if (is_file($filepath)) {
            #Process MIME
            if (extension_loaded('fileinfo')) {
                #Get MIME from file
                $mimeType = mime_content_type($filepath);
                if (!empty($allowedMime)) {
                    #Sanitize provided MIME types
                    foreach ($allowedMime as $key=>$mime) {
                        if (preg_match('/^'.$this->mimeRegex.'$/i', $mime) !== 1) {
                            unset($allowedMime[$key]);
                        }
                    }
                    #Check if MIME is allowed
                    if (!empty($allowedMime) && !in_array($mimeType, $allowedMime)) {
                        (new \Simbiat\HTTP20\Headers)->clientReturn('403', $exit);
                        return 403;
                    }
                }
            }
            #While above checks actual MIME type it may be different from the one client may be expecting based on extension. For example RSS file will be recognized as application/xml (or text/xml), instead of application/rss+xml. This may be minor, but depending on client can cause unexpected behaviour. Thus we rely on extension here, since it can provide a more approriate MIME type
            $extension = pathinfo($filepath)['extension'];
            #Set MIME from extesnion, of available
            if (!empty($extension) && !empty($this->extToMime[$extension])) {
                $mimeTypeAlt = $this->extToMime[$extension];
            }
            #Set MIME type to stream, if it's empty
            if (empty($mimeTypeAlt)) {
                if (empty($mimeType)) {
                    $mimeType = 'application/octet-stream';
                }
            } else {
                $mimeType = $mimeTypeAlt;
            }
            #Send Last Modified, eTag and Cache-Control headers
            (new \Simbiat\HTTP20\Headers)->lastModified(filemtime($filepath), true)->eTag(hash_file('sha3-256', $filepath), true)->cacheControl('', $cacheStrat, true);
            #Send MIME types. Add Charset to those, that are recommended to have it
            if (preg_match('/^(text\/.*)|(application\/(.*javascript|.*json|.*xml))$/i', $mimeType) === 1) {
                header('Content-Type: '.$mimeType.'; charset=utf-8');
            } else {
                header('Content-Type: '.$mimeType);
            }
            #Send content disposition
            header('Content-Disposition: inline; filename="'.basename($filepath).'"');
            #Open stream
            $stream = fopen($filepath, 'rb');
            if ($stream === false) {
                (new \Simbiat\HTTP20\Headers)->clientReturn('500', $exit);
                return 500;
            }
            #Some MIME types can be zipped nicely
            if (preg_match('/^((font|text)\/.*)|(application\/(.*javascript|.*json|.*xml|vnd\.ms-fontobject|wasm|x-font-ttf))|(image\/(bmp|svg\+xml|vnd.microsoft.icon))$/i', $mimeType) === 1) {
                #Read the file
                $output = fread($stream, filesize($filepath));
                #Close stream
                fclose($stream);
                if ($output === false) {
                    (new \Simbiat\HTTP20\Headers)->clientReturn('500', $exit);
                    return 500;
                } else {
                    (new \Simbiat\HTTP20\Common)->zEcho($output, $cacheStrat);
                }
            } else {
                #Send size information
                header('Content-Length: '.filesize($filepath));
                #Exit if HEAD method was used (by this time all headers should have been sent
                if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'HEAD') {
                    #Close session
                    if (session_status() === PHP_SESSION_ACTIVE) {
                        session_write_close();
                    }
                    exit;
                }
                #Send data
                if (fpassthru($stream) === false) {
                    (new \Simbiat\HTTP20\Headers)->clientReturn('500', $exit);
                    return 500;
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
               exit; 
            } else {
                return 200;
            }
        } else {
            (new \Simbiat\HTTP20\Headers)->clientReturn('404', $exit);
            return 404;
        }
    }
    
    #Function to proxy-stream file from another server
    public function proxyFile(string $url, string $cacheStrat = ''): void
    {
        #Cache headers object
        $headers = (new \Simbiat\HTTP20\Headers);
        #Get headers
        $headersData = @get_headers($url, context: stream_context_create(['http' => [
            'method' => 'HEAD',
            'follow_location' => 1,
            'protocol_version' => 2.0
        ]]));
        #Check that we did get headers
        if (!is_array($headersData)) {
            #Failed to get headers, meaning we most likely will not be able to get the content as well
            $headers->clientReturn('500', true);
        }
        #Cache-Control flag
        $cache = false;
        #Send the headers from remote server
        foreach ($headersData as $headerValue) {
            if (preg_match('/^Cache-Control:.*$/', $headerValue) === 1) {
                $cache = true;
            }
            header($headerValue);
        }
        #Add Cache-Control
        if ($cache === false) {
            $headers->cacheControl('', $cacheStrat, true);
        }
        #Process lastModified and eTag to attempt to rely on client cache and not waste server resources
        foreach ($headersData as $headerValue) {
            if (preg_match('/^Last-Modified:.*$/', $headerValue) === 1) {
               $headers->lastModified(strtotime(preg_replace('/^(Last-Modified:\s*"?)([^"]*)("?)$/', '$2', $headerValue)), true);
            } elseif (preg_match('/^ETag:.*$/', $headerValue) === 1) {
                $headers->eTag(preg_replace('/^(ETag:\s*"?)([^"]*)("?)$/', '$2', $headerValue), true);
            }
        }
        #Open streams
        #Supress warning for $url, since connection can be refused for some reason and it still may be "normal"
        $url = @fopen($url, 'rb', context: stream_context_create(['http' => [
            'method' => 'GET',
            'follow_location' => 1,
            'protocol_version' => 2.0
        ]]));
        $output = fopen('php://output', 'wb');
        #Send contents
        if ($url !== false && $output !== false) {
            stream_copy_to_stream($url, $output);
            fclose($output);
            fclose($url);
        } else {
            $headers->clientReturn('500', true);
        }
        #Close session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        #Ensure we exit
        exit;
    }
}

?>