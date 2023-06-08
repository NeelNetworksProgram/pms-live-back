<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'notifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['notification_for','notification_message','notification_to','is_read'];

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


// get notification from database

    public function getNotifications($current_user){
        $builder = $this->db->table('notifications');
        $builder->select('*');
        $builder->where('notification_to', $current_user);
        $query = $builder->get();
        return $query->getResult();
    }


    // validate notifications id are valid or not
   
    public function validateNotificationsId($notification_ids){
        $builder = $this->db->table('notifications');
        $result = $builder->whereIn('id', $notification_ids)->get()->getResult();
        $valid_ids = [];
        

        foreach ($result as $row) {
        $valid_ids[] = $row->id;
        }
        return $valid_ids;
    }
    

    
}
