<?php

// ---- Config ----
$username = "user";
$update_on_import = false;
$lstype = 0;            // 0 = anime, 1 = manga
$lstypestr = "ANIME";

$dic = [
    [ // anime
        "x" => [
            "id" => "series_animedb_id",
            "title" => "series_title",
            "type" => "series_type",
            "seps" => "series_episodes",
            "weps" => "my_watched_episodes",
            "sdate" => "my_start_date",
            "fdate" => "my_finish_date",
            "score" => "my_score",
            "store" => "my_storage",
            "storev" => "my_storage_value",
            "stat" => "my_status",
            "notes" => "my_comments",
            "times" => "my_times_watched",
            "pri" => "my_priority",
            "tags" => "my_tags",
            "rew" => "my_rewatching",
            "updimp" => "update_on_import",
        ],
        "j" => [
            "id" => "idMal",
            "title" => "title",
            "type" => "format",
            "seps" => "episodes",
            "weps" => "progress",
            "sdate" => "startedAt",
            "fdate" => "completedAt",
            "score" => "score",
            "stat" => "status",
            "notes" => "notes",
            "times" => "repeat",
        ],
    ],
    [ // manga
        "x" => [
            "id" => "manga_mangadb_id",
            "title" => "manga_title",
            "mvol" => "manga_volumes",
            "mch" => "manga_chapters",
            "rvol" => "my_read_volumes",
            "rch" => "my_read_chapters",
            "sdate" => "my_start_date",
            "fdate" => "my_finish_date",
            "score" => "my_score",
            "ret" => "my_retail_volumes",
            "stat" => "my_status",
            "notes" => "my_comments",
            "times" => "my_times_read",
            "tags" => "my_tags",
            "pri" => "my_priority",
            "rew" => "my_rereading",
            "updimp" => "update_on_import",
        ],
        "j" => [
            "id" => "idMal",
            "title" => "title",
            "mvol" => "volumes",
            "mch" => "chapters",
            "rvol" => "progressVolumes",
            "rch" => "progress",
            "sdate" => "startedAt",
            "fdate" => "completedAt",
            "score" => "score",
            "stat" => "status",
            "notes" => "notes",
            "times" => "repeat",
        ],
    ],
];

// Keep globals global as requested
function buildmyinfo($loadjson, $lstype, $lstypestr): array {
    global $username;

    $st = [
        "CURRENT"   => [$lstype ? "reading" : "watching", 1],
        "COMPLETED" => ["completed", 2],
        "PAUSED"    => ["onhold", 3],
        "DROPPED"   => ["dropped", 4],
        "PLANNING"  => [$lstype ? "plantoread" : "plantowatch", 6],
    ];

    $stats = array_fill(0, 7, 0);

    foreach ($loadjson as $entry) {
        global $dic;
        $status = $entry[$dic[$lstype]["j"]["stat"]] ?? null; // e.g. CURRENT/COMPLETED/...
        if ($status !== null && isset($st[$status])) {
            $stats[$st[$status][1]] += 1;
        }
    }

    $info = [
        "user_id" => "",
        "user_name" => $username,
        "user_export_type" => $lstype + 1,
        "user_total_" . $lstypestr => count($loadjson),
    ];

    foreach ($st as $i) {
        $info["user_total_" . $i[0]] = $stats[$i[1]];
    }

    return $info;
}

function CD($s): string {
    $s = strval($s);
    return "<![CDATA[" . str_replace("]]>", "]]]]><![CDATA[>", $s) . "]]>";
}

