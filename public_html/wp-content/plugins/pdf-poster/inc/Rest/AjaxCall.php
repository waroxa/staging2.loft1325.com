<?php

namespace PDFPro\Rest;

class AjaxCall
{
    protected static $_instance = null;

    public function __construct() {}

    /**
     * Create Instance
     */
    public static function instance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}

AjaxCall::instance();
