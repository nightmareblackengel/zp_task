<?php
use common\ext\helpers\Html;
use common\ext\widgets\ActiveForm;
use common\ext\widgets\ChatMsgActiveField;
use frontend\ext\helpers\Url;

/** @var \frontend\models\forms\ChatMessageForm $formModel */

$this->title = 'Главная страница';
?>
<div class="loaderContainer <?php //echo 'nbeLoading';?>">

    <div class="nbeWaitContainer">
        <div class="nbeWaitWrapper">
            <svg class="nbeLoader" viewBox="25 25 50 50" >
                <circle class="nbeLoaderCirc" cx="50" cy="50" r="20" fill="none" stroke="#5cb85c" stroke-width="2" />
            </svg>
        </div>
    </div>

    <div class="loaderContent">
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
    </div>
</div>
