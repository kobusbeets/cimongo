<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Mongodb {
    
    protected $ci;
    protected $collection;

    public function __construct() {
        $this->ci =& get_instance();
    }
    
    private function set_collection($collection_name = '') {
        $this->collection = (new MongoDB\Client)->cimongo->{$collection_name};
    }
    
    // api?projection=name&limit=5&sort=name|desc
    public function get($collection_name, $_id = '') {
        $this->set_collection($collection_name);
        
        $filter = [];
        
        $options = $this->ci->input->get();
        foreach($options as $i=>$j) {
            switch($i) {
                case 'limit': // ?limit=1&skip=2
                case 'skip': 
                    $options[$i] = intval($j);
                    break;
                case 'sort': // ?sort=name|asc,country|desc
                    $options[$i] = explode(',', $j);
                    foreach($options[$i] as $k=>$l) { // $l = name|asc
                        $sort_pieces = explode('|', $l);
                        $options[$i][$sort_pieces[0]] = $sort_pieces[1] == 'asc' ? 1 : -1;
                        unset($options[$i][$k]);
                    }
                    break;
                case 'projection': // ?projection=name,surname,age,etc.
                    $options[$i] = explode(',', $j);
                    foreach($options[$i] as $k=>$l) {
                        $options[$i][$l] = 1;
                        unset($options[$i][$k]);
                    }
                    break;
                default:
                    $filter[$i] = $j;
                    unset($options[$i]); //remove it from the options array
            }
        }
        
//        $this->response = $options;
            
        if($_id) {
            $filter['_id'] = new MongoDB\BSON\ObjectId($_id);
            $results = $this->collection->findOne($filter, $options);
            $results->_id = (string) $results->_id;
        } else {
            $documents = $this->collection->find($filter, $options);
            foreach($documents as $document) {
                $document->_id = (string) $document->_id;
                $results[] = $document;
            }
        }
        
        return $results;
    }

    /*
    [
       {
            "card_name": "Sample 2",
            "type": "location",
            "activated": "1"
       },
       {
            "card_name": "Sample 3",
            "type": "in",
            "activated": "0"
       }
    ]
     */
    public function insert($collection_name) {
        $this->set_collection($collection_name);
        
        $data = $this->ci->input->stream();
        
        $documents = isset($data[0]) ? $data[0] : [];
        $options   = isset($data[1]) ? $data[1] : [];
        
        // if documents is empty then it's very likely a single object contained in data
        if(empty($documents)) {
            $documents = $data;
        }
        
        if(array_values($documents) !== $documents) {
            /*
            if(isset($documents['_id'])) {
                $documents['_id'] = new MongoDB\BSON\ObjectID($documents['_id']);
            }
            //*/
            $this->collection->insertOne($documents, $options);
        } else {
            /*
            foreach($documents as $index=>$document) {
                if(isset($documents[$index]['_id'])) {
                    $documents[$index]['_id'] = new MongoDB\BSON\ObjectID($documents[$index]['_id']);
                }
            }
            //*/
            $this->collection->insertMany($documents, $options);
        }
    }

    public function update($collection_name, $_id = '') {
        $this->set_collection($collection_name);
        
        $data = $this->ci->input->stream();

        //*
        $filter = isset($data[0]) ? $data[0] : [];
        $update = isset($data[1]) ? $data[1] : [];
        $options = isset($data[2]) ? $data[2] : [];

        // filters should always include the user's account id

        if ($_id !== '') {
            // convert the id to a BSON object ID
            $filter['_id'] = new MongoDB\BSON\ObjectID($_id);
            // update a single record
            $this->collection->updateOne($filter, ['$set' => $update], $options);
        } else {
            //*
            if (isset($filter['_id'])) {
                if (is_array($filter['_id'])) {
                    foreach ($filter['_id'] as $key => $val) {
                        $filter['_id'][$key] = new MongoDB\BSON\ObjectID($val);
                    }
                    $filter['_id'] = ['$in' => $filter['_id']];
                } else {
                    $filter['_id'] = new MongoDB\BSON\ObjectID($filter['_id']);
                }
            }
            //*/
            $this->collection->updateMany($filter, ['$set' => $update], $options);
        }
        //*/
    }

    public function delete($collection_name, $_id = '') {
        $this->set_collection($collection_name);
        
        $data = $this->ci->input->stream();

        $filter = isset($data[0]) ? $data[0] : [];
        $options = isset($data[1]) ? $data[1] : [];

        // filters must always include the user's account id

        if ($_id !== '') {
            // convert the id to a BSON object ID
            $filter['_id'] = new MongoDB\BSON\ObjectID($_id);
            // update a single record
            $this->collection->deleteOne($filter, $options);
        } else {
            //*
            if (isset($filter['_id'])) {
                if (is_array($filter['_id'])) {
                    foreach ($filter['_id'] as $key => $val) {
                        $filter['_id'][$key] = new MongoDB\BSON\ObjectID($val);
                    }
                    $filter['_id'] = ['$in' => $filter['_id']];
                } else {
                    $filter['_id'] = new MongoDB\BSON\ObjectID($filter['_id']);
                }
            }
            //*/
            $this->collection->deleteMany($filter, $options);
        }
        //*/
    }
}