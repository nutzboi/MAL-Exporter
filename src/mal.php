<?php
// Converted from provided Python. Requires PHP 7.2+ and ext-json, ext-curl, ext-intl (for date parsing fallback).
// Usage example:
// $list = getdata('username', 'anime');
// echo json_to_xml($list);

$userid = 0;
$username = "user";
$update_on_import = false;

$dic = [
    // anime
    [
        "x" => [
            "id"    => "series_animedb_id",
            "title" => "series_title",
            "type"  => "series_type",
            "seps"  => "series_episodes",
            "weps"  => "my_watched_episodes",
            "sdate" => "my_start_date",
            "fdate" => "my_finish_date",
            "score" => "my_score",
            "store" => "my_storage",
            "storev"=> "my_storage_value",
            "stat"  => "my_status",
            "notes" => "my_comments",
            "pri"   => "my_priority",
            "tags"  => "my_tags",
            "rew"   => "my_rewatching",
            "updimp"=> "update_on_import"
        ],
        "j" => [
            "id"    => "anime_id",
            "title" => "anime_title",
            "type"  => "anime_media_type_string",
            "seps"  => "anime_num_episodes",
            "weps"  => "num_watched_episodes",
            "sdate" => "start_date_string",
            "fdate" => "finish_date_string",
            "score" => "score",
            "store" => "storage_string",
            "stat"  => "status",
            "notes" => "editable_notes",
            "pri"   => "priority_string",
            "tags"  => "tags",
            "rew"   => "is_rewatching"
        ]
    ],
    // manga
    [
        "x" => [
            "id"    => "manga_mangadb_id",
            "title" => "manga_title",
            "mvol"  => "manga_volumes",
            "mch"   => "manga_chapters",
            "rvol"  => "my_read_volumes",
            "rch"   => "my_read_chapters",
            "sdate" => "my_start_date",
            "fdate" => "my_finish_date",
            "score" => "my_score",
            "ret"   => "my_retail_volumes",
            "stat"  => "my_status",
            "notes" => "my_comments",
            "tags"  => "my_tags",
            "pri"   => "my_priority",
            "rew"   => "my_rereading",
            "updimp"=> "update_on_import"
        ],
        "j" => [
            "id"    => "id",
            "title" => "manga_title",
            "mvol"  => "manga_num_volumes",
            "mch"   => "manga_num_chapters",
            "rvol"  => "num_read_volumes",
            "rch"   => "num_read_chapters",
            "sdate" => "start_date_string",
            "fdate" => "finish_date_string",
            "score" => "score",
            "ret"   => "retail_string",
            "stat"  => "status",
            "notes" => "editable_notes",
            "tags"  => "tags",
            "pri"   => "priority_string",
            "rew"   => "is_rereading"
        ]
    ]
];

function parse_store($s) {
    $store = ["store" => "", "storev" => "0.00"];
    $storedic = [
        "RDVD" => "Retail DVD",
        "EHD"  => "External HD",
        "VHS"  => "VHS",
        ""     => "",
        "HD"   => "Hard Drive",
        "NAS"  => "NAS",
        "DVD"  => "DVD / CD"
    ];
    if ($s !== null && $s !== "") {
        $parts = preg_split('/\s+/', $s);
        $key = $parts[0] ?? "";
        $store["store"] = $storedic[$key] ?? "";
        $val = $parts[1] ?? "0";
        $store["storev"] = $val . "0";
    }
    return $store;
}

function buildmyinfo($loadjson, $lstype, $lstypestr) {
    global $userid, $username, $dic;
    // st mapping
    if ($lstype) {
        $st = [
            1 => "reading",
            2 => "completed",
            3 => "onhold",
            4 => "dropped",
            6 => "plantoread"
        ];
    } else {
        $st = [
            1 => "watching",
            2 => "completed",
            3 => "onhold",
            4 => "dropped",
            6 => "plantowatch"
        ];
    }

    $stats = array_fill(0, 7, 0);
    foreach ($loadjson as $entry) {
        $stats[$entry[$dic[$lstype]["j"]["stat"]]]++;
    }
	error_log(json_encode($stats));
    $info = [
        "user_id" => $userid,
        "user_name" => $username,
        "user_export_type" => $lstype + 1,
        "user_total_" . $lstypestr => count($loadjson)
    ];

    foreach ($st as $i => $label) {
        $info["user_total_" . $label] = $stats[$i] ?? 0;
    }

    return $info;
}

function CD($s) {
    $s = (string)$s;
    // escape any occurrences of "]]>" by splitting
    return "<![CDATA[" . str_replace("]]>", "]]]]><![CDATA[>", $s) . "]]>";
}

function parse_date_iso($datestr) {
    if ($datestr === null || $datestr === "") {
        return "0000-00-00";
    }
    // Try to parse using DateTime
    try {
        // Accepts many formats; if ISO-like with timezone, normalize to date portion.
        $dt = DateTime::createFromFormat("m-d-y", $datestr);
        return $dt->format("Y-m-d");
    } catch (Exception $e) {
        // Fallback: return "0000-00-00"
        return "0000-00-00";
    }
}

