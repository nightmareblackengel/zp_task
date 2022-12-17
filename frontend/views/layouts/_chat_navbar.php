<?php
$identity = Yii::$app->user->identity;
$userTitle = !empty($identity) ? $identity->getUserTitle() : '';
?>
<nav class="navbar-inverse">
    <div class="container-fluid">
        <button type="button" class="nbeFLeft">
            <span class="glyphicon glyphicon-send" aria-hidden="true"></span>
        </button>

        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <ul class="nav navbar-nav nbeAvatarCont">
                <li class="dropdown">
                    <a class="nbeAvatar dropdown-toggle" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <?= $userTitle; ?>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="#">Settings</a></li>
                    </ul>
                </li>
            </ul>
        </div>

        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <form class="navbar-form navbar-left">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search for...">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button">Search</button>
                    </span>
                </div>
            </form>
        </div>
    </div>
</nav>
