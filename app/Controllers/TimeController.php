<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TimeModel;
use App\Models\UsersModel;
use App\Models\ProjectModel;
use App\Models\ProjectAssignModel;
use App\Models\TaskModel;
use CodeIgniter\API\ResponseTrait;

class TimeController extends BaseController
{
    use ResponseTrait;
    protected $time_model;
    protected $project_model;
    protected $user_model;
    protected $assign_model;
    protected $task_model;

    public function __construct() {	
		$db = db_connect();
		$this->time_model = new TimeModel($db);
 		$this->project_model = new ProjectModel($db);
       $this->user_model = new UsersModel($db);
       $this->assign_model = new ProjectAssignModel($db);
       $this->task_model = new TaskModel($db);
    }
   public function addTimeEntry()
    {
        $request = service('request');
        $response = service('response');
// primary data required fields
        $rules = [
            "user_id" => "required",
            "date"=>"required|valid_date",
            "time"=>"required",
            "time_entries_for" =>"required",
            "description" => "required",
           ];


        $message = [
            "user_id"=>[
                "required" => "Please enter user id",
            ],
           
            "date"=>[
                "required" => "Please enter project date",
                "valid_date"=>"please enter valid date"
            ],
            "time"=>[
                "required" => "Please enter project time",
               
            ],
            "time_entries_for"=>[
                "required"=>"Please provide information on the type of work entries"
            ], 
            "description"=>[
                "required"=>"Please provide short descriptipon about your work"
            ], 

        ];

        if(!$this->validate($rules,$message)){
            $respnse_message = [
				'status' => 400,
				'error' => true,
				'message' => $this->validator->getErrors(),
				
			];
            $status = 400;
        
        $this->response->setJSON($respnse_message);
        $response->setStatusCode(400);
        return $response;

        }else{
            
            $data = json_decode(file_get_contents('php://input'), true);

            // check enter user id is valid or not 
            $check_user = $this->user_model->find($data['user_id']);
            if(empty($check_user)){
                $respnse_message = [
                    'status' => 404,
                    'error' => true,
                    'message' => 'Sorry your provided user id is not valid',
                    
                ];
                $status = 404;
            
            $this->response->setJSON($respnse_message);
            $response->setStatusCode(400);
            return $response;
            }else{
                $work_for = $data['time_entries_for'];
                switch($work_for)
        {
            case 'for_project':
                //  $this->add_time_entries_for_project();
    
                 list($response_message, $status) = $this->add_time_entries_for_project($data);
                 $this->response->setJSON($response_message);
                 $this->response->setStatusCode($status);
                 return $this->response;
                 break;
               
                break;
            case 'for_task':
                list($response_message, $status) = $this->add_time_entries_for_task($data);
                 $this->response->setJSON($response_message);
                 $this->response->setStatusCode($status);
                 return $this->response;
                 break;
                
                break;
            case 'for_proxy':
                
                list($response_message, $status) = $this->add_time_entries_for_proxy($data);
                 $this->response->setJSON($response_message);
                 $this->response->setStatusCode($status);
                 return $this->response;
                 break;
            
            default:
            $respnse_message = [
                'status' => 400,
                'error' => true,
                'message' => 'You have selected wrong operations',
                
            ];
            $status = 400;
            $this->response->setJSON($respnse_message);
            $response->setStatusCode(400);
            return $response;
                break;
        }
            }
            

        }
        
    }
    
