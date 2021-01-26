<?php
declare(strict_types=1);
namespace HTTP20;

class Common
{
    #Initially a function to convert time/date to ISO 8601 format string
    public function valueToTime($time, string $format = 'c', string $validregex = ''): string
    {
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
        if ($format === 'c') {
            $validregex = '/^(?:[1-9]\d{3}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1\d|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[1-9]\d(?:0[48]|[2468][048]|[13579][26])|(?:[2468][048]|[13579][26])00)-02-29)T(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d(?:Z|[+-][01]\d:[0-5]\d)$/i';
        }
        if (!empty($validregex) && preg_match($validregex, $time) !== 1) {
            throw new \UnexpectedValueException('Date provided to `feedIDGen` failed to be validated against the provided regex');
        }
        return $time;
    }
    
    #Function to prepare ID for Atom or RSS as suggested on http://web.archive.org/web/20110514113830/http://diveintomark.org/archives/2004/05/28/howto-atom-id
    public function feedIDGen(string $link, $date = NULL): string
    {
        $date = $this->valueToTime($date, 'Y-m-d', '/^\d{4}-\d{2}-\d{2}$/i');
        #Remove URI protocol (if any)
        $link = preg_replace('/^(?:[a-zA-Z]+?:\/\/)?/im', '', $link);
        #Replace any # with /
        $link = preg_replace('/#/im', '\/', $link);
        #Remove HTML/XML reserved characters as precaution
        $link = preg_replace('/[\'"<>&]/im', '', $link);
        #Add 'tag:' to beginning and ',Y-m-d:' after domain name
        $link = preg_replace('/(?<domain>^(?:www\.)?([^:\/\n?]+))(?<rest>.*)/im', 'tag:$1,'.$date.':$3', $link);
        return $link;
    } 
    
    #Function utilizes ob functions to attempt compresing output sent to browser and also provide browser with length of the output
    public function zEcho($string): void
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
}
?>