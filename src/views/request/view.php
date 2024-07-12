<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Request */

$this->title = $model->email;
$this->params['breadcrumbs'][] = ['label' => 'Заявки', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="request-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'created_at:datetime',
            'updated_at:datetime',
            'email:email',
            'phone',
            [
                'attribute' => 'manager_id',
                'value' => $model->manager ? $model->manager->name : null
            ],
            [
                'attribute' => 'duplicate_id',
                'label' => 'Предыдущая заявка',
                'format' => 'raw',
                'value' => function ($model) {
                    
                    return $model->duplicate_id ? Html::a('№' . Html::encode($model->duplicate_id), ['view', 'id' => $model->duplicate_id]) : '&mdash;';
                },
            ],
            'text:ntext',
        ],
    ]) ?>

</div>
