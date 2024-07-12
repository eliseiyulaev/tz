<?php

use yii\db\Migration;

class m240712_194701_add_duplicate extends Migration
{
    public function safeUp()
    {
        $this->addColumn('requests', 'duplicate_id', $this->integer());
    }

    public function safeDown()
    {
        $this->dropColumn('requests', 'duplicate_id');
    }
}