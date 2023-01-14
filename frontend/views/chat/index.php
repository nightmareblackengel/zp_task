<?php
use common\ext\helpers\Html;
use common\ext\widgets\ActiveForm;
use common\ext\widgets\ChatMsgActiveField;
use frontend\ext\helpers\Url;

/** @var \frontend\models\forms\ChatMessageForm $formModel */
/** @var stdClass[] $messages */

$this->title = 'Главная страница';
?>
<div class="row">
    <div class="col-lg-12">
        <div class="nbeLoaderWrapp">
            <svg class="nbeLoader" viewBox="25 25 50 50" >
                <circle class="nbeLoaderCirc" cx="50" cy="50" r="20" fill="none" stroke="#5cb85c" stroke-width="2" />
            </svg>
        </div>

        <div class="messages-items">
            <?php if (!empty($messages)) {
                foreach ($messages as $msgItem) { ?>

                    <div class="" data-msg-type="<?php echo $msgItem->t ?: ''; ?>">
                        <?php echo date('Y-m-d H:i:s', $msgItem->d ?: ''); ?>
                        <?php echo $msgItem->m ?: ''; ?>
                    </div>

                <?php }
            } else {
                echo 'Вы не написали еще ни одного сообщения! Теперь есть повод!)';
            } ?>
        </div>

        <?php $form = ActiveForm::begin([
            'method' => 'post',
            'action' => Url::to(['/chat/index', 'chat_id' => $formModel->chatId]),
            'options' => [
                'class' => 'row add-new-msg-form',
            ],
        ]); ?>

        <div class="col-sm-9">
            <div class=" nbeAddChatMsgGroup">
                <?php echo $form->field($formModel, 'message', [
                        'class' => ChatMsgActiveField::class,
                    ])
                    ->textInput(['class' => 'form-control'])
                    ->label(false); ?>

                <?php echo $form->field($formModel, 'chatId')->hiddenInput()->label(false); ?>
                <?php echo $form->field($formModel, 'messageType')->hiddenInput()->label(false); ?>
                <?php echo $form->field($formModel, 'userId')->hiddenInput()->label(false); ?>
            </div>
        </div>
        <div class="col-sm-3">
            <?php echo Html::submitButton('Отправить', ['class' => 'btn btn-success nbeAddNewMsgBtn']); ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
