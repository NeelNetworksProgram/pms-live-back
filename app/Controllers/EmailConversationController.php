<?php

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;
use App\Models\UsersModel;
use App\Models\ProjectAssignModel;
use App\Models\EmailConversationModel;
class EmailConversationController extends BaseController
{
    use ResponseTrait; 
    protected $user_model;
    protected $assign_model;
    protected $email_model;
    public function __construct() {	
		$db = db_connect();
		$this->user_model = new UsersModel($db);
		$this->assign_model = new ProjectAssignModel($db);
        $this->email_model =  new EmailConversationModel($db);
    }
    
    //function for get all email list for current user of the assign project by him/her
    public function email_list($current_user)
    {
        $response = service('response');
        // first check user is valid or not 
        $check_user = $this->user_model->find($current_user);
        if(!empty($check_user)){
           // check whether user account does not suspend and should be active
           if($check_user['is_active'] === '1' && $check_user['status'] === 'active'){
            // check user roles it must be admin/manager
            if($check_user['roles'] === 'admin' ||  $check_user['roles'] === 'manager'){
               // all email conversation list
               $get_data = $this->email_model->getAllMailListByUserId($current_user);
               if(!empty($get_data)){
                $message = [
                    'status' => 200,
                    'error' => false,
                    'message' => 'All email logs',
                    'email_logs'=>$get_data
                ];
                $status = 200;
               }else{
                $message = [
                    'status' => 404,
                    'error' => true,
                    'message' => 'sorry no any email logs available'
                ];
                $status = 404;
               }

            }else{
                $message = [
                    'status' => 403,
                    'error' => true,
                    'message' => 'You are not authorized to see any email logs'
                ];
                $status = 403;
            }
           }else{
            $message = [
                'status' => 403,
                'error' => true,
                'message' => 'Your account may be suspended or not verified yet'
            ];
            $status = 403;
           }
           
        }else{
            $message = [
                'status' => 404,
                'error' => true,
                'message' => 'Sorry provided user id is not valid'
            ];
            $status = 404;
        }

        //send response
        $this->response->setJSON($message);
        $response->setStatusCode($status);
        return $response;

    }
}
