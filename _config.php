<?php

/*******************************************************************************
 * Graceful Email Obfuscation
 * 
 * A SilverStripe implementation of Roel Van Gils' anti-email-theft technique.
 * http://www.alistapart.com/articles/gracefulemailobfuscation/
 * 
 * Optionally uses the Mollom module:
 * http://www.silverstripe.org/mollom-module
 * ...which in turn requires the Spam Protection module:
 * http://silverstripe.org/spam-protection-module/
 * 
 * To use, add the following line to your _config.php file:
 * GeoScannable::init();
 * 
 * You can now use $Content.Obfuscate in your templates to obfuscate any email 
 * links your content may contain.
 * If you have a field containing a single email address, you can use either 
 * $Emailfield.Mailto to output the link as a linked mailto: anchor tag, or
 * $Emailfield.MailtoAndObfuscate to output the link as a Geo obfuscated mailto:
 * anchor tag.
 *******************************************************************************/

