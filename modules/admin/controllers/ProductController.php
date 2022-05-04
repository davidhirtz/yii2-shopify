<?php

namespace davidhirtz\yii2\shopify\modules\admin\controllers;

use davidhirtz\yii2\shopify\modules\admin\data\ProductActiveDataProvider;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * Class ProductController.
 * @package davidhirtz\yii2\shopify\modules\admin\controllers
 */
class ProductController extends Controller
{
    use ModuleTrait;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create', 'index', 'order', 'update', 'delete'],
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'order' => ['post'],
                    'upload' => ['post'],
                ],
            ],
        ]);
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $provider = new ProductActiveDataProvider([
        ]);

        return $this->render('index', [
            'provider' => $provider,
        ]);
    }
//
//    /**
//     * @param int $id
//     * @param int $type
//     * @return string|\yii\web\Response
//     */
//    public function actionCreate($id = null, $type = null)
//    {
//        $entry = new ProductForm;
//
//        $entry->parent_id = $id;
//        $entry->type = $type;
//
//        if ($entry->load(Yii::$app->getRequest()->post()) && $entry->insert()) {
//            $this->success(Yii::t('shopify', 'The entry was created.'));
//            return $this->redirect(['update', 'id' => $entry->id]);
//        }
//
//        /** @noinspection MissedViewInspection */
//        return $this->render('create', [
//            'entry' => $entry,
//        ]);
//    }
//
//    /**
//     * @param int $id
//     * @return string|\yii\web\Response
//     */
//    public function actionUpdate($id)
//    {
//        if (!$entry = ProductForm::findOne($id)) {
//            throw new NotFoundHttpException;
//        }
//
//        if ($entry->load(Yii::$app->getRequest()->post())) {
//
//            if ($entry->update()) {
//                $this->success(Yii::t('shopify', 'The entry was updated.'));
//            }
//
//            if (!$entry->hasErrors()) {
//                return $this->redirect(['index', 'id' => $entry->parent_id]);
//            }
//        }
//
//        /** @noinspection MissedViewInspection */
//        return $this->render('update', [
//            'entry' => $entry,
//        ]);
//    }
//
//    /**
//     * @param int $id
//     * @return string|\yii\web\Response
//     */
//    public function actionDelete($id)
//    {
//        if (!$entry = Product::findOne($id)) {
//            throw new NotFoundHttpException;
//        }
//
//        if ($entry->delete()) {
//            $this->success(Yii::t('shopify', 'The entry was deleted.'));
//            return $this->redirect(['index']);
//        }
//
//        $errors = $entry->getFirstErrors();
//        throw new ServerErrorHttpException(reset($errors));
//    }
//
//    /**
//     * @param int $id
//     */
//    public function actionOrder($id = null)
//    {
//        $entries = Product::find()->select(['id', 'position'])
//            ->filterWhere(['parent_id' => $id])
//            ->orderBy(['position' => SORT_ASC])
//            ->all();
//
//        Product::updatePosition($entries, array_flip(Yii::$app->getRequest()->post('entry')));
//    }
//
//    /**
//     * @return Sort
//     */
//    protected function getSort(): Sort
//    {
//        return new Sort([
//            'attributes' => [
//                'type' => [
//                    'asc' => ['type' => SORT_ASC, 'name' => SORT_ASC],
//                    'desc' => ['type' => SORT_DESC, 'name' => SORT_DESC],
//                ],
//                'name' => [
//                    'asc' => ['name' => SORT_ASC],
//                    'desc' => ['name' => SORT_DESC],
//                ],
//                'asset_count' => [
//                    'asc' => ['asset_count' => SORT_ASC, 'name' => SORT_ASC],
//                    'desc' => ['asset_count' => SORT_DESC, 'name' => SORT_ASC],
//                    'default' => SORT_DESC,
//                ],
//                'section_count' => [
//                    'asc' => ['section_count' => SORT_ASC, 'name' => SORT_ASC],
//                    'desc' => ['section_count' => SORT_DESC, 'name' => SORT_ASC],
//                    'default' => SORT_DESC,
//                ],
//                'publish_date' => [
//                    'asc' => ['publish_date' => SORT_ASC],
//                    'desc' => ['publish_date' => SORT_DESC],
//                    'default' => SORT_DESC,
//                ],
//                'updated_at' => [
//                    'asc' => ['updated_at' => SORT_ASC],
//                    'desc' => ['updated_at' => SORT_DESC],
//                    'default' => SORT_DESC,
//                ],
//            ],
//        ]);
//    }
//
//    /**
//     * @return ProductQuery
//     */
//    protected function getQuery()
//    {
//        return ProductForm::find()->replaceI18nAttributes();
//    }
}