<?php

namespace YuyuTech\BREAD\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Validator;

class BREADValidator
{
   /**
    * Handle an incoming request, perform validations if applicable and returns proper response.
    *
    * @param  \Illuminate\Http\Request  $objRequest
    * @param  \Closure  $next
    * @return mixed
    */
   public function handle($objRequest, Closure $next)
   {
       /**
        * Common form fields validator feature.
        * 
        * For any controller/trait function if form fields needs validation then define followings
        *      - Function - which perform the action operation
        *      - Validator - which will be used to validate user inputs before calling the actual function
        * 
        * Formats: 
        *      - Function - any valid identifier
        *      - Validator - <function_identifier>Validator, it should be a 'PUBLIC' & 'STATIC' function
        * 
        * e.g. 
        *      - Function - storeResult, create, index
        *      - Validator - storeResultValidator, createValidator, indexValidator
        */
       $arrRouteAction     =   explode('@', \Route::currentRouteAction());

       if(!empty($arrRouteAction) && count($arrRouteAction) > 1){
           $strValidator       =   $arrRouteAction[1] . 'Validator';

           if(method_exists($arrRouteAction[0], $strValidator)){
               $objValidator   =   Validator::make($objRequest->all(), $arrRouteAction[0]::$strValidator($objRequest));

               if($objValidator->fails())
                   return response()->json($objValidator->errors(), 422);
           }
       }

       return $next($objRequest);
   }

}
