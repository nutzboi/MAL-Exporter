###
 # SPDX-FileCopyrightText: 2026 Nutzboie Funnimanne
 #
 # SPDX-License-Identifier: AGPL-3.0-only
###

import json, sys, re
import requests

username = "user"
update_on_import = False
lstype = 0
lstypestr = "ANIME"

dic = [
    { # anime
        "x": { # xml tag names
            "id": "series_animedb_id",
            "title": "series_title",
            "type": "series_type",
            "seps": "series_episodes",
            # my_id
            "weps": "my_watched_episodes",
            "sdate": "my_start_date",
            "fdate": "my_finish_date",
            # my_rated
            "score": "my_score",
            "store": "my_storage",
            "storev": "my_storage_value",
            "stat": "my_status",
            "notes": "my_comments",
            "times": "my_times_watched",
            # "rewv": "my_rewatch_value",
            "pri": "my_priority",
            "tags": "my_tags",
            "rew": "my_rewatching",
            # my_rewatching_ep
            # my_discuss
            # my_sns
            "updimp": "update_on_import"
            },
        "j": { # json object names
            "id": "idMal",
            "title": "title",
            "type": "format",
            "seps": "episodes",
            "weps": "progress",
            "sdate": "startedAt",
            "fdate": "completedAt",
            "score": "score",
            "stat": "status",
            "notes": "notes",
            "times": "repeat"
        }
    },
    { # manga
        "x": {
            "id": "manga_mangadb_id",
            "title": "manga_title",
            "mvol": "manga_volumes",
            "mch": "manga_chapters",
            "rvol": "my_read_volumes",
            "rch": "my_read_chapters",
            # my_id
            "sdate": "my_start_date",
            "fdate": "my_finish_date",
            # "my_scanalation_group"
            "score": "my_score",
            # "my_storage"
            "ret": "my_retail_volumes",
            "stat": "my_status",
            "notes": "my_comments",
            "times": "my_times_read",
            "tags": "my_tags",
            "pri": "my_priority",
            # "rewv": "my_reread_value",
            "rew": "my_rereading",
            # my_discuss
            # my_sns
            "updimp": "update_on_import"
            },
        "j": {
            "id": "idMal",
            "title": "title",
            "mvol": "volumes",
            "mch": "chapters",
            "rvol": "progressVolumes",
            "rch": "progress",
            "sdate": "startedAt",
            "fdate": "completedAt",
            "score": "score",
            "stat": "status",
            "notes": "notes",
            "times": "repeat"
        }
    }
]

def buildmyinfo(loadjson, lstype, lstypestr):
    global userid, username
    
    st = {
        "CURRENT": ["reading" if lstype else "watching", 1],
        "COMPLETED": ["completed", 2],
        "PAUSED": ["onhold", 3],
        "DROPPED": ["dropped", 4],
        "PLANNING": ["plantoread" if lstype else "plantowatch", 6]
    }
    stats = [0,0,0,0,0,0,0]
    for entry in loadjson:
        stats[st[entry[dic[lstype]["j"]["stat"]]][1]]+=1
        
    info = {
        "user_id": "",
        "user_name": username,
        "user_export_type": lstype+1,
        "user_total_"+lstypestr: len(loadjson),
    }
    for i in st:
        info["user_total_"+st[i][0]] = stats[st[i][1]]
    
    return info

def CD(s):
    s = str(s)
    return "<![CDATA[" + s.replace("]]>", "]]]]><![CDATA[>") + "]]>"

