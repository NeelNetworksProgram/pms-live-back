<?php

namespace App\Models;

use CodeIgniter\Model;

class CurrentWorkModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'current_task';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id','task_short_description','task_long_description','insert_date','insert_time'];

    // Dates
    protected $useTimestamps = false;
    

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



    // function for find already data present or not for today's of the current user id 

    public function checkAlreadyDataInsertedOrNot($login_user, $today)
{
    $builder = $this->db->table('current_task');
    $builder->select('*');
    $builder->where('user_id', $login_user);
    $builder->where('insert_date', $today);
     $rowCount = $builder->countAllResults();
     return $rowCount > 0 ? 'yes' : 'no';
}


// fech already present data for todays date and current user id 

public function getAllDataForToday($login_user, $today){
    $builder = $this->db->table('current_task');

    $builder->select('*');
    $builder->where('user_id', $login_user);
    $builder->where('insert_date', $today);
    $query = $builder->get();
    return $query->getResult();

}

// fetch all records from database for current date
public function getAllPresentDataForAllUsers($date){
    $builder = $this->db->table('current_task');
    $builder->select(['user.username','current_task.user_id','current_task.task_short_description','current_task.task_long_description','current_task.insert_date','current_task.id']);
    $builder->join('user', 'current_task.user_id =  user.id');
    $builder->where('insert_date', $date);
    $query = $builder->get();
    return $query->getResult();
}

}
