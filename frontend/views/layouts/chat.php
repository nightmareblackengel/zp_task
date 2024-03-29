<?php
use frontend\assets\AppAsset;
use yii\bootstrap\BootstrapPluginAsset;
use yii\helpers\Html;

/** @var string $content */
/** @var \yii\web\View $this */

AppAsset::register($this);
BootstrapPluginAsset::register($this);
$this->registerCssFile('/css/chat.css');
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

        <?php echo $content; ?>

        <?php $this->endBody(); ?>
    </body>
</html>
<?php $this->endPage();
