<?php

namespace YuyuTech\BREAD\Traits;

use Illuminate\Http\Request;

trait AttributesProcess
{
	/**
	 * Attribute processting for listing on a table instance.
	 */
	public function applyListing(Request $request, $attributes, &$arrSelect = null, &$arrWhere = [], &$arrFilter = [], $strRelationName = ''){
		foreach ($attributes as $attribute){
			if($attribute->name === 'RCount' && $strRelationName !== ''){
				
				if(!is_null($request->{$strRelationName ."." .$attribute->name})){
		    		$this->arrWhereHasCount[$strRelationName] = $request->{$strRelationName ."." .$attribute->name};
		    	}
		    	continue;
			}

			if($attribute->listing){
				array_push($arrSelect, $attribute->name);
			}

			if($attribute->search){
				$this->applySearch($request, $attribute, $arrWhere, $strRelationName);
			}

			if($attribute->relation_filter){
				$this->applySearch($request, $attribute, $arrFilter, $strRelationName);
			}

		}
		// dd($arrSelect);
		return $this;
    }

    /**
     * Bind Request data with model.
     */
    protected function bindData(Request $request, &$model, &$attributes, $type='store'){
    	foreach ($attributes as $attribute) {
    		if($attribute->{$type}){
    			$model->{$attribute->name} = $request->{$attribute->name};
    		}
    	}
    }

    /**
     *
     */
    public function applySearch(Request $request, $attribute, &$arrWhere, $strRelationName){
    	$strAttributeName = ((empty($strRelationName) ? "" : ($strRelationName .".")) .$attribute->name);

    	if(is_null($request->{$strAttributeName})){
    		return 1;
    	}

    	array_push($arrWhere, [$attribute->name, '=', $request->{$strAttributeName}]);
    }

    /**
     *
     */
    public function getValidationRule(){
    	return 123;
    }
}
