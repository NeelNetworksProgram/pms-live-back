<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'task_assign';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
       protected $allowedFields    = ['project_id','login_id','assign_to','task_name','task_status','task_description','task_revert_description','revert_status','revert_time'];


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



// function for check task is assign or not to current user
    public function verifyTask($login_user,$task_id){
        $builder = $this->db->table('task_assign');
        $builder->select('*');
        $builder->where('assign_to', $login_user);
        $builder->where('id', $task_id);
        $query = $builder->get();
        return $query->getResult();
    }


    // function for check task is assign or not by current user
    public function checkTask($login_user,$task_id){
        $builder = $this->db->table('task_assign');
        $builder->select('*');
        $builder->where('login_id', $login_user);
        $builder->where('id', $task_id);
        $query = $builder->get();
        return $query->getResult();
    }
    
    

public function getTaskBySingleUser($login_user, $task_status) {
    $builder = $this->db->table('task_assign');
    $builder->select([
        'task_assign.assign_to',
        'task_assign.id as task_id',
        'task_assign.task_name as task_name',
        'projects.id as project_id',
        'user_assign_to.username as assign_to',
        'user_assign_by.username as assign_by',
        'IFNULL(projects.name, "N.A") as Project_name',
        'task_assign.task_status',
        'task_assign.task_description',
        'task_assign.created_at as assign_on'
    ]);
    $builder->join('user as user_assign_to', 'task_assign.assign_to = user_assign_to.id');
    $builder->join('user as user_assign_by', 'task_assign.login_id = user_assign_by.id');
    $builder->join('projects', 'task_assign.project_id = projects.id', 'left');
    $builder->where('task_assign.assign_to', $login_user);
    $builder->where('task_assign.task_status', $task_status);
    $builder->orderBy('task_assign.id', 'desc'); 
    $query = $builder->get();
    return $query->getResult();
}



   public function getTaskAssignByCurrentUser($login_user) {
    $builder = $this->db->table('task_assign');
    $builder->select([
        'task_assign.assign_to',
        'task_assign.id as task_id',
        'projects.id as project_id',
        'user.username as assign_by',
        'assignee.username as assign_to',
        'IFNULL(projects.name, "N.A") as Project_name',
        'task_assign.task_status',
        'task_assign.task_description',
        'task_assign.created_at as assign_on',
        'task_assign.revert_status',
        'REPLACE(task_assign.task_revert_description, "<p>", "") as task_revert_description',
        'task_assign.revert_time',
        'task_assign.task_name'
    ]);
    $builder->join('user', 'task_assign.login_id = user.id');
    $builder->join('user as assignee', 'task_assign.assign_to = assignee.id');
    $builder->join('projects', 'task_assign.project_id = projects.id', 'left');
    $builder->where('task_assign.login_id', $login_user);
    $builder->orderBy('task_assign.id', 'desc');
    $query = $builder->get();
    return $query->getResult();
}



    
    //function for check task status with task id

    public function checkTaskStatus($task_id){
        $builder = $this->db->table('task_assign');
        $builder->select('task_status');
        $builder->where('task_assign.id', $task_id);
        $tasks = $builder->get()->getResult();
        return $tasks;
    }
}
