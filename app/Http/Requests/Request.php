<?php namespace App\Http\Requests;

use EllipseSynergie\ApiResponse\Laravel\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use League\Fractal\Manager;

abstract class Request extends FormRequest {

	public function forbiddenResponse()
    {
        $resp = new Response(new Manager());
        return $resp->errorForbidden("Not authorized for this request. Forbidden.");
    }

    protected function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();
        $validator->addImplicitExtension('gt', function($attribute, $value, $parameters) {

            if($value > $parameters[0]) {
                return true;
            }
            return false;
        });

        return $validator;
    }

    public function response(array $errors)
    {
        $err_msg = "";
        foreach($errors as $error) {
            $err_msg .= implode(", ", $error);
        }

        $resp = new Response(new Manager());
        return $resp->errorWrongArgs($err_msg);
    }
}
