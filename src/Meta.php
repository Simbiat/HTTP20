<?php
declare(strict_types = 1);

namespace Simbiat\http20;

use function in_array, is_string;

/**
 * Functions, that generate sets of meta-tags, that may be useful for your website.
 */
class Meta
{
    /**
     * Function to prepare Twitter card as per https://developer.twitter.com/en/docs/twitter-for-websites/cards
     * @param array $general    Array of general settings used by cards
     * @param array $player_app Array of values used for cards with types `app` or `player`
     * @param bool  $pretty     If set to `true` will add new line to the end of each `meta` tag
     *
     * @return string
     */
    public static function twitter(array $general, array $player_app = [], bool $pretty = false): string
    {
        #Check that settings are not an empty array
        if (empty($general)) {
            \trigger_error('Empty array of general settings was provided for Twitter card');
            return '';
        }
        #Title is mandatory
        if (empty($general['title'])) {
            \trigger_error('Empty title was provided for Twitter card');
            return '';
        }
        #If card type is not set or is unsupported - use 'summary'
        if (empty($general['card']) || !in_array($general['card'], ['summary', 'summary_large_image', 'app', 'player'])) {
            $general['card'] = 'summary';
        }
        #Add main meta tag
        $output = '<meta name="twitter:card" content="'.\htmlspecialchars($general['card'], \ENT_QUOTES | \ENT_SUBSTITUTE).'" />';
        #Add title
        $output .= '<meta name="twitter:title" content="'.mb_substr(\htmlspecialchars($general['title'], \ENT_QUOTES | \ENT_SUBSTITUTE), 0, 70, 'UTF-8').'" />';
        #Add site if not empty and valid
        if (!empty($general['site']) && \preg_match('/^@?(\w){4,15}$/', $general['site']) === 1 && \preg_match('/^.*(twitter|admin).*$/i', $general['site']) === 0) {
            $output .= '<meta name="twitter:site" content="'.(str_starts_with($general['site'], '@') ? '' : '@').$general['site'].'" />';
        }
        #Add site:id if not empty and valid
        if (!empty($general['site:id']) && \preg_match('/^\d+$/', $general['site:id']) === 1) {
            $output .= '<meta name="twitter:site:id" content="'.$general['site:id'].'" />';
        }
        #Add creator if not empty and valid
        if (!empty($general['creator']) && \preg_match('/^@?(\w){4,15}$/', $general['creator']) === 1 && \preg_match('/^.*(twitter|admin).*$/i', $general['creator']) === 0) {
            $output .= '<meta name="twitter:creator" content="'.(str_starts_with($general['creator'], '@') ? '' : '@').$general['creator'].'" />';
        }
        #Add creator:id if not empty and valid
        if (!empty($general['creator:id']) && \preg_match('/^\d+$/', $general['creator:id']) === 1) {
            $output .= '<meta name="twitter:creator:id" content="'.$general['creator:id'].'" />';
        }
        #Add description if not empty
        if (!empty($general['description'])) {
            $output .= '<meta name="twitter:description" content="'.mb_substr(\htmlspecialchars($general['description'], \ENT_QUOTES | \ENT_SUBSTITUTE), 0, 200, 'UTF-8').'" />';
        }
        #Add images' tags, that are not use with 'app' cards
        if ($general['card'] !== 'app') {
            #Add only if URL looks like a valid URL for a supported image
            if (!empty($general['image']) && \preg_match('/\.(jpg|png|webp|gif)(\?.*)?$/i', $general['image']) === 1) {
                $output .= '<meta name="twitter:image" content="'.$general['image'].'" />';
            }
            #Add image description
            if (!empty($general['image:alt'])) {
                $output .= '<meta name="twitter:image:alt" content="'.mb_substr(\htmlspecialchars($general['image:alt'], \ENT_QUOTES | \ENT_SUBSTITUTE), 0, 420, 'UTF-8').'" />';
            }
        }
        #Process player tags
        if ($general['card'] === 'player') {
            #Check that mandatory fields are present as per https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/player-card
            if (empty($player_app['player']) || empty($player_app['width']) || empty($player_app['height']) || \preg_match('/twitter:site/i', $output) !== 1 || \preg_match('/twitter:image/i', $output) !== 1 || \preg_match('/^\d+$/', $player_app['width']) !== 1 || \preg_match('/^\d+$/', $player_app['height']) !== 1) {
                #Do not process if, since will be invalidated by Twitter either way
                \trigger_error('One or more Twitter player card parameter is missing or incorrect');
                return '';
            }
            #Add player URL
            $output .= '<meta name="twitter:player" content="'.$player_app['player'].'" />';
            #Add width and height
            $output .= '<meta name="twitter:player:width" content="'.$player_app['width'].'" />';
            $output .= '<meta name="twitter:player:height" content="'.$player_app['height'].'" />';
            #Add stream
            if (!empty($player_app['stream'])) {
                $output .= '<meta name="twitter:player:stream" content="'.$player_app['stream'].'" />';
            }
        } elseif ($general['card'] === 'app') {
            #Check that mandatory fields are present as per https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/app-card
            if (empty($player_app['id:iphone']) || empty($player_app['id:ipad']) || empty($player_app['id:googleplay']) || \preg_match('/twitter:site/i', $output) !== 1 || \preg_match('/^\d+$/', $player_app['id:iphone']) !== 1 || \preg_match('/^\d+$/', $player_app['id:ipad']) !== 1 || \preg_match('/^\d+$/', $player_app['id:googleplay']) !== 1) {
                #Do not process if, since will be invalidated by Twitter either way
                \trigger_error('One or more Twitter app card parameter is missing or incorrect');
                return '';
            }
            #Add IDs
            $output .= '<meta name="twitter:app:id:ipad" content="'.$player_app['id:ipad'].'" />';
            $output .= '<meta name="twitter:app:id:iphone" content="'.$player_app['id:iphone'].'" />';
            $output .= '<meta name="twitter:app:id:googleplay" content="'.$player_app['id:googleplay'].'" />';
            #Add custom schemes
            if (!empty($player_app['url:ipad']) && \preg_match('/^(?<scheme>[a-z][a-z0-9+.-]+):\/\//i', $player_app['url:ipad']) === 1) {
                $output .= '<meta name="twitter:app:url:ipad" content="'.$player_app['url:ipad'].'" />';
            }
            if (!empty($player_app['url:iphone']) && \preg_match('/^(?<scheme>[a-z][a-z0-9+.-]+):\/\//i', $player_app['url:iphone']) === 1) {
                $output .= '<meta name="twitter:app:url:iphone" content="'.$player_app['url:iphone'].'" />';
            }
            if (!empty($player_app['url:googleplay']) && \preg_match('/^(?<scheme>[a-z][a-z0-9+.-]+):\/\//i', $player_app['url:googleplay']) === 1) {
                $output .= '<meta name="twitter:app:url:googleplay" content="'.$player_app['url:googleplay'].'" />';
            }
            #Add country code
            if (!empty($player_app['country']) && \preg_match('/^A[^ABCHJKNPVY]|B[^CKPUX]|C[^BEJPQST]|D[EJKMOZ]|E[CEGHRST]|F[IJKMOR]|G[^CJKOVXZ]|H[KMNRTU]|I[DEL-OQ-T]|J[EMOP]|K[EGHIMNPRWYZ]|L[ABCIKR-VY]|M[^BIJ]|N[ACEFGILOPRUZ]|OM|P[AE-HK-NRSTWY]|QA|R[EOSUW]|S[^FPQUW]|T[^ABEIPQSUXY]|U[AGMSYZ]|V[ACEGINU]|WF|WS|YE|YT|Z[AMW]$/i', $player_app['country']) === 1) {
                $output .= '<meta name="twitter:app:country" content="'.$player_app['country'].'" />';
            }
        }
        #Add new lines at the end of the tags for a more readable output
        if ($pretty) {
            $output = \str_replace('>', '>'."\r\n", $output);
        }
        return $output;
    }
    
