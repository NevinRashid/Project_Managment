<?php

namespace App\Traits;

trait HandleServiceErrors
{
    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    protected function error($message = 'An error occurred', $code=500 ,$errors = null)
    {
        $response=[
            'status' =>'error',
            'message'=>$message
        ];

        if($errors){
            $response['errors']=$errors;
        }

        return response()->json($response,$code);
    }
}
