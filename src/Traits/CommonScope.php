<?php

namespace Yuyu\BREAD\Traits;

trait CommonScope
{
    public function scopeStatus($query, $strStatus){
    	return $query->where('status', $strStatus);
    }
}
