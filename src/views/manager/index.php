<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Manager;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ManagerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Менеджеры';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="manager-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Добавить нового менеджера', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'created_at:datetime',
            'updated_at:datetime',
            'name',
            'is_works:boolean',
// Кнопка для просмотра заявок
            [
                'class' => yii\grid\ActionColumn::class,
                'template' => '{requests}',// по умолчанию идем в index
                //'header' => 'Заявки',
                'buttons' => [
                    'requests' => function ($url, $model, $key) {
                        return Html::a('Заявки', ['request/index', 'RequestSearch[manager_id]' => $model->id], [
                            'class' => 'btn btn-success',
                        ]);// используем RequestSearch[manager_id] для передачи manager_id в GET и автоматом в параметр manager_id в RequestSearch
                    },
                ],
                'contentOptions' => ['style' => 'width:1px'],
            ],
// Кнопка для просмотра заявок
            [
                'class' => yii\grid\ActionColumn::class,
                'template' => '{view}',
                'buttons' => [
                    'view' => function ($url) {
                        return Html::a('Просмотр', $url, [
                            'class' => 'btn btn-primary',
                        ]);
                    },
                ],
                'contentOptions' => ['style' => 'width:1px'],
            ],
            [
                'class' => yii\grid\ActionColumn::class,
                'template' => '{update}',
                'buttons' => [
                    'update' => function ($url) {
                        return Html::a('Редактировать', $url, [
                            'class' => 'btn btn-primary',
                        ]);
                    },
                ],
                'contentOptions' => ['style' => 'width:1px'],
            ],
        ],
    ]); ?>


</div>
