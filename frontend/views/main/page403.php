<?php
use common\ext\helpers\Html;
use frontend\ext\helpers\Url;

$this->title = 'Access denied';
?>

<div class="row">
    <div class="col-lg-offset-4 col-lg-5">
        <div class="bs-callout bs-callout-danger" id="callout-overview-not-both">
            <h4>У Вас нет прав доступа для просмотра содержимого этой страницы</h4>
            <p>
                Для просмотра содержимого страницы Вам необходимо авторизироваться!
                <br/>Для авторизации Вам необходимо перейти по <?= Html::a('ссылке', [Url::to('/main/login')]); ?>.
            </p>
        </div>
    </div>
</div>
