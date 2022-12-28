<?php
use common\ext\helpers\Html;
use common\ext\widgets\ActiveForm;

/** @var \frontend\models\forms\ChatCreateForm $formModel */

$this->title = 'Страница создания нового чата';
?>
<div class="row">
    <div class="col-md-offset-2 col-md-8">
        <h2><?php echo $this->title; ?></h2>

        <div class="row">
            <?php
            $form = ActiveForm::begin([
                'options' => [
                        'class' => 'col-sm-12',
                    ]
                ]);
            ?>

            <?= $form->field($formModel, 'name')->textInput(); ?>

            <?= $form->field($formModel, 'userIdList')->dropDownList([]); ?>

            <?= $form->field($formModel, 'currentUserId')->hiddenInput()->label(false); ?>

            <?= $form->field($formModel, 'isChannel')->checkbox(); ?>

            <?= Html::submitButton('Создать', ['class' => 'btn btn-success']); ?>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
