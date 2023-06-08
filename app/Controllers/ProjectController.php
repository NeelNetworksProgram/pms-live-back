<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProjectModel;
use App\Models\ProjectAssignModel;
use CodeIgniter\API\ResponseTrait;
use App\Models\UsersModel;

class ProjectController extends BaseController
{
    protected $project_model;
    protected $user_model;
    protected $assign_model;
    use ResponseTrait;
    public function __construct() {	
		$db = db_connect();
		$this->project_model = new ProjectModel($db);
        $this->user_model = new UsersModel($db);
        $this->assign_model = new ProjectAssignModel($db);

    }

    // function for add new project
    public function addproject()
    {
        $response = service('response');


        $rules = [
            "user_id" =>"required",
            "name" => "required|is_unique[projects.name]"
            
           ];
        $message = [
            "user_id"=>[
                "required" => "Please enter valid user id "
                
            ],
            "name"=>[
                "required" => "Please enter project name",
                "is_unique" => "Project name '{value}'  already exit please enter new project name"
            ],
        ];
        if(!$this->validate($rules,$message)){
            $error = [
				'status' => 400,
				'error' => true,
				'message' => $this->validator->getErrors(),
				'data' => []
			];
            $this->response->setJSON($error);
            $response->setStatusCode(400);
            return $response;

        }else{
            //$user_model = new UsersModel();
            $data = json_decode(file_get_contents('php://input'), true);
            //fetch data from database to check user authority according to given user id 
            $user_data =   $this->user_model->where('id',$data['user_id'])->first();
            if(!empty($user_data)){
                $auth = $user_data['authorized_to'];
                // convert string to array
                $auth_array = explode(',',$auth);
                // check user has authority to create or not 
                if(in_array("create",$auth_array)){
                    $insert_date = [
                        'name'=> $data['name']
                    ];
                    $current_date = time();
                    $date = date('Y-m-d H:i:s', $current_date);
                    $data['created_at'] = $date;
                    $insert = $this->project_model->insert_project($data);
        
                    if($insert){
                        $response = [
                            'status' => 201,
                            'error' => false,
                            'message' => 'Project added succesfully',
                            'data' => ['project_id'=>$this->project_model->getInsertID()]
                        ];
                    }else{
                        $response = [
                            'status' => 500,
                            'error' => true,
                            'message' => 'Something went wrong please try after some time.....',
                            'data' => []
                        ];
                    }
                }else{
                    $error = [
                        'status' => 403,
                        'error' => true,
                        'message' => 'Sorry you are not authorized to add new project',
                        'data' => []
                    ];
                    $this->response->setJSON($error);
                    $response->setStatusCode(403);
                    return $response;
                }
            }else{
                $error = [
                    'status' => 400,
                    'error' => true,
                    'message' => 'Sorry your user id is not valid ',
                    'data' => []
                ];
                $this->response->setJSON($error);
                $response->setStatusCode(400);
                return $response;
                }
            
            }

        return $this->respondCreated($response);
       
        
    }


    // function for list all project list

    public function all_project(){
        $request = service('request');
        
       
        
        $status = 200;
        $error = false;
        
         $data = $this->assign_model->getUserListOnSingleProject();
        $message = "All project listed succesfully...";
    

        $response = [
          'status' => $status,
            'error' => $error,
            'message' => $message,
         'data'=>$data
        ];
         return $this->setResponseFormat('json')->respond($response);
   

    
}

    // function for update project

