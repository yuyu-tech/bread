<?php

namespace Yuyu\BREAD\Models;

use Illuminate\Database\Eloquent\Model;

class Relationship extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'bread';

    /**
     * Get the relationship table that owns the relationship.
     */
    public function attributes()
    {
        $arrRoleIds = session('api_role_ids');
        $arrRoleIds = is_array($arrRoleIds) ? $arrRoleIds : explode(',', $arrRoleIds);
        
        return $this->hasManyThrough('Yuyu\BREAD\Models\Attribute', 'Yuyu\BREAD\Models\ApiAttribute', 'relatioship_id', 'id', 'id', 'attribute_id')
            ->selectRaw('attributes.*, SUM(api_attributes.search) as search, SUM(api_attributes.relation_filter) as relation_filter, SUM(api_attributes.listing) as listing')
            ->join('api_role_api_attribute', 'api_role_api_attribute.api_attribute_id', '=', 'api_attributes.id')
            ->whereIn('api_role_api_attribute.api_role_id', $arrRoleIds)
            ->where('attributes.status', 1)
            ->where('api_attributes.status', 1)
            ->groupBy('attributes.id');
    }
}
