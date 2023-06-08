<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CurrentWorkModel;
use CodeIgniter\API\ResponseTrait;

class CronJobs extends BaseController
{
    use ResponseTrait;
    protected $current_work;
    public function __construct() {	
		$db = db_connect();
		$this->current_work = new CurrentWorkModel($db);
 	}
 	
 	public function deleteAllTaskForCurrentDate(){
 	     $current_date = time();
        $date = date('Y-m-d',  $current_date);
        
        $delete = $this->current_work->where('insert_date', $date)->delete();
        
        if($delete){
            // Call the sendEmail method of MailController
                    $subject = 'Task Deleted';
                    $message = 'Task Deleted';
                    $sendMail = new MailController();
                    $sendMail->send_mail('hemant@neelnetworks.com','hemant@neelnetworks.com','Task is deleted',$subject,$message);
        }
        
        
 	}
 		
 		
   
}
