<?php

namespace Yuyu\BREAD\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

use Yuyu\BREAD\Models\Table;

class BREADController extends BaseController
{
    private $table = null;

    public function __call($method,$arguments) {

		if(!in_array($method, ['loadTable'])){
			if($this->table === null){
				return 'Please load Table instance first.';
			}
		}
        if(method_exists($this, $method)) {
            return call_user_func_array(array($this,$method),$arguments);
        }

        return 'Method does not exists.';
    }

    private function getTable(){
    	return $this->table;
    }

    /**
     * Load Table instance
     */
    public function loadTable($intTableId){
    	$this->table = Table::with(['attributes', 'relationships.attributes'])
    		->find($intTableId);
    }

    // public 
    // public function __construct($intTableId){
    // 	dd($intTableId);
    // }
}
