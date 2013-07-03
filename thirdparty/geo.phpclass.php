<?php
/* ----------------------------------------------------------------------------------------
Graceful E-Mail Obfuscation - PHP class (encodes e-mail addresses)
Last updated: July 31th, 2007 by Roel Van Gils
---------------------------------------------------------------------------------------- */

class geo {
	var $buffer;
	var $folder = "contact"; // Name of virtual folder (should be the same in your .htaccess file)
	var $tooltip_js_on; // When JavaScript is enabled, this tooltip is added to mail links (client side)
	var $tooltip_js_off; // When JavaScript is unavailable, this tooltip is added to mail links (server side)

	function go() { ob_start(array(&$this, "prepareOutput"));	}

	function prepareOutput($output) { // Replaces e-mail links with user defined URL patterns and insert JavaScript reference
		$parsed = preg_replace_callback(
		    "/[\"\']mailto:([A-Za-z0-9._%-]+)\@([A-Za-z0-9._%-]+)\.([A-Za-z.]{2,4})[\"\'\?]/", 
		    array('geo','replace_rot13'), 
		    $output
		);
		$parsed = preg_replace("/([A-Za-z0-9._%-]+)\@/e", "substr('\\1',0,-3).'...@'", $parsed); // To be sure, truncate e-mail addresses that are *not* linked (bill.ga...@microsoft.com)
        return $parsed;
	}

    function replace_rot13($matches) {
        return '"'.$this->folder.'/?e='.str_rot13($matches[1]).urlencode('+').str_rot13($matches[2]).urlencode('+').str_rot13($matches[3]).'" rel="nofollow" title="'.$this->tooltip_js_off.'"';
    }

	function dropJS() { // Prepares reference to external JavaScript (required for 'decoding' email addresses)
	    return $this->root . "geo.js.php?folder=" . urlencode($this->folder) . "&amp;tooltip_js_on=" . urlencode($this->tooltip_js_on) . "&amp;tooltip_js_off=" . urlencode($this->tooltip_js_off);
	}
	
	function setTooltipJS($tooltip) {
		$this->tooltip_js_on = $tooltip;
	}
	function setTooltipNoJS($tooltip) {
		$this->tooltip_js_off = $tooltip;
	}
	function setFolder($folder) {
		$this->folder = $folder;
	}

    function decodeOutput($encoded) {
        $decoded = $encoded;
        $decoded = explode('+', $decoded);
        $decoded = strip_tags(
            str_rot13($decoded[0]) 
            . '@' 
            . str_rot13($decoded[1]) 
            . 
            '.' 
            . str_rot13($decoded[2])
        );
        return $decoded;
    }

}

