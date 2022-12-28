<?php
namespace frontend\ext;

use frontend\models\UserAuthIdentity;
use yii\base\InvalidRouteException;
use yii\base\Module;
use yii\web\Controller;
use Yii;

class AuthController extends Controller
{
    protected ?array $userArr = [];

    public function getCurrentUser()
    {
        if (!empty($this->userArr)) {
            return $this->userArr;
        }
        /** @var UserAuthIdentity $identity */
        $identity = Yii::$app->user->identity;
        if (empty($identity)) {
            return null;
        }

        $this->userArr = $identity->getUser();
        if (empty($this->userArr)) {
            return null;
        }

        return $this->userArr;
    }

    protected function hasAccess(): bool
    {
        $userArr = $this->getCurrentUser();

        return !empty($userArr);
    }

    public function runAction($id, $params = [])
    {
        $action = $this->createAction($id);
        if ($action === null) {
            throw new InvalidRouteException('Unable to resolve the request: ' . $this->getUniqueId() . '/' . $id);
        }

        Yii::debug('Route to run: ' . $action->getUniqueId(), __METHOD__);

        if (Yii::$app->requestedAction === null) {
            Yii::$app->requestedAction = $action;
        }

        $oldAction = $this->action;
        $this->action = $action;

        $modules = [];
        $runAction = true;

        // call beforeAction on modules
        foreach ($this->getModules() as $module) {
            if ($module->beforeAction($action)) {
                array_unshift($modules, $module);
            } else {
                $runAction = false;
                break;
            }
        }

        $result = null;

        if ($runAction && $this->beforeAction($action)) {
            if (!$this->hasAccess()) {
                $this->layout = 'main';
                return $this->render('/main/page403');
            }
            // run the action
            $result = $action->runWithParams($params);
            $result = $this->afterAction($action, $result);

            // call afterAction on modules
            foreach ($modules as $module) {
                /* @var $module Module */
                $result = $module->afterAction($action, $result);
            }
        }

        if ($oldAction !== null) {
            $this->action = $oldAction;
        }

        return $result;
    }

}
