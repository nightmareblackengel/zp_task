<?php
use frontend\assets\ChatAsset;
use yii\bootstrap\BootstrapPluginAsset;
use yii\helpers\Html;

/** @var string $content */

ChatAsset::register($this);
BootstrapPluginAsset::register($this);
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset; ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php $this->registerCsrfMetaTags(); ?>
        <title><?= Html::encode($this->title); ?></title>
        <?php $this->head(); ?>
    </head>
    <body>
        <?php $this->beginBody(); ?>
        <?= $this->render('_chat_navbar'); ?>

        <div class="wrap">
            <?= $this->render('_chat_chatlist'); ?>

            <div class="nbeContainer">
                <div class="container-fluid">
                    <?= $content ?>
                </div>
            </div>
        </div>

        <footer class="nbeFooter">
            <div class="container-fluid">
                <p class="pull-left">Chat created by <strong>Yepifanov Serhii</strong></p>
                <p class="pull-right">2022</p>
            </div>
        </footer>
        <?php $this->endBody(); ?>
    </body>
</html>
<?php $this->endPage();
