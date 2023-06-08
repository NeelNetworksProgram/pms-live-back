<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TaskComments extends Migration
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
            'task_id'=>[
                'type'       => 'INT'
               
            ],
            'task_commentor'=>[
                'type'       => 'INT'
                
            ],
            'task_comments'=>[
                'type'       => 'TEXT'
                
            ],
            'created_at datetime default current_timestamp',
         ]);
         $this->forge->addPrimaryKey('id');
        $this->forge->createTable('task_comments');
    }

    public function down()
    {
        $this->forge->dropTable('task_comments');
    }
}
