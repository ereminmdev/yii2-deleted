<?php

namespace ereminmdev\yii2\deleted;

use yii\base\Behavior;
use yii\db\ActiveRecord;


/**
 * DeletedBehavior store model to Deleted record after one will be deleted.
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

    protected $isDeleted = false;


    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    public function afterDelete()
    {
        if (!$this->isDeleted) {
            $this->isDeleted = Deleted::addDeletedModel($this->owner, $this->getComment()) !== false;
        }
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return is_callable($this->comment) ?
            call_user_func_array($this->comment, ['model' => $this->owner, 'behavior' => $this]) :
            $this->comment;
    }
}
