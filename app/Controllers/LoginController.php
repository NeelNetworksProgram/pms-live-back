<?php 
namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use App\Models\UsersModel;
use App\Models\ProjectAssignModel;
use App\Models\NotificationModel;
use App\Models\CurrentWorkModel;
use \Firebase\JWT\JWT;
class LoginController extends BaseController{
    use ResponseTrait; 
    protected $user_model;
    protected $assign_model;
     protected $notification_model;
     protected $current_work;
    public function __construct() {	
		$db = db_connect();
		$this->user_model = new UsersModel($db);
		$this->assign_model = new ProjectAssignModel($db);
		$this->notification_model = new NotificationModel($db);
		$this->current_work = new CurrentWorkModel($db);
        helper('text');
        helper('url');
    }
    // function for login
    public function index(){
        $request = service('request');
        $response = service('response');
        $referer = $request->getHeaderLine('Referer');
        
        $data = json_decode(file_get_contents('php://input'), true);

        $rules = [
           
			"email" => "required|valid_email",
			"password" => "required",
           
        ];

        $message = [
            "email" =>[
                "required" => "Please enter email",
                "valid_email" => "Please enter valid email format"
                
            ],

            "password" =>[
                "required" => "Please enter password"
            ],
        ];

        if(!$this->validate($rules,$message)){
            $response = [
				'status' => 500,
				'error' => true,
				'message' => $this->validator->getErrors(),
				'data' => []
			];
        }else{
            
            $email = $data['email'];
            $password = $data['password'];
            

            $fetch_data = $this->user_model->where('email',$email)->first();
            if($fetch_data){
                
                $db_pass = $fetch_data['password'];
                if ($db_pass !== null) {
                $authenticatePassword = password_verify($password, $db_pass);
                if($authenticatePassword){

             // check user account is activate or after login        
                  $check_verify = $fetch_data['is_active'];
                  
                  if($check_verify == '1'){
                    //now check user's status(active,suspend) if suspend then not allow to login
                    $check_status = $fetch_data['status'];
                    if($check_status == 'active'){

                    $key = getenv('JWT_SECRET');
                    $iat = time(); 
                    $exp = $iat + 28800;
                    $payload = array(
                        "iss" => "Neel networks",
                        "aud" => "customer",
                        "iat" => $iat, //Time the JWT issued at
                        "exp" => $exp, // Expiration time of token
                        "email" =>$fetch_data['email'],
                        "refer"=>$referer
                    );
                    
                    $token = JWT::encode($payload, $key, 'HS256');

                    $response = [
                        'status' => 200,
                        'error' => false,
                        'message' => 'User Login Successfully',
                        'token' => $token,
                        'data'=>["user_id"=>$fetch_data['id'],"user_roles"=>$fetch_data['roles'],"user_authority"=>$fetch_data['authorized_to'],'user_name'=>$fetch_data['username']]
                    ];
                }else{
                    $error = [
                        'status' => 403,
                        'error' => true,
                        'message' => 'Your account is suspended please contatct to administrator'
                    ];
                      $this->response->setJSON($error);
                      $response->setStatusCode(403);
                      return $response;
                }
                  }else{
                    $response = [
                        'status' => 500,
                        'error' => true,
                        'message' => "Your email " .$email . " does not activate yet",
                        'data' => ['rest_password_link'=> base_url('/user/resendlink/'.$fetch_data['id'].'/'.$random_string = random_string('alnum', 16))]
                    ];
                  }
                    
                    
                    
                }else{
                   
                    $response = [
                        'status' => 500,
                        'error' => true,
                        'message' => "Authentication failed beacuse password does not match for '$email' ",
                    ];
                }
            }else{
                $message = [
                    'status' => 500,
                    'error' => true,
                    'message' =>'Your password is not set yet check your email to reset password'
                    
                ];
                $this->response->setJSON($message);
                $response->setStatusCode(500);
                return $response;
            }
            }else{
                
                $response = [
                    'status' => 500,
                    'error' => true,
                    'message' => "Authentication failed beacuse enter email '$email'  does not match with our records  ",
                ];
            }

        }
        
        return $this->respondCreated($response);
    }




    // function for send verification link to user

