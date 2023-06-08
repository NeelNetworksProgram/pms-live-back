<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ProjectAssign extends Migration
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
            'user_id'=>[
                'type'       => 'INT'
               
            ],
            'assign_by'=>[
                'type'       => 'INT'
                
            ],

            'date'=>[
                'type'       => 'DATE'
            ],
            'created_at datetime default current_timestamp',
         ]);
         $this->forge->addPrimaryKey('id');
        $this->forge->createTable('project_assign');
    }

    public function down()
    {
        $this->forge->dropTable('project_assign');
    }
}
