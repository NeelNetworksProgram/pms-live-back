<?php 
namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use App\Models\UsersModel;
class RegisterController extends BaseController{
    use ResponseTrait; 
    protected $user_model;
    public function __construct() {	
		$db = db_connect();
		$this->user_model = new UsersModel($db);

        helper('text');
        helper('url');
    }


    // api function for user registration
    public function register(){
        
        $data = json_decode(file_get_contents('php://input'), true);
// check password is set or not
if(isset($data['password']) ){
    $rules = [
        "username" => "required",
        "email" => "required|valid_email|is_unique[user.email]|regex_match[/^[A-Za-z0-9._%+-]+@neelnetworks.com$/]",
        "password" => "required|min_length[8]|regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_])/]",
        
       
    ];

    $message = [
        "username"=>[
            "required" => "Please enter username"
        ],

        "email" =>[
            "required" => "Please enter email",
            "valid_email" => "Please enter valid email format",
            "is_unique" => "Email address already exist",
            "regex_match"=>"Email domian allowed only neelnetworks.com no other email allowed"
        ],

        "password" =>[
            "required" => "Please enter password",
            "min_length"=>"Password should be 8 characters",
            "regex_match" =>"Password should have 1 small letter , 1 capital letter and 1 special characters"
        ],

       
    ];
    }else{
        $rules = [
            "username" => "required",
			"email" => "required|valid_email|is_unique[user.email]|regex_match[/^[A-Za-z0-9._%+-]+@neelnetworks.com$/]",
            
           
        ];

        $message = [
            "username"=>[
                "required" => "Please enter username"
            ],

            "email" =>[
                "required" => "Please enter email",
                "valid_email" => "Please enter valid email format",
                "is_unique" => "Email address already exist",
                "regex_match"=>"Email domian allowed only neelnetworks.com no other email allowed"
            ],
           
        ];
    }
        if(!$this->validate($rules,$message)){
           
            $response = [
				'status' => 500,
				'error' => true,
				'message' => $this->validator->getErrors(),
				'data' => []
			];
			
        }else{
            if (array_key_exists("roles",$data)){
                $authorized = '';
                if($data['roles'] ==='admin'){
                    $authorized = 'edit,create,delete,view';
                }else if($data['roles'] ==='manager'){
                    $authorized = 'edit,create,view';
                }else{
                    $authorized = 'view';
                }
                
                // check password is exist in array or not
                if(isset($data['password']) ){
                    $user_data = [
                        "username" =>$data['username'],
                        "email" =>$data['email'],
                        'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                        'roles'=>$data['roles'],
                        'authorized_to'=>$authorized,
                        'verification_link'=>random_string('alnum', 16)
                        ];
                }else{
                
                $user_data = [
                    "username" =>$data['username'],
                    "email" =>$data['email'],
                    'roles'=>$data['roles'],
                    'authorized_to'=>$authorized,
                    'verification_link'=>random_string('alnum', 16)
                    ];
                }
            }else{
            $user_data = [
                "username" =>$data['username'],
                "email" =>$data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'roles'=>'employee',
                'verification_link'=>random_string('alnum', 16)
                ];
            }
            // if data inserted by admin then we bydefault verify the user to 1
            if(isset($data['is_active']) ){
                $user_data = [
                    "username" =>$data['username'],
                    "email" =>$data['email'],
                    'roles'=>$data['roles'],
                    'authorized_to'=>$authorized,
                    'is_active'=>$data['is_active']
                    ];
                $this->user_model->insert($user_data);

                $response = [
                    'status' => 200,
                    'error' => false,
                    'message' => 'User added and we have send reset password link to user ',
                    'data' => ['last_id'=>$this->user_model->getInsertID(),'email'=>$data['email']]
                ];
                

            }else{
                $logo = '<img src="https://neelnetworks.org/ems/image/neel.png" alt="Logo" style="width: 150px;">';

                                $email_for = 'Account Verification';
                                $subject = 'Account Verification - Welcome to Neel Networks Project Management System';
                                $url = getenv('redirect');
                                $message =  "Hello " . $data['username'] . ",<br><br>
                                            Welcome to the Neel Networks Project Management System!<br><br>
                                            Please verify your account by clicking the link below:<br><br>
                                            <a href = '$url/email-activation/$user_data[verification_link]'>Activate Now</a><br>";
                                            ;
                                 $template = view('emails/email-template');

                                $template = str_replace('{{email_for}}', $email_for, $template);
                                $template = str_replace('{{message}}', $message, $template);
                                $template = str_replace('{{logo}}', $logo, $template);
                                
                                $sendMail = new MailController();
                                $sendMail->send_mail($data['email'],'no-reply@neelnetworks.com',$email_for,$subject,$template);

                                $this->user_model->insert($user_data);
                                $response = [
                                    'status' => 200,
                                    'error' => false,
                                    'message' => 'Your registration get succesfully we have send a verification email to your email id please activate your account',
                                    'data' => ['last_id'=>$this->user_model->getInsertID()]
                                ];
        
                }
            

        }
        return $this->respondCreated($response);
    

    }


