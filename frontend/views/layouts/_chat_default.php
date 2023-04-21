<?php
/** @var string $content */
?>

<?php $this->beginContent('@frontend/views/layouts/chat.php'); ?>
    <div class="nbeTrHeader">
        <?php echo $this->render('_top_menu'); ?>
    </div>

    <div class="wrap">
        <div class="container-fluid">
            <?php echo $content ?>
        </div>
    </div>

    <footer class="nbeFooter">
        <div class="container-fluid">
            <p class="pull-left">Chat created by <strong>Yepifanov Serhii</strong></p>
            <p class="pull-right">2022 - <?php echo date('Y'); ?></p>
        </div>
    </footer>
<?php $this->endContent(); ?>
