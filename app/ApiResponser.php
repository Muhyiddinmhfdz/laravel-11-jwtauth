<?php

namespace App;
use Illuminate\Http\Response;

trait ApiResponser
{
    //
    public function successResponse($data, $httpCode=Response::HTTP_OK)
    {
        return response()->json(['data'=>$data],$httpCode);
    }

    public function errorResponse($message, $httpCode)
    {
        return response()->json(['error'=>$message,'code'=>$httpCode],$httpCode);
    }
}
