<?php

namespace App\Models;

use CodeIgniter\Model;

class TimeModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'time_entries';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
   protected $allowedFields    = ['project_id','user_id','date','time','description','task_id','entries_for','work_description_for_proxy','is_edit'];

    // Dates
    protected $useTimestamps = false;
    // protected $dateFormat    = 'datetime';
    // protected $createdField  = 'created_at';
    // protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at';

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
    
    
    // function for get all time entries by selected user with RDBMS
    function get_data($user_id,$date){
        $builder = $this->db->table('time_entries');
        $builder->select(['time_entries.task_id as task_id','time_entries.id as entries_id','time_entries.project_id', 'projects.name as Project_name','time_entries.date','time_entries.time','time_entries.description','task_assign.task_name','time_entries.entries_for','work_description_for_proxy','time_entries.is_edit']);
        $builder->join('projects', 'projects.id = time_entries.project_id', 'left');
        $builder->join('task_assign', 'task_assign.id = time_entries.task_id', 'left');
        $builder->where('time_entries.user_id', $user_id);
        $builder->where('time_entries.date', $date);
        $query = $builder->get();
        return $query->getResult();
    }
}
