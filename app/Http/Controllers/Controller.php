<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    protected function success($data, $message = 'Operation succcessful', $code = 200)
    {
        return response()->json([
            'status' =>'success',
            'message'=>$message,
            'data'   =>$data
        ],$code);
    }

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

