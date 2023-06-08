<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\HolidayListModel;
use App\Models\UsersModel;
use CodeIgniter\API\ResponseTrait;

class HolidayListController extends BaseController
{
    use ResponseTrait;
    protected $holiday_model;
    public function __construct() {	
		$db = db_connect();
    $this->holiday_model= new HolidayListModel($db);
     $this->user_model= new UsersModel($db);
		
        

    }
    
   public function index()
    {
        $data = $this->holiday_model->findAll();

        // Add weekends as holidays
        $start = strtotime('last Saturday'); // set start to the timestamp of the next Saturday
        $end = strtotime('+1 year', $start);
        while ($start < $end) {
            $date = date('Y-m-d', $start);

            $weekend[] = ['holiday_name' => 'Weekend (Saturday)', 'date' => $date];
            $start = strtotime('+7 days', $start);

            if ($date == date('Y-m-d')) {
                // if current Saturday is today, add next Sunday
                $nextSunday = strtotime('+1 day', strtotime('next Sunday'));
                $weekend[] = ['holiday_name' => 'Weekend (Sunday)', 'date' => date('Y-m-d', $nextSunday)];
            } else {
                // if current Saturday is not today, add current Sunday
                $currentSunday = strtotime('next Sunday', strtotime($date));
                $weekend[] = ['holiday_name' => 'Weekend (Sunday)', 'date' => date('Y-m-d', $currentSunday)];
            }
            
            
        }

        $data = array_merge($data, $weekend);
        $response = [
            'status' => 200,
            'error' => false,
            'message' => 'All Holiday List',
            'data' => $data
        ];
        return $this->setResponseFormat('json')->respond($response);


            }
    
    // function for add new holiday from admin
    public function addHolidayList(){
        $response = service('response');
        $data = json_decode(file_get_contents('php://input'), true);
        $rules = [
            "current_user"=>"required",
            "holiday_name"=>"required|is_unique[holidaylist.holiday_name]",
            "holiday_date"=>"required|valid_date|is_unique[holidaylist.date]"
        ];
        $message = [
            "current_user"=>[
                "required"=>"Please provide valid login user id"
            ],
            "holiday_name"=>[
                "required"=>"Please provide holiday name",
                "is_unique"=>"Already Addedd this holiday"
            ],
            "holiday_date"=>[
                "required"=>"Please provide holiday date",
                "valid_date"=>"Please provide valid date format",
                "is_unique"=>"This date is already added"
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
                // check provided user id is valid or not also its roles only admin allow to add new holiday list
                $user = $this->user_model->where('id',$data['current_user'])->find();
                if(!empty($user)){
                $roles =$user[0]['roles'];
                // check user roles

                if($roles === 'admin'){
                    // create array for insert data
                    $insert_array = [
                        "holiday_name"=>$data["holiday_name"],
                        "date"=>$data["holiday_date"]
                    ];
                    // insert data into database
                    $insert = $this->holiday_model->insert($insert_array);
                    if($insert){
                        $success = [
                            'status' => 201,
                            'error' => false,
                            'message' => 'Add successfully...'
                        ];
                        $this->response->setJSON($success);
                        $response->setStatusCode(201);
                        return $response;
                    }else{
                        $error = [
                            'status' => 500,
                            'error' => true,
                            'message' => 'Something went wrong...'
                        ];
                        $this->response->setJSON($error);
                        $response->setStatusCode(500);
                        return $response;
                    }

                }else{
                    $error = [
                        'status' => 403,
                        'error' => true,
                        'message' => 'You are not authorized to add new holiday'
                    ];
                    $this->response->setJSON($error);
                    $response->setStatusCode(403);
                    return $response;
                }

                }else{
                    $error = [
                        'status' => 404,
                        'error' => true,
                        'message' => 'Your user id does not exist with our database'
                    ];
                    $this->response->setJSON($error);
                    $response->setStatusCode(404);
                    return $response;
                }

            }
    }
}
