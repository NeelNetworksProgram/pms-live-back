<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectAssignModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'project_assign';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
   protected $allowedFields    = ['project_id','user_id','assign_by','work_description','project_deallocate','user_category','completion_time','project_completed','deallocate_at','id'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';


    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

// function for fetch data from 3 diffrent table at once
    public function fetch_data(){
        $builder = $this->db->table('project_assign');
        $builder->select(['user.username as Assigned_to','user2.username as assigned_by','projects.name as Project_name','project_assign.created_at as Assigned_Date','projects.id as project_id','user.id as user_id','project_assign.user_category','project_assign.completion_time as estimation_time','project_assign.work_description','projects.project_no','projects.project_category']);
        $builder->join(' user', 'project_assign.user_id =  user.id');
        $builder->join('user as user2', 'project_assign.assign_by =  user2.id');
        $builder->join('projects', 'project_assign.project_id = projects.id');
        $builder->where("(project_assign.project_deallocate = 'no' OR project_assign.project_deallocate IS NULL)");
        $builder->orderBy('project_assign.id', 'DESC');

        $query = $builder->get();
        return $query->getResult();
}

// function for get all assign project list where status project status is running loggin user
public function getAssignProjectList($current_user){
    $builder = $this->db->table('project_assign');
    $builder->select(['projects.name as Project_name','projects.id as Project_id' ,'projects.project_stage as project_stage','project_assign.created_at as assign_date','project_assign.work_description as description','projects.project_no','project_assign.completion_time','projects.project_category']);
    $builder->join('user', 'project_assign.user_id =  user.id');
    $builder->join('projects', 'project_assign.project_id = projects.id');
    $builder->where('user_id', $current_user);
    $builder->where('projects.project_status', 'Running');
    $builder->where('project_assign.project_deallocate', 'no');
    $query = $builder->get();
    $results = $query->getResult();

    // Remove HTML tags from work_description
    foreach ($results as &$result) {
        $result->description = strip_tags($result->description);
    }

    return $results;
}

// function for get all assign project list which is contain all like complete,running,hold
public function getAllAssignProjectList($current_user) {
    $builder = $this->db->table('project_assign');
    $builder->select(['projects.name as Project_name','projects.id as Project_id' ,'projects.project_status','project_assign.created_at as assign_date','project_assign.work_description as description','projects.project_no','project_assign.completion_time','projects.project_category','projects.project_stage',
    'user2.username as assign_by','project_assign.user_category','project_assign.project_completed','project_deallocate'
]);
    $builder->join('user', 'project_assign.user_id =  user.id');
    $builder->join('user as user2 ', 'project_assign.assign_by =  user2.id');
    $builder->join('projects', 'project_assign.project_id = projects.id');
    $builder->where('user_id', $current_user);
    $builder->where("(`project_assign`.`project_deallocate` = 'no' OR (`project_assign`.`project_deallocate` = 'yes' AND `project_assign`.`project_completed` = 'yes'))");
    
    $query = $builder->get();
    $result = $query->getResult();
    return $result;
    // foreach ($result as $row) {
    //     $row->description = strip_tags($row->description);
    // }
    // return $result;
}

// check project is assign or not to current user by project id
public function verifyProjectAssignOrNot($current_user, $project_id){
    $builder = $this->db->table('project_assign');
    $builder->select(['user.id as assign_user_id','user.email as assign_by', 'project_assign.project_deallocate']);
    $builder->join('user', 'project_assign.assign_by =  user.id');
    $builder->where('user_id', $current_user);
    $builder->where('project_id', $project_id);
    $builder->where("(project_deallocate = 'no' OR project_deallocate IS NULL OR project_deallocate = '')");
    $query = $builder->get();
    $result = $query->getResult();
    if (!empty($result)) {
        if ($result[0]->project_deallocate === 'no') {
            return $query->getResult();
        } else {
            return 'no';
        }
    } else {
        return 'no';
    }
}

// check current user has assign any project to someone or not 
public function anyProjectAssignByCurrentUser($current_user){
    $builder = $this->db->table('project_assign');
    $builder->select(['project_assign.project_id']);
    $builder->where('assign_by', $current_user);
    $count = $builder->countAllResults();
    return $count;
}


// function for get all assign project list by current user
public function getAllAssignProjectListByUserId($current_user){
    $builder= $this->db->table('project_assign');
    $builder->select(['project_assign.id as assign_id','project_assign.project_id','user.id as assign_to_user_id','user.username as assign_to_user_name',
    'user2.username as assigned_by_username','user2.id as assigned_by_user_id','projects.project_category',
    'projects.name as Project_name','projects.project_no','project_assign.project_deallocate',
    'project_assign.work_description','project_assign.user_category','projects.project_status','project_assign.completion_time','project_assign.created_at as assigned_date','project_assign.deallocate_at as deallocate'
]);
    $builder->join('user', 'project_assign.user_id =  user.id');
    $builder->join('user as user2', 'project_assign.assign_by =  user2.id');
    
    $builder->join('projects', 'project_assign.project_id =  projects.id');
    $builder->where('assign_by', $current_user);
    $query = $builder->get();
    $result = $query->getResult();
    return $result;
}



// function for return count of all project to the current user (how much project he/she completed,running,on hold etc..)
public function countAllAssignProject($login_user) {
    $builder = $this->db->table('project_assign');
$builder->select('*');
$builder->join('projects', 'project_assign.project_id = projects.id');
$builder->where('project_assign.user_id', $login_user);
$builder->groupStart()
    ->where('project_assign.project_deallocate', 'no')
    ->orWhere('project_assign.project_deallocate', 'yes')
        ->where('project_assign.project_completed', 'yes')
    ->groupEnd();
$query = $builder->get();
$result = $query->getResult();
$countAll = count($result);

    $builder->select('*');
    $builder->join('projects', 'project_assign.project_id = projects.id');
    $builder->where('projects.project_status', 'Running');
    $builder->where('project_assign.user_id', $login_user);
    $builder->where('project_assign.project_deallocate', 'no');
    $query = $builder->get();
    $result = $query->getResult();
    $countRunning = count($result);

    $builder->select('*');
    $builder->join('projects', 'project_assign.project_id = projects.id');
    $builder->where('projects.project_status', 'Completed');
    $builder->where('project_assign.user_id', $login_user);
    $builder->where('project_assign.project_completed', 'yes');
    
    $query = $builder->get();
    $result = $query->getResult();
    $countCompleted = count($result);

    $builder->select('*');
    $builder->join('projects', 'project_assign.project_id = projects.id');
    $builder->where('projects.project_status', 'Hold');
    $builder->where('project_assign.user_id', $login_user);
    $builder->where('project_assign.project_deallocate', 'no');
    $query = $builder->get();
    $result = $query->getResult();
    $countHold = count($result);

    $all_project_count = $countAll > 0 ? $countAll : 'N.A.';
    $running_count = $countRunning > 0 ? $countRunning : 'N.A.';
    $completed_count = $countCompleted > 0 ? $countCompleted : 'N.A.';
    $hold_count = $countHold > 0 ? $countHold : 'N.A.';

    return [
        'all_project' => $all_project_count,
        'running' => $running_count,
        'completed' => $completed_count,
        'hold' => $hold_count
    ];
}

// check how much user currently active on single project
public function getUserListOnSingleProject(){
    $builder = $this->db->table('projects');
    $builder->select(['projects.id as project_id',
        'GROUP_CONCAT(user.username) as assign_to',
        'projects.name as project_name',
        'projects.project_category',
        'projects.project_status',
        'projects.created_at',
        'projects.updated_at',
        'projects.project_stage',
        'projects.project_no'
    ]);
    $builder->join('project_assign', 'project_assign.project_id = projects.id AND project_assign.project_deallocate != "yes"', 'left');
    $builder->join('user', 'user.id = project_assign.user_id', 'left');
    $builder->groupBy('projects.name');
    $builder->orderBy('projects.id', 'DESC'); 
    $query = $builder->get();
    $result = $query->getResult();
    return $result;
}
}
