<?php
use yii\helpers\Html;

/** @var array $chatList */
?>
<div class="panel-default nbeLeftPanel">
    <div class="panel-heading">
        <h3 class="panel-title"><strong>Chat lists</strong></h3>
    </div>
    <div class="panel-body">
        <button type="button" class="btn btn-success nbeAddNewChat">Add new Chat</button>
        <?php
        if (empty($chatList) || !is_array($chatList)) {
            echo "<strong>There isn't any chat. Please, create a new chat or join the existing one.</strong>";
        } else { ?>
            <div class="list-group">
                <?php foreach ($chatList as $chatItem) {
                    $linkClasses = ['list-group-item'];
                    if (!empty($chatItem['isActive'])) {
                        $linkClasses[] = 'active';
                    }
                    $chatIconClasses = ['glyphicon', 'nbeChatIcon'];
                    $chatIconClasses[] = !empty($chatItem['isChannel']) ? 'glyphicon-th-list' : 'glyphicon-user';

                    $linkContent =
                        Html::tag('span', '', ['class' => implode(' ', $chatIconClasses)])
                        . Html::tag('span', $chatItem['unreadCount'] ?? 0, ['class' => 'badge'])
                        . Html::encode($chatItem['label']);

                    echo Html::tag(
                        'a',
                        $linkContent,
                        [
                            'class' => implode(' ', $linkClasses),
                            'href' => $chatItem['ajax-link'] ?? '#',
                        ]);
                } ?>
            </div>
        <?php } ?>
    </div>
</div>
