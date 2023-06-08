<?php
namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;
use App\Models\UsersModel;
use App\Models\ProjectModel;
use App\Models\TaskModel;
use App\Models\ProjectAssignModel;
class TaskController extends BaseController
{
    use ResponseTrait; 
    protected $user_model;
    protected $project_model;
    protected $task_model;
    protected $assign_model;
    public function __construct() {	
		$db = db_connect();
		
 		$this->project_model = new ProjectModel($db);
       $this->user_model = new UsersModel($db);
       $this->task_model = new TaskModel($db);
       $this->assign_model = new ProjectAssignModel($db);
    }
   // function for assign a task with project 
    public function assignTaskWithProject()
    {
        $response = service('response');

        // validation 
        $rules = [
            'login_user' => 'required',
            'assign_to' => 'required',
            'project_id' => 'required',
            'task_description' =>'required|max_length[800]',
            'task_name'=>'required'
            
        ];
        
        $message = [
            "login_user"=>[
                "required" => "Please enter login user id"
            ],
            "assign_to"=>[
                "required" => "Please enter task receiver id",
                
            ],
            "project_id"=>[
                "required" => "Please enter valid project id"
            ],
            "task_description"=>[
                "required" => "Please enter short description about task",
                "max_length"=>"Task description should not be greater then 800 letters"
            ],
            "task_name"=>[
                "required" => "Could you please suggest a task name for notifying users? ",
                
            ],
        ];
        if(!$this->validate($rules,$message)){
            $message = [
				'status' => 400,
				'error' => true,
				'message' => $this->validator->getErrors(),
				
			];
            $this->response->setJSON($message);
            $response->setStatusCode(400);
            return $response;
        }else{

        $data = json_decode(file_get_contents('php://input'), true);
        // check entered user id is valid or not 
        $check_user = $this->user_model->find($data['login_user']); 
        if(empty($check_user)){
            $message = [
				'status' => 404,
				'error' => true,
				'message' =>'Sorry login user id is not valid'
				
			];
            $this->response->setJSON($message);
            $response->setStatusCode(404);
            return $response;
        }else{
            // check task receiver user id is valid or not 
            $check_assign_user = $this->user_model->find($data['assign_to']); 
            if(empty($check_assign_user)){
                $message = [
                    'status' => 404,
                    'error' => true,
                    'message' =>'Sorry Your assign user id is not valid'
                    
                ];
                $this->response->setJSON($message);
                $response->setStatusCode(404);
                return $response;
            }else{
               
                
                // check task assign user is not suspend
                 $check_suspend = $check_assign_user['status'];
                 $check_verify = $check_assign_user['is_active'];
                
                if($check_suspend != 'suspend' && $check_verify !=0){
                   // check project id is valid or not 
                   $check_project = $this->project_model->find($data['project_id']);
                   if(empty($check_project)){
                    $message = [
                        'status' => 404,
                        'error' => true,
                        'message' =>'Sorry Provided project id is not valid'
                        
                    ];
                    $this->response->setJSON($message);
                    $response->setStatusCode(404);
                    return $response;
                   }else{
                    // check project status its not be completed or not be on hold
                    $project_status = $check_project['project_status'];
                    if($project_status ==='Completed' ||  $project_status ==='Hold'){
                        $message = [
                            'status' => 403,
                            'error' => true,
                            'message' =>'Your provided project id may be on hold or completed '
                            
                        ];
                        $this->response->setJSON($message);
                        $response->setStatusCode(403);
                        return $response;
                    }
                    // validate the task_assigner and task receiver should not match
                    if($data['login_user'] === $data['assign_to']){
                        $message = [
                            'status' => 403,
                            'error' => false,
                            'message' =>'You cannot assign task to your self'
                            
                        ];
                        $this->response->setJSON($message);
                        $response->setStatusCode(403);
                        return $response;
                    }else{
                        // check wether entered project is already assign to selected user or not
                        $check = $this->assign_model
                        ->where('project_id', $data['project_id'])
                        ->where('user_id', $data['login_user'])
                        ->where('project_deallocate !=', 'yes')
                        ->get()
                        ->getRow();
                        if ($check) {
                        // if all condition statisfied then assign task for approval to user
                        $insert_data = [
                            "project_id"=>$data['project_id'],
                            "login_id"=>$data['login_user'],
                            "assign_to"=>$data['assign_to'],
                            "task_description"=>$data['task_description'],
                            "task_name"=>$data['task_name']
                        ];
                        $insert = $this->task_model->insert($insert_data);
                        if($insert){
                            // send mail to user when assign task
                        // Call the sendEmail method of MailController
                        $logo = '<img src="https://neelnetworks.org/ems/image/neel.png" alt="Logo" style="width: 150px;">';

                                $email_for = 'New Task Assign';
                                $subject = 'You have a new Task for project "' . $check_project['name'] . '" assigned by "' . $check_user['username'] . '"';
                                
                                $message =  "I hope this email finds you well. I wanted to inform you that you have been assigned a new task for the project titled " .  $check_project['name']  . "by " .$check_user['username']."<br><br>
                                            The details of the task are as follows:<br><br>
                                            Task:".$data['task_description']."<br><br>";
                                            
                                            
                                 $template = view('emails/email-template');

                                $template = str_replace('{{email_for}}', $email_for, $template);
                                $template = str_replace('{{message}}', $message, $template);
                                $template = str_replace('{{logo}}', $logo, $template);


                                $sendMail = new MailController();
                                $sendMail->send_mail($check_assign_user['email'],$check_user['email'],$email_for,$subject,$template);

                        

                        // call the addNewNotification method of NotifictionController
                       $for = 'New Task';
                       $message = 'You have a new Task for project "' . $check_project['name'] . '" assigned by "' . $check_user['username'] . '"';

                       $notification  = new NotificationController();
                       $notification->addNewNotification($for,$message,$data['assign_to']);
                       
                       
                            $message = [
                                'status' => 201,
                                'error' => false,
                                'message' =>'Your task has been assigned..',
                                'data' => ['task_id'=>$this->task_model->getInsertID()]
                                
                            ];
                            $this->response->setJSON($message);
                            $response->setStatusCode(201);
                            return $response;
                        }else{
                            $message = [
                                'status' => 500,
                                'error' => true,
                                'message' =>'Something went wrong...'
                                
                            ];
                            $this->response->setJSON($message);
                            $response->setStatusCode(500);
                            return $response;
                        }
                        }else{
                            $message = [
                                'status' => 403,
                                'error' => true,
                                'message' =>'Sorry this project is not assigned to you so you cannot assign task for this project'
                                
                            ];
                            $this->response->setJSON($message);
                            $response->setStatusCode(403);
                            return $response;
                        }
                    
                    }

                   }
                }else{
                    $message = [
                        'status' => 403,
                        'error' => true,
                        'message' =>'May be task reciever account is suspended or not activate yet'
                        
                    ];
                    $this->response->setJSON($message);
                    $response->setStatusCode(403);
                    return $response;
                }
            }
        }
        }
    }
    
    
    // function for assign task without any project (can be assign employee to employee or admin/manager to employee)
    public function assignTaskWithoutProject(){
        $response = service('response');

        //validation
        $rules = [
            'assign_by'=>'required|numeric',
            'assign_to'=>'required|numeric',
            'task_name'=>'required',
            'task_description'=>'required',
        ];

        $message = [
            'assign_by'=>[
                'required'=>'Please provide task assigner user id'
            ],
            'assign_to'=>[
                'required'=>'Please provide task receiver user id'
            ],
            'task_name'=>[
                'required'=>'Could you please suggest a task name for notifying users '
            ],
            'task_description'=>[
                'required'=>"Please enter short description about task"
            ],
        ];

        if($this->validate($rules,$message)){
            // verify task assigner user id
            $data = json_decode(file_get_contents('php://input'), true);
            $verify_assigner = $this->user_model->find($data['assign_by']);
            if(!empty($verify_assigner)){
                // verify task receiver user id
                $verify_receiver = $this->user_model->find($data['assign_to']);
                if(!empty($verify_receiver)){
                    //verify that task resceiver user account should be activate and verified.
                    if($verify_receiver['is_active'] === '1' && $verify_receiver['status'] === 'active'){
                        // verify task assigner and task receiver should not match 
                        if($data['assign_by'] == $data['assign_to'] ){
                            $message = [
                                'status' => 403,
                                'error' => true,
                                'message' => 'Sorry you can not assign a task to your self ',
                                
                            ];
                            $status = 403;
                        }else{
                            // assign a task when all condition are true

                           $insert_data = [
                            'login_id'=> $data['assign_by'],
                            'assign_to' => $data['assign_to'],
                            'task_name' => $data['task_name'],
                            'task_description' => $data['task_description'],
                           ];

                           $insert = $this->task_model->insert($insert_data);

                           if($insert){
                               // send mail to user when assign task
                        // Call the sendEmail method of MailController
                        $logo = '<img src="https://neelnetworks.org/ems/image/neel.png" alt="Logo" style="width: 150px;">';

                                $email_for = 'New Task Assign ';
                                $subject = 'New Task Assigned: "' . $data['task_name'] . '" by ' . $verify_assigner['username'];
                                
                                $message =  "I hope this email finds you well. I wanted to inform you that you have been assigned a new task titled " . $data['task_name'] . "by " .$verify_assigner['username'].",<br><br>
                                            The details of the task are as follows:<br><br>
                                            Task:".$data['task_description']."<br><br>";
                                 $template = view('emails/email-template');

                                $template = str_replace('{{email_for}}', $email_for, $template);
                                $template = str_replace('{{message}}', $message, $template);
                                $template = str_replace('{{logo}}', $logo, $template);


                                $sendMail = new MailController();
                                $sendMail->send_mail($verify_receiver['email'], $verify_assigner['email'],$email_for,$subject,$template);

                       
                        // call the addNewNotification method of NotifictionController
                       $for = 'New Task';
                       $message = 'You have a new Task name   "' . $data['task_name'] . '" assigned by "' . $verify_assigner['username'] . '"';

                       $notification  = new NotificationController();
                       $notification->addNewNotification($for,$message,$data['assign_to']);
                       
                       
                            $message = [
                                'status' => 201,
                                'error' => false,
                                'message' => 'Your task is assign to '. $verify_receiver['username'],
                                
                            ];
                            $status = 201;
                           }else{
                            $message = [
                                'status' => 500,
                                'error' => true,
                                'message' => 'Something went wrong',
                                
                            ];
                            $status = 500;
                           }
                        }
                    }else{
                        $message = [
                            'status' => 403,
                            'error' => true,
                            'message' => 'The task receiver account may be suspended or not verified',
                            
                        ];
                        $status = 403;
                    }
                }else{
                    $message = [
                        'status' => 404,
                        'error' => true,
                        'message' => 'Sorry your task receiver user id is not valid',
                        
                    ];
                    $status = 404;
                }

            }else{
                $message = [
                    'status' => 404,
                    'error' => true,
                    'message' => 'Sorry your task asigner user id is not valid',
                    
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


    //function for revert task update to task assigner 
    public function returnTaskToUser(){
        $response = service('response');

        // validation 
        $rules = [
            'task_id'=>"required",
            // 'project_id'=>"required",
            'current_user'=>"required",
            'revert_description'=>"required",
            'revert_status'=>"required"
        ];

        $message = [
            'task_id'=>[
                'required'=>'Please provide valid task id'
            ],
            // 'project_id'=>[
            //     'required'=>'Please provide valid project id'
            // ],
            'current_user'=>[
                'required'=>'Please provide valid login user id'
            ],

        ];

        if(!$this->validate($rules,$message)){
            $message = [
				'status' => 400,
				'error' => true,
				'message' => $this->validator->getErrors(),
				
			];
            $staus = 400;
        }else{
            $data = json_decode(file_get_contents('php://input'), true);
            // verfiy task id
            $check_task = $this->task_model->find($data['task_id']);
            if(empty($check_task)){
                $message = [
                    'status' => 404,
                    'error' => true,
                    'message' => 'Sorry your task id is not valid',
                    
                ];
                $staus = 404;
            }else{
                // check task status its not be completed
                $task_status = $check_task['task_status'];
                if($task_status === 'Completed'){
                    $message = [
                        'status' => 403,
                        'error' => true,
                        'message' => 'Any kinds of updataion  are not allow when task is marked as Completed',
                        
                    ];
                    $staus = 403;
                    
                }else{
                    // check project id is valid or not
                    $check_project = $this->project_model->find($data['project_id']);
                    if(empty($check_project)){
                        $message = [
                            'status' => 404,
                            'error' => true,
                            'message' => 'Sorry your project id is not valid',
                            ];
                        $staus = 404;
                    }else{
                        //check login id is valid or not
                        $check_user = $this->user_model->find($data['current_user']);
                        if(empty($check_user)){
                            $message = [
                                'status' => 404,
                                'error' => true,
                                'message' => 'Sorry your login user id is not valid',
                                ];
                            $staus = 404;
                        }else{
                            // check task is assign to that user or not
                            $verify_task = $this->task_model->verifyTask($data['current_user'],$data['task_id']);
                            if(empty($verify_task)){
                                $message = [
                                    'status' => 403,
                                    'error' => true,
                                    'message' => 'Sorry this task is not assigned to you',
                                    ];
                                $staus = 403;
                            }else{
                         // check already rever to task or not
                        if($task_status === 'Review'){
                            $message = [
                                'status' => 403,
                                'error' => true,
                                'message' => 'You already give update about this task currently this task is in review process',
                                
                            ];
                            $staus = 403;
                        }else{
                            // insert revert data into datbase 
                            $current_time = time();
                         $time = date('Y-m-d H:i:s', $current_time);
                         $revert_data = [
                            'task_status'=>'Review',
                            'task_revert_description'=>$data['revert_description'],
                            'revert_status'=>$data['revert_status'],
                            'revert_time'=>$time];
                            
                            $update = $this->task_model->update($data['task_id'],$revert_data);
                            if($update){
                                // call the addNewNotification method of NotifictionController
                                    $for = 'Task Submission';
                                    $message = 'Task submited by ' .$check_user['username']. ' for task name ' .$verify_task[0]->task_name;

                                    $notification  = new NotificationController();
                                    $notification->addNewNotification($for,$message,$verify_task[0]->login_id);
                                    
                                    
                                $message = [
                                    'status' => 200,
                                    'error' => false,
                                    'message' => 'Your task is updated please wait for reviewing your task to assigner',
                                    
                                ];
                                $staus = 200;
                                
                                


                            }else{
                                $message = [
                                    'status' => 500,
                                    'error' => true,
                                    'message' => 'Something went wrong please try after some time..',
                                    
                                ];
                                $staus = 500;
                            }
                        }
                   
                            }
                        }
                    }
                }

            }
        }
         // send response 
         $this->response->setJSON($message);
         $response->setStatusCode($staus);
         return $response;
    }




    //function for update task only by task assigner  
    public function updateTask($current_user,$task_id,$task_status){
        $response = service('response');
        // check current_user id is valid or not
        $check_user = $this->user_model->find($current_user);
        if(empty($check_user)){
            $message = [
                'status' => 404,
                'error' => true,
                'message' => 'Your login user id is not valid',
                
            ];
            $staus = 404;
        }else{
        // check _task_id is valid or not
            $verify_task = $this->task_model->find($task_id);
            if(empty($verify_task)){
                $message = [
                    'status' => 404,
                    'error' => true,
                    'message' => 'Your task id is not valid',
                    
                ];
                $staus = 404;
            }else{
                // check given task is assigned by login  user or not
                $check_task = $this->task_model->checkTask($current_user,$task_id);
                if(empty($check_task)){
                    $message = [
                        'status' => 403,
                        'error' => true,
                        'message' => 'Sorry this task is not assigned by you...',
                        
                    ];
                    $staus = 403;
                }else{
                    
                    // update the task status when all condition is true
                    $update_data = [
                        'task_status'=>$task_status
                    ];
                    $update_task = $this->task_model->update($task_id,$update_data);
                    if($update_task){
                         // call the addNewNotification method of NotifictionController
                    $for = 'Task Updated';
                    //$message = 'Task submited by '.$check_user['username'].'for task name '.$verify_task['task_name'];
                    $message = 'You task ' . $verify_task['task_name'] . ' is updated by ' .$check_user['username'].' now your task status is '.$task_status;

                    $notification  = new NotificationController();
                    $notification->addNewNotification($for,$message,$verify_task['assign_to']); 
                    
                    
                        $message = [
                            'status' => 200,
                            'error' => false,
                            'message' => 'Your task is updated',
                            
                        ];
                        $staus = 200;
                                                      
                    
                    }else{
                        $message = [
                            'status' => 500,
                            'error' => true,
                            'message' => 'Something went wrong please try after some time...',
                            
                        ];
                        $staus = 500;
                    }
                }
            }
            }
            // send response 
        $this->response->setJSON($message);
        $response->setStatusCode($staus);
        return $response;
        }
        
        //function for get task list for login user 
        public function getTaskListByUserId(){
            helper('input');
            $request = service('request');
            $response = service('response');
            $uri = service('uri');
            
            $query =  $request->getGetPost();
           // check query string is present or not
            if(empty($query)){
                $message = [
                    'status' => 400,
                    'error' => true,
                    'message' => 'Please provide data to filter ',
                    
                ];
                $staus = 400;
            }else{
                // check required query string is present or not 
                if(!array_key_exists("task_status",$query) || !array_key_exists("login_user",$query)){
                $message = [
                    'status' => 400,
                    'error' => true,
                    'message' => 'Required query is not provide...',
                    
                ];
                $staus = 400;
            }else{
            // santize the data
            $task_status = $request->getGet('task_status');
            $sanitized_task_status = filter_var($task_status, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
            
            $login_user = $request->getGet('login_user');
            $sanitized_login_user = filter_var($login_user, FILTER_SANITIZE_NUMBER_INT);
            
            // first check the given id is valid or not \
            $check = $this->user_model->find($sanitized_login_user);
            if(!empty($check)){
               
                $get  =  $this->task_model->getTaskBySingleUser($sanitized_login_user,$sanitized_task_status);
            if(!empty($get)){
                $message = [
                    'status' => 200,
                    'error' => false,
                    'message' => "Data for your recommended input",
                    'data'=>$get
                    
                ];
                $staus = 202;
            }else{
                $message = [
                    'status' => 404,
                    'error' => true,
                    'message' => "Sorry no data available for '{$sanitized_task_status}' AND user id '{$sanitized_login_user}'"
                    
                ];
                $staus = 404;
            }
        }else{
            $message = [
                'status' => 404,
                'error' => true,
                'message' => "Your provided user id does not match with our database"
                
            ];
            $staus = 404;
        }

            }
            }
        // send response 
        $this->response->setJSON($message);
        $response->setStatusCode($staus);
        return $response;

        }
        
        //function for get task assign list or login user
        public function getmyAssignTaskListByUserId($login_user){
            $response = service('response');
            
                // check provided user id is valid or not
                $data = json_decode(file_get_contents('php://input'), true);
                $check_user = $this->user_model->find($login_user);
                if(empty($check_user)){
                    $message = [
                        'status' => 404,
                        'error' => true,
                        'message' => 'Sorry your provide user id does not match with our database',
                        ];
                    $status = 404;
                }else{
                    // find all task assign list records from database
                    $get_assign_task_list = $this->task_model->getTaskAssignByCurrentUser($login_user);
                    if(empty($get_assign_task_list)){
                        $message = [
                            'status' => 404,
                            'error' => true,
                            'message' => 'You have not assign any projects to any one yet',
                            ];
                        $status = 404;
                    }else{
                        $message = [
                            'status' => 200,
                            'error' => false,
                            'message' => 'Your assign task list',
                            'data'=>$get_assign_task_list
                            ];
                        $status = 200;
                    }
                }
            
             // send response 
        $this->response->setJSON($message);
        $response->setStatusCode($status);
        return $response;
        }

        
        
    
}
