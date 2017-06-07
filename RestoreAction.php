<?php

namespace ereminmdev\yii2\deleted;

use Yii;
use yii\base\Action;


class RestoreAction extends Action
{
    /**
     * @var string|array route to redirect after restoring
     */
    public $returnUrl;


    public function run($id)
    {
        $id = Yii::$app->request->get('id', '');
        $ids = explode(',', $id);

        $restoredCount = 0;

        /** @var Deleted[] $models */
        $models = Deleted::find()->andWhere(['id' => $ids])->all();
        foreach ($models as $model) {
            if ($model->restoreModel()) {
                $model->delete();
                $restoredCount++;
            }
        }

        if ($restoredCount > 0) {
            Yii::$app->session->addFlash('success', Yii::t('app', 'The requested model(s) was successfully restored.'));
        } else {
            Yii::$app->session->addFlash('error', Yii::t('app', 'The requested model(s) could not be restored.'));
        }

        $returnUrl = Yii::$app->request->get('returnUrl', $this->returnUrl);
        $controller = $this->controller;
        return $returnUrl !== null ? $controller->redirect($returnUrl) : $controller->goBack();
    }
}
