<?php
use frontend\assets\ChatAsset;
use frontend\widgets\LeftNavBar;

ChatAsset::register($this);

/** @var string $content */
?>

<?php $this->beginContent('@frontend/views/layouts/chat.php'); ?>

<table class="nbeTableLayout">
    <tbody>
        <tr class="nbeTrHeader">
            <td colspan="2">
                <?php echo $this->render('_top_menu'); ?>
            </td>
        </tr>
        <tr>
            <td id="nbeLeftSideBar" class="nbeLeftNavBar">
                <?php echo LeftNavBar::widget(); ?>
            </td>
            <td class="nbeContainer">
                <div class="container-fluid">
                    <?php echo $content; ?>
                </div>
            </td>
        </tr>
        <tr class="nbeTrFooter">
            <td colspan="2">
                <footer class="nbeFooter">
                    <div class="container-fluid">
                        <p class="pull-left">Chat created by <strong>Yepifanov Serhii</strong></p>
                        <p class="pull-right">2022 - <?php echo date('Y'); ?></p>
                    </div>
                </footer>
            </td>
        </tr>
    </tbody>
</table>

<?php $this->endContent(); ?>
