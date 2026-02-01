<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta name="description" content="MAL Exporter">
	<meta name="keywords" content="MAL, MyAnimeList, Export, Scrape, scraper, XML ">
	<meta property="og:title" content="MAL Exporter">
	<meta property="og:description" content="Best third-party tool to Export MyAnimeList anime and manga lists.">
	<meta property="og:image" content="/favicon.png" />
	<meta name="twitter:card" content="summary">
	<title>MAL Exporter</title>
	<style>
		body{
			font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial;
			background: #f7f7f8;
			font-size: 120%
		}
		
		footer{
			font-size: 80%
		}
		
		.wrapper {
			margin: 0;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.form-block {
			padding: 10px;
			border-radius: 8px;
			box-shadow: 0 4px 14px rgba(20,20,30,0.06);
			background: #fff;
		}
		
		.form-block p{
			font-size: 90%;
			max-width: 22em
		}
		
		.form-row {
			padding: 2px;
			display: flex;
			gap: 8px;
			align-items: stretch; /* ensures children stretch to same height */
		}

		/* Use a CSS custom property for control height */
		.form-row {
			--control-height: 44px;
		}

		.control {
			height: var(--control-height);
			display: inline-flex;
			align-items: center;
		}

		/* Inputs/select must fill the flex item */
		.form-row input[type="text"],
		.form-row select,
		.form-row button {
			height: 100%;
			padding: 0 12px;
			border: 1px solid #d0d5dd;
			border-radius: 6px;
			font-size: 14px;
			outline: none;
			background: #fff;
			box-sizing: border-box;
		}

		/* Make input and select expand while button keeps natural width */
		.form-row .grow { flex: 1 1 200px; }

		.form-row button {
			background: #2563eb;
			color: #fff;
			border: none;
			cursor: pointer;
			padding: 0 16px;
			border-radius: 6px;
		}

		.form-row input[type="text"]:focus,
		.form-row select:focus {
			border-color: #7c8cff;
			box-shadow: 0 0 0 3px rgba(124,140,255,0.12);
		}

		@media (max-width: 480px) {
			.form-row {
				flex-direction: column;
				gap: 10px;
				width: calc(100% - 32px);
			}
			.form-row .control { height: var(--control-height); width: 100%; }
			.form-row input[type="text"],
			.form-row select,
			.form-row button { width: 100%; }
		}
		
		/* <!-------- img tooltip ---------> */
		
		.tooltip {
		  text-decoration:none;
		  position:relative;
		  // cursor: pointer;
		}
		
		.tooltip span {
		  display:none;	
		}
		
		.tooltip img {
			width: 100%;
			height: auto
		}
		
		.tooltip:hover span {
		  display:block;
		  position:absolute;
		  top:0;
		  left:0;
		  z-index:1000;
		  width:auto;
		  max-width:480px;
		  min-width:300px;
		  border:1px solid black;
		  margin-top:2.0em;
		  overflow:hidden;
		  padding:0px;
		}
	</style>
</head>
<body>
	<center>
	<h1>MAL Exporter</h1>
	<p>Export MyAnimeList anime/manga lists as XML!</p>
	<div class="wrapper">
		<form class="form-block" action="export.php" method="post">
		<span class="form-row">
			<div class="control grow"><input type="text" name="user" placeholder="Username" /></div>
			<div class="control"><select name="type" aria-label="List Type">
				<option value="malanime">MAL Anime List</option>
				<option value="malmanga">MAL Manga List</option>
			</select></div>
			<div class="control"><button type="submit">Export</button></div>
		</span>
		<span class="form-row">
			<label for="update" style="font-size:90%">Enable <code>update_on_import</code> <input type="checkbox" name="update">
		</span>
		<?php require "export.php"; ?>
		<script>let p = window.location.href; let pi = p.indexOf("?");
				if(pi!==-1){ window.history.pushState({}, "", p.substr(0,pi)); }</script>
	</div>
	<br><br>
	<footer><span style="font-size:110%">🌟 Like it? Star it on <a href="https://github.com/nutzboi/MAL-Exporter">GitHub</a>!</span><br><br><br>
	Looking to export from other platforms (AniList/Kitsu/etc.)?<br>Check out our <span class="tooltip">proprietary<span><img src="proprietary.jpg"></span></span> <a href="https://malscraper.azurewebsites.net" target="_blank">competitor</a> <em>for now</em>.
	</center>
</body>
</html>
