<?php namespace App\Exceptions;

use Exception;
use Bugsnag\BugsnagLaravel\BugsnagExceptionHandler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler {

	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		'Symfony\Component\HttpKernel\Exception\HttpException'
	];

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Exception  $e
	 * @return void
	 */
	public function report(Exception $e)
	{
		return parent::report($e);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Exception  $e
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Exception $e)
	{
        if($e instanceof NotFoundHttpException)
        {
            return response()->json(['error' => ['http_code'=>404, 'message' => 'Not Found']], 404);
        }
        $err_msg = ($e->getCode() == 400) ? $e->getMessage() : "Server Error" ;
        $http_code = ($e->getCode() == 400) ? 400 : 500 ;

        $resp = ['error' => ['http_code'=>$http_code, 'message' => $err_msg]];
        if(config('app.debug')) {
            $resp['error']['message'] = $e->getMessage();
            $resp['error']['trace'] = $e->getTrace();
        }
        return response()->json($resp, $http_code);

	}

}
