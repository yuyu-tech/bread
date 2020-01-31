<?php

namespace YuyuTech\BREAD\Models;

use Illuminate\Database\Eloquent\Model;

use YuyuTech\BREAD\Traits\CommonScope;
use YuyuTech\BREAD\Http\Controllers\BREADController;

class Table extends Model
{
	use CommonScope;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'bread';

    /**
     * Get the relationships for the table.
	 */
    public function relationships()
    {
        return $this->hasMany('YuyuTech\BREAD\Models\Relationship')
        	->where('status', 1);
    }

    /**
     * Get all of the table's attributes.
     */
    public function attributes()
    {
        $arrRoleIds = BREADController::$apiRoleIds;
        $arrRoleIds = is_array($arrRoleIds) ? $arrRoleIds : explode(',', $arrRoleIds);

        return $this->hasManyThrough('YuyuTech\BREAD\Models\Attribute', 'YuyuTech\BREAD\Models\ApiAttribute', 'table_id', 'id', 'id', 'attribute_id')
            ->selectRaw('attributes.*, SUM(api_attributes.search) as search, SUM(api_attributes.listing) as listing, SUM(api_attributes.store) as store, SUM(api_attributes.`update`) as `update`')
            ->join('api_role_api_attribute', 'api_role_api_attribute.api_attribute_id', '=', 'api_attributes.id')
            ->whereIn('api_role_api_attribute.api_role_id', $arrRoleIds)
            ->where('attributes.status', 1)
            ->where('api_attributes.status', 1)
            ->groupBy('attributes.id');
    }
}
