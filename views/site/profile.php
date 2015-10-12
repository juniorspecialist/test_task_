<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'Страница пользователя';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode('Добрый день,'.Yii::$app->user->identity->username) ?></h1>

    <?=Html::a('Выход', ['/site/logout'],['class'=>'btn btn-primary'])?>
</div>
