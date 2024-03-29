<?php

class m221030_220922_chat_init extends \yii\db\Migration
{
	public function safeUp()
	{
		$sqlCommands = [
			"CREATE TABLE `user` (
				`id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`email` VARCHAR(255) NOT NULL,
				`name` VARCHAR(30),
				`status` TINYINT UNSIGNED NOT NULL DEFAULT 0,
				`createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				INDEX `user__email` (`email`(30))
            ) ENGINE=InnoDB;",
			"CREATE TABLE `chat` (
				`id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`name` VARCHAR(255) NOT NULL,
				`isChannel` SMALLINT,
				`status` SMALLINT NOT NULL DEFAULT 0,
				`createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				INDEX `chat__name` (`name`(100))
			) ENGINE=InnoDB;",
			"CREATE TABLE `user_chat` (
				`userId` INT UNSIGNED NOT NULL,
				`chatId` INT UNSIGNED NOT NULL,
				`isUserBanned` SMALLINT,
				`isChatOwner` SMALLINT,
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
            "CREATE OR REPLACE ALGORITHM = MERGE VIEW `vw_chat_user_name` AS
                SELECT uc1.`chatId`, uc1.`userId` AS user1, uc2.`userId` AS user2, u1.`email`, u1.`name`
                FROM `user_chat` uc1
                INNER JOIN `chat` c1 ON c1.`id` = uc1.`chatId`
                INNER JOIN `user_chat` uc2 ON uc2.`chatId` = uc1.`chatId` AND uc2.`userId` <> uc1.`userId`
                INNER JOIN `user` u1 ON u1.`id` = uc2.`userId`
                WHERE c1.`isChannel` IS NULL",
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