    //function for add time entries for project
    private function add_time_entries_for_project($data){
        
                 
    //validation for required data 
    

    $rules = [
        'project_id' => 'required'
        
    ];

    $message = [
        'project_id' =>[
            "required" => "Please enter valid project id",
        ],

    ];

    if(!$this->validate($rules,$message)){
        $response_message = [
            'status' => 400,
            'error' => true,
            'message' => $this->validator->getErrors()
            ];

        $status = 400;
    }else{
        // check entered project is valid or not 
        $check = $this->project_model->find($data['project_id']);
        if(!empty($check)){
            // check project is assign to current user or not
            $verify_assign = $this->assign_model->verifyProjectAssignOrNot($data['user_id'],$data['project_id']);
            if($verify_assign === 'no'){
                $response_message = [
                    'status' => 403,
                    'error' => true,
                    'message' => 'Sorry this project is not assigned to you'
                    ];
        
                $status = 403;
            }else{
                
                // time entry for project should be couple of 15 minute
                $time = (float)$data['time'];
                if (fmod($time * 60, 15) != 0) {
                    $response_message = [
                        'status' => 400,
                        'error' => true,
                        'message' => 'Your work time should be a multiple of 15 minutes'
                    ];
                    $status = 400;
                }else{
                    // check project status should not be hold or not completed
                    $check_project_status = $this->project_model->checkProjectStatus($data['project_id']);
                    if($check_project_status[0]->project_status === 'Hold' || $check_project_status[0]->project_status === 'Completed' ){
                        $response_message = [
                            'status' => 403,
                            'error' => true,
                            'message' => 'May be your project is on hold or already Completed'
                            ];
                
                        $status = 403;
                    }else{
                        $insert_data = [
                            'user_id' => $data['user_id'],
                            'project_id' => $data['project_id'],
                            'time' => sprintf('%02d:%02d', (int) $time, fmod($time, 1) * 60),
                            'description' => $data['description'],
                            'date' => $data['date'],
                            'entries_for'=>$data['time_entries_for']
                        ];
                        
                        $insert = $this->time_model->insert($insert_data);
                        if($insert){
                            $response_message = [
                                'status' => 201,
                                'error' => false,
                                'message' => 'Your time entries added for project'
                                ];
                    
                            $status = 201;
                        }else{
                            $response_message = [
                                'status' => 500,
                                'error' => true,
                                'message' => 'Something went wrong'
                                ];
                    
                            $status = 500;
                        }
                    }

                    
                 }
            }
        }else{
            $response_message = [
                'status' => 404,
                'error' => true,
                'message' => 'Sorry your project id does not match with our database'
                ];
    
            $status = 404;
        }
        

        
        }

        return [$response_message, $status];

}

    //function for add time entries for task
    private function add_time_entries_for_task($data){
        // validation for required data
        $rules = [
            'task_id'=>'required'
        ];
        $message =[
            'task_id'=>[
                'required'=>'Please provide valid task id'
            ],


        ];

        if(!$this->validate($rules,$message)){
            $response_message = [
                'status' => 400,
                'error' => true,
                'message' => $this->validator->getErrors()
                ];
    
            $status = 400;
        }else{
            //check task id is valid or not
            $check_task = $this->task_model->find($data['task_id']);
            if(!empty($check_task)){
                // verify task is assign or not to login user
                $verify_task = $this->task_model->verifyTask($data['user_id'],$data['task_id']);
                if(!empty($verify_task)){
                    // check task status .It should be pending 
                    $task_status = $this->task_model->checkTaskStatus($data['task_id']);
                   if($task_status[0]->task_status === 'Pending'){
                    // time entry for project should be couple of 15 minute
                    $time = (float)$data['time'];
                    if (fmod($time * 60, 15) != 0) {
                    $response_message = [
                        'status' => 400,
                        'error' => true,
                        'message' => 'Your work time should be a multiple of 15 minutes'
                    ];
                    $status = 400;
                }else{
                    $insert_data = [
                        'user_id' => $data['user_id'],
                        'task_id' => $data['task_id'],
                        'time' => sprintf('%02d:%02d', (int) $time, fmod($time, 1) * 60),
                        'description' => $data['description'],
                        'date' => $data['date'],
                        'entries_for'=>$data['time_entries_for']
                    ];

                    $insert = $this->time_model->insert($insert_data);
                    if($insert){
                        $response_message = [
                            'status' => 201,
                            'error' => false,
                            'message' => 'Your time entries added for task'
                            ];
                
                        $status = 201;
                    }else{
                        $response_message = [
                            'status' => 500,
                            'error' => true,
                            'message' => 'Something went wrong please try again after some time....'
                            ];
                
                        $status = 500;
                    }

                }
                   }else{
                    $response_message = [
                        'status' => 403,
                        'error' => true,
                        'message' => 'Time entries are not allow when task status is in completed or review'
                        ];
            
                    $status = 403;
                   }
                }else{
                    $response_message = [
                        'status' => 403,
                        'error' => true,
                        'message' => 'Sorry this task is not assign to you'
                        ];
            
                    $status = 403;
                }
            }else{
                $response_message = [
                    'status' => 404,
                    'error' => true,
                    'message' => 'Sorry your provided task id is not valid'
                    ];
        
                $status = 404;
            }
        }
        return [$response_message, $status];
    }

