<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets extends API_Controller {

	public function __construct() {
		parent::__construct();

		$this->load->model('tickets_model');
	}

	public function index() {
		//$this->load->view('welcome_message');
	}

	public function get($_id = '') {

	}

	// create a new ticket
	public function insert() {

		$data = $this->input->stream();

		$this->tickets_model->insert($data);
	}

	public function update($_id = '') {
		$data = $this->input->stream();

		//*
		$filter  = isset($data[0]) ? $data[0] : [];
		$update  = isset($data[1]) ? $data[1] : [];
		$options = isset($data[2]) ? $data[2] : [];

		// filters should always include the user's account id

		if($_id !== '') {
			// convert the id to a BSON object ID
			$filter['_id'] = new MongoDB\BSON\ObjectID($_id);
			// update a single record
			$this->tickets_model->update($filter, ['$set' => $update], $options);
		} else {
			//*
			if(isset($filter['_id'])) {
				if(is_array($filter['_id'])) {
					foreach($filter['_id'] as $key=>$val) {
						$filter['_id'][$key] = new MongoDB\BSON\ObjectID($val);
					}
				} else {
					$filter['_id'] = new MongoDB\BSON\ObjectID($filter['_id']);
				}
			}
			//*/

			$this->tickets_model->update($filter, ['$set' => $update], $options, false); // false to update many
		}
		//*/
	}

	public function delete($_id = '') {

	}
}
