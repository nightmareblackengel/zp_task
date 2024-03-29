<?php
use common\ext\helpers\Html;
use frontend\ext\helpers\Url;

$identity = Yii::$app->user->identity;
$userTitle = !empty($identity) ? $identity->getUserTitle() : '';
?>
<nav class="navbar-inverse">
    <div class="container-fluid">
        <button type="button" class="nbeFLeft">
            <span class="glyphicon glyphicon-send" aria-hidden="true"></span>
        </button>

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
                            <?= $userTitle; ?>
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="<?= Url::to('/chat/settings') ?>">Настройки</a></li>
                            <li>
                                <?php
                                echo Html::beginForm(['/main/logout'], 'post')
                                    . Html::submitButton('Выйти (' . Yii::$app->user->identity->getUserTitle() . ')', ['class' => 'nbeLiLink'])
                                    . Html::endForm();
                                ?>
                            </li>
                        </ul>
                    </li>
                </ul>
            <?php endif; ?>
        </div>

        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <form class="navbar-form navbar-left">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Введите название чата">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button">Поиск</button>
                    </span>
                </div>
            </form>
            <ul class="nav navbar-nav">
                <li><a href="<?= Url::to('/chat/index') ?>">Главная страница</a></li>
            </ul>
        </div>
    </div>
</nav>
