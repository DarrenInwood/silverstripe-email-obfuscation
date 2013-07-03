<?php

class GeoScannable extends DataObjectDecorator {

    public static function init() {
        Object::add_extension('Text', 'GeoScannable');
        Object::add_extension('HTMLText', 'GeoScannable');
        Object::add_extension('Varchar', 'GeoScannable');
    }

    public function getMailto($string=null) {
        if ( $string === null ) $string = $this->owner->value;
        return '<a href="mailto:'.$string.'">'.$string.'</a>';
    }

    public function getObfuscate($string=null) {
        if ( $string === null ) $string = $this->owner->value;    
        $geo = new geo();
        $geo->root = 'http://'.$_SERVER['HTTP_HOST'].'/geo/thirdparty/'; // Full server path (include trailing slash)
        $geo->setTooltipNoJS('To reveal this e-mail address, you will need to answer a simple question. '); // When JavaScript is unavailable, this title is added to e-mail links
        $geo->setTooltipJS('Send e-mail'); // When JavaScript is available, tooltip will be replaced by this one
        $geo->setFolder('email');
        Requirements::javascript($geo->dropJS());
        return $geo->prepareOutput($string);
    }

    public function getMailtoAndObfuscate() {
        $string = $this->owner->value;
        $string = $this->getMailto($string);
        $string = $this->getObfuscate($string);
        return $string;
    }

}