    //function for add time entries for proxy
    private function add_time_entries_for_proxy($data){
         // validation for required data
         $rules = [
            'work_for' => 'required',
         ];

         $message = [
            'work_for'=>[
                'required'=>'Kindly share details about the work you have done, including any testing, learning, or other relevant activities that you have undertaken.'
            ],
        ];

        if($this->validate($rules,$message)){
            // time entry for project should be couple of 15 minute
            $time = (float)$data['time'];
            if (fmod($time * 60, 15) != 0) {
                $response_message = [
                    'status' => 400,
                    'error' => true,
                    'message' => 'Your work time should be a multiple of 15 minutes'
                ];
                $status = 400;
            }else{
            //insert the data into db
            $insert_data = [
                'user_id' => $data['user_id'],
                'time' => sprintf('%02d:%02d', (int) $time, fmod($time, 1) * 60),
                'description' => $data['description'],
                'date' => $data['date'],
                'work_description_for_proxy' =>$data['work_for'],
                'entries_for'=>$data['time_entries_for']
            ];

            $insert = $this->time_model->insert($insert_data);
            if($insert){
                $response_message = [
                    'status' => 201,
                    'error' => false,
                    'message' => 'Your time entries added for task'
                    ];
        
                $status = 201;
            }else{
                $response_message = [
                    'status' => 500,
                    'error' => true,
                    'message' => 'Something went wrong please try again after some time....'
                    ];
        
                $status = 500;
            }
        }
        }else{
            $response_message = [
                'status' => 400,
                'error' => true,
                'message' => $this->validator->getErrors()
                ];
    
            $status = 400;
        }
        return [$response_message, $status];
    }
    