    public function resendlink($user_id,$link){
        $fetch_data = $this->user_model->where('id',$user_id)->first();
        
        if(!empty($fetch_data)){

            // check whether user already verified or not
        $check = $fetch_data['is_active'];

        if($check == 0){
            // update verification link
            $update = $this->user_model->where(["id" => $user_id])->set(["verification_link" => $link])->update();
         if($update){
             $url = getenv('redirect');
            //send mail after update resend verification link to the database 
             $email_message = "Hello " .$fetch_data['username']. " Your email new verification link  is  <a href = '$url/email-activation/$link'>Re-Activate Now</a>"; 
            $email = \Config\Services::email();
            $email->setTo($fetch_data['email']);
            $email->setFrom('hemant@neelnetworks.com', 'New  activation link');
            
            $email->setSubject('User activation link');
            $email->setMessage($email_message);
            if($email->send()){
                $response = [
                    'status' => 200,
                    'error' => false,
                    'message' => "Please check your email id we have send new activation link to " .$fetch_data['email']
                ];
            }else{
                $response = [
                    'status' => 500,
                    'error' => true,
                    'message' => "Something went wrong for send email activation link please try after some time ....."
                ];
            }
                

         }else{
            $response = [
                'status' => 500,
                'error' => true,
                'message' => "Something went wrong try again after some time .....",
            ];
         }
        }else{
            $response = [
                'status' => 200,
                'error' => false,
                'message' => "You have already verfied your account",
            ];
        }
            
        }else{
            $response = [
                'status' => 500,
                'error' => true,
                'message' => "user verification error .....",
            ];
        }
        return $this->setResponseFormat('json')->respond($response);

    }
    