def json_to_xml(loadjson):
    global update_on_import
    global lstype, lstypestr
    
    lstypestr = lstypestr.lower()
    seen_ids = set()
    st = {
        "0": "0",
        "CURRENT": "Reading" if lstype else "Watching",
        "COMPLETED": "Completed",
        "PAUSED": "On-Hold",
        "DROPPED": "Dropped",
        "PLANNING": "Plan to Read" if lstype else "Plan to Watch"
    }
    mediaprops = ["id", "title", "seps", "mch", "mvol", "type"]
    newlist = []

    for jsonentry in loadjson:
        newlist.append({lstypestr: {}});
        newprop = {}
        
        for prop in dic[lstype]["j"]:
            propval = None
            if prop in mediaprops:
                propval = jsonentry["media"][dic[lstype]["j"][prop]]
            else:
                propval = jsonentry[dic[lstype]["j"][prop]]
            # print("propval = " + str(propval))
            # print("prop = " + str(prop))
            if prop == "title" or prop == "notes" or prop == "tags":
                if prop == "title":
                    propval = propval["romaji"]
                if not propval:
                    propval = ""
                newprop = {prop: CD(propval)}
            elif prop == "type":
                anitype = propval
                if anitype == "TV_SHORT":
                    anitype = "TV"
                elif anitype in ["MOVIE", "SPECIAL", "MUSIC"]:
                    anitype = anitype.capitalize()
                newprop = {prop: anitype}
            elif "date" in prop:
                if(not propval["year"]):
                    newprop = {prop: "0000-00-00"}
                else:
                    year = str(propval["year"])
                    month = str(propval["month"]) if propval["month"] else "01"
                    day = str(propval["day"]) if propval["day"] else "01"
                    
                    month = "0" + month if len(month) < 2 else month
                    day = "0" + day if len(day) < 2 else day
                    
                    newprop = {prop: f"{year}-{month}-{day}"}
            elif prop == "stat":
                newprop = {prop: st[propval]}
            elif prop == "id":
                if propval in seen_ids:
                    break
                else:
                    seen_ids.add(propval)
                    newprop = {prop: propval}
            else:
                newprop = {prop: propval}
            
            newlist[-1][lstypestr].update(newprop)
        newlist[-1][lstypestr]["updimp"] = str(int(update_on_import))
    
    xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n\t\t<myanimelist>\n\t\t\n"
    
    xml+= "\t\t\t<myinfo>\n"
    myinfo = buildmyinfo(loadjson, lstype, lstypestr)
    for prop in myinfo:
        xml+= "\t\t\t\t<" + prop + ">" + str(myinfo[prop]) + "</" + prop + ">\n"
    xml+= "\t\t\t</myinfo>\n\t\t\n\t\t\n"
    
    for entry in newlist:
        xml+= "\t\t\t\t<" + lstypestr + ">\n"
        for prop in entry[lstypestr]:
            propval = entry[lstypestr][prop]
            xml+= "\t\t\t\t\t<" + dic[lstype]["x"][prop] + ">" + str(propval) + "</" + dic[lstype]["x"][prop] + ">\n"
        xml+= "\t\t\t\t</" + lstypestr + ">\n\t\t\t\n"
    xml += "\n\t\t</myanimelist>\n"
        
    # print("hi")
    
    return xml


def getdata(user, listtype="ANIME"):
    global username
    gqlreq = { "query": """query  ($username: String, $type: MediaType) {
        User(name: $username) {
            name
        }
        MediaListCollection(userName: $username, type: $type) {
            lists {
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
    }""", "variables": {}}
    gqlreq["variables"]["username"] = user
    gqlreq["variables"]["type"] = listtype
    r = []
    try:
        r = requests.post("https://graphql.anilist.co/", json=gqlreq)
    except:
        print("Failed to make request to AniList. No internet connection?")
        return
    if r.status_code == 200:
        username = r.json()["data"]["User"]["name"]
        loadjson = []
        for i in r.json()["data"]["MediaListCollection"]["lists"]:
            loadjson+=i["entries"]
        return loadjson
    elif r.status_code >= 400 and r.status_code <= 499:
        try:
            print("AniList Error: " + r.json()["errors"][0]["message"])
        except:
            print(f"AniList Error {r.status_code}.")
    elif r.status_code > 0:
        print(f"AniList Error {r.status_code}.")
    else:
        print("Error. AniList API unreachable?")
    return


if __name__ == "__main__":
    outfile = "output.xml"
    update_on_import = ("--update-on-import" in sys.argv) or (len(sys.argv) > 4 and (sys.argv[4].lower() in ["1", "true"]))
    
    if len(sys.argv) >= 3 and ".json" in sys.argv[1]:
        if(len(sys.argv) > 3):
            update_on_import = update_on_import or (sys.argv[3].lower() in ["1", "true"])
        
        with open(sys.argv[1], 'r', encoding="utf-8") as f:
            loadjson = json.load(f)
        
        xml = json_to_xml(loadjson)
        
        outfile = sys.argv[2]
        with open(outfile, 'w', encoding="utf-8") as f:
            f.write(xml)
        
    elif len(sys.argv) >= 2 and sys.argv[1] == re.search("[\w-]{2,20}", sys.argv[1]).group():
        username = sys.argv[1]
        if len(sys.argv) > 2:
            if sys.argv[2].lower() == "manga":
                lstype = 1
                lstypestr = "MANGA"
        loadjson = getdata(username, lstypestr.upper())
        if(not loadjson):
            if type(loadjson) == list:
                print("Empty list.")
            exit()
        
        xml = json_to_xml(loadjson)
        
        if(len(sys.argv) > 3 and "xml" in sys.argv[3]):
            outfile = sys.argv[3]
        else:
            outfile = username+"."+lstypestr.lower()+".al.xml"
        
        with open(outfile, 'w', encoding="utf-8") as f:
            f.write(xml)
        
    else:
        scriptname = sys.argv[0].split('\\')[-1].split('/')[-1]
        print("\nUsage: " + scriptname + " al_username [anime|manga] (output.xml) (--update-on-import)\n"
            + "   OR: " + scriptname + " list.json list.xml (--update-on-import)\n\n"
            + "[values]: argument takes one of the values, (value): optional argument.\n")
        exit()
    
    print(f"Exported to '{outfile}'.")
