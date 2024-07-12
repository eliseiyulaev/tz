<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

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
                'value' => new Expression('CONVERT_TZ(NOW(), @@session.time_zone, "+03:00")'),
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
            ['duplicate_id', 'integer'],
            ['duplicate_id', 'exist', 'targetClass' => self::class, 'targetAttribute' => 'id'],
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
     
    public function findDuplicate()
    {
        $timeNow = (new \DateTime())->format('Y-m-d H:i:s');
        $thirtyDaysAgo = (new \DateTime($timeNow))->modify('-30 days')->format('Y-m-d H:i:s');
        $query = self::find()
            ->where(['or', ['email' => $this->email], ['phone' => $this->phone]])
            ->andWhere(['<', 'created_at', $timeNow])
            ->andWhere(['>', 'created_at', $thirtyDaysAgo])
            ->orderBy(['created_at' => SORT_DESC]);

        return $query->one();
    }

    public function getManager()
    {
        return $this->hasOne(Manager::class, ['id' => 'manager_id']);
    }

    public function assignManager()
    {
        $duplicate = self::findOne($this->duplicate_id);
        if ($duplicate && $duplicate->manager && $duplicate->manager->is_works) {
            $this->manager_id = $duplicate->manager_id;
        } else {
            $managerWithMinRequests = Manager::getManagerWithMinRequests();
            if ($managerWithMinRequests !== null) {
                $this->manager_id = $managerWithMinRequests->id;
            }
        }
    }
}
