<?php
use common\ext\helpers\Html;
use common\ext\widgets\ActiveForm;

$this->title = 'Страница Авторизации';

/** @var \frontend\models\forms\AuthForm $model */
?>
<div class="row">
    <div class="col-lg-5">
        <?php $form = ActiveForm::begin([]); ?>

        <div class="bs-callout bs-callout-warning" id="callout-overview-not-both">
            <h4>Страница авторизации пользователя</h4>
            <p>Для регистрации/авторизации достаточно ввести свой <code>email</code>. <br/>Если Вы авторизируетесь первый раз, система создаст новую запись, с новыми настройками. <br/>Если это повторная авторизация, то система найдёт все ваши прошлые настройки и сообщения.</p>
        </div>

        <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>

        <?= $form->field($model, 'cap')->widget(\yii\captcha\Captcha::class, [
            'captchaAction' => '/main/captcha',
        ]); ?>

        <div class="form-group">
                <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
            </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
