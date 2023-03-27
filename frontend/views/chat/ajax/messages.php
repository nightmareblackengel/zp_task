<?php
use common\ext\helpers\Html;
use common\models\ChatMessageModel;
use frontend\models\helpers\MessageCommandHelper;
use frontend\models\redis\FlashMsgSetStorage;

/** @var stdClass[] $messages */
/** @var int $currentUserId */
/** @var array $userList */
/** @var array $chat */
/** @var ?int $chatOwnerId */
?>

<?php
if (!empty($chat['isChannel'])) { ?>
    <div class="chatMsgHeader" data-chat-type="<?php echo $chat['isChannel']; ?>"
        title="<?php echo "Владелец: " . $userList[$chatOwnerId] ?? ''; ?>">

        <?php echo Html::encode($chat['name']); ?>
    </div>
<?php
}
if (empty($messages)) {
    echo Html::tag('div', 'Вы не написали еще ни одного сообщения! Теперь есть повод!)', ['class' => 'oneMsgContainer']);
} else {
    foreach ($messages as $msgItem) {
        $userId = $msgItem->u ?? 0;
        $flash = FlashMsgSetStorage::getInstance()->getAndRemove($userId);

        $contClasses = 'oneMsgContainer';
        if ($msgItem->t === ChatMessageModel::MESSAGE_TYPE_SYSTEM) {
            $contClasses .= ' nbeMsgSystem';
        } elseif ($userId === $currentUserId) {
            $contClasses .= ' nbeMsgToRight';
        }
        ?>

        <div class="<?php echo $contClasses; ?>" data-msg-type="<?php echo $msgItem->t ?? '0'; ?>">
            <?php if ($msgItem->t === ChatMessageModel::MESSAGE_TYPE_SYSTEM) { ?>
                <?php echo MessageCommandHelper::printCmd($msgItem->m, $msgItem, $userList); ?>
            <?php } else { ?>
                <div class="nbeUser">
                    <?php if ($msgItem->u === $currentUserId) { ?>
                        Вы:
                    <?php } else { ?>
                        от пользователя: <?php echo Html::encode($userList[$msgItem->u] ?? '-'); ?>
                    <?php } ?>
                </div>
                <span class="nbeMessage <?php echo $userId !== $currentUserId ? 'nbeBgLGolden' : 'nbeLCyan'; ?>">
                    <?php echo Html::encode($msgItem->m ?? '[пустое сообщение]'); ?>
                    <span class="nbeDate">
                        <?php
                        if (!empty($msgItem->d)) {
                            $createdAt = date('Y-m-d H:i:s', (int) $msgItem->d);
                        }
                        echo $createdAt ?? '-';
                        ?>
                    </span>
                </span>
            <?php } ?>
        </div>

        <?php if (!empty($flash)) { ?>
            <div class="alert alert-success" role="alert">
                <?php echo $flash; ?>
            </div>
        <?php } ?>

    <?php }
}
