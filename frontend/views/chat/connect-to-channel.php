<?php
use common\ext\helpers\Html;
use common\ext\widgets\ActiveForm;
use conquer\select2\Select2Widget;
use frontend\ext\helpers\Url;

/** @var \frontend\models\forms\ConnectToChannelForm $formModel */
/** @var int $userId */

$this->title = 'Присоединение к существующему каналу';
?>
<div class="row">
    <div class="col-md-offset-2 col-md-8">
        <div class="bs-callout bs-callout-warning" id="callout-overview-not-both">
            <h4>Поиск каналов</h4>
            <p>
                Вы можете найти существующий канал и присоединиться к нему.
                <br/>Для этого Вам необходимо в соотвествующем поле ввести часть имени канала,
                <br/>выбрать из выпадающего списка канал (если такой существует).
                <br/>Нажать на кнопку "Присоединиться"
            </p>
        </div>

        <?php $form = ActiveForm::begin([
            'method' => 'POST',
            'action' => Url::to('/chat/connect-to-channel'),
        ]); ?>

        <?= $form->field($formModel, 'channelId', [
            'errorOptions' => [
                'class' => 'help-block',
                'encode' => false,
            ]
        ])->widget(Select2Widget::class, [
            'ajax' => ['chat/channel-list', 'user_id' => $userId],
            'settings' => [
                'ajax' => ['delay' => 250],
                'minimumInputLength' => 2,
            ]
        ]); ?>

        <?php echo Html::submitButton('присоединиться', ['class' => 'btn btn-success',]); ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>
