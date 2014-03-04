<?php namespace Dec\Api\Auth;

use Dec\Api\Api;
use Dec\Api\Exceptions\NotAuthorizedException;
use Illuminate\Routing\Controller;
use Input;
use Request;
use Response;
use Validator;

class AuthController extends Controller {

    /**
     * @var \Dec\Api\Auth\ApiSessionDriver
     */
    protected $driver;


    public function __construct(ApiSessionDriver $driver)
    {
        $this->driver = $driver;
    }

    public function index()
    {
        $payload = Api::retrieveApiSession();
        $user = $this->driver->validate($payload);

        if (!$user)
            throw new NotAuthorizedException;

        return Response::json($user);
    }

    /**
     * Login
     */
    public function store()
    {
        $input = Input::all();

        $credentials = [
            'password' => Input::get('password')
        ];

        if(Input::has('email'))
        {
            $email = Input::get('email');

            // Make sure username and password are valid
            $validator = Validator::make(['email' => $email], ['email' => ['email']]);

            if ($validator->fails())
                return Response::error('Invalid email address');

            $credentials['email'] = $email;
        }
        else if(Input::has('username'))
            $credentials['username'] = Input::get('username');
        else
            return Response::error('Email missing');

        $token = $this->driver->attempt(
            $credentials,
            Input::getBoolean('expires', true)
        );

        if (!$token)
            return Response::error('Unable to generate token');

        $serializedToken = $this->driver->getProvider()->serializeToken($token);

        $user = $this->driver->user($token);

        return Response::json([
            'token' => $serializedToken,
            'data'  => $user->toArray()
        ]);
    }

    public function destroy()
    {
        $payload = Api::retrieveApiSession();

        $user = $this->driver->validate($payload);

        if (!$user)
            return Response::notAuthorized();

        if (Input::getBoolean('all'))
            // Delete all tokens for this user
            $success = $this->driver->getProvider()->purge($user);

        else
            // Just delete this one
            $success = $this->driver->getProvider()->delete($payload);

        if (!$success)
            return Response::error("Unable to end session");

        return Response::json(null, 204);
    }
}