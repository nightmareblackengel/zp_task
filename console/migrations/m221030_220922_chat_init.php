<?php

class m221030_220922_chat_init extends \yii\db\Migration
{
	public function safeUp()
	{
		$sqlCommands = [
			"CREATE TABLE `user` (
				`id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`email` VARCHAR(255) NOT NULL,
				`status` TINYINT UNSIGNED NOT NULL DEFAULT 0,
				`createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				UNIQUE `user__email` (`email`)
            ) ENGINE=InnoDB;",
			"CREATE TABLE `chat` (
				`id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`name` VARCHAR(255) NOT NULL,
				`isChannel` SMALLINT NOT NULL DEFAULT 0,
				`status` SMALLINT NOT NULL DEFAULT 0,
				`createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				INDEX `chat__name` (`name`(100))
			) ENGINE=InnoDB;",
			"CREATE TABLE `user_chat` (
				`userId` INT UNSIGNED NOT NULL,
				`chatId` INT UNSIGNED NOT NULL,
				`isUserBanned` SMALLINT NOT NULL DEFAULT 0,
				`isChatOwner` SMALLINT NOT NULL DEFAULT 0,
				`createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`chatId`, `userId`),
				INDEX `user_chat__user_idx` (`userId`)
			) ENGINE=InnoDB;",
//			"CREATE TABLE `chat_message` (
//				`id` BIGINT UNSIGNED NOT NULL  PRIMARY KEY AUTO_INCREMENT,
//              `chatId` BIGINT UNSIGNED NOT NULL,
//              `userId` BIGINT UNSIGNED NOT NULL,
//				`text` TEXT,
//              `type` TINYINT (simple, system - видно только тому, кто отправил, тоже сохраняется в БД)
//				`status` SMALLINT NOT NULL DEFAULT 0,
//				`createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
//			) ENGINE=Memory;",
			"CREATE TABLE `user_setting` (
				`userId` INT UNSIGNED NOT NULL PRIMARY KEY,
				`historyStoreType` SMALLINT NOT NULL DEFAULT 0,
				`historyStoreTime` SMALLINT NOT NULL DEFAULT 0
			) ENGINE=InnoDB;",
		];

		foreach ($sqlCommands as $sql) {
			$this->execute($sql);
		}

		return true;
	}

	public function safeDown()
	{
		echo "m221030_220922_chat_init cannot be reverted.\n";

		return false;
	}

}
