<?php
use common\ext\helpers\Html;
use common\ext\widgets\ActiveForm;
?>
<div class="row">
    <div class="col-lg-5">
        <?php $form = ActiveForm::begin([]); ?>

            <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>

            <div class="form-group">
                <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
            </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>