<?php 

class MY_Input extends CI_Input {

    protected $stream;

    public function __construct() {
        parent::__construct();

        $this->init();
    }

    private function init() {
        $this->stream = json_decode($this->raw_input_stream, true);
    }

    public function stream($key = '') {
        if($key === '') {
            return $this->stream;
        } elseif(isset($this->stream[$key])) {
            return $this->stream[$key];
        } else {
            return null;
        }
    }
}