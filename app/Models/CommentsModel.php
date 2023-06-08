<?php

namespace App\Models;

use CodeIgniter\Model;

class CommentsModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'task_comments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['task_id','task_commentor','task_comments'];

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
    
    // get all comments for particular task by  current user id
    public function getAllComments($task_id){
        $builder = $this->db->table('task_comments');
        $builder->select(['task_assign.id as task_id','user.username as commentor','task_assign.created_at as comments_on','task_comments']);
        $builder->join('user', 'task_comments.task_commentor = user.id');
        $builder->join('task_assign', 'task_comments.task_id = task_assign.id');
        $builder->where('task_id', $task_id);
        $builder->orderBy('task_comments.id', 'desc'); // sort by task_id in ascending order
        $query = $builder->get();
        $result = $query->getResult();
        foreach ($result as &$comment) {
        $comment->task_comments = strip_tags($comment->task_comments);
    }
    return $result;
    }
}
