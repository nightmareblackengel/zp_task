<?php
use common\ext\helpers\Html;
use common\ext\widgets\ActiveForm;
use common\ext\widgets\ChatMsgActiveField;
use frontend\ext\helpers\Url;
use frontend\widgets\AjaxLoader;

/** @var \frontend\models\forms\ChatMessageForm $formModel */

$this->title = 'Главная страница';
?>
<?php echo AjaxLoader::begin(['code' => 'messages']); ?>
    <?php echo Html::tag('div', '', [
        'class' => 'nbeAjaxMessageContainer'
    ]); ?>

    <div class="addNewMsgContainer">
        <?php $form = ActiveForm::begin([
            'method' => 'post',
            'action' => Url::to(['/chat/index', 'chat_id' => $formModel->chatId]),
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
                        ->textInput(['class' => 'form-control', 'placeholder' => 'Введите сообщение...'])
                        ->label(false); ?>

                    <?php echo $form->field($formModel, 'chatId')->hiddenInput()->label(false); ?>
                    <?php echo $form->field($formModel, 'messageType')->hiddenInput()->label(false); ?>
                    <?php echo $form->field($formModel, 'userId')->hiddenInput()->label(false); ?>
                </div>
            </div>
            <div class="col-sm-3">
                <?php echo Html::submitButton('Отправить', ['class' => 'btn btn-success nbeAddNewMsgBtn']); ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
<?php
echo AjaxLoader::end();
