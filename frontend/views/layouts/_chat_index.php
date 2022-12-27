<?php
use frontend\assets\ChatAsset;

ChatAsset::register($this);

/** @var string $content */
?>

<?php $this->beginContent('@frontend/views/layouts/chat.php'); ?>
    <div class="wrap">
        <?= $this->render('_chat_chatlist'); ?>

        <div class="nbeContainer">
            <div class="container-fluid">
                <?php echo $content ?>
            </div>
        </div>
    </div>

    <footer class="nbeFooter">
        <div class="container-fluid">
            <p class="pull-left">Chat created by <strong>Yepifanov Serhii</strong></p>
            <p class="pull-right">2022</p>
        </div>
    </footer>
<?php $this->endContent(); ?>