    public function update(){
        $response = service('response');
        $data = json_decode(file_get_contents('php://input'), true);
        if(!isset($data['id']) || empty($data['id'])){
            $error = [
                'status' => 400,
                'error' => true,
                'message' => 'Please enter project id atleast',
                'data' => []
            ];
            $this->response->setJSON($error);
            $response->setStatusCode(400);
            return $response;
        }
        $project_user = $this->project_model->find($data['id']);
   //We added a validation to check if the project name in the JSON data already exists in the database, and if so, ensured that the new project name is not the same.
  if(isset($data['name']) && $data['name'] != $project_user['name'] ){
    $rules = [
        "user_id"=>"required",
        "id" =>"required",
        "name" => "is_unique[projects.name]",
    ];
    $message =[
        "user_id"=>[
            "required" => "Please enter valid user id "
        ],
        "id"=>[
            "required" => "Please enter valid project id"
        ],
        "name"=>[
            "required"=>"Please enter project name for updation",
             "is_unique"=>"Your project name already exist"
        ],
        
    ];
}else{
    $rules = [
        "user_id"=>"required",
        "id" =>"required",
        
    ];
    $message =[
        "user_id"=>[
            "required" => "Please enter valid user id "
        ],
        "id"=>[
            "required" => "Please enter valid project id"
        ],
        
    ];
}
        

        if(!$this->validate($rules,$message)){
            
            $error = [
				'status' => 400,
				'error' => true,
				'message' => $this->validator->getErrors()
			];
            $this->response->setJSON($error);
            $response->setStatusCode(400);
            return $response;


        }else{

        
       //fetch data from database to check user authority according to given user id
       $fetch_user = $this->user_model->where('id',$data['user_id'])->first();
        if(!empty($fetch_user)){
        // extract the authorized data from above array
         $auth = $fetch_user['roles'];
       // check user have authority of updation or not
       if($auth === 'admin' ||  $auth ==='manager'){
        //check entred project id is exist or not in our database..
        $project = $this->project_model->find($data['id']);
        if(!empty($project)){
            // check if project status is come for data updation
            if (array_key_exists("project_status",$data)){
                
            if($data['project_status']=== 'Running'){
                $data['project_stage'] = '20';
            }if($data['project_status'] === 'Completed'){
                $data['project_stage'] = '100';
                 // deallocated user 
                 $current_time = time();
                $deallocat_time = date('Y-m-d H:i:s', $current_time);
                                $this->assign_model
        ->where('project_id', $data['id'])
        ->where('project_deallocate !=', 'yes')
        ->set(['project_deallocate' => 'yes', 'project_completed' => 'yes','deallocate_at'=>$deallocat_time])
        ->update();
            }

            }
            
           
    
        $update = $this->project_model->update($data['id'],$data);
            if($update){
                $response = [
                    'status' => 200,
                    'error' => false,
                    'message' => 'Your project data updated'
                ];
            }else{
            $response = [
                'status' => 500,
                'error' => false,
                'message' => 'Something went wrong......'
            ];
        }
    
    }else{
        $error = [
            'status' => 400,
            'error' => true,
            'message' => 'Sorry you project id does not match with our records',
            'data' => []
        ];
        $this->response->setJSON($error);
        $response->setStatusCode(400);
        return $response;
    }
        }else{
            $error = [
                'status' => 403,
                'error' => true,
                'message' => 'Sorry you are not authorized to any updation process...',
                'data' => []
            ];
            $this->response->setJSON($error);
            $response->setStatusCode(403);
            return $response;
        }



       
    }else{
        $error = [
            'status' => 400,
            'error' => true,
            'message' => 'Sorry your user id is not valid ',
            'data' => []
        ];
        $this->response->setJSON($error);
        $response->setStatusCode(400);
        return $response;
    }
        
    }
        return $this->setResponseFormat('json')->respond($response);
        
    }



    //function for delete project

    public function delete(){
        $response = service('response');

        $rules = [
            "user_id"=>"required",
            "id" =>"required",
            
        ];
        $message =[
            "user_id"=>[
                "required" => "Please enter valid user id"
            ],
            "id"=>[
                "required" => "Please enter valid project id for delete process"
            ],
            
        ];

        if(!$this->validate($rules,$message)){
            $error = [
				'status' => 400,
				'error' => true,
				'message' => $this->validator->getErrors()
			];
            $this->response->setJSON($error);
            $response->setStatusCode(400);
            return $response;
           
           
        }else{
            $data = json_decode(file_get_contents('php://input'), true);
            //fetch data from database to check user authority according to given user id
            $fetch_user = $fetch_user = $this->user_model->where('id',$data['user_id'])->first();
           if(!empty($fetch_user)){
            $auth = $fetch_user['authorized_to'];

         // convert auth list to array
           $auth_array = explode(',',$auth);

       // check user have authority of deletion  or not

       if(in_array("delete",$auth_array)){
        // fetch project data from database according to given user id
        $project = $this->project_model->find($data['id']);
        if(!empty($project)){
        
            $delete = $this->project_model->where('id',  $data['id'])->delete($data['id']);
            if($delete){
            $response = [
                'status' => 200,
                'error' => false,
                'message'=>'Your project delete succesfully.....',
                'data'=>['project_id'=>$data['id']]
            ];
        }else{
            $response = [
                'status' => 500,
                'error' => true,
                'message' => 'something went wrong try after some time......',
                'data' => []
            ];
        }
        }else{
            $error = [
                'status' => 400,
                'error' => true,
                'message' => 'Sorry your project id does not match with our records  ',
                'data' => []
            ];
            $this->response->setJSON($error);
            $response->setStatusCode(400);
            return $response;
        }

       }else{
        $error = [
            'status' => 403,
            'error' => true,
            'message' => 'Sorry you are not authorized to any deletion process...',
            'data' => []
        ];
        $this->response->setJSON($error);
        $response->setStatusCode(403);
        return $response;
       }
            
            
           }else{
            $error = [
                'status' => 400,
                'error' => true,
                'message' => 'Sorry your user id is not valid ',
                'data' => []
            ];
            $this->response->setJSON($error);
            $response->setStatusCode(400);
            return $response;
           }
           
        }

        return $this->setResponseFormat('json')->respond($response);
    }

