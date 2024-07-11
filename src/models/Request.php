<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
//use yii\debug;

/**
 * @property int $id
 * @property string $created_at
 * @property string $updated_at
 * @property string $email
 * @property string $phone
 * @property string|null $text
 * @property int|null $manager_id
 *
 * @property Manager|null $manager
 */
class Request extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'requests';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ]
        ];
    }

    public function rules()
    {
        return [
            [['email', 'phone'], 'required'],
            ['email', 'email'],
            ['manager_id', 'integer'],
            ['manager_id', 'exist', 'targetClass' => Manager::class, 'targetAttribute' => 'id'],
            [['email', 'phone'], 'string', 'max' => 255],
            ['text', 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Добавлен',
            'updated_at' => 'Изменен',
            'email' => 'Email',
            'phone' => 'Номер телефона',
            'manager_id' => 'Ответственный менеджер',
            'text' => 'Текст заявки',
        ];
    }

    // Поиск предыдущей заявки  
    public function findDuplicate()
    {
        $thirtyDaysAgo = (new \DateTime($this->created_at))->modify('-30 days')->format('Y-m-d H:i:s');
        $query = self::find()
            ->where(['or', ['email' => $this->email], ['phone' => $this->phone]])// совпадает email или телефон
            ->andWhere(['<', 'created_at', $this->created_at])// дата создания меньше текущей
            ->andWhere(['>', 'created_at', $thirtyDaysAgo])// дата создания больше 30 дней назад
            ->orderBy(['created_at' => SORT_DESC]);// сортировка по дате создания в порядке убывания
            

            return $query->one();// Возвращаем последнюю добавленную заявку
    }

    public function getManager()
    {
        return $this->hasOne(Manager::class, ['id' => 'manager_id']);
    }
}
