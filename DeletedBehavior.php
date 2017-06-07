<?php

namespace ereminmdev\yii2\deleted;

use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;


/**
 * DeletedBehavior store model to Deleted record before one will be deleted.
 *
 * @property ActiveRecord $owner
 */
class DeletedBehavior extends Behavior
{
    /**
     * @var string|callable
     * If callable, function(self $model, DeletedBehavior $behavior) must return comment as string.
     */
    public $comment = '';


    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
        ];
    }

    public function beforeDelete(ModelEvent $event)
    {
        Deleted::addModel($this->owner, $this->getComment());
    }

    public function getComment()
    {
        return is_callable($this->comment) ?
            call_user_func_array($this->comment, ['model' => $this->owner, 'behavior' => $this]) :
            $this->comment;
    }
}