    //function for get all assign project list for single user for user not for admin with project status running 
    public function getProjectListForSingleUser($current_user){
        $response = service('response');
        // check provided user id is valid or not
        $user = $this->user_model->where('id',$current_user)->find();
        if(!empty($user)){
        $result = $this->assign_model->getAssignProjectList($current_user);
        if(!empty($result)){
            $success = [
                'status'=>200,
                'error'=>false,
                'message'=>'Your assign projects list are',
                'data'=>$result
            ];
            $this->response->setJSON($success);
            $response->setStatusCode(200);
            return $response;
        }else{
            $error = [
				'status' => 400,
				'error' => true,
				'message' => 'You have not been assigned any projects yet.'
			];
            $this->response->setJSON($error);
            $response->setStatusCode(400);
            return $response;
        }
        }else{
            $error = [
                'status' => 400,
                'error' => true,
                'message' => 'Sorry provided user id is not valid'
            ];
              $this->response->setJSON($error);
              $response->setStatusCode(400);
              return $response;
        }
      
    }

//delete user from database
public function delete($user_id){
    $response = service('response');
    // first check provided user id exist or not in database
     $id = $this->user_model->find($user_id); 
    if(!empty($id)){
        // delete records
        $delete = $this->user_model->delete($user_id);
        if($delete){
            $success = [
                'status' => 201,
                'error' => false,
                'message' => 'Data deleted.....'
            ];
              $this->response->setJSON($success);
              $response->setStatusCode(201);
              return $response;
        }else{
             $error = [
                'status' => 500,
                'error' => true,
                'message' => 'Something went wrong please try after some time....'
            ];
              $this->response->setJSON($error);
              $response->setStatusCode(500);
              return $response;
        }
    }else{
         $error = [
                'status' => 400,
                'error' => true,
                'message' => 'Sorry provided user id is not valid'
            ];
              $this->response->setJSON($error);
              $response->setStatusCode(400);
              return $response;
    }
}

// function for count all project assign for single user
    public function countAllProjectForSingleUser($login_user){
        $response = service('response');
        // first check provided login user id is valid or not
        $check_user = $this->user_model->find($login_user);
        if(!empty($check_user)){
        // for get count data 
        $count = $this->assign_model->countAllAssignProject($login_user);
        $message = [
            'status' => 200,
            'error' => false,
            'message' => 'Count data fo current user',
            'count'=>[$count]
        ];
        $status = 200;
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
    
    
    //function for get all assign project list forsingle user it contain all project
    public function getAllProjectListForSingleUser($current_user){
        $response = service('response');

        // for verify current_user id is valid
        $verify_user = $this->user_model->find($current_user);
        if(!empty($verify_user)){
            //check project is assign or not if yes then fetch
            $get_data = $this->assign_model->getAllAssignProjectList($current_user);
           
            if(!empty($get_data)){
                $message = [
                    'status' => 200,
                    'error' => false,
                    'message' => 'Your data are ...',
                    'project_list'=>$get_data
                ];
                $status = 200;
            }else{
                $message = [
                    'status' => 404,
                    'error' => true,
                    'message' => 'Your have not assign any project yet',
                    
                ];
                $status = 200;
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
    
    
    // function for get new notification for current user

    public function getNotifications($current_user){
        $response = service('response');
        // check current user is valid or not
        $check_user = $this->user_model->find($current_user);
        if(!empty($check_user)){
            // get all notifications
        $notifications = $this->notification_model->getNotifications($current_user);
        if(!empty($notifications)){
            $message = [
                'status' => 200,
                'error' => false,
                'message' => 'Your all notifications are ',
                'notification'=>$notifications
            ];
            $status = 200;
        }else{
            $message = [
                'status' => 404,
                'error' => true,
                'message' => 'No new notification found',
            ];
            $status = 404;
        }
        }else{
            $message = [
                'status' => 404,
                'error' => true,
                'message' => 'Your user id does not exist in our database',
            ];
            $status = 404;
        }
        //send response
        $this->response->setJSON($message);
        $response->setStatusCode($status);
        return $response;

    }


    // update notification database when notification reads

    public function updateNotifications($current_user){
        $response = service('response');
        //validation
        $data = json_decode(file_get_contents('php://input'), true);

        $rules = [
            "notification_id" => "required",
           ];
           $message = [
            "notification_id"=>[
                "required"=>"Please provide valid notification id to update "
            ],
           ];

           if($this->validate($rules,$message)){
            // check current user is valid or not
            $check_user = $this->user_model->find($current_user);
            if(!empty($check_user)){
                $ids = explode(',', $data['notification_id']);
                
               //check notification's id are valid or not
                $validate_ids = $this->notification_model->validateNotificationsId($ids);
                // Update the data for the valid IDs
                if (count($validate_ids) > 0) {
                    
                    $update = $this->notification_model->whereIn('id',$validate_ids)->set(["is_read" => 'yes'])->update();

                   if($update){
                    $message = [
                        'status' => 200,
                        'error' => false,
                    ];
                    $status = 200;
                   }else{
                    $message = [
                        'status' => 500,
                        'error' => true,
                        'message'=>'Something went wrong '
                    ];
                    $status = 500;
                   }
                }else{
                    $message = [
                        'status' => 404,
                        'error' => true,
                        'message' => 'Your notifications ids are not match with our database ',
                    ];
                    $status = 404;
                }
                
            }else{
                $message = [
                    'status' => 404,
                    'error' => true,
                    'message' => 'Your user id does not exist in our database',
                ];
                $status = 404;
            }
           }else{
            $message = [
                'status' => 400,
                'error' => true,
                'message' => $this->validator->getErrors(),
            ];
            $status = 400;
           }

            //send response
            $this->response->setJSON($message);
            $response->setStatusCode($status);
            return $response;
        
    }
    
    
    //Task assignment monitoring function for real-time updates on who is working on what.

    public function addCurrentWorkDetails(){
        $response = service('response');
    // validation 
    $rules = [
        'user_id'=>'required',
        'task_short_description'=>'required',
        'task_long_description'=>'required'
    ];

    $message = [
        "user_id" =>[
            "required" => "Please provide valid user id",
        ],  
        "task_short_description"=>[
            "required" => "Please enter small description for your work",
        ],

        "task_long_description" =>[
            "required" => "Would you please provide a description of the task or activity that you are planning to execute?",
        ],

    ];

    if($this->validate($rules,$message)){
        $data = json_decode(file_get_contents('php://input'), true);
    //check current user id is valid or not
    $check_user = $this->user_model->find($data['user_id']);

    if(!empty($check_user)){
        // check user account should be active and verified
        if($check_user['is_active'] == '1' && $check_user['status'] === 'active'){
            // check data is already inserted into database for current date 
            $current_date = time();
           $date = date('Y-m-d',  $current_date);

           $current_time = time();
           $time = date('H:i:s', $current_time);

            $check_data = $this->current_work->checkAlreadyDataInsertedOrNot($data['user_id'], $date);

            if($check_data === 'yes'){
                //fetch data from database for current user with current date
                $fetch = $this->current_work->getAllDataForToday($data['user_id'],$date);
                
                $update_data = [
                    'task_short_description' =>$data['task_short_description'],
                    'task_long_description'=>$data['task_long_description'],
                    'insert_time' => $time
                ];

                //update data if data already present
                $update = $this->current_work->where(["user_id" => $fetch[0]->user_id])->set($update_data)->update();
                if($update){
                    $message = [
                        'status' => 201,
                        'error' => false,
                        'message' => 'Your current task is added..',
                    ];
                    $status = 201;
                }else{
                    $message = [
                        'status' => 500,
                        'error' => true,
                        'message' => 'Something went wrong please try after some time...',
                    ];
                    $status = 500;
                }

            }else{
                
                $inserted_data = [
                    'user_id' => $data['user_id'],
                    'task_short_description' =>$data['task_short_description'],
                    'task_long_description'=>$data['task_long_description'],
                    'insert_date' => $date,
                    'insert_time' => $time
                ];
                $insert = $this->current_work->insert($inserted_data);
                if($insert){
                    $message = [
                        'status' => 201,
                        'error' => false,
                        'message' => 'Your current task is added..',
                    ];
                    $status = 201;
                }else{
                    $message = [
                        'status' => 500,
                        'error' => true,
                        'message' => 'Something went wrong please try after some time...',
                    ];
                    $status = 500;
                }
            }
           
        }else{
            $message = [
                'status' => 403,
                'error' => true,
                'message' => 'Sorry your account may be suspended or not verified yet',
            ];
            $status = 404;
        }
    }else{
        $message = [
            'status' => 404,
            'error' => true,
            'message' => 'Sorry you provided user id is not valid',
        ];
        $status = 404;
    }
        
    }else{
        $message = [
            'status' => 400,
            'error' => true,
            'message' => $this->validator->getErrors(),
        ];
        $status = 400;
    }

     //send response
     $this->response->setJSON($message);
     $response->setStatusCode($status);
     return $response;


    }
    
    // function for get all current work details for current date by all the employee
    function getCurrentWorkDetails($user_id){
        $response = service('response');

        // first check provided user id is valid or not
        $check_user = $this->user_model->find($user_id);
        if(!empty($check_user)){
            // now check user role (it should be admin or manager)
            $check_roles = $check_user['roles'];
            if($check_roles === 'admin' || $check_roles === 'manager'){
                // get data from database
                $current_date = time();
                $date = date('Y-m-d',  $current_date);

                $get_data = $this->current_work->getAllPresentDataForAllUsers($date);
                if(!empty($get_data)){
                    $message = [
                        'status' => 200,
                        'error' => false,
                        'message' => 'All Data',
                        'data'=>$get_data
                    ];
                    $status = 200;
                }else{
                    $message = [
                        'status' => 200,
                        'error' => false,
                        'message' => 'No data found.',
                        
                    ];
                    $status = 200;
                }
            }else{
                $message = [
                    'status' => 403,
                    'error' => true,
                    'message' => 'Sorry you are not authorized to view any records..',
                    
                ];
                $status = 403;
            }
        
        }else{
            $message = [
                'status' => 404,
                'error' => true,
                'message' => 'Sorry your provided user id does not match with our database',
                
            ];
            $status = 404;
        }
        

        //send response
     $this->response->setJSON($message);
     $response->setStatusCode($status);
     return $response;
    }
    
   //function for get real-time work updates for employee
    function getMyCurrentWork($current_user){
        $response = service('response');
        // first validate the current user is valid or not
        $check_user = $this->user_model->find($current_user);
        if(!empty($check_user)){
            // fetch record from data base
            $current_date = time();
            $date = date('Y-m-d',  $current_date);
            $fetch = $this->current_work->getAllDataForToday($current_user,$date);

            if(!empty($fetch)){
                $message = [
                    'status' =>200,
                    'error' => false,
                    'data' => $fetch,
                    
                ];
                $status = 200;
            }else{
                $message = [
                    'status' =>404,
                    'error' => true,
                    'message' => 'Sorry you have not added any tasks or work for today',
                    
                    
                ];
                $status = 404;
            }
        }else{
            $message = [
                'status' => 404,
                'error' => true,
                'message' => 'Sorry your provided user id does not match with our database',
                
            ];
            $status = 404;
        }

     //send response
     $this->response->setJSON($message);
     $response->setStatusCode($status);
     return $response;
    }


}

?>