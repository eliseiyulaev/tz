<?php

namespace app\models;

use Yii;
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

    private function isDateTime($date) // функция для проверки соответсвия строки формату DataTime
    {
        $d = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
        return $d && $d->format('Y-m-d H:i:s') === $date;
    }
        
    // Поиск предыдущей заявки  
    public function findDuplicate()
    {
        if (!$this->isDateTime($this->created_at)) {
            $this->created_at = (new \DateTime())->format('Y-m-d H:i:s');
        }

        //Yii::info('Дата создания заявки: ' . $this->created_at, __METHOD__);

        $thirtyDaysAgo = (new \DateTime($this->created_at))->modify('-30 days')->format('Y-m-d H:i:s');
        $query = self::find()
            ->where(['or', ['email' => $this->email], ['phone' => $this->phone]])
            ->andWhere(['<', 'created_at', $this->created_at])// дата создания меньше текущей
            ->andWhere(['>', 'created_at', $thirtyDaysAgo])// дата создания больше 30 дней назад
            ->orderBy(['created_at' => SORT_DESC]);
            
        return $query->one();// Возвращаем последнюю добавленную заявку
    }

    public function getManager()
    {
        return $this->hasOne(Manager::class, ['id' => 'manager_id']);
    }

    public function assignManager()// назначение менеджера на заявку
    {
        $duplicate = $this->findDuplicate();
        if ($duplicate && $duplicate->manager && $duplicate->manager->is_works) {
            $this->manager_id = $duplicate->manager_id;
        } else {// если не дубль или менеджер не работает, то назначаем менеджера с минимальным количеством заявок
            $managerWithMinRequests = Manager::getManagerWithMinRequests();
            if ($managerWithMinRequests !== null) {
                $this->manager_id = $managerWithMinRequests->id;
            }
        }
    }

    public function beforeSave($insert)// до сохранения проверяем новая ли заявка и если да, то назначаем менеджера
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->assignManager();
            }
            return true;
        }
        return false;
    }
}