function json_to_xml($loadjson): string {
    global $update_on_import, $lstype, $lstypestr, $dic;
	
	$lstypestr = strtolower($lstypestr);
    $seen_ids = [];

    $st = [
        "0" => "0",
        "CURRENT" => $lstype ? "Reading" : "Watching",
        "COMPLETED" => "Completed",
        "PAUSED" => "On-Hold",
        "DROPPED" => "Dropped",
        "PLANNING" => $lstype ? "Plan to Read" : "Plan to Watch",
    ];

    $mediaprops = ["id", "title", "seps", "mch", "mvol", "type"];
    $newlist = [];

    foreach ($loadjson as $jsonentry) {
        $newlist[] = [$lstypestr => ["tags" => ""]];
        $newprop = [];

		if (isset($jsonentry["tag"]) && strlen($jsonentry["tag"]) > 0) {
			$tag = str_replace(", ", "，", $jsonentry["tag"]);
			$tag = str_replace(",", "，", $tag);

			$mediaKey = $dic[$lstype]["j"]["id"];
			$mediaId  = $jsonentry["media"][$mediaKey];

			if (isset($seen_ids[$mediaId])) {
				$seenIndex = $seen_ids[$mediaId];

				if (!empty($newlist[$seenIndex][$lstypestr]["tags"])) {
					$newlist[$seenIndex][$lstypestr]["tags"] .= ", ";
				}
				$newlist[$seenIndex][$lstypestr]["tags"] .= $tag;
				continue;
			}

			$newlist[-1][$lstypestr]["tags"] .= $tag;
		}

        foreach ($dic[$lstype]["j"] as $prop => $jsonKey) {
            if (in_array($prop, $mediaprops, true)) {
                $propval = $jsonentry["media"][$dic[$lstype]["j"][$prop]] ?? null; // same as py
            } else {
                $propval = $jsonentry[$dic[$lstype]["j"][$prop]] ?? null;
            }

            if ($prop === "title" || $prop === "notes") {
                if ($prop === "title") {
                    $propval = $propval["romaji"] ?? null;
                }
                if (!$propval) $propval = "";
                $newprop = [$prop => CD($propval)];

            } elseif ($prop === "type") {
                $anitype = $propval;
                if ($anitype === "TV_SHORT") $anitype = "TV";
                elseif (in_array($anitype, ["MOVIE", "SPECIAL", "MUSIC"], true)) $anitype = ucfirst(strtolower($anitype));
                $newprop = [$prop => $anitype];

            } elseif (strpos($prop, "date") !== false) {
                if (!$propval || empty($propval["year"])) {
                    $newprop = [$prop => "0000-00-00"];
                } else {
                    $year = strval($propval["year"]);
                    $month = $propval["month"] ? strval($propval["month"]) : "01";
                    $day = $propval["day"] ? strval($propval["day"]) : "01";

                    if (strlen($month) < 2) $month = "0" . $month;
                    if (strlen($day) < 2) $day = "0" . $day;

                    $newprop = [$prop => "{$year}-{$month}-{$day}"];
                }

            } elseif ($prop === "stat") {
                $newprop = [$prop => $st[$propval] ?? "0"];

            } elseif ($prop === "id") {
                if ($propval !== null && isset($seen_ids[$propval])) {
                    // Python's break: stop processing this jsonentry
                    // We'll just stop this prop-loop and avoid setting updimp for it later by pruning.
                    $newprop = null;
                    break;
                } else {
                    if ($propval !== null) $seen_ids[$propval] = count($newlist)-1;
                    $newprop = [$prop => $propval];
                }

            } else {
                $newprop = [$prop => $propval];
            }

            if ($newprop !== null) {
                $lastIndex = count($newlist) - 1;
                $newlist[$lastIndex][$lstypestr] = array_merge($newlist[$lastIndex][$lstypestr], $newprop);
            }
        }

        $lastIndex = count($newlist) - 1;
        if (isset($newlist[$lastIndex][$lstypestr]) && isset($newlist[$lastIndex][$lstypestr]["id"])) {
            $newlist[$lastIndex][$lstypestr]["updimp"] = strval((int)$update_on_import);
        } else {
            // drop entries where "id" never got set due to duplicate break
            array_pop($newlist);
        }
    }

    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n\t\t<myanimelist>\n\t\t\n";

    $xml .= "\t\t\t<myinfo>\n";
    $myinfo = buildmyinfo($loadjson, $lstype, $lstypestr);
    foreach ($myinfo as $prop => $val) {
        $xml .= "\t\t\t\t<{$prop}>{$val}</{$prop}>\n";
    }
    $xml .= "\t\t\t</myinfo>\n\t\t\n\t\t\n";

    foreach ($newlist as $entry) {
        $xml .= "\t\t\t\t<{$lstypestr}>\n";
        foreach ($entry[$lstypestr] as $prop => $propval) {
			if($prop == "tags"){
				$propval = CD($propval);
			}
            $tag = $dic[$lstype]["x"][$prop];
            $xml .= "\t\t\t\t\t<{$tag}>{$propval}</{$tag}>\n";
        }
        $xml .= "\t\t\t\t</{$lstypestr}>\n\t\t\t\n";
    }

    $xml .= "\n\t\t</myanimelist>\n";
    return $xml;
}

function getdata($user, $listtype = "ANIME") {
    global $username;

    $gqlreq = [
        "query" => "query  (\$username: String, \$type: MediaType) {
        User(name: \$username) {
            name
        }
        MediaListCollection(userName: \$username, type: \$type) {
            lists {
				isCustomList
				name
                entries {
                    status
                    score(format: POINT_10)
                    progress
                    progressVolumes
                    repeat
                    notes
                    startedAt { year, month, day }
                    completedAt { year, month, day }
                    
                    media {
                        idMal
                        title { romaji }
                        episodes
                        chapters
                        volumes
                        format
                    }
                }
            }
        }
    }",
        "variables" => [],
    ];

    $gqlreq["variables"]["username"] = $user;
    $gqlreq["variables"]["type"] = $listtype;

    try {
        $ch = curl_init("https://graphql.anilist.co/");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
            CURLOPT_POSTFIELDS => json_encode($gqlreq),
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    } catch (Throwable $e) {
        echo "Failed to make request to AniList. No internet connection?\n";
        return null;
    }

    if ($code === 200) {
        $data = json_decode($resp, true);
        $username = $data["data"]["User"]["name"];
        $loadjson = [];
        foreach ($data["data"]["MediaListCollection"]["lists"] as $i) {
			if($i["isCustomList"] === true){
				for($j = 0; $j < count($i["entries"]); $j++){
					$i["entries"][$j]["tag"] = $i["name"];
				}
			}
            $loadjson = array_merge($loadjson, $i["entries"] ?? []);
        }
        return $loadjson;
    } elseif ($code >= 400 && $code <= 499) {
        $data = json_decode($resp, true);
        if (isset($data["errors"]) && is_array($data["errors"])) {
            foreach ($data["errors"] as $i) {
                echo "AniList Error: " . ($i["message"] ?? "") . "\n<br>";
            }
        } else {
            echo "AniList Error {$code}.\n";
        }
    } else {
        echo "AniList Error {$code}.\n";
    }

    return null;
}
