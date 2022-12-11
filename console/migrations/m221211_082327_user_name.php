<?php

use yii\db\Migration;

class m221211_082327_user_name extends Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `user` ADD COLUMN `name` VARCHAR(30)");
    }

    public function down()
    {
        return false;
    }
}
