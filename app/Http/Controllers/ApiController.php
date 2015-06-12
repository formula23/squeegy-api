<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/3/15
 * Time: 11:30
 */

namespace App\Http\Controllers;

use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;
use Response;
use \Illuminate\Http\Response as IlluminateResponse;


/**
 * Class ApiController
 * @package App\Http\Controllers
 */
class ApiController extends ApiGuardController {

    /**
     * @var int
     */
    protected $statusCode = IlluminateResponse::HTTP_OK;

    /**
     * @param mixed $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function respondNotFound($message = 'Not Found!')
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_NOT_FOUND)->respondWithError($message);

    }

    /**
     * @param string $message
     * @return mixed
     */
    public function respondInternalError($message = 'Internal Error!')
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_INTERNAL_SERVER_ERROR)->respondWithError($message);
    }

    /**
     * @param $data
     * @param array $headers
     * @return mixed
     */
    public function respond($data, $headers = [])
    {
        return Response::json($data, $this->getStatusCode(), $headers);
    }

    /**
     * @param $message
     * @return mixed
     */
    public function respondWithError($message)
    {
        return $this->respond([
            'error' => [
                'message' => $message,
                'status_code' => $this->getStatusCode()
            ]
        ]);
    }

    /**
     * @param $item
     * @param $message
     * @return mixed
     */
    protected function respondCreated($item, $message)
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_CREATED)->respond([
            'status' => 'success',
            'message' => $message,
            'data' => $item,
        ]);
    }

    /**
     * @return mixed
     */
    protected function respondValidationError($message = "Required parameters missing!")
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY)
            ->respondWithError($message);
    }

} 