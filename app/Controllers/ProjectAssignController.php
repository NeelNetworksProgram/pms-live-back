<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProjectModel;
use CodeIgniter\API\ResponseTrait;
use App\Models\UsersModel;
use App\Models\ProjectAssignModel;
use App\Models\EmailConversationModel;


class ProjectAssignController extends BaseController
{
    use ResponseTrait;
    protected $project_model;
    protected $user_model;
    protected $assign_model;
    protected $email_model;
    public function __construct() {	
		$db = db_connect();
		$this->project_model = new ProjectModel($db);
        $this->user_model = new UsersModel($db);
        $this->assign_model = new ProjectAssignModel($db);
        $this->email_model = new EmailConversationModel($db);
        

    }

    // function for assign new project
    public function index()
    {
        $response = service('response');
        $data = json_decode(file_get_contents('php://input'), true);
        $rules = [
            "user_id" => "required",
            "project_id"=>"required",
            "assign_by"=>"required",
            "work_description"=>"required",
            "completion_time" =>"required",
            "user_category"=>"required"
           ];

           $message = [
            "user_id"=>[
                "required"=>"Please provide valid user id "
            ],
            "project_id"=>[
                "required"=>"Please provide valid project id"
            ],
            "assign_by"=>[
                "required"=>"Please provide admin id"
            ],
            "work_description"=>[
                "required"=>"Please provide project description"
            ],
            "completion_time"=>[
                "required"=>"Please provide project completion_time",
                
            ],
            "user_category"=>[
                "required"=>"Please select atleast one user category",
                
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
           }
           else{
            // first check entered project id is valid or not 
            $fetch_project = $this->project_model->where('id',$data['project_id'])->first();
            if(!empty($fetch_project)){
                // check status of project
                if($fetch_project['project_status'] === 'Completed'){
                    $error = [
                        'status'=>403,
                        'error'=>true,
                        'message'=>'Sorry Project assign not allowed when project status is completed......'
                    ];
                    $this->response->setJSON($error);
                    $response->setStatusCode(403);
                    return $response;
                }else if($fetch_project['project_status'] === 'Hold'){
                    $error = [
                        'status'=>403,
                        'error'=>true,
                        'message'=>'Sorry Project assign not allowed when project on Hold'
                    ];
                    $this->response->setJSON($error);
                    $response->setStatusCode(403);
                    return $response;
                }else{
                    // check entered user id is valid or not 
                    $fetch_user = $this->user_model->where('id',$data['user_id'])->find();
                    if(!empty($fetch_user)){
                       
                        // check user staus if user is suspended then not allow to assign project
                        $check_status = $fetch_user[0]['status'];
                        $check_verify = $fetch_user[0]['is_active'];
                        if($check_status === 'active' && $check_verify == '1'){
                        // check wether entered project is already assign to selected user or not
                       
                        $check = $this->assign_model
           ->where('project_id', $data['project_id'])
           ->where('user_id', $data['user_id'])
           ->where('project_deallocate !=', 'yes')
           ->get()
           ->getRow();
            // check this above query is not null and also project_deallocate yes or not
           if (!$check) {
            // check entered admin id is valid or not 
            $fetch_admin=$this->user_model->where('id',$data['assign_by'])->find();
            if(!empty($fetch_admin)){
            // extract the authorized data from above array
            $auth = $fetch_admin[0]['authorized_to'];
        // convert auth list to array
            $auth_array = explode(',',$auth);
            // check user have authority of assign or not
            if(in_array("create",$auth_array)){
                $insert = $this->assign_model->insert($data);
                // check assign process are done or not 
                if($insert){
                    $response_data = [
                        'status'=>201,
                        'error'=>false,
                        'message'=>"Project assigned to '".$fetch_user[0]['username']."' successfully....",
                        'data'=>[
                            "assign_to"=>$fetch_user[0]['username'],"project_name"=>$fetch_project['name'],"assign_by"=>$fetch_admin[0]['username']
                        ]
                    ];
                    // send mail to user when assign project
                    // Call the sendEmail method of MailController
                    $logo = '<img src="https://neelnetworks.org/ems/image/neel.png" alt="Logo" style="width: 150px;">';

                                $email_for = 'New Project Assign';
                                $subject = 'You have been assigned a new project titled "' . $fetch_project['name'] . '" by "' . $fetch_admin[0]['username'] . '".';
                                $message =  $data['work_description'];
                                            
                                 $template = view('emails/email-template');

                                $template = str_replace('{{email_for}}', $email_for, $template);
                                $template = str_replace('{{message}}', $message, $template);
                                $template = str_replace('{{logo}}', $logo, $template);


                                $sendMail = new MailController();
                                $sendMail->send_mail($fetch_user[0]['email'],$fetch_admin[0]['email'],$email_for,$subject, $template);
                    
                    // call the addNewNotification method of NotifictionController
                    $for = 'New Project';
                    $message = 'You have a new project "' . $fetch_project['name'] . '" assigned by "' . $fetch_admin[0]['username'] . '"';

                    $notification  = new NotificationController();
                    $notification->addNewNotification($for,$message,$data['user_id']);
                    
                    $this->response->setJSON($response_data);
                    $response->setStatusCode(201);
                    return $response;

                }else{
                    $error = [
                        'status'=>500,
                        'error'=>true,
                        'message'=>'Something went wrong please try again after some time.....'
                    ];
                    $this->response->setJSON($error);
                    $response->setStatusCode(403);
                    return $response;
                }
            }else{
                $error = [
                    'status'=>403,
                    'error'=>true,
                    'message'=>'Sorry you are not authorized for assign project to someone'
                ];
                $this->response->setJSON($error);
                $response->setStatusCode(403);
                return $response;
            }

            }else{
                $error = [
                    'status'=>400,
                    'error'=>true,
                    'message'=>'Sorry admin id is not valid'
                ];
                $this->response->setJSON($error);
                $response->setStatusCode(400);
                return $response;
            }
        } else {
            $error = [
                'status'=>403,
                'error'=>true,
                'message'=>'Sorry Project is already assign to this user '
            ];
            $this->response->setJSON($error);
            $response->setStatusCode(403);
            return $response;
        }
               
                }else{
                    $error = [
                        'status'=>403,
                        'error'=>true,
                        'message'=>'May be user account is suspend or not verified yet so project assign is not allow..'
                    ];
                    $this->response->setJSON($error);
                    $response->setStatusCode(403);
                    return $response;
                }

                        
                    }else{
                        $error = [
                            'status'=>400,
                            'error'=>true,
                            'message'=>'Sorry your given user id does not match with our records..'
                        ];
                        $this->response->setJSON($error);
                        $response->setStatusCode(400);
                        return $response;
                    }
                }
            }else{
                $error = [
                    'status' => 400,
                    'error' => true,
                    'message' =>'Sorry your given project id does not match with our records..'
                ];
                $this->response->setJSON($error);
                $response->setStatusCode(400);
                return $response;
            }
           }

           
    }

   // function for get all list of assign project by current user

        public function assignlist($current_user){
            $response = service('response');
            // first check user is valid or not 
            $check_user = $this->user_model->find($current_user);
            if(!empty($check_user)){
               // check whether user account does not suspend and should be active
               if($check_user['is_active'] === '1' && $check_user['status'] === 'active'){
                // check user roles it must be admin/manager
                if($check_user['roles'] === 'admin' ||  $check_user['roles'] === 'manager'){
                   // now check any project is assign by current user or not 
                   $count_project_assign = $this->assign_model->anyProjectAssignByCurrentUser($current_user);
                   if($count_project_assign > 0){
                    // get all project list assign by current user
                    $get_project_lists = $this->assign_model->getAllAssignProjectListByUserId($current_user);
                    if(!empty($get_project_lists)){
                        $message = [
                            'status' => 200,
                            'error' => false,
                            'message' => 'All listed data',
                            'project_list'=>$get_project_lists
                        ];
                        $status = 200;
                    }else{
                        $message = [
                            'status' => 404,
                            'error' => true,
                            'message' => 'Sorry no any data found'
                        ];
                        $status = 404;
                    }
                   }else{
                    $message = [
                        'status' => 404,
                        'error' => true,
                        'message' => 'Sorry you have not assign any project yet to anyone'
                    ];
                    $status = 404;
                   }
                }else{
                    $message = [
                        'status' => 403,
                        'error' => true,
                        'message' => 'You are not authorized to see any assign project list'
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



        // function for deallocate project from user
        public function deallocate(){
            $response = service('response');
            $data = json_decode(file_get_contents('php://input'), true);

            // validation message
            $rules=[
                'login_user'=>"required",
                'deallocate_user' => "required",
                'project_id'=>"required",
                'project_assign_id'=>"required"
            ];

            $message = [
                'login_user'=>[
                    "required"=>"Login user id must required"
                ],
                'deallocate_user'=>[
                    "required"=>"Please provide that user id which you want to deallocate from project"
                ],
                'project_id'=>[
                    "required"=>"Please provide project id"
                ],
                
                'project_assign_id'=>[
                    "required"=>"Something is missing please contact to your developer"
                ],
                
            ];
            
            if($this->validate($rules,$message)){
                //first check login user is valid or not
                $fetch_user = $this->user_model->where('id',$data['login_user'])->find();
                if(!empty($fetch_user)){
                    
                    // extract the authorized data from above array
                        $auth = $fetch_user[0]['authorized_to'];
             // convert auth list to array
            $auth_array = explode(',',$auth);
     
            // check user have authority of updation or not
              if(in_array("edit",$auth_array)){
            // fetch deallocated user 
            $fetch_dealloc = $this->user_model->where('id',$data['deallocate_user'])->find();
            if(!empty($fetch_dealloc)){
            
                $status = $fetch_dealloc[0]['status'];
                // status must be activated
                if($status==='active'){
                // check project id is valid or not
                $fetch_project = $this->project_model->where('id',$data['project_id'])->first();
                if(!empty($fetch_project)){
                    // check project status 
                    $project_status = $fetch_project['project_status'];
                    if($project_status === 'Running'){
                        // check this is project is assigned or not to this user
                    $check  =  $this->assign_model
                    ->where('project_id', $fetch_project['id'])
                    ->Where('user_id', $fetch_dealloc[0]['id'])
                    ->get()->getResult();
                    if(!empty($check)){
                        
                        // check provided assigning id is valid or not
                        $check_assign_id = $this->assign_model->find($data['project_assign_id']);
                        if(!empty($check_assign_id)){
                        //   print_r($check_assign_id);
                        //   die();
                            // check project is already deallocate or not 
                            if($check_assign_id['project_deallocate'] != 'yes'){
                                
                                $current_time = time();
                $deallocat_time = date('Y-m-d H:i:s', $current_time);
                        $update_data = ['project_deallocate' => 'yes','deallocate_at'=>$deallocat_time];
                        $update = $this->assign_model->where('id', $check_assign_id['id'])->set($update_data)->update();
    
                        if($update){
                            // // send mail to user when deallocate project
                                // // Call the sendEmail method of MailController
                                $logo = '<img src="https://neelnetworks.org/ems/image/neel.png" alt="Logo" style="width: 150px;">';

                                $email_for = 'Project Deallocation';
                                $subject = 'You have been deallocated from the project "' . $fetch_project['name'] . '" by "' . $fetch_user[0]['username'] . '".';
                                $url = getenv('redirect');
                                $message =  "This email is to inform you that you have been deallocated from the project " . $fetch_project['name'] . ",<br><br>
                                            Please be aware that your involvement in the project has ended.<br><br>
                                            If you have any questions or need further information, please don't hesitate to contact us.:<br><br>";
                                            
                                 $template = view('emails/email-template');

                                $template = str_replace('{{email_for}}', $email_for, $template);
                                $template = str_replace('{{message}}', $message, $template);
                                $template = str_replace('{{logo}}', $logo, $template);


                                $sendMail = new MailController();
                                $sendMail->send_mail($fetch_dealloc[0]['email'],$fetch_user[0]['email'],$email_for,$subject,$template);
                                
                                
                                // call the addNewNotification method of NotifictionController
                                $for = 'Project Deallocation';
                                $message = 'You have deallocate from "' . $fetch_project['name'] . '"  by "' . $fetch_user[0]['username'] . '"';

                                $notification  = new NotificationController();
                                $notification->addNewNotification($for,$message,$data['deallocate_user']);
                                
                                
                                
                            $message = [
                                'status' => 200,
                                'error' => false,
                                'message' => 'Project deallocation successfully....',
                            ];
                            $this->response->setJSON($message);
                            $response->setStatusCode(200);
                            return $response;
                                
    
                        }else{
                            $message = [
                                'status' => 500,
                                'error' => false,
                                'message' => 'Something went wrong',
                            ];
                            $this->response->setJSON($message);
                            $response->setStatusCode(500);
                            return $response;
                        }
                            }else{
                                $message = [
                            'status' => 403,
                            'error' => true,
                            'message' => 'Sorry this user is already deallocated',
                        ];
                        $this->response->setJSON($message);
                        $response->setStatusCode(403);
                        return $response;
                            }
                            
                        
                        }else{
                            $message = [
                            'status' => 404,
                            'error' => true,
                            'message' => 'Sorry your provide project assign id is not valid...',
                        ];
                        $this->response->setJSON($message);
                        $response->setStatusCode(404);
                        return $response;
                        }
                         
                        
                    }else{
                        $message = [
                            'status' => 403,
                            'error' => true,
                            'message' => 'Project deallocation is not allowed beacuse this project is not assign to this user',
                        ];
                        $this->response->setJSON($message);
                        $response->setStatusCode(403);
                        return $response;
                    }
                    }else{
                        $message = [
                            'status' => 403,
                            'error' => true,
                            'message' => 'Project deallocation is not allowed beacuse this project may be on Hold or completed',
                        ];
                        $this->response->setJSON($message);
                        $response->setStatusCode(403);
                        return $response;
                    }

                }else{
                    $message = [
                        'status' => 403,
                        'error' => true,
                        'message' => 'Project id is not valid',
                    ];
                    $this->response->setJSON($message);
                    $response->setStatusCode(403);
                    return $response;
                }
                }else{
                    $message = [
                        'status' => 403,
                        'error' => true,
                        'message' => 'Project deallocation is not allowed because user is suspended',
                    ];
                    $this->response->setJSON($message);
                    $response->setStatusCode(403);
                    return $response;
                }
            }else{
                $message = [
                    'status' => 404,
                    'error' => true,
                    'message' => 'Sorry deallocate user id is not valid',
                ];
                $this->response->setJSON($message);
                $response->setStatusCode(404);
                return $response;
            }
              }else{
                $message = [
                    'status' => 403,
                    'error' => true,
                    'message' => 'Sorry you are not authorized to deallocate process..',
                ];
                $this->response->setJSON($message);
                $response->setStatusCode(403);
                return $response;
              }
                    
                }else{
                    $message = [
                        'status' => 404,
                        'error' => true,
                        'message' => 'Sorry your current user id is not valid',
                    ];
                    $this->response->setJSON($message);
                    $response->setStatusCode(404);
                    return $response;
                }
            }else{
                $message = [
                    'status' => 400,
                    'error' => true,
                    'message' => $this->validator->getErrors(),
                ];
                $this->response->setJSON($message);
                $response->setStatusCode(400);
                return $response;
            }
            
        }

        // for submit the project from employee when project get completed...
        public function submit(){
            $response = service('response');
            
            // validation
            $rules = [
                'current_user'=>"required",
                'project_id'=>"required",
                'message' =>"required",
            ];
            $message = [
                'current_user'=>[
                    "required"=>'Please provide valid current user login id'
                ],
                'project_id'=>[
                    "required"=>'Please enter valid project id'
                ],
                'message'=>[
                    "required" =>'Please provide your message'
                ]
            ];
            if($this->validate($rules,$message)){
            $data = json_decode(file_get_contents('php://input'), true);
            // check current user is valid or not
            $check_user = $this->user_model->find($data['current_user']);
            if(!empty($check_user)){
            // check project id is valid or not
               $check_project = $this->project_model->find($data['project_id']);
               if(!empty($check_project)){
                // check project_status it should be in running stage
                if($check_project['project_status'] === 'Running'){
                    //check project is assign or not to current user
                    $verify_assign_project = $this->assign_model->verifyProjectAssignOrNot($data['current_user'],$data['project_id']);
                    if(!empty($verify_assign_project)){
                        $assign_by = $verify_assign_project[0]->assign_by;
                        $assign_id = $verify_assign_project[0]->assign_user_id;
                    if($verify_assign_project === 'no'){
                        $message = [
                            'status' => 403,
                            'error' => true,
                            'message' => 'Sorry this project is not assigned to you',
                        ];
                        $status = 403;
                    }else{
                         // // send mail to project assigner when project is submited by user
                                // // Call the sendEmail method of MailController
                                $logo = '<img src="https://neelnetworks.org/ems/image/neel.png" alt="Logo" style="width: 150px;">';

                                $email_for = 'Project Submission';
                                $subject = 'Project Submitted: "' . $check_project['name'] . '" by "' . $check_user['username'] . '"';
                                $message = $data['message'];
                                 $template = view('emails/email-template');

                                $template = str_replace('{{email_for}}', $email_for, $template);
                                $template = str_replace('{{message}}', $message, $template);
                                $template = str_replace('{{logo}}', $logo, $template);


                                $sendMail = new MailController();
                                $sendMail->send_mail($assign_by,$check_user['email'],$email_for,$subject,$template);
            
                                // call the addNewNotification method of NotifictionController
                                $for = 'Project Submission';
                                
                                $message = 'Project submited by  "' . $check_user['username'] . '"  for  "' . $check_project['name'] . '"';

                                $notification  = new NotificationController();
                                $notification->addNewNotification($for,$message,$assign_id);
                                
                                
                                $message = [
                                    'status' => 201,
                                    'error' => false,
                                    'message' => 'Thank you your work is submited please wait while admin/manager checking your changes....',
                                ];
                                $status = 201;

                                // insert into database
                                //$this->email_model
                                $insert_data = [
                                     "project_id" =>$data['project_id'],
                                     "email_to" =>$verify_assign_project[0]->assign_user_id,
                                     'email_by'=>$data['current_user'],
                                     'email_content'=>$data['message'],
                                    
                                    //   "project_id" =>1,
                                    //  "email_to" =>1,
                                    //  'email_by'=>1,
                                    //  'email_content'=>'test',
                                    
                                    
                                    ];
                                    
                                $email_logs = $this->email_model->insert($insert_data);
                    }
                    }else{
                        $message = [
                            'status' => 403,
                            'error' => true,
                            'message' => 'Sorry this project is not assigned to you',
                        ];
                        $status = 403;
                    }
                    
                }else{
                    $message = [
                        'status' => 403,
                        'error' => true,
                        'message' => 'Your project may be on hold or already completed....',
                    ];
                    $status = 403;
                }
               }else{
                $message = [
                    'status' => 404,
                    'error' => true,
                    'message' => 'Sorry your provided project id does not match with our database',
                ];
                $status = 404;
               }
            }
            else{
                $message = [
                    'status' => 404,
                    'error' => true,
                    'message' => 'Sorry your provided user id does not match with our database',
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
            $this->response->setJSON($message);
                $response->setStatusCode($status);
                return $response;

        }

}
