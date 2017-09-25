<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$hook['post_controller'] = array(
    'class'    => 'API_Output',
    'function' => 'set_output',
    'filename' => 'API_Output.php',
    'filepath' => 'hooks',
    'params'   => []
);