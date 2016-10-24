<?php

namespace App\Exceptions;

use Exception;
use App\Exceptions\MsgException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
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
        if ($e instanceof ModelNotFoundException) {
            return response()->json(['error' => $e->getModel().' not found.'], 404);
        } elseif ($e instanceof NotFoundHttpException) {
            return response()->json(['error' => $request->path().' not found.'], 404);
        } elseif ($e instanceof ValidationException) {
            // todo
            return response()->json(['error' => $e->validator->messages()], 400);
        } elseif ($e instanceof AuthorizationException) {
            return response()->json(['error' => 'Unauthorized'], 401);
        } elseif ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        } elseif ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
            return response()->json(['error' => 'token_invalid'], $e->getStatusCode());
        } elseif ($e instanceof MsgException) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        } elseif (!env('LOCAL', false)) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        // production
        // return response()->json(['error' => 'internal_error'], 500);
        // development
        return parent::render($request, $e);
    }
}
