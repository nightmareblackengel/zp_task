<?php
use frontend\ext\helpers\Url;
use yii\helpers\Html;

/** @var array $chatList */
/** @var int $requestChatId */
?>

<?php
if (empty($chatList) || !is_array($chatList)) {
    echo "<strong>Вы еще не создали ни одного чата.<br/> Создайте новый чат или присоединитесь к существующему, через поиск.</strong>";
} else { ?>
    <div class="list-group nbeChatList">
        <?php foreach ($chatList as $chatItem) {
            $linkClasses = ['list-group-item'];
            if ($chatItem['chatId'] === $requestChatId) {
                $linkClasses[] = 'active';
            }
            $chatIconClasses = ['glyphicon', 'nbeChatIcon'];
            $chatIconClasses[] = !empty($chatItem['isChannel']) ? 'glyphicon-th-list' : 'glyphicon-user';

            $linkContent =
                Html::tag('span', '', ['class' => implode(' ', $chatIconClasses)])
                . Html::tag('span', $chatItem['count'] ?? 0, ['class' => 'badge'])
                . Html::encode($chatItem['name']);

            echo Html::tag(
                'a',
                $linkContent,
                [
                    'class' => implode(' ', $linkClasses),
                    'href' => Url::to(['/chat/index', 'chat_id' => $chatItem['chatId']]),
                ]);
        } ?>
    </div>
<?php } ?>

