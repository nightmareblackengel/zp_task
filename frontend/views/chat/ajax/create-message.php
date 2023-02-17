<?php
use common\ext\helpers\Html;
use common\ext\widgets\ActiveForm;
use common\ext\widgets\ChatMsgActiveField;
use frontend\ext\helpers\Url;

/** @var \frontend\models\forms\MessageAddForm $formModel */
?>

<?php $form = ActiveForm::begin([
    'id' => 'addNewMessageForm',
    'method' => 'post',
    'action' => Url::to(['/']),
    'options' => [
        'class' => 'container-fluid addNewMsgForm',
    ],
]); ?>

<div class="row">
    <div class="col-sm-9">
        <div class="nbeAddChatMsgGroup">
            <?php echo $form->field($formModel, 'message', [
                    'class' => ChatMsgActiveField::class,
                ])
                ->sendMessageText(['class' => 'form-control', 'placeholder' => 'Введите сообщение...'])
                ->label(false); ?>

            <?php echo $form->field($formModel, 'chatId')->hiddenInput()->label(false); ?>
            <?php echo $form->field($formModel, 'messageType')->hiddenInput()->label(false); ?>
            <?php echo $form->field($formModel, 'userId')->hiddenInput()->label(false); ?>
        </div>
    </div>
    <div class="col-sm-3">
        <?php echo Html::button('Отправить', ['type' => 'button', 'class' => 'btn btn-success nbeAddNewMsgBtn']); ?>
    </div>
</div>

<?php ActiveForm::end(); ?>
