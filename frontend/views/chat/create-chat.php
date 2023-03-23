<?php
use common\ext\helpers\Html;
use common\ext\widgets\ActiveForm;
use conquer\select2\Select2Widget;
use frontend\assets\ChatEditFormAsset;

/** @var \frontend\models\forms\ChatCreateForm $formModel */

ChatEditFormAsset::register($this);

$this->title = 'Страница создания нового чата';
?>
<div class="row">
    <div class="col-md-offset-2 col-md-8">
        <h2><?php echo $this->title; ?></h2>
        <div class="bs-callout bs-callout-warning" id="callout-overview-not-both">
            <p>Для того, чтобы создать чат, достаточно выбрать одного пользователя из списка.</p>
            <p>Для того, чтобы создать канал, нужно выбрать соотвествующую опцию, и ввести название канала.</p>
        </div>

        <div class="row">
            <?php
            $form = ActiveForm::begin([
                'options' => [
                        'class' => 'col-sm-12',
                    ]
                ]);
            ?>

            <?= $form->field($formModel, 'userIdList', [
                    'errorOptions' => [
                        'class' => 'help-block',
                        'encode' => false,
                    ]
                ])->widget(Select2Widget::class, [
                    'multiple' => 'multiple',
                    'ajax' => ['chat/user-list', 'chat_id' => -1],
                    'settings' => [
                        'ajax' => ['delay' => 250],
                        'minimumInputLength' => 2,
                    ]
                ]); ?>

            <?= $form->field($formModel, 'currentUserId')->hiddenInput()->label(false); ?>

            <?= $form->field($formModel, 'isChannel')->checkbox(['id' => 'chatIsChannel']); ?>

            <?= $form->field($formModel, 'name', [
                    'options' => [
                        'class' => 'form-group chatNameFieldWrapper',
                    ],
                ])->textInput(['id' => 'chatName']); ?>

            <?= Html::submitButton('Создать', ['class' => 'btn btn-success']); ?>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
