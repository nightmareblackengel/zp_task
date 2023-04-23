<?php
use common\ext\helpers\Html;
use common\models\mysql\ChatModel;
use common\models\mysql\UserChatModel;
use frontend\ext\helpers\Url;

/** @var $chatId */

$identity = Yii::$app->user->identity;
$userTitle = !empty($identity) ? Html::encode($identity->getUserTitle()) : '';
$hideChatBtnClass = (Yii::$app->controller->id === 'chat' && Yii::$app->controller->action->id === 'index') ? '' : 'nbeDisplayNone';

if (!empty($chatId)) {
    $isChatOwner = UserChatModel::getInstance()->isUserChatOwner($identity->getId(), $chatId);
    $chat = ChatModel::getInstance()->getItemBy(['id' => $chatId]);
}
?>
<nav class="navbar-inverse">
    <div class="container-fluid">
<!--        <button type="button" class="nbeFLeft --><?php //echo $hideChatBtnClass; ?><!--">-->
<!--            <span class="glyphicon glyphicon-send" aria-hidden="true"></span>-->
<!--        </button>-->

        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <?php if(!empty($identity)): ?>
                <ul class="nav navbar-nav nbeAvatarCont">
                    <li class="dropdown">
                        <a class="nbeAvatar dropdown-toggle" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <span class="nbeUserNick" title="<?php echo $userTitle; ?>"><?php echo $userTitle; ?></span>
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="<?= Url::to('/settings/index') ?>">Настройки</a></li>
                            <li>
                                <?php
                                echo Html::beginForm(['/main/logout'], 'post')
                                    . Html::submitButton('Выйти (' . $userTitle . ')', ['class' => 'nbeLiLink', 'title' => $userTitle])
                                    . Html::endForm();
                                ?>
                            </li>
                        </ul>
                    </li>
                </ul>
            <?php endif; ?>
        </div>

        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <!-- <form class="navbar-form navbar-left">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Введите название чата">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button">Поиск</button>
                    </span>
                </div>
            </form> -->
            <ul class="nav navbar-nav">
                <li><a href="<?= Url::to('/chat/index'); ?>">Главная страница</a></li>
                <li><a href="<?= Url::to('/chat/connect-to-channel'); ?>">Поиск канала</a></li>
                <?php if (!empty($isChatOwner) && !empty($chat['isChannel']) && $chat['isChannel'] === ChatModel::IS_CHANNEL_TRUE) { ?>
                    <li><a href="<?= Url::to(['/chat/add-user-to-channel', 'chat_id' => $chatId]); ?>">Добавить пользователя</a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>
