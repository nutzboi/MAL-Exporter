# MAL Exporter
[![Install Userscript - GitHub](https://img.shields.io/badge/Install_Userscript-GitHub-2ea44f?logo=github)](https://raw.githubusercontent.com/nutzboi/MAL-Exporter/master/userscript/MAL-Exporter.user.js) [![Install Userscript - GreasyFork](https://img.shields.io/badge/Install_Userscript-GreasyFork-2ea44f?logo=greasyfork)](https://greasyfork.org/en/scripts/563051-mal-list-exporter) [![License](https://img.shields.io/badge/License-AGPL--3.0--only-blue)](https://github.com/nutzboi/MAL-Exporter/blob/master/LICENSE)

Free (as in "freedom") open-source MyAnimeList list exporter.<br>
Web version available now at [malscraper.42web.io](https://malscraper.42web.io).
____
## Current Features:
- Export both anime and manga lists from MyAnimeList as XML
- Seamlessly inserts _Export_ button into list page
- Supports any list you have access to via the userscript
- Parses and converts storage types and values
- Properly handles [CDATA](https://en.wikipedia.org/wiki/CDATA)
- Prompts to enable `update_on_import` in exported list

## Planned Features:
- Support other platforms (AniList, Kitsu, etc.)

## Non-features:
- "Times Rewatched" and "Rewatch value" properties ([Why?](https://github.com/nutzboi/MAL-exporter/tree/master/Why.md#Q-Why-no-rewatches-support))
____
ts is [primarily](https://github.com/nutzboi/MAL-exporter/tree/master/pysrc) written in glorious compilable pseudocode _(A.K.A Python)_ and vibe-converted to j\*vascript with the assistance of cl\*nkers.

Also check out [MAL-Stalker](https://github.com/nutzboi/MAL-name2id).