    //for fetch data for added all time entry for login user 
    public function list($user_id,$date){
        $response = service('response');
        // first check user id is exist or not
        $user = $this->user_model->where('id',$user_id)->first();
        

        if(!empty($user)){
            // create variable for store what we to find in multiple condition
            
            
            $data = $this->time_model->get_data($user_id,$date);
            
            
            
           if(empty($data)){
               $error = [
				'status' => 400,
				'error' => true,
				'message' => 'Sorry no data found for your requested date and the current user id',
			];
			$this->response->setJSON($error);
            $response->setStatusCode(400);
            return $response;
           }else{
                
          $response = [
                    'status' =>  200,
                    'error' => false,
                    'message' => 'Time enties details for your requested date = " '.$date.' "and user id = " '.$user_id.'',
                    'data' => $data
                   
                ];
           }
            
            
            
            
        }else{
            $error = [
				'status' => 400,
				'error' => true,
				'message' => 'Sorry user id does not match with our records',
			];
			$this->response->setJSON($error);
            $response->setStatusCode(400);
            return $response;
        }
        return $this->setResponseFormat('json')->respond($response);
    }

//function for reupdate the time entries after add
    public function EditExistingTimeEntries(){
        $response = service('response');
       // required primary data 
       $rules = [
        "user_id" => "required",
        "entries_for"=>"required",
        "entries_id"=>"required",
        ];

        $message = [
            'user_id'=>[
                'required'=>'Please provide valid user id'
            ],
            'entries_for'=>[
                'required'=>'Please provide information on the type of work entries'
            ],
            'entries_id'=>[
                'required'=> 'Something is missing please contact to your developer.'
            ]
        ];

        if($this->validate($rules,$message)){
            $data = json_decode(file_get_contents('php://input'), true);
            // check user id is valid or not 
            $check_user = $this->user_model->find($data['user_id']);
            if(!empty($check_user)){
               //check time entries is valid or not 
               $check_entries_id = $this->time_model->find($data['entries_id']);
               if(!empty($check_entries_id)){
                // check provided time entries releted to user id or not
                if($data['user_id'] == $check_entries_id['user_id']){
                   // check work entries for 
                   $work_for = $data['entries_for'];
                   switch($work_for){
                    case 'for_project':
                        list($response_message, $status) = $this->edit_time_entries_for_project($data);
                        $this->response->setJSON($response_message);
                        $this->response->setStatusCode($status);
                        return $this->response;
                        break;

                        case 'for_task':
                            list($response_message, $status) = $this->edit_time_entries_for_task($data);
                        $this->response->setJSON($response_message);
                        $this->response->setStatusCode($status);
                        return $this->response;
                        break;
                            
                        

                        case 'for_proxy':
                            list($response_message, $status) = $this->edit_time_entries_for_proxy($data);
                        $this->response->setJSON($response_message);
                        $this->response->setStatusCode($status);
                        return $this->response;
                        break;
                           
                        default:
                        $respnse_message = [
                            'status' => 400,
                            'error' => true,
                            'message' => 'You have selected wrong operations',
                            
                        ];
                        $status = 400;
                        break;
                   }
                }else{
                    $respnse_message = [
                        'status' => 403,
                        'error' => true,
                        'message' => 'Sorry this time entries is not releted to you',
                        
                    ];
                    $status = 403;
                }
               }else{
                $respnse_message = [
                    'status' => 404,
                    'error' => true,
                    'message' => 'Something is not valid please contact with your developer',
                    
                ];
                $status = 404;
               }
               
            }else{
                $respnse_message = [
                    'status' => 404,
                    'error' => true,
                    'message' => 'Sorry your provided user id is not valid',
                    
                ];
                $status = 404;
            }
                
            
        }else{
            $respnse_message = [
				'status' => 400,
				'error' => true,
				'message' => $this->validator->getErrors(),
				
			];
            $status = 400;
        
        
        }

        //send response
        $this->response->setJSON($respnse_message);
        $response->setStatusCode(400);
        return $response;
    }


    // method for update the time entries for project
    private function edit_time_entries_for_project($data){
        // required data for edit the project time entry
        $rules = [
            "project_id"=>"required",
            "date"=>"required|valid_date",
            "time"=>"required",
            "description" => "required",
        ];

        $message = [
            "project_id"=>[
                "required"=>"Please provide valid project id"
            ],

            "date"=>[
                "required" => "Please enter project date",
                "valid_date"=>"please enter valid date"
            ],

            "time"=>[
                "required"=>"Please enter valid time"
            ],

            "description"=>[
                "required" => "Please provide short descriptipon about your work"
            ]

            ];

            if(!$this->validate($rules,$message)){
                $response_message = [
                    'status' => 400,
                    'error' => true,
                    'message' => $this->validator->getErrors(),
                ];
                $status = 400;
                
            }else{
                // please provide valid project id
                $valid_project = $this->project_model->find($data['project_id']);
                if(!empty($valid_project)){
                    // entered date should not be future date 
                    $currentDate = date('Y-m-d'); // Get the current date
                    if($data['date'] > $currentDate){
                        $response_message = [
                            'status' => 403,
                            'error' => true,
                            'message' => 'Sorry you can not entered future date ',
                        ];
                        $status = 403;
                    }
                    $previousDay = date('Y-m-d', strtotime('-1 day'));
                    if ($data['date'] < $previousDay || $data['date'] > $currentDate) {
                        $response_message = [
                            'status' => 403,
                            'error' => true,
                            'message' => 'Invalid date selection. Only today and the previous day are allowed.'
                            ];
                
                        $status = 403;
                    }else{
                        // verify entered project is assign to current user or not 
                        $verify_assign = $this->assign_model->verifyProjectAssignOrNot($data['user_id'],$data['project_id']);
                        if($verify_assign === 'no'){
                            $response_message = [
                                'status' => 403,
                                'error' => true,
                                'message' => 'Sorry this project is not assigned to you'
                                ];
                    
                            $status = 403;
                    }else{
                        // check project status 
                        $check_project_status = $this->project_model->checkProjectStatus($data['project_id']);
                        if($check_project_status[0]->project_status === 'Hold' || $check_project_status[0]->project_status === 'Completed' ){
                            $response_message = [
                                'status' => 403,
                                'error' => true,
                                'message' => 'May be your project is on hold or already Completed'
                                ];
                    
                            $status = 403;
                    }else{
                        // work time should couple of 15 minute
                        // time entry for project should be couple of 15 minute
                        $time = (float)$data['time'];
                        if (fmod($time * 60, 15) != 0) {
                            $response_message = [
                                'status' => 400,
                                'error' => true,
                                'message' => 'Your work time should be a multiple of 15 minutes'
                            ];
                            $status = 400;
                        }else{
                            // all the validation is passed then update data
                            $update_data = [
                                'project_id' =>$data['project_id'],
                                'date' => $data['date'],
                                'time' => sprintf('%02d:%02d', (int) $time, fmod($time, 1) * 60),
                                'description' => $data['description'],
                                'is_edit' => 'yes'
                            ];

                            $update = $this->time_model->where(["id" => $data['entries_id']])->set($update_data)->update();

                            if($update){
                                $response_message = [
                                    'status' => 200,
                                    'error' => false,
                                    'message' => 'The updates to your time entries for the project have been made.',
                                ];
                                $status = 200;
                            }else{
                                $response_message = [
                                    'status' => 500,
                                    'error' => true,
                                    'message' => 'Something went wrong please try again after some time',
                                ];
                                $status = 500;
                            }
                            
                        }   
                    }
                }
                }
                }else{
                    $response_message = [
                        'status' => 404,
                        'error' => true,
                        'message' => 'Sorry your provided project id does not match with our database..',
                    ];
                    $status = 404;
                }
            }

            // return response
            return [$response_message, $status];
    }

