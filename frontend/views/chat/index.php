<?php
use common\ext\helpers\Html;
use frontend\widgets\AjaxLoader;

/** @var \frontend\models\forms\MessageAddForm $formModel */

$this->title = 'Главная страница';
?>
<?php echo AjaxLoader::begin(['code' => 'messages']); ?>
    <?php echo Html::tag('div', '', [
        'class' => 'nbeAjaxMessageContainer'
    ]); ?>

    <div class="addNewMsgContainer nbeDisplayNone">

    </div>
<?php
echo AjaxLoader::end();
