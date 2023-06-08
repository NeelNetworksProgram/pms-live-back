<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Time extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'project_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'user_id'=>[
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'date'=>[
                'type'       => 'DATE'
            ],
            'time'=>[
                'type'       => 'TIME'
            ],
            'description'=>[
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
         ]);
         $this->forge->addPrimaryKey('id');
        $this->forge->createTable('time_entries');
    
}

    public function down()
    {
        $this->forge->dropTable('time_entries');
    }
}
