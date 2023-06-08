<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TaskAssign extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'project_id' => [
                'type'       => 'INT'
               
            ],
            'login_id'=>[
                'type'       => 'INT'
               
            ],
            'assign_to'=>[
                'type'       => 'INT'
                
            ],

            'date'=>[
                'type'       => 'DATE'
            ],
            'created_at datetime default current_timestamp',
         ]);
         $this->forge->addPrimaryKey('id');
        $this->forge->createTable('task_assign');
    }

    public function down()
    {
        $this->forge->dropTable('task_assign');
    }
}
