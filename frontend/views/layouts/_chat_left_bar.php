<?php
use frontend\ext\helpers\Url;
use yii\helpers\Html;

?>
<div class="container-fluid">
    <div class="panel-default nbeLeftPanel">
        <div class="panel-heading">
            <h3 class="panel-title"><strong>Список чатов</strong></h3>
        </div>
        <div class="panel-body nbeChatContainer">
            <?php echo Html::a('Создать новый чат', Url::to('/chat/create'), ['class' => 'btn btn-success nbeAddNewChat']); ?>
            <br/>
            <br/>
            <?php echo Html::beginTag('div', [
                'class' => 'nbeAjaxChatContainer',
                'data' => [
                    'chat-id' => Yii::$app->request->get('chat_id', 0),
                    'chat-updated' => time(),
                ],
            ]); ?>
            </div>
        </div>
    </div>
</div>
