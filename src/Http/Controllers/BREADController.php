<?php

namespace YuyuTech\BREAD\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

use YuyuTech\BREAD\Http\Controllers\RelationshipController;
use YuyuTech\BREAD\Models\Table;
use YuyuTech\BREAD\Traits\AttributesProcess;

class BREADController extends BaseController
{
    use AttributesProcess;

    private $table, $model, $arrTableWhere = [], $arrTableAttributes = [];

    public static $apiRoleIds = [];

    public function __construct()
    {
        $this->table = new Table;
    }
    public function getTable(){
    	return $this->table;
    }

    /**
     * Load Table instance
     * @return object instance
     */
    public function loadTable($intTableId, $apiRoleIds = []){
        self::$apiRoleIds = $apiRoleIds;
        $this->table = Table::with(['attributes', 'relationships.attributes'])
    		->find($intTableId);
        return $this;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Initialize BREAD Configuration
        $this->initialize();

        // Remove relationships which doesn't havy any attributes
        $this->table->relationships = $this->table->relationships->filter(function($value, $key){
            return $value->attributes->count() > 0;
        });

        $this->applyListing($request, $this->table->attributes, $this->arrTableAttributes, $this->arrTableWhere);

        // Initialize relationship controller
        $objRelationshipController =  new RelationshipController($request, $this->model);
        
        $this->model = $objRelationshipController->applyRelationships($this->table->relationships);
        
        $this->arrTableAttributes = array_unique(array_merge($this->arrTableAttributes, $objRelationshipController->getModelAttributes()));
                
        return $this->model
            ->select($this->arrTableAttributes)
            ->when($this->arrTableWhere, function($query, $arrWhere){
                return $query->where($arrWhere);
            })
            ->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Initialize BREAD Configuration        
        $this->initialize()
            ->bindData($request, $this->model, $this->table->attributes);

        $this->model->save();

        return $this->model;
    }

    private function initialize(){
        // 404 if a table instance not found
        if(empty($this->table)){
            return abort(404, 'Table instance not found');
        }
        
        // Generate Model object of associated Table.
        $this->model = (new $this->table->model);

        return $this;
    }
}
