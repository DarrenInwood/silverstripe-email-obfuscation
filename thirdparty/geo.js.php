/* ----------------------------------------------------------------------------------------
Graceful E-Mail Obfuscation - JavaScript function (decodes e-mail addresses)
Last updated: July 31th, 2007 by Roel Van Gils
---------------------------------------------------------------------------------------- */

window.onload = function () {
	geo();
}

function geo() {
	if (!document.getElementsByTagName) // Check for browser support
			return false;
	if (rot13) // Initiate ROT13 only if needed
		var map = rot13init();
	var tooltip_js_on = '<?php echo urldecode(stripslashes($_GET['tooltip_js_on']))?>';
	var tooltip_js_off = '<?php echo urldecode(stripslashes($_GET['tooltip_js_off']))?>';
	var links = document.getElementsByTagName('a'); // Get all anchors
	function geo_decode(anchor) { // function to recompose the orginal address
		var href = anchor.getAttribute('href');
		var address = href.replace(/.*<?php echo $_GET['folder'] ?>\/\?e=([a-z0-9._%-]+)%2B([a-z0-9._%-]+)%2B([a-z.]+)/i, '$1' + '@' + '$2' + '.' + '$3');
		var linktext = anchor.innerHTML; // IE Fix
		if (href != address) {
			anchor.setAttribute('href','mailto:' + (rot13 ? str_rot13(address,map) : address)); // Add mailto link	
			anchor.innerHTML = linktext; // IE Fix
		}
	}
	for (var l = 0 ; l < links.length ; l++) { // Loop through the anchors
		links[l].onclick = function() { // Encode links when clicked
			geo_decode(this);
		}
		links[l].onmouseover = function() { // Display tooltip when links are hovered
			if (this.getAttribute('title') == tooltip_js_off) { // Set custom tooltip if specified
				this.setAttribute('title',tooltip_js_on);
				geo_decode(this); // Encode links when hovered (so that the address appears correctly in the browser's status bar)
			}
		}
		// Change the links back to actual email addresses onload
		var href = links[l].href;
		var address = href.replace(/.*<?php echo $_GET['folder'] ?>\/\?e=([a-z0-9._%-]+)%2B([a-z0-9._%-]+)%2B([a-z.]+)/i, '$1' + '@' + '$2' + '.' + '$3');
		if (href != address) {
		    links[l].innerHTML = str_rot13(address,map);
		}
	}
}

//var rot13 = <?=$_GET['rot13']?>;
var rot13 = true;

function rot13init() {
	var map = new Array();
	var s = "abcdefghijklmnopqrstuvwxyz";
	for (var i = 0 ; i < s.length ; i++) {
		map[s.charAt(i)] = s.charAt((i+13)%26);
    }
	for (var i = 0 ; i < s.length ; i++)
		map[s.charAt(i).toUpperCase()] = s.charAt((i+13)%26).toUpperCase();
	return map;
}

function str_rot13(a,map) {
	var s = "";
	for (var i = 0 ; i < a.length ; i++) {
		var b = a.charAt(i);
		s += (b>='A' && b<='Z' || b>='a' && b<='z' ? map[b] : b);
	}
	return s;
}
