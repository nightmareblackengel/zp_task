<?php
use frontend\assets\MessagesAsset;

MessagesAsset::register($this);

/** @var string $content */
?>

<?php $this->beginContent('@frontend/views/layouts/chat.php'); ?>

<table class="nbeTableLayout">
    <thead>
        <tr>
            <td class="firstLayoutColumn"></td>
            <td></td>
        </tr>
    </thead>
    <tbody>
        <tr class="nbeTrHeader">
            <td colspan="2">
                <?php echo $this->render('_top_menu', ['chatId' => Yii::$app->request->get('chat_id')]); ?>
            </td>
        </tr>
        <tr>
            <td id="nbeLeftSideBar" class="firstLayoutColumn">
                <?php echo $this->render('_chat_left_bar'); ?>
            </td>
            <td class="nbeContainer">
                <div class="container-fluid">
                    <?php echo $content; ?>
                </div>
            </td>
        </tr>
        <tr class="nbeTrFooter">
            <td colspan="2">
                <?php echo $this->render('_chat_footer'); ?>
            </td>
        </tr>
    </tbody>
</table>

<?php $this->endContent(); ?>
