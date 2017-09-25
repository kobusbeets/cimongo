<?php defined('BASEPATH') OR exit('No direct script access allowed');

class API_Controller extends MY_Controller {

    public $response;

	public function __construct() {
        parent::__construct();

        $this->init();
    }

    private function init() {
        $this->response = [];
    }
}