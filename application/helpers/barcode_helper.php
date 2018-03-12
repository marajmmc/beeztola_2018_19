<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Barcode_helper
{
    public static function get_barcode_farmer($id)
    {
        return 'F-'.str_pad($id,6,0,STR_PAD_LEFT);
    }

}
