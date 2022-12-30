<?php
/** @var string $content */
?>

<?php $this->beginContent('@frontend/views/layouts/chat.php'); ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-offset-2 col-md-8">
                <?= \frontend\widgets\CookieAlert::widget(); ?>
            </div>
        </div>

        <?php echo $content ?>
    </div>
<?php $this->endContent(); ?>
