# Q&A
### Q: Why no rewatches support?
#### A: Because there is no convenient way to fetch rewatch info of multiple entries at a time. That info is not present in the `load.json` and for each entry that you click MAL specifically requests its rewatch data from `ajax-no-auth.inc.php` which would easily get you rate-limited if automated.<br>If you do know a way to do so, please [open an issue](https://github.com/nutzboi/MAL-Exporter/issues/new/).
