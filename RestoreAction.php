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
        $ids = explode(',', $id);

        foreach (Deleted::find()->andWhere(['id' => $ids])->each() as $model) {
            /** @var Deleted $model */

            $restoredModel = $model->getDeletedModel();

            if ($restoredModel->validate() && $restoredModel->save()) {
                $model->delete();
                Yii::$app->session->addFlash('success', Yii::t('app', 'Model successfully restored: {comment}', ['comment' => $model->comment]));
            } elseif ($restoredModel->hasErrors()) {
                Yii::$app->session->addFlash('error', Yii::t('app', 'Model could not be restored: {error}', ['error' => var_export($restoredModel->getFirstErrors(), true)]));
            }
        }

        $returnUrl = Yii::$app->request->get('returnUrl', $this->returnUrl);
        $controller = $this->controller;
        return $returnUrl !== null ? $controller->redirect($returnUrl) : $controller->goBack();
    }
}
