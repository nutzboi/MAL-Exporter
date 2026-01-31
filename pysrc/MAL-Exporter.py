###
 # SPDX-FileCopyrightText: 2025 Nutzboie Funnimanne
 #
 # SPDX-License-Identifier: AGPL-3.0-only
###

import json, sys, re
import requests
from dateutil import parser

userid = 0
username = "user"
update_on_import = False

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
            # "times": "my_times_watched",
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
            "id": "anime_id",
            "title": "anime_title",
            "type": "anime_media_type_string",
            "seps": "anime_num_episodes",
            "weps": "num_watched_episodes",
            "sdate": "start_date_string",
            "fdate": "finish_date_string",
            "score": "score",
            "store": "storage_string",
            "stat": "status",
            "notes": "editable_notes",
            # "times": ajax-no-auth.inc.php,
            # "rewv": ajax-no-auth.inc.php,
            "pri": "priority_string",
            "tags": "tags",
            "rew": "is_rewatching"
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
            # "times": "my_times_read",
            "tags": "my_tags",
            "pri": "my_priority",
            # "rewv": "my_reread_value",
            "rew": "my_rereading",
            # my_discuss
            # my_sns
            "updimp": "update_on_import"
            },
        "j": {
            "id": "id",
            "title": "manga_title",
            "mvol": "manga_num_volumes",
            "mch": "manga_num_chapters",
            "rvol": "num_read_volumes",
            "rch": "num_read_chapters",
            "sdate": "start_date_string",
            "fdate": "finish_date_string",
            "score": "score",
            # "store": ajax-no-auth.inc.php,
            "ret": "retail_string",
            "stat": "status",
            "notes": "editable_notes",
            # "times": ajax-no-auth.inc.php,
            "tags": "tags",
            "pri": "priority_string",
            # "rewv": ajax-no-auth.inc.php,
            "rew": "is_rereading"
        }
    }
]

def parse_store(s):
    store = {"store": "", "storev": "0.00"}
    storedic = {
        "RDVD": "Retail DVD",
        "EHD": "External HD",
        "VHS": "VHS",
        "": "",
        "HD":"Hard Drive",
        "NAS": "NAS",
        "DVD": "DVD / CD"
    }
    if s:
        s = s.split(" ")
        store["store"] = storedic[s[0]]
        store["storev"] = s[1]+"0"
    return store

def buildmyinfo(loadjson, lstype, lstypestr):
    global userid, username
    
    st = {
        1: "reading" if lstype else "watching",
        2: "completed",
        3: "onhold",
        4: "dropped",
        6: "plantoread" if lstype else "plantowatch"
    }
    stats = [0,0,0,0,0,0,0]
    for entry in loadjson:
        stats[entry[dic[lstype]["j"]["stat"]]]+=1
        
    info = {
        "user_id": userid,
        "user_name": username,
        "user_export_type": lstype+1,
        "user_total_"+lstypestr: len(loadjson),
    }
    for i in st:
        info["user_total_"+st[i]] = stats[i]
    
    return info

def CD(s):
    s = str(s)
    return "<![CDATA[" + s.replace("]]>", "]]]]><![CDATA[>") + "]]>"

def json_to_xml(loadjson):
    global update_on_import
    lstype = 0
    lstypestr = "anime"
    if "manga_id" in loadjson[0]:
        lstype = 1 # manga
        lstypestr = "manga"
    
    st = {
        0: "0",
        1: "Reading" if lstype else "Watching",
        2: "Completed",
        3: "On Hold",
        4: "Dropped",
        6: "Plan to Read" if lstype else "Plan to Watch"
    }
    
    newlist = []

    for jsonentry in loadjson:
        newlist.append({lstypestr: {}});
        newprop = {}
        
        for prop in dic[lstype]["j"]:
            propval = jsonentry[dic[lstype]["j"][prop]]
            # print("propval = " + str(propval))
            # print("prop = " + str(prop))
            if prop == "title" or prop == "notes" or prop == "tags":
                newprop = {prop: CD(propval)}
            elif prop == "pri":
                newprop = {prop: propval.upper()}
            elif "date" in prop:
                if(propval == None):
                    newprop = {prop: "0000-00-00"}
                else:
                    newprop = {prop: parser.parse(propval).isoformat().split("T")[0]}
            elif prop == "store":
                newprop = parse_store(propval)
            elif prop == "stat":
                newprop = {prop: st[propval]}
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

def getdata(user, listtype="anime"):
    global userid
    r = requests.get("https://myanimelist.net/profile/"+user)
    if(r.status_code != 200):
        print(str(r.status_code) + "User does not exist.")
        return
    else:
        try:
            startpos = r.text.index("https://myanimelist.net/modules.php?go=report&amp;type=profile&amp;id=")
            endpos = r.text.index("\"", startpos)
            userid = int(r.text[startpos+70:endpos])
        except:
            print("Warning: Failed to extract user id.")
    
    
    r = requests.get("https://myanimelist.net/"+listtype+"list/"+user+"/load.json");
    if r.status_code == 200:
        loadjson = r.json()
        offset = 300
        while True:
            r = requests.get("https://myanimelist.net/"+listtype+
                "list/"+user+"/load.json?offset="+str(offset))
            nextjson = r.json()
            if(not nextjson):
                break # reached end of list.
            loadjson+=nextjson
            offset+=300
    else:
        print("User's " + listtype + " list is not public.")
        return
    return loadjson
    
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
        
    elif len(sys.argv) >= 2 and sys.argv[1] == re.search("[\w-]{2,16}", sys.argv[1]).group():
        username = sys.argv[1]
        loadjson = getdata(username, sys.argv[2] if len(sys.argv) > 2 else "anime")
        if(not loadjson):
            exit()
        
        xml = json_to_xml(loadjson)
        
        if(len(sys.argv) > 3 and "xml" in sys.argv[3]):
            outfile = sys.argv[3]
        else:
            outfile = username+".xml"
        
        with open(outfile, 'w', encoding="utf-8") as f:
            f.write(xml)
        
    else:
        scriptname = sys.argv[0].split('\\')[-1].split('/')[-1]
        print("\nUsage: " + scriptname + " mal_username [anime|manga] (output.xml) (--update-on-import)\n"
            + "   OR: " + scriptname + " list.json list.xml (--update-on-import)\n\n"
            + "[values]: argument takes one of the values, (value): optional argument.\n")
        exit()
    
    print(f"Exported to '{outfile}'.")