    // get all data api

    public function listing(){
        $request = service('request');
        $response = service('response');
        $uri = service('uri');
          // get all value from uri query string
           $getdata =  $request->getGetPost();
           if (array_key_exists("limit",$getdata)){
             $data = $this->user_model->findAll($getdata['limit']);
            
           }else{
        $data = $this->user_model->findAll();
        
           }
        $response = [
            'status' => 201,
            'error' => false,
            'message' => 'All Data',
            'data' => $data
        ];
    
return $this->setResponseFormat('json')->respond($response);
        //return $this->respondCreated($response);
    }


    // get single Users data according to user id
    
    public function single($user_id){
        $response = service('response');
       
        $id = $this->user_model->find($user_id); 

        if(!empty($id)){
            $success = [
                'status' => 200,
                'error' => false,
                'message' => 'User details',
                
            ];
            $this->response->setJSON($success);
            $response->setStatusCode(200);
            return $response;
        }else{
            $error = [
                'status' => 400,
                'error' => true,
                'message' => 'Your account does not exist',
            ];
            $this->response->setJSON($error);
            $response->setStatusCode(400);
            return $response;
            }
        
        
    }


    // function for user update
    public function update($user_id){
        $data = json_decode(file_get_contents('php://input'), true);
        if (array_key_exists("password",$data)){
        $rules = [
            "password" => "required|min_length[8]|regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_])/]",

        ];

        $message = [
            "password" =>[
                "required" => "Please enter password",
                "min_length"=>"Password should be 8 characters",
                "regex_match" =>"Password should have 1 small letter , 1 capital letter and 1 special characters"
            ],
        ];
        }else{
            $rules = [
                "roles" => "required"
    
            ];
            $message = [
                "roles" =>[
                    "required" => "Please enter roles",
                ],
            ];
        }

        if($this->validate($rules,$message)){
            $id = $this->user_model->find($user_id);
            if(!empty($id)){
                
                if (array_key_exists("password",$data))
                    {
                    $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                    }
                // automatically updated value according to roles
               
                
                if (in_array("admin",$data)){
                    $data['authorized_to'] = 'edit,view,delete,create';
                }
                if (in_array("employee",$data)){
                    $data['authorized_to'] = 'view';
                }
                if (in_array("manager",$data)){
                    $data['authorized_to'] = 'edit,view,create';
                }
                                         
           
                $update = $this->user_model->update($user_id ,$data);
                if($update){
                    $response = [
                        'status' => 201,
                        'error' => false,
                        'message' => 'User Data updated',
                        'data' =>[$data]
                    ];
                }else{
                    $response = [
                        'status' => 500,
                        'error' => true,
                        'message' => 'Something went wrong',
                        'data' =>[]
                    ];
                }

            }else{
                $response = [
                    'status' => 500,
                    'error' => true,
                    'message' => 'Invalid user id',
                    'data' =>[]
                ];
            }
        }else{
            $response = [
                
                'status' => 500,
                'error' => true,
                'message' => $this->validator->getErrors(),
                'data' =>[]
            ];
        }
        return $this->setResponseFormat('json')->respond($response);
    
    }
    
    
    

    // function for verify user email key

