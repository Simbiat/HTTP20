<?php
declare(strict_types=1);
namespace HTTP20;

class Common
{
    #Wrapper for date(), that handles strings and allows validation of result
    public function valueToTime($time, string $format, string $validregex = ''): string
    {
        #If we want to use a constant, but it was sent as a string
        if (str_starts_with(strtoupper($format), 'DATE_')) {
            $format = constant($format);
        }
        if (empty($time)) {
            $time = date($format, time());
        } else {
            if (is_numeric($time)) {
                #Ensure we use int
                $time = date($format, intval($time));
            } else {
                if (is_string($time)) {
                    #Attempt to convert string to time
                    $time = date($format, strtotime($time));
                } else {
                    throw new \UnexpectedValueException('Time provided to `valueToTime` is neither numeric or string');
                }
            }
        }
        if ($format === 'c' || $format === \DATE_ATOM) {
            $validregex = '/^(?:[1-9]\d{3}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1\d|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[1-9]\d(?:0[48]|[2468][048]|[13579][26])|(?:[2468][048]|[13579][26])00)-02-29)T(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d(?:Z|[+-][01]\d:[0-5]\d)$/i';
        }
        if (!empty($validregex) && preg_match($validregex, $time) !== 1) {
            throw new \UnexpectedValueException('Date provided to `feedIDGen` failed to be validated against the provided regex');
        }
        return $time;
    }
    
    #Function to prepare ID for Atom feed as suggested on http://web.archive.org/web/20110514113830/http://diveintomark.org/archives/2004/05/28/howto-atom-id
    public function atomIDGen(string $link, $date = NULL): string
    {
        $date = $this->valueToTime($date, 'Y-m-d', '/^\d{4}-\d{2}-\d{2}$/i');
        #Remove URI protocol (if any)
        $link = preg_replace('/^(?:[a-zA-Z]+?:\/\/)?/im', '', $this->htmlToRFC3986($link));
        #Replace any # with /
        $link = preg_replace('/#/im', '/', $link);
        #Remove HTML/XML reserved characters as precaution
        $link = preg_replace('/[\\\'"<>&]/im', '', $link);
        #Add 'tag:' to beginning and ',Y-m-d:' after domain name
        $link = preg_replace('/(?<domain>^(?:www\.)?([^:\/\n?]+))(?<rest>.*)/im', 'tag:$1,'.$date.':$3', $link);
        return $link;
    } 
    
    #Function utilizes ob functions to attempt compresing output sent to browser and also provide browser with length of the output
    public function zEcho(string $string): void
    {
        #Check that zlib is loaded. If not - do not zip, but do send size of the content
        if (extension_loaded('zlib')) {
            #Check if output_ompression is On. If not - use ob_gzhandler
            if (ini_get('zlib.output_compression') === 'On') {
                #Initiate buffer
                ob_start();
                #Send the output to buffer
                echo $string;
                #Flush buffer
                ob_end_flush();
            } else {
                #Initiate buffer
                ob_start('ob_gzhandler');
                #Send the output to buffer
                echo $string;
                #Flush buffer
                ob_end_flush();
                #Send header with length (needs to be sent after flushing or it wil show the length of unzipped version
                header('Content-Length: '.ob_get_length());
            }
        } else {
            #Initiate buffer
            ob_start();
            #Send the output to buffer
            echo $string;
            #Send header with length
            header('Content-Length: '.ob_get_length());
            #Flush buffer
            ob_end_flush();
        }
    }
    
    #Function to check if string is a mail address as per RFC 5322
    public function emailValidator(string $string): bool
    {
        if (preg_match('/^(?:[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9]))\.){3}(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9])|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])$/i', $string) === 1) {
            return true;
        } else {
            return false;
        }
    }
    
    #Function to check if string is an URI as per RFC 3986
    public function uriValidator(string $string): bool
    {
        if (preg_match('/^(?<scheme>[a-z][a-z0-9+.-]+):(?<authority>\/\/(?<user>[^@]+@)?(?<host>[a-z0-9.\-_~]+)(?<port>:\d+)?)?(?<path>(?:[a-z0-9-._~]|%[a-f0-9]|[!$&\'()*+,;=:@])+(?:\/(?:[a-z0-9-._~]|%[a-f0-9]|[!$&\'()*+,;=:@])*)*|(?:\/(?:[a-z0-9-._~]|%[a-f0-9]|[!$&\'()*+,;=:@])+)*)?(?<query>\?(?:[a-z0-9-._~]|%[a-f0-9]|[!$&\'()*+,;=:@]|[\/?])+)?(?<fragment>\#(?:[a-z0-9-._~]|%[a-f0-9]|[!$&\'()*+,;=:@]|[\/?])+)?$/i', $this->htmlToRFC3986($string)) === 1) {
            return true;
        } else {
            return false;
        }
    }
    
    #Function to check if string is a valid language code
    public function LangCodeCheck(string $string): bool
    {
        if (in_array(strtolower($string),
            ['af', 'sq', 'eu', 'be', 'bg', 'ca', 'zh-cn', 'zh-tw', 'hr', 'cs', 'da', 'nl', 'nl-be', 'nl-nl', 'en', 'en-au', 'en-bz', 'en-ca', 'en-ie', 'en-jm', 'en-nz', 'en-ph', 'en-za', 'en-tt', 'en-gb', 'en-us', 'en-zw', 'et', 'fo', 'fi', 'fr', 'fr-be', 'fr-ca', 'fr-fr', 'fr-lu', 'fr-mc', 'fr-ch', 'gl', 'gd', 'de', 'de-at', 'de-de', 'de-li', 'de-lu', 'de-ch', 'el', 'haw', 'hu', 'is', 'in', 'ga', 'it', 'it-it', 'it-ch', 'ja', 'ko', 'mk', 'no', 'pl', 'pt', 'pt-br', 'pt-pt', 'ro', 'ro-mo', 'ro-ro', 'ru', 'ru-mo', 'ru-ru', 'sr', 'sk', 'sl', 'es', 'es-ar', 'es-bo', 'es-cl', 'es-co', 'es-cr', 'es-do', 'es-ec', 'es-sv', 'es-gt', 'es-hn', 'es-mx', 'es-ni', 'es-pa', 'es-py', 'es-pe', 'es-pr', 'es-es', 'es-uy', 'es-ve', 'sv', 'sv-fi', 'sv-se', 'tr', 'uk']
            )) {
            return true;
        } else {
            return false;
        }
    }
    
    #Function does the same as rawurlencode but only for selected characters, that are restricted in HTML/XML. Useful for URIs that can have these characters and need to be used in HTML/XML and thus can't use htmlentities, but otherwise break HTML/XML
    #$full means that all of them will be converted (useful when text inside a tag). If `false` only < and & are converted (useful when inside attribute). If `false` is used - be careful with quotes inside the string you provide, because they can invalidate your HTML/XML
    public function htmlToRFC3986(string $string, bool $full = true): string
    {
        if ($full) {
            return str_replace(['\'', '"', '&', '<', '>'], ['%27', '%22', '%26', '%3C', '%3E'], $string);
        } else {
            return str_replace(['&', '<'], ['%26', '%3C'], $string);
        }
    }
}
?>