    /**
     * Function to generate either set of meta tags for Microsoft [Live] Tile (for pinned sites) or XML file for appropriate config file.
     * Meta specification: https://docs.microsoft.com/en-us/previous-versions/windows/internet-explorer/ie-developer/platform-apis/dn255024(v=vs.85)
     * `browserconfig` specification: https://docs.microsoft.com/en-us/previous-versions/windows/internet-explorer/ie-developer/platform-apis/dn320426(v=vs.85)
     *
     * @param array $general       Array of basic settings
     * @param array $tasks         Array of so-called "tasks", that appear as pinned links, if pinned from IE
     * @param array $notifications Array of settings for notifications, URLs referencing XML files formatted as per https://docs.microsoft.com/en-us/uwp/schemas/tiles/tilesschema/schema-root
     * @param bool  $xml           Whether to generate XML
     * @param bool  $pretty_direct If `$xml` is `false` this setting will govern whether a new line is added after each `<meta>` tag. If `$xml` is `true`, this setting will govern whether function will return a string or output the XML directly to browser.
     *
     * @return string
     */
    public static function msTile(array $general, array $tasks = [], array $notifications = [], bool $xml = false, bool $pretty_direct = true): string
    {
        #Check that settings are not an empty array
        if (empty($general)) {
            \trigger_error('Empty array of general settings was provided for Microsoft Tile');
            return '';
        }
        #Check name
        if (empty($general['name']) && !$xml) {
            #While name (application-name) is replaced by page title by default, this may not be a good idea, if some "random" page is pinned by user. You do want to have at least some control of how your website is presented on user's system.
            \trigger_error('Empty name was provided for Microsoft Tile');
            return '';
        }
        #If tooltip is not provided, use the name
        if (empty($general['tooltip'])) {
            $general['tooltip'] = $general['name'];
        }
        #If starturl is not provided set it to current host.
        if (empty($general['starturl'])) {
            $general['starturl'] = (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].($_SERVER['SERVER_PORT'] !== 443 ? ':'.$_SERVER['SERVER_PORT'] : '');
        }
        #If window is not provided set or incorrect
        if (empty($general['window']) || \preg_match('/^(width=([89]|[1-9]\d+)\d{2};\s*height=([89]|[1-9]\d+)\d{2})|(height=([89]|[1-9]\d+)\d{2};\s*width=([89]|[1-9]\d+)\d{2})$/i', $general['window']) === 0) {
            $general['window'] = 'width=800;height=600';
        }
        #If allowDomainApiCalls is not set, set it to true by default
        if (isset($general['allowDomainApiCalls'])) {
            $general['allowDomainApiCalls'] = (bool)$general['allowDomainApiCalls'];
            $general['allowDomainApiCalls'] = ($general['allowDomainApiCalls'] ? 'true' : 'false');
        } else {
            $general['allowDomainApiCalls'] = 'true';
        }
        #If allowDomainApiCalls is not set, set it to true by default
        if (isset($general['allowDomainMetaTags'])) {
            $general['allowDomainMetaTags'] = (bool)$general['allowDomainMetaTags'];
            $general['allowDomainMetaTags'] = ($general['allowDomainMetaTags'] ? 'true' : 'false');
        } else {
            $general['allowDomainMetaTags'] = 'true';
        }
        #Validate URI for badge (https://docs.microsoft.com/en-us/uwp/schemas/tiles/badgeschema/schema-root) and remove it if it's invalid
        if (empty($general['badge']) || \preg_match('/^(frequency=(30|60|360|720|1440);\s*)?(polling-uri=)(https:\/\/.*)$/i', $general['badge']) !== 1) {
            unset($general['badge']);
            #Enforce explicit frequency, if it's not set
        } elseif (\preg_match('/^frequency=(30|60|360|720|1440);.*$/i', $general['badge']) !== 1) {
            $general['badge'] = 'frequency=1440; '.$general['badge'];
        }
        #Validate colors
        if (empty($general['navbutton-color']) || \preg_match('/^#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/', $general['navbutton-color']) !== 1) {
            unset($general['navbutton-color']);
        }
        #Technically TileColor supports named colors, but forcing only Hex values makes it consistent with navbutton-color
        if (empty($general['TileColor']) || \preg_match('/^#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/', $general['TileColor']) !== 1) {
            unset($general['TileColor']);
        }
        #Validate images
        if (empty($general['square150x150logo']) || \preg_match('/\.(jpg|png|gif)(\?.*)?$/i', $general['square150x150logo']) !== 1) {
            unset($general['square150x150logo']);
        }
        if (empty($general['square310x310logo']) || \preg_match('/\.(jpg|png|gif)(\?.*)?$/i', $general['square310x310logo']) !== 1) {
            unset($general['square310x310logo']);
        }
        if (empty($general['square70x70logo']) || \preg_match('/\.(jpg|png|gif)(\?.*)?$/i', $general['square70x70logo']) !== 1) {
            unset($general['square70x70logo']);
        }
        if (empty($general['wide310x150logo']) || \preg_match('/\.(jpg|png|gif)(\?.*)?$/i', $general['wide310x150logo']) !== 1) {
            unset($general['wide310x150logo']);
        }
        if (empty($general['TileImage']) || \preg_match('/\.(jpg|png|gif)(\?.*)?$/i', $general['TileImage']) !== 1) {
            unset($general['TileImage']);
        }
        #Prepare notification value (should lead to XML formatted like https://docs.microsoft.com/en-us/uwp/schemas/tiles/tilesschema/schema-root)
        if (!empty($notifications)) {
            #Count elements (links)
            $links = \count($notifications);
            #Exclude frequency
            if (isset($notifications['frequency'])) {
                $links--;
            }
            #Exclude cycle
            if (isset($notifications['cycle'])) {
                $links--;
            }
            if ($links > 0) {
                if ($links > 5) {
                    $links = 0;
                    foreach ($notifications as $key => $value) {
                        #Skip frequency and cycle
                        if (!in_array($key, ['frequency', 'cycle'])) {
                            #Remove any extra links
                            if ($links > 5) {
                                unset($notifications[$key]);
                            } else {
                                $links++;
                            }
                        }
                    }
                }
                #Sanitize frequency
                if (empty($notifications['frequency']) || \preg_match('/^(30|60|360|720|1440)$/', $notifications['frequency']) !== 1) {
                    $notifications['frequency'] = 1440;
                }
                #Sanitize cycle
                if (empty($notifications['cycle']) || \preg_match('/^[0-7]$/', $notifications['cycle']) !== 1) {
                    if ($links > 1) {
                        $notifications['cycle'] = 1;
                    } else {
                        $notifications['cycle'] = 0;
                    }
                }
            } else {
                #We do not have any links left (if we even had any)
                $notifications = [];
            }
        }
        #Prepare tasks
        if (!empty($tasks)) {
            #Start counter for valid tasks
            $tasks_count = 0;
            foreach ($tasks as $key => $task) {
                #Skip separators
                if ($task !== 'separator') {
                    #Validate settings
                    if (\is_array($task) && !empty($task['name']) && is_string($task['name']) &&
                        !empty($task['action-uri']) && is_string($task['action-uri']) &&
                        !empty($task['icon-uri']) && is_string($task['icon-uri']) && \preg_match('/\.(jpg|png|gif|ico)(\?.*)?$/i', $task['icon-uri']) === 1
                    ) {
                        $tasks_count++;
                        if (empty($task['window-type']) || !in_array($task['window-type'], ['tab', 'self', 'window'])) {
                            $tasks[$key]['window-type'] = 'tab';
                        }
                    } else {
                        #Remove if not an array
                        unset($tasks[$key]);
                    }
                }
            }
            #If there are no valid tasks, unset the array
            if ($tasks_count === 0) {
                $tasks = [];
            } else {
                #Reset indexes for future use
                $tasks = \array_values($tasks);
            }
        }
        #Generate output
        #msapplication-config
        if ($xml) {
            #Open XML
            $output = '<?xml version="1.0" encoding="utf-8"?><browserconfig><msapplication>';
            #Add tile settings, if any are set
            if (!empty($general['square70x70logo']) || !empty($general['square150x150logo']) || !empty($general['wide310x150logo']) || !empty($general['square310x310logo']) || !empty($general['TileImage']) || !empty($general['TileColor'])) {
                $output .= '<tile>';
                if (!empty($general['square70x70logo'])) {
                    $output .= '<square70x70logo src="'.\htmlspecialchars($general['square70x70logo'], \ENT_QUOTES | \ENT_SUBSTITUTE).'"/>';
                }
                if (!empty($general['square150x150logo'])) {
                    $output .= '<square150x150logo src="'.\htmlspecialchars($general['square150x150logo'], \ENT_QUOTES | \ENT_SUBSTITUTE).'"/>';
                }
                if (!empty($general['wide310x150logo'])) {
                    $output .= '<wide310x150logo src="'.\htmlspecialchars($general['wide310x150logo'], \ENT_QUOTES | \ENT_SUBSTITUTE).'"/>';
                }
                if (!empty($general['square310x310logo'])) {
                    $output .= '<square310x310logo src="'.\htmlspecialchars($general['square310x310logo'], \ENT_QUOTES | \ENT_SUBSTITUTE).'"/>';
                }
                if (!empty($general['TileImage'])) {
                    $output .= '<TileImage src="'.\htmlspecialchars($general['TileImage'], \ENT_QUOTES | \ENT_SUBSTITUTE).'"/>';
                }
                if (!empty($general['TileColor'])) {
                    $output .= '<TileColor>'.$general['TileColor'].'</TileColor>';
                }
                $output .= '</tile>';
            }
            #Add badge if set
            if (!empty($general['badge'])) {
                $output .= '<badge><polling-uri src="'.\preg_replace('/^(frequency=(30|60|360|720|1440);\s*)?(polling-uri=)(https:\/\/.*)$/i', $general['badge'], '$4').'"/><frequency>'.\preg_replace('/^(frequency=(30|60|360|720|1440);\s*)?(polling-uri=)(https:\/\/.*)$/i', $general['badge'], '$2').'</frequency></badge>';
            }
            #Add notifications if set
            if (!empty($notifications)) {
                $output .= '<notification><frequency>'.$notifications['frequency'].'</frequency><cycle>'.$notifications['cycle'].'</cycle>';
                unset($notifications['frequency'], $notifications['cycle']);
                #Reset keys just in case
                $notifications = \array_values($notifications);
                #Add URLs
                foreach ($notifications as $key => $value) {
                    $output .= '<polling-uri'.($key !== 0 ? $key : '').' src="'.\htmlspecialchars($value, \ENT_QUOTES | \ENT_SUBSTITUTE).'"/>';
                }
                $output .= '</notification>';
            }
            #Close XML
            $output .= '</msapplication></browserconfig>';
            #Output directly to client if parameter is true
            if ($pretty_direct) {
                if (!\headers_sent()) {
                    \header('Content-Type: text/xml; charset=utf-8');
                }
                Common::zEcho($output);
            }
        } else {
            $output = '';
            #Iterate through settings adding them to the output
            foreach ($general as $setting => $value) {
                #'name' is the only special case, since it uses 'application' prefix, not 'msapplication'
                if ($setting === 'name') {
                    $output .= '<meta name="application-name" content="'.\htmlspecialchars($value, \ENT_QUOTES | \ENT_SUBSTITUTE).'" />';
                } else {
                    $output .= '<meta name="msapplication-'.$setting.'" content="'.\htmlspecialchars($value, \ENT_QUOTES | \ENT_SUBSTITUTE).'" />';
                }
            }
            #Add notifications if set
            if (!empty($notifications)) {
                $output .= '<meta name="msapplication-notification" content="frequency='.$notifications['frequency'].';cycle='.$notifications['cycle'].';';
                unset($notifications['frequency'], $notifications['cycle']);
                #Reset keys just in case
                $notifications = \array_values($notifications);
                #Add URLs
                foreach ($notifications as $key => $value) {
                    $output .= 'polling-uri'.($key !== 0 ? $key : '').'='.\htmlspecialchars($value, \ENT_QUOTES | \ENT_SUBSTITUTE).';';
                }
                #Close the tag
                $output .= '" />';
            }
            #Add tasks if set (seems to be used only by IE11 at the time of writing)
            if (!empty($tasks)) {
                foreach ($tasks as $key => $task) {
                    if ($task === 'separator') {
                        $output .= '<meta name="msapplication-task-separator" content="'.$key.'" />';
                    } else {
                        $output .= '<meta name="msapplication-task" content="name='.\htmlspecialchars($task['name'], \ENT_QUOTES | \ENT_SUBSTITUTE).'; action-uri='.\htmlspecialchars($task['action-uri'], \ENT_QUOTES | \ENT_SUBSTITUTE).'; icon-uri='.\htmlspecialchars($task['icon-uri'], \ENT_QUOTES | \ENT_SUBSTITUTE).'; window-type='.$task['window-type'].'" />';
                    }
                }
            }
            #Add new lines at the end of the tags for a more readable output
            if ($pretty_direct) {
                $output = \str_replace('>', '>'."\r\n", $output);
            }
        }
        return $output;
    }
    
    /**
     * Function to generate Facebook special meta-tags
     * @param int   $app_id Facebook appID
     * @param array $admins List of Facebook admin IDs
     *
     * @return string
     */
    public static function facebook(int $app_id, array $admins = []): string
    {
        #Add appId tag
        $output = '<meta property="fb:app_id" content="'.$app_id.'"/>';
        #Check the values of admins' IDs
        foreach ($admins as $key => $admin) {
            if (\is_numeric($admin)) {
                #Convert to int
                $admins[$key] = (int)$admin;
            } else {
                #Remove value
                unset($admins[$key]);
            }
        }
        #Add admins, if any
        if (!empty($admins)) {
            $output .= '<meta property="fb:admins" content="'.\implode(',', $admins).'"/>';
        }
        return $output;
    }
}
