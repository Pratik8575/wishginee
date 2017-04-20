<?php
namespace Wishginee\Foundation;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class Response extends JsonResponse{

    /**
     * Method to bind data to the corresponding JSON Format
     * @param $status
     * @param $data
     * @param array $errors
     * @return Response
     */
    public static function envelope($status = 200, $data, $errors = []){
        return new Response([
            'status' => [
               'code' => $status,
               'message' => HttpResponse::$statusTexts[$status]
            ],
            'data' => $data,
            'error' => $errors
        ], $status);
    }

    /**
     * Response method for data only
     * @param $status
     * @param $data
     * @return Response
     */
    public static function raw($status = 200, $data){
        return self::envelope($status, $data, []);
    }

    /**
     * Response method for errors only
     * @param $status
     * @param $errors
     * @return Response
     */
    public static function error($status = 500, $errors){
        return self::envelope($status, [], $errors);
    }
}