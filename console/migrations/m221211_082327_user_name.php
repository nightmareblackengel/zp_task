<?php

use yii\db\Migration;

class m221211_082327_user_name extends Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `user` ADD COLUMN `name` VARCHAR(30)");
        $this->execute("ALTER TABLE `user` ADD COLUMN `status` TINYINT UNSIGNED NOT NULL DEFAULT 0");

        $this->createIndex('user__email', 'user', 'email(30)', false);
    }

    public function down()
    {
        return false;
    }
}