     // method for update the time entries for task
     private function edit_time_entries_for_task($data){
        // validation for required data 
        $rules = [
            "task_id" => "required",
            "date"=>"required|valid_date",
            "time"=>"required",
            "description" => "required",
        ];

        $message = [
            'task_id'=>[
                'required' => 'Please provide valid task id'
            ],
            "date"=>[
                "required" => "Please enter working  date",
                "valid_date"=>"please enter valid date"
            ],

            "time"=>[
                "required"=>"Please enter valid time"
            ],

            "description"=>[
                "required" => "Please provide short descriptipon about your work"
            ]
        ];

        if(!$this->validate($rules,$message)){
            $response_message = [
                'status' => 400,
                'error' => true,
                'message' => $this->validator->getErrors(),
            ];
            $status = 400;
     }else{
        // check task id is valid or not
        $task_id =  $this->task_model->find($data['task_id']);
        if(!empty($task_id)){
            // entered date should not be future date 
            $currentDate = date('Y-m-d'); // Get the current date
            if($data['date'] > $currentDate){
                $response_message = [
                    'status' => 403,
                    'error' => true,
                    'message' => 'Sorry you can not entered future date ',
                ];
                $status = 403;
            }
            $previousDay = date('Y-m-d', strtotime('-1 day'));
            if ($data['date'] < $previousDay || $data['date'] > $currentDate) {
                $response_message = [
                    'status' => 403,
                    'error' => true,
                    'message' => 'Invalid date selection. Only today and the previous day are allowed.'
                    ];
        
                $status = 403;
            }else{
                // verify entered task is assign to current user or not 
                $verify_task = $this->task_model->verifyTask($data['user_id'],$data['task_id']);
                if(!empty($verify_task)){
                   // check task status
                   $check_task_status = $this->task_model->checkTaskStatus($data['task_id']);
                   if($check_task_status[0]->task_status === 'Completed' || $check_task_status[0]->task_status === 'Review'){
                    $response_message = [
                        'status' => 403,
                        'error' => true,
                        'message' => 'Apologies, but this task is currently under review or has already been completed.',
                    ];
                    $status = 403;
                   }else{
                     // time entry for project should be couple of 15 minute
                     $time = (float)$data['time'];
                     if (fmod($time * 60, 15) != 0) {
                         $response_message = [
                             'status' => 400,
                             'error' => true,
                             'message' => 'Your work time should be a multiple of 15 minutes'
                         ];
                         $status = 400;
                   }else{
                    // all the validation is passed then update data
                    $update_data = [
                        'task_id' =>$data['task_id'],
                        'date' => $data['date'],
                        'time' => sprintf('%02d:%02d', (int) $time, fmod($time, 1) * 60),
                        'description' => $data['description'],
                        'is_edit' => 'yes'
                    ];
                    
                    $update = $this->time_model->where(["id" => $data['entries_id']])->set($update_data)->update();

                    if($update){
                        $response_message = [
                            'status' => 200,
                            'error' => false,
                            'message' => 'The updates to your time entries for the task have been made',
                        ];
                        $status =200;
                    }else{
                        $response_message = [
                            'status' => 500,
                            'error' => true,
                            'message' => 'Something went wrong',
                        ];
                        $status = 500;
                    }

                   }
                }
                }else{
                    $response_message = [
                        'status' => 403,
                        'error' => true,
                        'message' => 'Sorry this task is not assigned to you',
                    ];
                    $status = 403;
                }
            }
        }else{
            $response_message = [
                'status' => 404,
                'error' => true,
                'message' => 'Sorry your task id is not valid',
            ];
            $status = 404;
        }
     }
     
     // return response
     return [$response_message, $status];

    }