    public function verify($link){
        
        $activation_link = $this->user_model->where('verification_link',$link)->first();

        if(!empty($activation_link)){

            // check whether user account is already verifed or not 
        $check = $activation_link['is_active'];
        if($check == 0){
            $update_data = [
                'is_active' => '1',
                'verification_link' => ''
            ];
        $update = $this->user_model->update($activation_link ,$update_data);
             
            if($update){
                $response = [
                
                    'status' => 201,
                    'error' => false,
                    'message' => 'Your account has been activated, you can login now',
                    
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
                'message' => 'Your activation key is not valid ',
                
            ];

        }

        return $this->setResponseFormat('json')->respond($response);
    }



    // Function for forgot password

    
    public function forgot(){
        

        $rules = [
            "email" =>"required|valid_email"
        ];

        $message = [
            "email" =>[
            "required" => "Please enter email",
            "valid_email" => "Please enter valid email format",
            ],    
        ];

        if($this->validate($rules,$message)){

            $data = json_decode(file_get_contents('php://input'), true);

            $email_fetch = $this->user_model->where('email',$data['email'])->first();
            if(!empty($email_fetch)){
                //check whether user account is active or not
           $check_active = $email_fetch['is_active'];
           if($check_active == '1'){
            // send password reset link after check user is activate or not 
            $random_string = random_string('alnum', 16);
            $url = getenv('redirect');
         $email_message = "Hello your password reset link is  <a href = '$url/reset-password/$random_string'>Reset Now</a>" ; 
        $email = \Config\Services::email();
        $email->setTo($data['email']);
        $email->setFrom('hemant@neelnetworks.com', 'Reset password link');
        
        $email->setSubject('Reset password link');
        $email->setMessage($email_message);
        if($email->send()){
            $update_data = [
                'reset_link' =>$random_string
            ];
            //$this->user_model->update($data['email'] ,$update_data);
            $this->user_model->where(["email" => $data['email']])->set($update_data)->update();

        $response = [
                
            'status' => 200,
            'error' => false,
            'message' => 'We have send reset password link to the '.$data['email'].' Please check'
            
        ];
    }else{
        $response = [
                
            'status' => 500,
            'error' => true,
            'message' => 'Something went wrong please try after some time .....'
            
        ];
    }
    }else{
    $response = [
                
        'status' => 500,
        'error' => true,
        'message' => 'Your account must be activated before send reset password link '
        
    ];
    }
        }else{
                $response = [
                
                    'status' => 500,
                    'error' => true,
                    'message' => 'Your entered email is not exits in our database '
                    
                ];
            }
            


        }else{
            $response = [
                
                'status' => 500,
                'error' => true,
                'message' => $this->validator->getErrors()
                
            ];
        }

        return $this->respondCreated($response);

        
    }



    // function for verify reset password link

    public function resetlink($link){
        $reset_link = $this->user_model->where('reset_link',$link)->first();
      if($reset_link){
        $update_data = [
            'reset_link' => ''
        ];
       $update = $this->user_model->update($reset_link ,$update_data );
       if($update){
        $response = [
                
            'status' => 200,
            'error' => false,
            'message' => 'Your reset passsword link is verified ',
            'data' =>['email'=>$reset_link['email']]
            
        ];
       }else{
        $response = [
                
            'status' => 500,
            'error' => true,
            'message' => 'something went wrong'
            
        ];
       }
      }else{
        $response = [
                
            'status' => 500,
            'error' => true,
            'message' => 'Reset passsword  link is not valid'
            
        ];
        
      }
      return $this->setResponseFormat('json')->respond($response);
    }





    // function for reset passsword
    public function resetpassword(){
        $request = service('request');
        $response = service('response');
        
        $rules = [
            "email" => "required|valid_email",
            "password" => "required|min_length[8]|regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_])/]",
            "confirm_password" => "required|matches[password]"
        ];

        $message = [
            "email" =>[
                "required" => "Please enter email",
                "valid_email" => "Please enter valid email format"
                
            ],
            "password" =>[
                "required" => "Please enter password",
                "min_length"=>"Password should be 8 characters",
                "regex_match" =>"Password should have 1 small letter , 1 capital letter and 1 special characters"
            ],    

            "confirm_password" =>[
                "required" => "Please enter confirm password",
                "matches" =>"Your password and confirm password should match"
            ],
        ];

        if($this->validate($rules,$message)){
            $data = json_decode(file_get_contents('php://input'), true);
            if (array_key_exists("password",$data))
            {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            // check email is exist or not
            
            $check_email = $this->user_model->where('email',$data['email'])->first();
            if($check_email){
                $update = $this->user_model->where(["email" => $data['email']])->set(["password" => $data['password'],"reset_link"=>""])->update();
            if($update){
                $response = [
                    'status' => 201,
                    'error' => false,
                    'message' => 'Your password updated successfully .......'
                ];
            }else{
                $response = [
                    'status' => 500,
                    'error' => true,
                    'message' => 'Something went wrong please try after some time......'
                ];
            }
            }else{
                $error = [
                    'status' => 401,
                    'error' => true,
                    'message' => "Your enter email '$email'  does not match with our records  ",
                ];
                $this->response->setJSON($error);
            $response->setStatusCode(401);
            return $response;
            }
             

            

        }else{
            $response = [
                
                'status' => 500,
                'error' => true,
                'message' => $this->validator->getErrors()
                
            ]; 
        }
        return $this->respondCreated($response);
    }
    
    
    
    
    //fetch email api load email address when user reset password
    
    function fetchemail($link){
        $request = service('request');
        $response = service('response');
        $reset_link = $this->user_model->where('reset_link',$link)->first();
        if($reset_link){
            $email = $reset_link['email'];
            $response = [
                'status' => 200,
                'error' => false,
                'data' => $email
                ];
            return $this->setResponseFormat('json')->respond($response);
        }else{
            $error = [
                'status'=>401,
                'error'=>true,
                'message'=> 'Your reset password link is not valid '
                ];
            $this->response->setJSON($error);
            $response->setStatusCode(401);
            return $response;
        }
        
         
    }

    




    
}





?>