<?php

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;
use App\Models\UsersModel;
use App\Models\ProjectModel;
use App\Models\TaskModel;
use App\Models\CommentsModel;
class CommentsController extends BaseController
{
    protected $user_model;
    protected $project_model;
    protected $task_model;
    protected $comments_model;
    use ResponseTrait;
    public function __construct() {	
		$db = db_connect();
		
 		$this->project_model = new ProjectModel($db);
       $this->user_model = new UsersModel($db);
       $this->task_model = new TaskModel($db);
       $this->comments_model = new CommentsModel($db);
    }
    public function add_comments()
    {
        $response = service('response');
        // validation
        $rules = [
            'task_id' => 'required',
            'task_commentor' =>'required',
            'task_comments'=>'required'
            
        ];

        $message = [
            'task_id'=>[
                "required"=>'Please provide valid task id'
            ],
            'project_id'=>[
                "required"=>'Please provide valid project id'
            ],
            'task_commentor'=>[
                "required"=>'Please provide valid commentor id'
            ],
            'task_comments'=>[
                "required"=>'Please provide valid comments'
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
            // first check task id is valid or not 
            $check_task = $this->task_model->find($data['task_id']);
            if(!empty($check_task)){
                // check task status its not be completed
                $task_status = $check_task['task_status'];
                if($task_status === 'Completed'){
                    $message = [
                        'status' => 403,
                        'error' => true,
                        'message' => 'Comments are not allow when task is marked as Completed',
                        
                    ];
                    $staus = 403;
                }else{
                    // check project id is valid or not 
               
                
                     // check commentor id is valid or not 
                     $check_commentor = $this->user_model->find($data['task_commentor']);
                     if(!empty($check_commentor)){
                        // all condition true then insert it into database
                        $insert_data = $this->comments_model->insert($data);
                        if($insert_data){
                            $message = [
                                'status' => 201,
                                'error' => false,
                                'message' => 'Your comments added ',
                                'data' => ['comments_id'=>$this->comments_model->getInsertID(),'Commentor'=>$check_commentor['username']]
                            ];
                            $staus = 201;
                        }else{
                            $message = [
                                'status' => 500,
                                'error' => true,
                                'message' => 'Something went wrong..... ',
                                
                            ];
                            $staus = 500;
                        }
                     }else{
                        $message = [
                            'status' => 404,
                            'error' => true,
                            'message' => 'Sorry your login  id is not valid ',
                            
                        ];
                        $staus = 404;
                     }
                     
                     
               
                }
                
            }else{
                $message = [
                    'status' => 404,
                    'error' => true,
                    'message' => 'Sorry your task id is not valid ',
                    
                ];
                $staus = 404;
            }
        }
        // send response 
        $this->response->setJSON($message);
        $response->setStatusCode($staus);
        return $response;
    }
    
   //function for get all comments for login user by task id
     public function allComments($current_user,$task_id){
        // first check current user id is valid or not
        $response = service('response');
        $check_user = $this->user_model->find($current_user);
            if(!empty($check_user)){
                //check task id is valid or not
                $check_task =  $this->task_model->find($task_id);
                if(!empty($check_task)){
               //check this task is assign or not to current user
               $check_assign = $this->task_model->verifyTask($current_user,$task_id);
               if(!empty($check_assign)){
               // find all comments in between task assigner and task current user
               $comments = $this->comments_model->getAllComments($task_id);
               if(!empty($comments)){
                $message = [
                    'status' => 200,
                    'error' => false,
                    'message' => 'All comments for this task',
                    'comments'=>[$comments]
                    
                ];
                $staus = 200;
                
               }else{
                $message = [
                    'status' => 200,
                    'error' => false,
                    'message' => 'You dont have any comments yet',
                    
                ];
                $staus = 200;
               }
               }else{
                $message = [
                    'status' => 403,
                    'error' => true,
                    'message' => 'Sorry this task is not assign to you',
                    
                ];
                $staus = 403;
               }
                }else{
                    $message = [
                        'status' => 404,
                        'error' => true,
                        'message' => 'Sorry your provided task id does not match with our database',
                        
                    ];
                    $staus = 404;
                }
            }else{
                $message = [
                    'status' => 404,
                    'error' => true,
                    'message' => 'Sorry your provided login user id does not match with our database',
                    
                ];
                $staus = 404;
            }
            // send response 
        $this->response->setJSON($message);
        $response->setStatusCode($staus);
        return $response;
    }
    //function for get all comments on particular task for task assigner(who assign a task also get comment list)
    public function comments_on_perticular_task($task_assigner,$task_id){
        $response = service('response');
        // first check task assigner id is valid or not
        $verify_task_assigner = $this->user_model->find($task_assigner);
        if(!empty($verify_task_assigner)){
            // verify task id is valid or not
            $verify_task =  $this->task_model->find($task_id);
            if(!empty($verify_task)){
            // check task is assign by provided id or not
            $check_assign = $this->task_model->checkTask($task_assigner,$task_id);
            if(!empty($check_assign)){
            // get all comments 
            $comments = $this->comments_model->getAllComments($task_id);
            if(!empty($comments)){
                $message = [
                    'status' => 200,
                    'error' => false,
                    'message' => 'All comments for this task',
                    'comments'=>[$comments]
                    
                ];
                $staus = 200;
            }else{
                $message = [
                    'status' => 200,
                    'error' => false,
                    'message' => 'You dont have any comments yet for task assign by you',
                    
                ];
                $staus = 200; 
            }
            }else{
                 $message = [
                    'status' => 403,
                    'error' => true,
                    'message' => 'Sorry this task is not assign by you',
                        
                    ];
                    $staus = 403;
            }
            }else{
                $message = [
                    'status' => 404,
                    'error' => true,
                    'message' => 'Sorry your provided task id does not match with our database',
                    
                ];
                $staus = 404;
            }
        }else{
            $message = [
                'status' => 404,
                'error' => true,
                'message' => 'Sorry your provided task assigner id does not match with our database',
                
            ];
            $staus = 404;
        }
        // send response 
        $this->response->setJSON($message);
        $response->setStatusCode($staus);
        return $response;
    }
}
