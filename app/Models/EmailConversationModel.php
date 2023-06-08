<?php

namespace App\Models;

use CodeIgniter\Model;

class EmailConversationModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'email_conversation';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['project_id','email_to','email_by','email_content'];

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



    public function getAllMailListByUserId($current_user){
        $builder= $this->db->table('email_conversation');
        $builder->select([
            'user.email as to',
            'user2.email as from',
            'projects.name as project_name',
            'email_conversation.created_at as emailed_on'
        ]);
        $builder->select("email_conversation.email_content as email_body");
        $builder->join('user', 'email_conversation.email_to =  user.id');
        $builder->join('user as user2', 'email_conversation.email_by =  user2.id');
        $builder->join('projects', 'email_conversation.project_id =  projects.id');
        $builder->where('email_to', $current_user);
        $query = $builder->get();
        $result = $query->getResult();
    
        foreach ($result as $row) {
            $row->email_body = strip_tags($row->email_body);
        }
    
        return $result;
    }
    
    
}
