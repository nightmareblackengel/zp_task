<?php

class m221030_220922_chat_init extends \yii\db\Migration
{
	public function safeUp()
	{
		$sqlCommands = [
			"CREATE TABLE `user` (
				`id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`email` VARCHAR(255) NOT NULL
            ) ENGINE=InnoDB;",
			"CREATE TABLE `chat` (
				`id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`name` VARCHAR(255) NOT NULL,
				`isChannel` SMALLINT NOT NULL DEFAULT 0
			) ENGINE=InnoDB;",
			"CREATE TABLE `user_chat` (
				`userId` INT UNSIGNED NOT NULL,
				`chatId` INT UNSIGNED NOT NULL,
				`isUserBanned` SMALLINT NOT NULL DEFAULT 0,
				PRIMARY KEY (`userId`, `chatId`)
			) ENGINE=InnoDB;",
			"CREATE TABLE `chat_message` (
				`id` BIGINT UNSIGNED NOT NULL  PRIMARY KEY AUTO_INCREMENT,
				`text` TEXT,
				`status` SMALLINT NOT NULL DEFAULT 0
			) ENGINE=InnoDB;",
			"CREATE TABLE `user_settings` (
				`user_id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`historyStoreType` SMALLINT NOT NULL DEFAULT 0,
				`historyStoreTime` SMALLINT NOT NULL DEFAULT 0
			) ENGINE=InnoDB;",

		];

		foreach ($sqlCommands as $sql) {
			$this->execute($sql);
		}

		return false;
	}

	public function safeDown()
	{
		echo "m221030_220922_chat_init cannot be reverted.\n";

		return false;
	}

}
