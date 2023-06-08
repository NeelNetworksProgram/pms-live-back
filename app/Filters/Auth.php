<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use CodeIgniter\API\ResponseTrait;

class Auth implements FilterInterface
{
    use ResponseTrait;
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
{
    $request = service('request');
    $response = service('response');
    $key =  getenv('JWT_SECRET');
    
    $header = $request->getHeader("Authorization");
    $token = null;

    if(!empty($header)){
        if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            $token = $matches[1];
        }
    }

    // check whether token is present or not

    if(is_null($token) || empty($token)){
        
        $error = [
            'status'=>401,
            'error'=>true,
            'message' => 'Sorry you are not authorized '
        ];
        $response->setJSON($error);
        $response->setStatusCode(401);
        return $response;
    }
    
    try {
        
        $decoded = JWT::decode($token, new Key($key, 'HS256'));

        // fetch user information from the database using the decoded token
        $userModel = new \App\Models\UsersModel();
        $user = $userModel->where('email',$decoded->email)->first();

        // check if user status is suspended
        if ($user && $user['status'] == 'suspend') {
            $error = [
                'status'=>403,
                'error'=>true,
                'message'=>'Sorry your account has been suspended. Please contact administrator'
            ];
            $response->setJSON($error);
            $response->setStatusCode(403);
            return $response;
        }
        
    } catch (Exception $ex) {
        $error = [
            'status'=>403,
            'error'=>true,
            'message'=>'Sorry your token is not valid '
        ];
        $response->setJSON($error);
        $response->setStatusCode(403);
        return $response;
    }
}


    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
