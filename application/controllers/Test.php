<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller
{
    public function index()
    {
        echo (md5('abcdabcdabcdabcdabcdabcdabcdabcd'));
        echo '<br>';
        echo (md5('abcdabcdabcdabcdabcdabcdabcdabcde'));
    }
}
