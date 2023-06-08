<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UsersModel;
use App\Models\ProjectModel;
use App\Models\TimeModel;
use App\Models\ProjectAssignModel;
class ReportsController extends BaseController
{ 
    use ResponseTrait;
    protected $user_model;
    protected $project_model;
    protected $time_model;
    public function __construct() {	
		$db = db_connect();
    $this->project_model= new ProjectModel($db);
		$this->user_model = new UsersModel($db);
    $this->time_model = new TimeModel($db);  
    $this->assign_model = new ProjectAssignModel($db);

    }
    // function for get reports for user all works and assign project with respect to provided user id 
    public function index($current_user,$search_user)
    {
        $response = service('response');
      // first fetch current user login verification and its authorities for get reports 
      $login_user = $this->user_model->where('id',$current_user)->find();
      if(!empty($login_user)){
        $roles = $login_user[0]['roles'];
        // allow only to admin
        if($roles === 'admin' ||$roles === 'manager' ){
          // for check serach user's id is verified or not
          $user_verify = $this->user_model->where('id',$search_user)->find();
          if(!empty($user_verify)){
            $reports_result = $this->user_model->getAllData($search_user);
            if(!empty($reports_result)){
              $success =[
                'status'=>200,
                'error'=>false,
                'message'=>'All data listed...',
                'data'=> $reports_result
                ]; 
            $this->response->setJSON($success);
            $response->setStatusCode(200);
            return $response;

            }else{
              $error = [
                'status' => 400,
                'error' => true,
                'message' => 'Sorry no any data found for selected user ',
            ];
            $this->response->setJSON($error);
            $response->setStatusCode(400);
            return $response;
            }
          }else{
            $error = [
              'status' => 400,
              'error' => true,
              'message' => 'Sorry selected users records  does not match with database'
          ];
          $this->response->setJSON($error);
          $response->setStatusCode(400);
          return $response;
          }

        }else{
            $error = [
                'status' => 403,
                'error' => true,
                'message' => 'Sorry you are not authorized to generating any reports'
            ];
            $this->response->setJSON($error);
            $response->setStatusCode(403);
            return $response;
        }
      }else{
        $error = [
            'status' => 400,
            'error' => true,
            'message' => 'Sorry your entered login user id is not valid .....'
        ];
        $this->response->setJSON($error);
        $response->setStatusCode(400);
        return $response;
      }
      
    }


    // for get all time entries for selected user and selected project id 
//     public function getTimeEntries($current_user,$selected_user,$project_id){
//       $response = service('response');
//       // first check current user id' verification and also check its roles
//       $verify_user = $this->user_model->where('id',$current_user)->find();
//       if(!empty($verify_user)){
       
//         // now check the user roles only allow to admin
        
        
//       $user_roles = $verify_user[0]['roles'];
      
//       if($user_roles === 'admin'|| $user_roles === 'manager'){
//         // now check the selected user is valid or not
//         $verify_selected_user = $this->user_model->where('id',$selected_user)->find();
//         if(!empty($verify_selected_user)){
// // get assigned date of project from databse

// //  $get_data = $this->assign_model->where('project_id', $project_id)
// //                   ->where('user_id', $selected_user)->first();
                   
// //                   echo $created_at =  $get_data['created_at'];
// //                     echo $updated_at = $get_data['Updated_at'];

// $entries = $this->assign_model->where('project_id', $project_id)
//                   ->where('user_id', $selected_user)->find();

// foreach ($entries as $entry) {
    
//      $created_at = $entry['created_at'];
//       $updated_at = $entry['deallocate_at'];

// $time_records = $this->user_model->getTimeEntries($selected_user,$project_id,$created_at,$updated_at);
//          if(!empty($time_records)){
//           $success =[
//             'status'=>200,
//             'error'=>false,
//             'message'=>'All time entries data listed....',
//             'data'=> $time_records
//             ]; 
//         $this->response->setJSON($success);
//         $response->setStatusCode(200);
//         return $response;
//          }else{
//           $error = [
//             'status' => 400,
//             'error' => true,
//             'message' => 'Sorry no any data found for selected user with given project id',
//         ];
//         $this->response->setJSON($error);
//         $response->setStatusCode(400);
//         return $response;
//          }
// }




                    
                   
         
//         }else{
//           $error = [
//             'status' => 400,
//             'error' => true,
//             'message' => 'Sorry selected users records  does not match with database '
//         ];
//           $this->response->setJSON($error);
//           $response->setStatusCode(400);
//           return $response;
//         }
//       }else{
//         $error = [
//           'status' => 403,
//           'error' => true,
//           'message' => 'Sorry you are not authorized to generating any reports'
//       ];
//         $this->response->setJSON($error);
//         $response->setStatusCode(403);
//         return $response;
//       }
//       }else{
//         $error = [
//           'status' => 400,
//           'error' => true,
//           'message' => 'Sorry your entered login user id is not valid .....'
//       ];
//         $this->response->setJSON($error);
//         $response->setStatusCode(400);
//         return $response;
//       }
//     }
public function getTimeEntries($current_user,$selected_user,$project_id){
      $response = service('response');
      // first check current user id' verification and also check its roles
      $verify_user = $this->user_model->where('id',$current_user)->find();
      if(!empty($verify_user)){
       
        // now check the user roles only allow to admin
        
        
       $user_roles = $verify_user[0]['roles'];
      
      if($user_roles === 'admin'|| $user_roles === 'manager'){
        // now check the selected user is valid or not
        $verify_selected_user = $this->user_model->where('id',$selected_user)->find();
        if(!empty($verify_selected_user)){
 

         $time_records = $this->user_model->getTimeEntries($selected_user,$project_id);
         if(!empty($time_records)){
          $success =[
            'status'=>200,
            'error'=>false,
            'message'=>'All time entries data listed....',
            'data'=> $time_records
            ]; 
        $this->response->setJSON($success);
        $response->setStatusCode(200);
        return $response;
         }else{
          $error = [
            'status' => 400,
            'error' => true,
            'message' => 'Sorry no any data found for selected user with given project id',
        ];
        $this->response->setJSON($error);
        $response->setStatusCode(400);
        return $response;
         }
        }else{
          $error = [
            'status' => 400,
            'error' => true,
            'message' => 'Sorry selected users records  does not match with database '
        ];
          $this->response->setJSON($error);
          $response->setStatusCode(400);
          return $response;
        }
      }else{
        $error = [
          'status' => 403,
          'error' => true,
          'message' => 'Sorry you are not authorized to generating any reports'
      ];
        $this->response->setJSON($error);
        $response->setStatusCode(403);
        return $response;
      }
      }else{
        $error = [
          'status' => 400,
          'error' => true,
          'message' => 'Sorry your entered login user id is not valid .....'
      ];
        $this->response->setJSON($error);
        $response->setStatusCode(400);
        return $response;
      }
    }
    