        // function for update only project stage from user
    public function update_project_stage(){
        $response = service('response');
        // validation
        $rules = [
            'project_id'=>"required",
            'user_id'=>"required",
            'project_stage' => "required"
        ];
        $message = [
            'project_id'=>[
                'required'=>"Please provide valid project_id"
            ],
            'user_id'=>[
                'required'=>"Please provide valid user_id"
            ],
            'project_stage' => [
                'required'=>"Please provide data for update"
            ],
        ];

        if($this->validate($rules,$message)){
            // check user_id is valid or not
            $data = json_decode(file_get_contents('php://input'), true);
            $check_user = $this->user_model->where('id',$data['user_id'])->first();
            if(!empty($check_user)){
               
                // check project id is valid or not 
                $check_project = $this->project_model->where('id',$data['project_id'])->first();
                if(!empty($check_project)){
                    // check project is assign or not 
                    $check_assign =  $this->assign_model->verifyProjectAssignOrNot($data['user_id'],$data['project_id']);
                    if($check_assign !='no'){
                    // check project status if completed then not allow
                    $check_status = $check_project['project_status'];
                    if($check_status === 'Completed' || $check_status === 'Hold'){
                        $message = [
                            'status' => 403,
                            'error' => true,
                            'message' => 'May be your project is already completed or on hold',
                            
                        ];
                        $status = 404;
                    }else{
                        $current_date = time();
                        $date = date('Y-m-d H:i:s', $current_date);
                      
                        // update
                        $update_data = ['project_stage' => $data['project_stage'], 'updated_at' => $date];
                        $update = $this->project_model->update($check_project['id'],$update_data);
                        if($update){
                            $message = [
                                'status' => 200,
                                'error' => false,
                                'message' => 'Your data updated...',
                                
                            ];
                            $status = 200;
                            // if project status is 100 then project status automatic update as complete
                            if($data['project_stage'] === '100'){
                                $update_status = ['project_status' => 'Completed'];
                                $update_query = $this->project_model->update($check_project['id'],$update_status);
                                // deallocated user 
                                
                                 $current_time = time();
                $deallocat_time = date('Y-m-d H:i:s', $current_time);
                                $this->assign_model->where('project_id', $data['project_id'])
                                    ->where('user_id', $data['user_id'])
                                ->where('project_deallocate !=', '')
                                ->where('project_deallocate !=', 'yes')
                                ->set(['project_deallocate' => 'yes', 'project_completed' => 'yes','deallocate_at'=>$deallocat_time])
                                ->update();

                                
                        }
                        }else{
                            $message = [
                                'status' => 500,
                                'error' => true,
                                'message' => 'Something went wrong....',
                                
                            ];
                            $status = 505;
                        }

                    }
                    }else{
                        $message = [
                            'status' => 403,
                            'error' => true,
                            'message' => 'Sorry this project is not assign to you',
                            
                        ];
                        $status = 404;
                    }
                }else{
                    $message = [
                        'status' => 404,
                        'error' => true,
                        'message' => 'Sorry your provided project id is not valid',
                        
                    ];
                    $status = 404;
                }
            }else{
                $message = [
                    'status' => 404,
                    'error' => true,
                    'message' => 'Sorry your provided user id is not valid',
                    
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

        // send response 
        $this->response->setJSON($message);
        $response->setStatusCode($status);
        return $response;
    }
    
    
    


}