    //function for edit proxy time entries
    private function edit_time_entries_for_proxy($data){
        // validation for required data 
        $rules = [
            "work_description_for_proxy" => "required",
            "date"=>"required|valid_date",
            "time"=>"required",
            "description" => "required",
        ];

        $message = [
            'work_description_for_proxy'=>[
                'required'=>'Kindly share details about the work you have done, including any testing, learning, or other relevant activities that you have undertaken.'
            ],
            "date"=>[
                "required" => "Please enter working  date",
                "valid_date"=>"please enter valid date"
            ],

            "time"=>[
                "required"=>"Please enter valid time"
            ],

            "description"=>[
                "required" => "Please provide short descriptipon about your work"
            ]
        ];

        if(!$this->validate($rules,$message)){
            $response_message = [
                'status' => 400,
                'error' => true,
                'message' => $this->validator->getErrors(),
            ];
            $status = 400;
     }else{
        // entered date should not be future date 
        $currentDate = date('Y-m-d'); // Get the current date
        if($data['date'] > $currentDate){
            $response_message = [
                'status' => 403,
                'error' => true,
                'message' => 'Sorry you can not entered future date ',
            ];
            $status = 403;
        }
        // no previous date are allow
        $previousDay = date('Y-m-d', strtotime('-1 day'));
        if ($data['date'] < $previousDay || $data['date'] > $currentDate) {
            $response_message = [
                'status' => 403,
                'error' => true,
                'message' => 'Invalid date selection. Only today and the previous day are allowed.'
                ];
    
            $status = 403;
        }else{
            // time entry for proxy should be couple of 15 minute
            $time = (float)$data['time'];
            if (fmod($time * 60, 15) != 0) {
                $response_message = [
                    'status' => 400,
                    'error' => true,
                    'message' => 'Your work time should be a multiple of 15 minutes'
                ];
                $status = 400;
          }else{
            // all the validation is passed then update data
            $update_data = [
                'work_description_for_proxy' =>$data['work_description_for_proxy'],
                'date' => $data['date'],
                'time' => sprintf('%02d:%02d', (int) $time, fmod($time, 1) * 60),
                'description' => $data['description'],
                'is_edit' => 'yes'
            ];
            $update = $this->time_model->where(["id" => $data['entries_id']])->set($update_data)->update();
            if($update){
                $response_message = [
                    'status' => 200,
                    'error' => false,
                    'message' => 'The updates to your time entries for the proxy have been made',
                ];
                $status =200;
            }else{
                $response_message = [
                    'status' => 500,
                    'error' => true,
                    'message' => 'Something went wrong',
                ];
                $status =500;
            }

          }
        }
     }


        return [$response_message, $status];
    }
    
}


