<?php
use frontend\assets\ChatAsset;
use yii\helpers\Html;

/** @var string $content */

ChatAsset::register($this);
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php $this->registerCsrfMetaTags(); ?>
        <title><?= Html::encode($this->title); ?></title>
        <?php $this->head() ?>
    </head>
    <body>
        <div class="wrap">
            <?php $this->beginBody(); ?>
            <div class="container">
                <?= $content ?>
            </div>
        </div>
    </body>
</html>
<?php $this->endPage();
