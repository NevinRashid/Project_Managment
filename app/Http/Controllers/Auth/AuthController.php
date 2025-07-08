<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CreateUserRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * This property is used to handle various operations related to users,
     * such as creating, geting user, deleting user tokens
     *
     * @var UserService
     */
    protected $userService;

    /**
     * Constructor for the UserController class.
     * 
     * Initializes the $userService property via dependency injection.
     * 
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Handle user Register.
     * 
     * Register a new User in the database using the UserService via the createUser method
     * passes the validated request data to createUser.
     * 
     * @param CreateUserRequest $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function register(CreateUserRequest $request)    
    {
        return $this->success(
                $this->userService->createUser($request->validated()),
                    'User has been registered successfully',
                    201);
    }

    /**
     * Handle user login.
     * 
     * Get the specified user using the UserService via the getUser method.
     * passes the validated request data to getUser.
     * Validates the user's credentials (email and password), 
     * and returns an access token if authentication is successful.
     * 
     * @param LoginRequest $request
     * 
     * @return \Illuminate\Http\Response 
     */
    public function login(LoginRequest $request)
    {
        $user=$this->userService->getUser($request->validated());

        if (empty($user) || !Hash::check($request->validated()['password'], $user->password)) {
            return $this->error('The email or password is incorrect or the user is not registered', 401); 
        }
        $token = $user->createToken('auth_token', ['*'], now()->addDay())->plainTextToken;

        return $this->success(['token' => $token],'You have logged in successfully');
    }


    /**
     * Handle user Logout.
     * 
     * Deleting users's tokens using the UserService via the deleteUserTokens method.
     *  passes the validated request data to deleteUserTokens.
     * 
     *  @return \Illuminate\Http\Response 
     */
    public function logout(Request $request)
    {
        $this->userService->deleteUserTokens($request);
        return $this->success(null, 'You have successfully logged out', 200);
    }
}