# MAL Exporter
[![Install Userscript - GitHub](https://img.shields.io/badge/Install_Userscript-GitHub-2ea44f?logo=github)](https://raw.githubusercontent.com/nutzboi/MAL-Exporter/master/userscript/MAL-Exporter.user.js) [![Install Userscript - GreasyFork](https://img.shields.io/badge/Install_Userscript-GreasyFork-2ea44f?logo=greasyfork)](https://greasyfork.org/en/scripts/563051-mal-list-exporter) [![License](https://img.shields.io/badge/License-AGPL--3.0--only-blue)](https://github.com/nutzboi/MAL-Exporter/blob/master/LICENSE)

Free (as in "freedom") open-source MyAnimeList list exporter.
____
## Current Features:
- Export both anime and manga lists from MyAnimeList as XML
- Properly handles [CDATA](https://en.wikipedia.org/wiki/CDATA)
- Parses and converts storage types and values
- Supports any list you have access to via the userscript
- Seamlessly inserts _Export_ button into list page

## Planned Features:
- Support `update_on_import` property
- Support other platforms (AniList, Kitsu, etc.)
- Website (in PHP, since MAL doesn't allow CORS)

## Non-features:
- "Times Rewatched" and "Rewatch value" properties ([Why?](https://github.com/nutzboi/MAL-exporter/tree/master/Why.md#Q-Why-no-rewatches-support))
____
ts is [primarily](https://github.com/nutzboi/MAL-exporter/tree/master/pysrc) written in glorious compilable pseudocode _(A.K.A Python)_ and vibe-converted to j\*vascript with the assistance of cl\*nkers.

Also check out [MAL-Stalker](https://github.com/nutzboi/MAL-name2id).
