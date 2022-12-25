<?php
use common\ext\helpers\Html;
use common\ext\widgets\ActiveForm;
use common\models\UserSettingsModel;
use frontend\ext\helpers\Url;
use yii\web\JqueryAsset;

/** @var \frontend\models\forms\UserSettingsForm $formModel */
/** @var \yii\web\View $this */
$this->registerJsFile('/js/settings.js', [
    'depends' => [
        JqueryAsset::class,
    ],
]);
?>

<div class="row">
    <div class="col-md-offset-2 col-md-8">

        <div class="bs-callout bs-callout-warning" id="callout-overview-not-both">
            <h4>Страница настроек пользователя</h4>
            <p>
                Здесь можно выбрать способы хранения истории чатов.
                <br/>История хранится одним из двух способов: либо по кол-ву сообщений, либо по кол-ву дней, после чего история удаляется.
                <br/>Количество сообщений/дней можно выбрать в соотвествующем поле.
            </p>
        </div>

        <?php $form = ActiveForm::begin([
            'method' => 'POST',
            'action' => Url::to('/main/settings'),
        ]); ?>

        <?php echo $form->field($formModel, 'userId')->hiddenInput()->label(false); ?>

        <div class="row">
            <div class="col-sm-12">
                <?php echo $form->field($formModel, 'historyStoreType')
                    ->dropDownList(UserSettingsModel::getHistoryStoreTypeList(), [
                        'class' => 'nbeStoreTypeDd form-control',
                        'prompt' => 'Выберите значение',
                        'options' => UserSettingsModel::getStoreTypeDropdownOptions(),
                    ]); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <?php echo $form->field($formModel, 'historyStoreTime')
                    ->dropDownList(UserSettingsModel::getHistoryStoreTime(), [
                        'class' => 'nbeStoreTimeDd form-control',
                        'prompt' => 'Выберите значение',
                        'options' => UserSettingsModel::getStoreTimeDropdownOptions(),
                    ]); ?>
            </div>
        </div>

        <?php echo Html::submitButton('Сохранить', ['class' => 'btn btn-success',]); ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>