     // function for verify date format
    private function validateDate(string $date)
    {
        // Check if the date format is Y-m-d
        return preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date);
    }
    
    // function for get all works reports for all user within given range
    public function getReportsWithInTimeRange($current_user,$start_date,$end_date){
     
      $response = service('response');
      
          // check user found or not 
          $user = $this->user_model->where('id',$current_user)->find();
          if(!empty($user)){
            // check current user is admin or not 
            $user_roles = $user[0]['roles'];
            if($user_roles === 'admin' || $user_roles === 'manager'){
            
             // check both dates date format
            if (!$this->validateDate($start_date) || !$this->validateDate($end_date)) {
              // Return an error response if the date format is invalid
              return $this->fail("Invalid date format");
              // check end dates should not lesser then start date

          }elseif($end_date < $start_date){
            return $this->fail("End date should be greater then start date");
          }else{
            // get reports function
            $result =$this->user_model->getReportsWithInTimeRange($start_date,$end_date);
            if(!empty($result)){
              $success = [
                'status' => 200,
                'error' => false,
                'message' => "All data in between '{$start_date}' and '{$end_date}' are as listed below:",
                'data'=>$result
            ];
              $this->response->setJSON($success);
              $response->setStatusCode(200);
              return $response;
            }else{
              $error = [
                'status' => 400,
                'error' => true,
                'message' => 'Sorry no data found for given time range'
            ];
              $this->response->setJSON($error);
              $response->setStatusCode(400);
              return $response;
            }
          }
            }else{
              $error = [
                'status' => 403,
                'error' => true,
                'message' => 'Sorry you are not authorized to generating any reports'
            ];
              $this->response->setJSON($error);
              $response->setStatusCode(403);
              return $response;
            }
           
          }else{
            $error = [
                  'status' => 400,
                  'error' => true,
                  'message' => 'Sorry your entered login user id is not valid .....'
              ];
                $this->response->setJSON($error);
                $response->setStatusCode(400);
                return $response;
          }
        
        }
    
    public function getReportsForSingleProject($current_user,$project_id){
    
      $response = service('response');
       
        // check current user id is valid or not
        $user = $this->user_model->where('id',$current_user)->find();
        if(!empty($user)){
          
        // check user roles for provide user id
        $roles = $user[0]['roles'];
        if($roles === 'admin' || $roles === 'manager'){
          
          
          // check given project id is valid or not
          $project_data = $this->project_model->where('id',$project_id)->first();
          if(!empty($project_data)){
            $result = $this->project_model->getUserReportOnSingleProject($project_id);
            if(!empty($result)){
              $success = [
                'status' => 200,
                'error' => false,
                'message' => 'Reports for your selected project',
                'data'=>$result
            ];
              $this->response->setJSON($success);
              $response->setStatusCode(200);
              return $response;
            }else{
              $error = [
                'status' => 400,
                'error' => true,
                'message' => 'No records found for your selected project'
            ];
              $this->response->setJSON($error);
              $response->setStatusCode(400);
              return $response;
            }
          }else{
            $error = [
              'status' => 400,
              'error' => true,
              'message' => 'Your given project id is not valid please provide valid project id'
          ];
            $this->response->setJSON($error);
            $response->setStatusCode(400);
            return $response;
          }
          
        }else{
          $error = [
            'status' => 403,
            'error' => true,
            'message' => 'Sorry you are not authorized to generating any reports'
        ];
          $this->response->setJSON($error);
          $response->setStatusCode(403);
          return $response;
        }
        }else{
          $error = [
            'status' => 400,
            'error' => true,
            'message' => 'Your provided user id does not exist with our database'
        ];
          $this->response->setJSON($error);
          $response->setStatusCode(400);
          return $response;
        }
      

  }
}

