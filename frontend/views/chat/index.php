<?php
use common\ext\helpers\Html;
use common\ext\widgets\ActiveForm;
use common\ext\widgets\ChatMsgActiveField;
use frontend\ext\helpers\Url;

/** @var \frontend\models\forms\ChatMessageForm $formModel */
/** @var stdClass[] $messages */
/** @var int $currentUserId */

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
        <div class="msgItemsContainer">
            <?php if (!empty($messages)) {
                foreach ($messages as $msgItem) {
                    $userId = $msgItem->u ?? 0;
                    ?>

                    <div class="oneMsgContainer <?php echo $userId === $currentUserId ? 'nbeMsgToRight' : ''; ?>"
                         data-msg-type="<?php echo $msgItem->t ?? '0'; ?>">

                        <div class="nbeUser">от пользователя: <?php echo Html::encode($msgItem->u ?? '-'); ?></div>
                        <span class="nbeMessage <?php echo $userId !== $currentUserId ? 'nbeBgLGolden' : 'nbeLCyan'; ?>">
                            <?php echo Html::encode($msgItem->m ?? '[пустое сообщение]'); ?>
                            <span class="nbeDate">
                                <?php
                                if (!empty($msgItem->d)) {
                                    $createdAt = date('Y-m-d H:i:s', (int) $msgItem->d);
                                }
                                echo $createdAt ?? '-';
                                ?>
                            </span>
                        </span>
                    </div>

                <?php }
            } else {
                echo 'Вы не написали еще ни одного сообщения! Теперь есть повод!)';
            } ?>
        </div>

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
