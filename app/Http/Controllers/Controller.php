<?php

namespace App\Http\Controllers;

use App\Http\Controllers\api\v1\ApiController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public const STATUS_OK=200;
    public const STATUS_CREATED=201;
    public const STATUS_NO_CONTENT=204;
    public const STATUS_RESET_CONTENT=205;

    //Exception
    public const STATUS_BAD_REQUEST=400;
    public const STATUS_UNAUTHORIZED=401;
    public const STATUS_NOT_AUTHENTICATED =402;
    public const STATUS_FORBIDDEN=403;
    public const STATUS_NOT_FOUND=404;
    public const STATUS_VALIDATION=405;
    public const TOKEN_EXPIRATION=406;
    public const GOOGLE_TOKEN_EXPIRATION=407;
    public const EMAIL_VERIFY_EXCEPTION = 408;
    /**
     * this function will determine the api response structure to make all responses has the same structure
     * @param null $data
     * @param int $code
     * @param null $message
     * @param null $paginate
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function apiResponse($data = null  , $code = 200 , $message = null , $paginate = null){
        $arrayResponse = [
                'data' => $data ,
                'status' => $code == 200 || $code==201 || $code==204 || $code==205 ,
                'message' => $message ,
                'code' => $code ,
                'paginate' => $paginate
        ];
        return response($arrayResponse,$code);
    }

    /**
     * standard for pagination
     * @param $data
     * @return array
     */
    public function formatPaginateData($data)
    {
        $paginated_arr = $data->toArray();
        return $paginateData = [
                'currentPage'   => $paginated_arr['current_page'],
                'from'          => $paginated_arr['from'],
                'to'            => $paginated_arr['to'],
                'total'         => $paginated_arr['total'],
                'per_page'      => $paginated_arr['per_page'],
        ];
    }

    public function apiValidation($request , $array){
        $validator = Validator::make($request->all(), $array);
        if ($validator->fails()) {
            $msg = [
                    'text' => 'the given data is invalid',
                    'errors' => $validator->errors()
            ];
            return $this->apiResponse(null, Controller::STATUS_VALIDATION, $msg);
        }
        return $validator->valid();
    }
}
