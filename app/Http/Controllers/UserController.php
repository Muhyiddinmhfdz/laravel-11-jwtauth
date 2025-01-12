<?php

namespace App\Http\Controllers;

use App\ApiResponser;
use App\Models\UserDetail;
use Elegant\Sanitizer\Sanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //
    use ApiResponser;
    public function update(Request $request)
    {
        $user = auth('api')->user();
        $validator = Validator::make($request->all(), [
            'gender' => 'required',
            'nik' => 'required',
            'date_of_birth' => 'required',
            'place_of_birth' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'data' => $this->validationErrorsToString($validator->errors())], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $filters = [
            'gender'    =>  'trim|escape',
            'nik'    =>  'trim|escape',
            'date_of_birth'    =>  'trim|escape',
            'place_of_birth'    =>  'trim|escape',
        ];
        $sanitizer  = new Sanitizer($request->all(), $filters);
        $attrclean = $sanitizer->sanitize();

        try {
            DB::beginTransaction();

            $check = UserDetail::where('user_id',$user->id)->count();
            if($check>0)
            {
                $user_detail = UserDetail::where('user_id',$user->id)->update([
                    'gender'=>$attrclean['gender'],
                    'nik'=>$attrclean['nik'],
                    'date_of_birth'=>date("Y-m-d H:i:s",strtotime($attrclean['date_of_birth'])),
                    'place_of_birth'=>$attrclean['place_of_birth'],
                ]);
            }
            else{
                $user_detail =  UserDetail::create([
                    'user_id'=>$user->id,
                    'gender'=>$attrclean['gender'],
                    'nik'=>$attrclean['nik'],
                    'date_of_birth'=>date("Y-m-d H:i:s",strtotime($attrclean['date_of_birth'])),
                    'place_of_birth'=>$attrclean['place_of_birth'],
                ]);
            }
            $user=auth('api')->user()->load('user_detail')->toArray();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            // dd($e);
            return $this->errorResponse('Error, Internal Server Error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->successResponse($user);
    }
}
