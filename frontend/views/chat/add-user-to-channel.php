<?php
use common\ext\helpers\Html;
use common\ext\widgets\ActiveForm;
use conquer\select2\Select2Widget;

/** @var \frontend\models\forms\ChatAddUserForm $usersForm */
/** @var \common\models\mysql\ChatModel $chat */

$this->title = 'Добавление пользователей';
?>
<div class="row">
    <div class="col-md-offset-2 col-md-8">
        <h2><?php echo $this->title; ?></h2>
        <div class="bs-callout bs-callout-warning" id="callout-overview-not-both">
            <p>Для того, чтобы добавить новых, существующих пользователей в канал "<?= Html::encode($chat['name']); ?>"</p>
            <p>необходимо выбрать необходимых пользователей из списка ниже и нажать Сохранить.</p>
        </div>
        <div class="row">
            <?php
            $form = ActiveForm::begin([
                'options' => [
                    'class' => 'col-sm-12',
                ]
            ]);
            ?>

            <?= $form->field($usersForm, 'userIds', [
                'errorOptions' => [
                    'class' => 'help-block',
                    'encode' => false,
                ]
            ])->widget(Select2Widget::class, [
                'items' => [],
                'multiple' => 'multiple',
            ]); ?>

            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']); ?>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

// TODO: список пользователей этого канала