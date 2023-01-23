<?php
use common\ext\helpers\Html;

/** @var stdClass[] $messages */
/** @var int $currentUserId */
/** @var array $userList */
?>

<?php
if ($messages === false) {
    echo 'Чтобы просмотреть сообщения выберите, пожалуйста чат из списка слева. <br/>Если нет ни одного чата, то создайте его!';
} elseif (empty($messages)) {
    echo 'Вы не написали еще ни одного сообщения! Теперь есть повод!)';
} else {
    foreach ($messages as $msgItem) {
        $userId = $msgItem->u ?? 0;
        ?>

        <div class="oneMsgContainer <?php echo $userId === $currentUserId ? 'nbeMsgToRight' : ''; ?>"
             data-msg-type="<?php echo $msgItem->t ?? '0'; ?>">

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
        </div>

    <?php }
} ?>
