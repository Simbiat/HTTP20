<?php
declare(strict_types=1);
namespace http20;

class Meta
{
    #Function to prepare Twitter card as per https://developer.twitter.com/en/docs/twitter-for-websites/cards
    public function twitter(array $general, array $playerApp = [], bool $pretty = false): string
    {
        #Check that settings are not an empty array
        if (empty($general)) {
            trigger_error('Empty array of general settings was provided for Twitter card', E_USER_NOTICE);
            return '';
        }
        #Title is manadtory
        if (empty($general['title'])) {
            trigger_error('Empty title was provided for Twitter card', E_USER_NOTICE);
            return '';
        }
        
        #If card type is not set or is unsupported - use 'summary'
        if (empty($general['card']) || !in_array($general['card'], ['summary', 'summary_large_image', 'app', 'player'])) {
            $general['card'] = 'summary';
        }
        #Add main meta tag
        $output = '<meta name="twitter:card" content="'.htmlspecialchars($general['card']).'" />';
        #Add title
        $output .= '<meta name="twitter:title" content="'.mb_substr(htmlspecialchars($general['title']), 0, 70, 'UTF-8').'" />';
        #Add site if not empty and valid
        if (!empty($general['site']) && preg_match('/^@?(\w){4,15}$/', $general['site']) === 1 && preg_match('/^.*(twitter|admin).*$/i', $general['site']) !== 1) {
            $output .= '<meta name="twitter:site" content="'.(substr($general['site'], 0, 1) === '@' ? '' : '@').$general['site'].'" />';
        }
        #Add site:id if not empty and valid
        if (!empty($general['site:id']) && preg_match('/^\d{1,}$/', $general['site:id']) === 1) {
            $output .= '<meta name="twitter:site:id" content="'.$general['site:id'].'" />';
        }
        #Add creator if not empty and valid
        if (!empty($general['creator']) && preg_match('/^@?(\w){4,15}$/', $general['creator']) === 1 && preg_match('/^.*(twitter|admin).*$/i', $general['creator']) !== 1) {
            $output .= '<meta name="twitter:creator" content="'.(substr($general['creator'], 0, 1) === '@' ? '' : '@').$general['creator'].'" />';
        }
        #Add creator:id if not empty and valid
        if (!empty($general['creator:id']) && preg_match('/^\d{1,}$/', $general['creator:id']) === 1) {
            $output .= '<meta name="twitter:creator:id" content="'.$general['creator:id'].'" />';
        }
        #Add description if not empty
        if (!empty($general['description'])) {
            $output .= '<meta name="twitter:description" content="'.mb_substr(htmlspecialchars($general['description']), 0, 200, 'UTF-8').'" />';
        }
        #Add images' tags, that are not use with 'app' cards
        if ($general['card'] !== 'app') {
            if (!empty($general['image'])) {
                #Add only if URL looks like a valid URL for a supported image
                if ($this->isHTTPS($general['image']) === true && preg_match('/.*\.(jpg|png|webp|gif)(\?.*)?$/i', $general['image']) === 1) {
                    $output .= '<meta name="twitter:image" content="'.$general['image'].'" />';
                }
            }
            #Add image description
            if (!empty($general['image:alt'])) {
                $output .= '<meta name="twitter:image:alt" content="'.mb_substr(htmlspecialchars($general['image:alt']), 0, 420, 'UTF-8').'" />';
            }
        }
        #Process player tags
        if ($general['card'] === 'player') {
            #Check that mandatory fields are present as per https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/player-card
            if (preg_match('/.*twitter:site.*/i', $output) !== 1 || preg_match('/.*twitter:image".*/i', $output) !== 1 || empty($playerApp['player']) || empty($playerApp['width']) || empty($playerApp['height']) || $this->isHTTPS($playerApp['player']) === false || preg_match('/^\d{1,}$/', $playerApp['width']) !== 1 || preg_match('/^\d{1,}$/', $playerApp['height']) !== 1) {
                #Do not process if, since will be invalidated by Twitter either way
                trigger_error('One or more Twitter player card parameter is missing or incorrect', E_USER_NOTICE);
                return '';
            }
            #Add player URL
            $output .= '<meta name="twitter:player" content="'.$playerApp['player'].'" />';
            #Add width and height
            $output .= '<meta name="twitter:player:width" content="'.$playerApp['width'].'" />';
            $output .= '<meta name="twitter:player:height" content="'.$playerApp['height'].'" />';
            #Add stream
            if (!empty($playerApp['stream']) && $this->isHTTPS($playerApp['stream']) === true) {
                $output .= '<meta name="twitter:player:stream" content="'.$playerApp['stream'].'" />';
            }
        } elseif ($general['card'] === 'app') {
            #Check that mandatory fields are present as per https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/app-card
            if (preg_match('/.*twitter:site.*/i', $output) !== 1 || empty($playerApp['id:iphone']) || empty($playerApp['id:ipad']) || empty($playerApp['id:googleplay']) || preg_match('/^\d{1,}$/', $playerApp['id:iphone']) !== 1 || preg_match('/^\d{1,}$/', $playerApp['id:ipad']) !== 1 || preg_match('/^\d{1,}$/', $playerApp['id:googleplay']) !== 1) {
                #Do not process if, since will be invalidated by Twitter either way
                trigger_error('One or more Twitter app card parameter is missing or incorrect', E_USER_NOTICE);
                return '';
            }
            #Add IDs
            $output .= '<meta name="twitter:app:id:ipad" content="'.$playerApp['id:ipad'].'" />';
            $output .= '<meta name="twitter:app:id:iphone" content="'.$playerApp['id:iphone'].'" />';
            $output .= '<meta name="twitter:app:id:googleplay" content="'.$playerApp['id:googleplay'].'" />';
            #Add custom schemes
            if (!empty($playerApp['url:ipad']) && preg_match('/^(?<scheme>[a-z][a-z0-9+.-]+):\/\//i', $playerApp['url:ipad']) === 1) {
                $output .= '<meta name="twitter:app:url:ipad" content="'.$playerApp['url:ipad'].'" />';
            }
            if (!empty($playerApp['url:iphone']) && preg_match('/^(?<scheme>[a-z][a-z0-9+.-]+):\/\//i', $playerApp['url:iphone']) === 1) {
                $output .= '<meta name="twitter:app:url:iphone" content="'.$playerApp['url:iphone'].'" />';
            }
            if (!empty($playerApp['url:googleplay']) && preg_match('/^(?<scheme>[a-z][a-z0-9+.-]+):\/\//i', $playerApp['url:googleplay']) === 1) {
                $output .= '<meta name="twitter:app:url:googleplay" content="'.$playerApp['url:googleplay'].'" />';
            }
            #Add country code
            if (!empty($playerApp['country']) && preg_match('/^A[^ABCHJKNPVY]|B[^CKPUX]|C  [^BEJPQST]|D[EJKMOZ]|E[CEGHRST]|F[IJKMOR]|G[^CJKOVXZ]|H[KMNRTU]|I[DEL-OQ-T]|J[EMOP]|K[EGHIMNPRWYZ]|L[ABCIKR-VY]|M[^BIJ]|N[ACEFGILOPRUZ]|OM|P[AE-HK-NRSTWY]|QA|R[EOSUW]|S[^FPQUW]|T[^ABEIPQSUXY]|U[AGMSYZ]|V[ACEGINU]|WF|WS|YE|YT|Z[AMW]$/i', $playerApp['country']) === 1) {
                $output .= '<meta name="twitter:app:country" content="'.$playerApp['country'].'" />';
            }
        }
        #Add new lines at the end of the tags for a more readable output
        if ($pretty) {
            $output = str_replace('>', '>'."\r\n", $output);
        }
        return $output;
    }
    
    #Helper function to check whether a string is a recognizable URL (not URI!) with HTTPS scheme
    private function isHTTPS(string $url): bool
    {
        $url = parse_url($url);
        if (empty($url['scheme']) || empty($url['host']) || empty($url['path']) || $url['scheme'] !== 'https') {
            return false;
        } else {
            return true;
        }
    }
}
?>