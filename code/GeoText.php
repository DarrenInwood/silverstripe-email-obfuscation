<?php

class GeoText extends Text {

    function Geo() {
        $geo = new geo();
        $geo->root = 'http://'.$_SERVER['HTTP_HOST'].'/geo/thirdparty/'; // Full server path (include trailing slash)
        $geo->setTooltipNoJS('To reveal this e-mail address, you will need to answer a simple question. '); // When JavaScript is unavailable, this title is added to e-mail links
        $geo->setTooltipJS('Send e-mail'); // When JavaScript is available, tooltip will be replaced by this one
        $geo->setFolder('email');
        Requirements::javascript($geo->dropJS());
        return $geo->prepareOutput('<a href="mailto:'.$this->value.'">'.$this->value.'</a>');
    }

}

