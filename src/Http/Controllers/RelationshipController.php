<?php

namespace YuyuTech\BREAD\Http\Controllers;

use Illuminate\Http\Request;

use YuyuTech\BREAD\Models\Relationship;
use YuyuTech\BREAD\Traits\AttributesProcess;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RelationshipController
{
	use AttributesProcess;

	private $model, $relationship, $modelAttributes = [], $request, $arrAttributes = [], $arrWhere = [], $strRelationName, $arrWith = [], $arrWhereHasCount = [];

	public function __construct(Request $request, $model){
		$this->request = $request;
		$this->model = $model;
	}

	public function handle(Relationship $relationship){
		$relation = null;
    	$this->strRelationName = '';

		// Get all relationships that asociated with this.
		$arrTrhoughRelations = explode('.', $relationship->name);

		foreach ($arrTrhoughRelations as $strRelation) {
			if(empty($relation)){
				$relation = (new $this->model)->{$strRelation}();
    		}
    		else{
    			$relation = $relation->getRelated()->{$strRelation}();
    		}
    		
    		switch ($relation) {
    			case $relation instanceof BelongsTo :
    				$this->relationshipUpdate($relation->getForeignKeyName(), $relation->getOwnerKeyName(), $strRelation);
    				break;

    			case $relation instanceof HasMany :
    				$this->relationshipUpdate($relation->getLocalKeyName(), $relation->getForeignKeyName(), $strRelation);
    				break;

    			case $relation instanceof HasOne :
    				$this->relationshipUpdate($relation->getLocalKeyName(), $relation->getForeignKeyName(), $strRelation);
    				break;

    			case $relation instanceof BelongsToMany :
    				$this->relationshipUpdate($relation->getParentKeyName(), $relation->getRelatedKeyName(), $strRelation);
    				break;
    			
    			default:
    				dd($relation);
    				break;
    		}
		}
	}

	public function applyRelationships($relationships){
		foreach ($relationships as $relationship) {
			$this->handle($relationship);
		}
		
		foreach ($relationships as $relationship) {
			$this->applyRelationship($relationship);
			unset($this->arrAttributes[$relationship->name]);
		}
		
		if(count($this->arrWith)){
			$this->model = $this->model->with($this->arrWith);
		}
		
		foreach ($this->arrAttributes as $strRelationName => $arrSelect) {
			if(empty($arrSelect)){
				continue;
			}
			$this->model = $this->model->with([
				$strRelationName => function($query) use($arrSelect){
					$query->select(array_unique($arrSelect));
				}
			]);
			unset($this->arrAttributes[$strRelationName]);
		}

		foreach ($relationships as $relationship) {
			if(isset($this->arrWhere[$relationship->name]) && count($this->arrWhere[$relationship->name])){
				if(!isset($this->arrWhereHasCount[$relationship->name])){
					$this->arrWhereHasCount[$relationship->name] = 1;
				}
			}
			
			if( (isset($this->arrWhere[$relationship->name]) && count($this->arrWhere[$relationship->name]))
				|| (isset($this->arrWhereHasCount[$relationship->name]) && $this->arrWhereHasCount[$relationship->name] > 0)
			){
				// dd($this->arrWhereHasCount);
				$this->model = $this->model->whereHas($relationship->name, function($query) use($relationship){
					$query->where($this->arrWhere[$relationship->name]);
				}, '>=', $this->arrWhereHasCount[$relationship->name]);				
			}
		}
		
		// foreach ($this->arrWhere as $strRelation => $arrWhere) {
		// 	if(count($arrWhere)){
		// 		$this->model = $this->model->whereHas($strRelation, function($query) use($arrWhere){
		// 			$query->where($arrWhere);
		// 		}, '>=', $this->arrWhereHasCount[$strRelation]);				
		// 	}
		// }

		return $this->model;
	}

	public function applyRelationship(Relationship $relationship){
		$this->arrWhere[$relationship->name] = [];
		$arrRelationFilter = [];
		$intWhereHasCount = 1;

		if(!isset($this->arrAttributes[$relationship->name])){
			$this->arrAttributes[$relationship->name] = [];
		}

		$this->applyListing($this->request, $relationship->attributes, $this->arrAttributes[$relationship->name], $this->arrWhere[$relationship->name], $arrRelationFilter, $relationship->name);
		$arrSelect = array_unique($this->arrAttributes[$relationship->name]);

		$this->arrWith[$relationship->name] = function($query) use($relationship, $arrSelect, $arrRelationFilter){
			$query = $query->select($arrSelect);
			if(count($arrRelationFilter)){
				$query->where($arrRelationFilter);
			}

		};
		
	}

	public function getModelAttributes(){
		return $this->modelAttributes;
	}

	public function getModel(){
		return $this->model;
	}

	// public function getRelationsAttributes($strRelationName){
	public function getRelationsAttributes(){
		dd($this->arrWhere);
		return $this->arrAttributes;
	}

    private function relationshipUpdate($strForeignKey, $strLocalKey, $strRelation){
		// First relation will directly related with our main model so strRelationName will empty so for that add foreign key into main model attributes else update in a respective relationship attributes.
		if(empty($this->strRelationName)){
		    $this->modelAttributes[] = $strForeignKey;
		}
		else{
			$this->arrAttributes[$this->strRelationName][] = $strForeignKey;
		}
		// Update Relation name for current relation
		$this->strRelationName .= (empty($this->strRelationName) ? '' : '.') .$strRelation;
		// Update OwnerKey name in Respective relationship attributes
		$this->arrAttributes[$this->strRelationName][] = $strLocalKey;
		// dd($this->strRelationName);
    }
}
