<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'projects';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name','project_status','project_stage','project_no','project_category'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    //protected $deletedField  = 'deleted_at';

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
    
    public function getUserReportOnSingleProject($project_id){
    $builder = $this->db->table('project_assign');
    $builder->select(['user.username as username', 'user2.username as assigned_by', 'projects.name as project_name', 'projects.project_status as project_status',
                      'project_assign.created_at as Project_assigned_on', 'project_assign.assign_by as project_assigned_by', 'time_entries.date as worked_on',
                      'time_entries.time as total_spend_time', 'time_entries.description as work_description']);
    $builder->join('user', 'project_assign.user_id =  user.id');
    $builder->join('user as user2', 'project_assign.assign_by =  user2.id');
    $builder->join('projects', 'project_assign.project_id =  projects.id');
    $builder->join('time_entries', 'project_assign.user_id =  time_entries.user_id AND project_assign.project_id = time_entries.project_id');
    $builder->where('project_assign.project_id', $project_id);
    $query = $builder->get();
    return $query->getResult();
}

// function for add project number with prefix and auto increment
public function insert_project($data) {
    $sql = "SELECT MAX(SUBSTR(project_no, 3)) as max_project_no FROM projects";
    $query = $this->db->query($sql);
    $max_project_no = $query->getRow()->max_project_no;

    if (is_numeric($max_project_no) && $max_project_no >= 100) {
        $next_project_no = 'NN' . strval(intval($max_project_no) + 1);
    } else {
        $next_project_no = 'NN100';
    }

    $data['project_no'] = $next_project_no;

    return $this->insert($data) ? $next_project_no : null;
}

// check status of project 
public function checkProjectStatus($project_id){
    $builder = $this->db->table('projects');
    $builder->select(['projects.project_status']);
    $builder->where('projects.id', $project_id);
    $query = $builder->get();
    return $query->getResult();
}
}
