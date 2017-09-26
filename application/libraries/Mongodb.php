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
            "option": "Option value"
       }
    ]
     */
    public function insert($collection_name) {
        $this->set_collection($collection_name);
        
        $data = $this->ci->input->stream();
        
        $this->collection->insertMany($data);
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

//    //remove multiple records
//    {
//        "_id": {
//            "$in": ["59c9a7dae2eb773cb800182f", "59c9a7dfe2eb773cb8001830"]
//        }
//    }
//    //remove one record
//    {
//	"_id": "59c9a7e0e2eb773cb8001831"
//    }
//    //remove by url
//    api/collection/_id
    
    public function delete($collection_name, $_id = '') {
        $this->set_collection($collection_name);
        
        if($_id) {
            //remove the specified record
            $results = $this->collection->deleteOne([
                '_id' => new MongoDB\BSON\ObjectID($_id)
            ]);
        } else {
            //get the remove criteria from the input stream
            $data = $this->ci->input->stream();
            if(!empty($data)) {
                //convert all ids
                if(isset($data['_id'])) {
                    if(!is_array($data['_id'])) {
                        $data['_id'] = new MongoDB\BSON\ObjectID($data['_id']);
                    } else {
                        foreach($data['_id'] as $key_i=>$val_i) {
                            if(!is_array($val_i)) {
                                $data['_id'][$key_i] = new MongoDB\BSON\ObjectID($val_i);
                            } else {
                                foreach($val_i as $key_j=>$val_j) {
                                    $data['_id'][$key_i][$key_j] = new MongoDB\BSON\ObjectID($val_j);
                                }
                            }
                        }
                    }
                }

                $results = $this->collection->deleteMany($data);
            } else {
                $results = 0;
            }
        }
        //return the count of deleted records
        return $results->getDeletedCount();
        
        
        
//        $filter = [];
////        $filter = isset($data[0]) ? $data[0] : [];
////        $options = isset($data[1]) ? $data[1] : [];
//
//        // filters must always include the user's account id
//
//        if ($_id !== '') {
//            // convert the id to a BSON object ID
//            $filter['_id'] = new MongoDB\BSON\ObjectID($_id);
//            // update a single record
//            $this->collection->deleteOne($filter);
//        } else {
//            //*
//            if (isset($data['_id'])) {
//                if (is_array($data['_id'])) {
//                    foreach ($data['_id'] as $key => $val) {
//                        $data['_id'][$key] = new MongoDB\BSON\ObjectID($val);
//                    }
//                    $filter['_id'] = ['$in' => $data['_id']];
//                } else {
//                    $filter['_id'] = new MongoDB\BSON\ObjectID($data['_id']);
//                }
//            }
//            //*/
//            $this->collection->deleteMany($filter);
//        }
        //*/
    }
}