<?php
use common\ext\helpers\Html;
use frontend\ext\helpers\Url;

$this->title = 'Access denied';
?>

<div class="row">
    <div class="col-sm-offset-3 col-sm-6">
        <div class="bs-callout bs-callout-danger" id="callout-overview-not-both">
            <h4>У Вас нет прав доступа для просмотра содержимого этой страницы</h4>
            <p>
                Если Вы не авторизированы, то для просмотра содержимого страницы Вам необходимо авторизироваться!
                <br/><br/>Для авторизации Вам необходимо перейти по <?= Html::a('ссылке', [Url::to('/main/login')]); ?>.
            </p>
        </div>
    </div>
</div>
