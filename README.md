# yii2-deleted

Model, behavior and action to store/restore active records.

## Install

``composer require ereminmdev/yii2-deleted``

and run migrations:

``yii migrate --migrationPath=@ereminmdev/deleted/migrations --interactive=0``

## Use

1) Add behavior to active record model:

```
public function behaviors()
    {
        return [
            DeletedBehavior::className(),
        ];
    }
```

or with custom comment:

```
public function behaviors()
    {
        return [
            [
                'class' => DeletedBehavior::className(),
                'comment' => function (self $model, DeletedBehavior $behavior) {
                    return 'Модель «' . $model->title . '»';
                },
            ],
        ];
    }
```

2) Add action to site controller:

```
public function actions()
{
    return [
        'deleted-restore' => [
            'class' => 'ereminmdev\yii2\deleted\RestoreAction',
        ],
    ];
}
```

## Restore

Create url to restore action and specify `id` parameter of needed Deleted model:

```
echo Url::toRoute(['/site/deleted-restore', 'id'=>MODEL_ID]);
```

## Cron clean word

For example, clear Deleted models older than 1 month: 

```
Deleted::deleteAll(['<', 'created_at', strtotime('-1 month')]);
```

If EVENT_BEFORE_DELETE or EVENT_AFTER_DELETE events needed: 

```
$query = Deleted::find()->andWhere(['<', 'created_at', strtotime('-1 month')]);
foreach ($query->each() as $model) $model->delete();
```
