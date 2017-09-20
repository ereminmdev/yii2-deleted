<?php

namespace ereminmdev\yii2\deleted;

use common\models\User;
use ereminmdev\yii2\crud\components\Crud;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\StringHelper;


/**
 * This is the model class for table "{{%deleted}}".
 *
 * @property integer $id
 * @property integer $type
 * @property string $comment
 * @property string $class_name
 * @property string $model_data
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 *
 * @property ActiveRecord $deletedModel
 */
class Deleted extends ActiveRecord
{
    const TYPE_DEFAULT = 0;
    /**
     * @var string|array route to restore action
     */
    public static $restoreAction = ['/site/deleted-restore'];


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%deleted}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            BlameableBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['class_name', 'comment'], 'string', 'max' => 255],
            [['model_data'], 'string'],

            ['type', 'in', 'range' => array_keys(self::types())],
            ['type', 'default', 'value' => self::TYPE_DEFAULT],

            [['class_name', 'model_data'], 'required'],

            // for GridView filter
            [['created_by'], 'integer'],
            [['created_at'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'comment' => 'Комментарий',
            'type' => 'Тип',
            'class_name' => 'Модель',
            'model_data' => 'Данные',
            'created_at' => 'Дата удаления',
            'created_by' => 'Кто удалил',
        ];
    }

    public static function types()
    {
        return [
            self::TYPE_DEFAULT => '',
        ];
    }

    /**
     * @param ActiveRecord $model
     * @param string $comment
     * @return self|false
     */
    public static function addDeletedModel($model, $comment = '')
    {
        $deleted = new static();
        $deleted->class_name = $model::className();
        $deleted->model_data = Json::encode($model->getAttributes());
        $deleted->comment = StringHelper::truncate($comment, 255);
        return $deleted->save() ? $deleted : false;
    }

    /**
     * @return ActiveRecord
     */
    public function getDeletedModel()
    {
        $modelData = Json::decode($this->model_data);

        /** @var ActiveRecord $model */
        $model = new $this->class_name;
        $model->loadDefaultValues();
        $model->setAttributes($modelData, false);
        return $model;
    }

    /**
     * @return ActiveRecord|false
     */
    public function restoreModel()
    {
        $deletedModel = $this->getDeletedModel();
        return $deletedModel->save() ? $deletedModel : false;
    }

    /**
     * Get configuration for ereminmdev\yii2\crud\components\Crud module
     * @return array
     */
    public static function crudConfig()
    {
        return [
            'title' => 'Удаленные',
            'dataProvider' => function ($dataProvider) {
                $dataProvider->sort = [
                    'defaultOrder' => [
                        'created_at' => SORT_DESC,
                    ],
                ];
            },
            'gridColumnsOnly' => ['created_at', 'created_by', 'type', 'class_name', 'comment'],
            'columnsSchema' => [
                'type' => [
                    'type' => 'array',
                    'itemList' => function () {
                        return static::types();
                    },
                ],
                'created_by' => [
                    'type' => 'array',
                    'itemList' => function () {
                        return User::find()
                            ->select(['username', 'id'])
                            ->orderBy(['username' => SORT_ASC])
                            ->indexBy('id')
                            ->asArray()
                            ->column();
                    },
                ],
            ],
            'gridActionsTemplate' => "{restore}\n{--}\n{update}",
            'gridActions' => [
                '{restore}' => function (self $model, $key, $crud) {
                    return [
                        'label' => 'Восстановить',
                        'url' => ArrayHelper::merge(self::getRestoreAction($crud), ['id' => $model->id]),
                        'linkOptions' => [
                            'data-confirm' => Yii::t('app', 'Are you sure you want to restore this item?'),
                        ],
                    ];
                },
            ],
            'gridCheckedActionsTemplate' => "{restore}\n{--}\n{setvals}\n{--}\n{duplicate}\n{--}\n{export}\n{--}\n{delete}",
            'gridCheckedActions' => [
                '{restore}' => function ($crud) {
                    return [
                        'label' => 'Восстановить',
                        'url' => self::getRestoreAction($crud),
                        'linkOptions' => [
                            'data-confirm' => Yii::t('app', 'Are you sure you want to restore this item?'),
                        ],
                    ];
                },
            ],
        ];
    }

    /**
     * @param Crud $crud
     * @return array route to restore action
     */
    public static function getRestoreAction(Crud $crud)
    {
        $restoreAction = !is_array(self::$restoreAction) ? [self::$restoreAction] : self::$restoreAction;
        $restoreAction['returnUrl'] = $crud->context->getReturnUrl();
        return $restoreAction;
    }
}
