<?php 
namespace App\Models;
use CodeIgniter\Model;

class UsersModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'id';
    protected $allowedFields = ['username', 'email','password','is_active','status','authorized_to','roles','verification_link','reset_link'];
    
    // function for get selected user all data all works done by him/her
   function getAllData($search_user){
    $builder = $this->db->table('project_assign');
     $builder->select(['user.username as Employee_Name','projects.name as Project_name','projects.id as Project_id','user2.username as Project_assigned_by','project_assign.created_at as Assigned_Date','projects.project_status as Project_Status']);
    $builder->join('user', 'project_assign.user_id =  user.id');
    $builder->join('user as user2', 'project_assign.assign_by =  user2.id');
    $builder->join('projects', 'project_assign.project_id = projects.id');
    
    $builder->where('user.id', $search_user);
    
    $query = $builder->get();
    return $query->getResult();
}

// function for get selected user's each projects time entries
// function getTimeEntries($search_user, $project_id, $start_date, $end_date){
//     $builder = $this->db->table('time_entries');
//     $builder->select(['projects.name as Project_Name', 'time_entries.date as Worked_on', 'time_entries.time as Total_spent_time', 'time_entries.description as Work_Description']);
//     $builder->join('user', 'time_entries.user_id = user.id');
//     $builder->join('projects', 'time_entries.project_id = projects.id');
//     $builder->join('project_assign', 'project_assign.project_id = projects.id');
//     $builder->where('user.id', $search_user);
//     $builder->where('projects.id', $project_id);
//     $builder->where('project_assign.created_at >=', $start_date);
//     $builder->where('project_assign.created_at <=', $end_date);
//     $query = $builder->get();
//     return $query->getResult();
// }

// function getTimeEntries($search_user, $project_id, $start_date, $end_date) {
//     $builder = $this->db->table('time_entries');
//     $builder->select([
//         'projects.name as Project_Name',
//         'time_entries.date as Worked_on',
//         'time_entries.time as Total_spent_time',
//         'time_entries.description as Work_Description',
//         'project_assign.created_at',
//         'project_assign.deallocate_at'
//     ]);
//     $builder->join('user', 'time_entries.user_id =  user.id');
//     $builder->join('projects', 'time_entries.project_id = projects.id');
//     $builder->join('project_assign', 'project_assign.project_id = projects.id');
//     $builder->where('user.id', $search_user);
//     $builder->where('projects.id', $project_id);
//     $builder->where('project_assign.user_id', $search_user);
//     $builder->where('project_assign.project_id', $project_id);
//     $builder->where('project_assign.created_at <=', $end_date);
//     $builder->groupStart();
//     $builder->where('project_assign.deallocate_at >=', $start_date);
//     $builder->orWhere('project_assign.deallocate_at', null);
//     $builder->groupEnd();
//     $query = $builder->get();
//     return $query->getResult();
// }
function getTimeEntries($search_user, $project_id) {
    $builder = $this->db->table('time_entries');
    $builder->select(['projects.name as Project_Name','time_entries.date as Worked_on','time_entries.time as Total_spent_time','time_entries.description as Work_Description','project_assign.created_at as assigned_on', 'project_assign.deallocate_at as deallocate']);
    $builder->join('user', 'time_entries.user_id =  user.id');
    $builder->join('projects', 'time_entries.project_id = projects.id');
    $builder->join('project_assign', 'project_assign.project_id = projects.id');
    $builder->where('user.id', $search_user);
    $builder->where('projects.id', $project_id);
    $builder->groupBy('time_entries.id'); 

    $query = $builder->get();
    return $query->getResult();
}


// function for get all works reports for all user within given range
function getReportsWithInTimeRange($start_date,$end_date){
    $builder = $this->db->table('time_entries');
    $builder->select(['user.username as Employee_Name',
    'COALESCE(projects.name, "NA") as Project_Name',
        'time_entries.date as Worked_on',
    'time_entries.date as Worked_on',
    'time_entries.time as Total_spent_time',
    'time_entries.description as Work_Description',
    'COALESCE(task_assign.task_name, "NA") as Task_Name',
    'time_entries.entries_for',
    'work_description_for_proxy'

]);
    $builder->join('user', 'time_entries.user_id =  user.id');
    $builder->join('projects', 'time_entries.project_id = projects.id', 'left');
    $builder->join('task_assign', 'time_entries.task_id = task_assign.id', 'left');
    $builder->where('date >=', $start_date);
    $builder->where('date <=', $end_date);
    $builder->orderBy('time_entries.id', 'DESC');
    $query = $builder->get();
    return $query->getResult();
}
}