function json_to_xml($loadjson) {
    global $update_on_import, $dic;
    $lstype = 0;
    $lstypestr = "anime";
    if (!is_array($loadjson) || count($loadjson) === 0) {
        return "";
    }
    // detect manga by presence of "manga_id" in first element (Python used that)
    if (array_key_exists("manga_id", $loadjson[0])) {
        $lstype = 1;
        $lstypestr = "manga";
    }

    $st = $lstype ? [
		0 => "0",
        1 => "Reading",
        2 => "Completed",
        3 => "On Hold",
        4 => "Dropped",
        6 => "Plan to Read"
    ] : [
		0 => "0",
        1 => "Watching",
        2 => "Completed",
        3 => "On Hold",
        4 => "Dropped",
        6 => "Plan to Watch"
    ];

    $newlist = [];

    foreach ($loadjson as $jsonentry) {
        $item = [$lstypestr => []];
        foreach ($dic[$lstype]["j"] as $prop => $jsonKey) {
            $propval = array_key_exists($jsonKey, $jsonentry) ? $jsonentry[$jsonKey] : null;
            if (in_array($prop, ["title","notes","tags"])) {
                $item[$lstypestr][$prop] = CD($propval ?? "");
            } elseif ($prop === "pri") {
                $item[$lstypestr][$prop] = strtoupper((string)($propval ?? ""));
            } elseif (strpos($prop, "date") !== false) {
                if ($propval === null) {
                    $item[$lstypestr][$prop] = "0000-00-00";
                } else {
                    $item[$lstypestr][$prop] = parse_date_iso($propval);
                }
            } elseif ($prop === "store") {
                $item[$lstypestr][$prop] = parse_store($propval ?? "");
            } 
			elseif ($prop == "stat") {
				$item[$lstypestr][$prop] = $st[$propval];
			}else {
                $item[$lstypestr][$prop] = $propval;
            }
        }
        $item[$lstypestr]["updimp"] = (string)(int)$update_on_import;
        $newlist[] = $item;
    }

    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n\t\t<myanimelist>\n\t\t\n";

    $xml .= "\t\t\t<myinfo>\n";
    $myinfo = buildmyinfo($loadjson, $lstype, $lstypestr);
    foreach ($myinfo as $prop => $val) {
        $xml .= "\t\t\t\t<" . $prop . ">" . htmlspecialchars((string)$val, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</" . $prop . ">\n";
    }
    $xml .= "\t\t\t</myinfo>\n\t\t\n\t\t\n";

    foreach ($newlist as $entry) {
        $xml .= "\t\t\t\t<" . $lstypestr . ">\n";
        foreach ($entry[$lstypestr] as $prop => $propval) {
            $tag = $dic[$lstype]["x"][$prop] ?? $prop;
            // propval may be array for store => ["store"=>"...", "storev"=>"..."]
            if (is_array($propval)) {
                foreach ($propval as $subk => $subv) {
                    $sTag = $dic[$lstype]["x"][$prop . ( $subk === "storev" ? "v" : "" )] ?? ($tag . "_" . $subk);
                    $xml .= "\t\t\t\t\t<" . $sTag . ">" . (string)$subv . "</" . $sTag . ">\n";
                }
            } else {
                $xml .= "\t\t\t\t\t<" . $tag . ">" . (string)$propval . "</" . $tag . ">\n";
            }
        }
        $xml .= "\t\t\t\t</" . $lstypestr . ">\n\t\t\t\n";
    }

    $xml .= "\n\t\t</myanimelist>\n";

    return $xml;
}

function curl_get($url, &$http_code = null) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible)'
    ]);
    $body = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $body;
}

function getdata($user, $listtype = "anime") {
    global $userid;
    if(!(preg_match("/[\w-]{2,16}/", $user, $matches) && $matches[0] == $user)){
        echo "MAL usernames must be between 2 and 16 characters; and contain only letters, " .
            "digits, underscores and hyphens." ;
        return null;
    }
    $profileUrl = "https://myanimelist.net/profile/" . rawurlencode($user);
    $html = curl_get($profileUrl, $code);
    if ($code !== 200 || $html === false) {
        echo $code . " User does not exist.\n";
        return null;
    } else {
        // attempt to extract user id
        $needle = 'https://myanimelist.net/modules.php?go=report&amp;type=profile&amp;id=';
        $pos = strpos($html, $needle);
        if ($pos === false) {
            // try alternative with unescaped amp
            $needle2 = 'https://myanimelist.net/modules.php?go=report&type=profile&id=';
            $pos2 = strpos($html, $needle2);
            if ($pos2 !== false) {
                $start = $pos2 + strlen($needle2);
                $end = strpos($html, '"', $start);
                if ($end !== false) {
                    $userid = (int) substr($html, $start, $end - $start);
                } else {
                    // echo "Warning: Failed to extract user id.\n";
                }
            } else {
                // echo "Warning: Failed to extract user id.\n";
            }
        } else {
            $start = $pos + strlen($needle);
            $end = strpos($html, '"', $start);
            if ($end !== false) {
                $userid = (int) substr($html, $start, $end - $start);
            } else {
                // echo "Warning: Failed to extract user id.\n";
            }
        }
    }

    $base = "https://myanimelist.net/{$listtype}list/" . rawurlencode($user) . "/load.json";
    $body = curl_get($base, $code2);
    if ($code2 === 200 && $body !== false) {
        $loadjson = json_decode($body, true);
        if (!is_array($loadjson)) return null;
        $offset = 300;
        while (true) {
            $nextUrl = $base . "?status=7&offset=" . $offset;
            $nextBody = curl_get($nextUrl, $c2);
            if ($c2 !== 200) break;
            $nextjson = json_decode($nextBody, true);
            if (empty($nextjson)) break;
            $loadjson = array_merge($loadjson, $nextjson);
            $offset += 300;
        }
        return $loadjson;
    } else {
        echo "User's " . $listtype . " list is not public.\n<br>";
		echo "Do you have access to it?
		<b>Try the <a href=\"https://greasyfork.org/en/scripts/563051-mal-list-exporter\">userscript</a> instead!\n<br></b>";
        return null;
    }
}
