<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets_model extends CI_Model {

    protected $collection_name = 'tickets';
    protected $collection;

    public function __construct() {
        // connect to the cimongo database
        $this->collection = (new MongoDB\Client)->cimongo->{$this->collection_name};
    }

    public function insert($data = []) {
        $results = false;
        if(!empty($data)) {
            $results = $this->collection->insertOne($data);
        }
        return $results;
    }

    public function update($filter = [], $update = [], $options = [], $update_one = true) {
        if($update_one) {
            $results = $this->collection->updateOne($filter, $update, $options);
        } else {
            $results = $this->collection->updateMany($filter, $update, $options);
        }
        return $results->getModifiedCount();
    }
}