<?php
use frontend\ext\helpers\Url;
use yii\helpers\Html;

/** @var array $chatList */
/** @var int $requestChatId */
?>

<?php
if (empty($chatList) || !is_array($chatList)) {
    echo "<strong>Вы еще не создали ни одного чата.</strong>";
} else { ?>
    <div class="list-group nbeChatList">
        <?php foreach ($chatList as $chatItem) {
            $isChannel = !empty($chatItem['isChannel']);
            $chatName = Html::encode($chatItem['name']);
            $linkClasses = ['list-group-item'];
            if ($chatItem['chatId'] === $requestChatId) {
                $linkClasses[] = 'active';
            }
            $chatIconClasses = ['glyphicon', 'nbeChatIcon'];
            $chatIconClasses[] = $isChannel ? 'glyphicon-th-list' : 'glyphicon-user';

            $linkContent =
                Html::tag('span', '', ['class' => implode(' ', $chatIconClasses)])
                . Html::tag('span', $chatItem['count'] ?? 0, ['class' => 'badge'])
                . Html::tag('span', $chatName, ['class' => 'nbeChatContent']);

            echo Html::tag(
                'a',
                $linkContent,
                [
                    // 'id' => 'divChatId' . $chatItem['chatId'],
                    'class' => implode(' ', $linkClasses),
                    'data' => [
                        'id' => $chatItem['chatId'],
                        'type' => $isChannel ? 1 : 0,
                    ],
                    'href' => Url::to(['/chat/index', 'chat_id' => $chatItem['chatId']]),
                    'title' => $chatName,
                ]);
        } ?>
    </div>
<?php } ?>
