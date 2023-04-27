<?php
namespace frontend\ext;

use frontend\models\helpers\AjaxHelper;
use frontend\models\redis\UserAuthIdentity;
use Yii;
use yii\base\InvalidRouteException;
use yii\base\Module;
use yii\web\Controller;

class AuthController extends Controller
{
    protected ?array $userArr = [];
    protected $userId = null;

    public function beforeAction($action)
    {
        if ($action->id === 'load' && $action->controller->id === 'ajax') {
            $this->enableCsrfValidation = false;
        }
        // принудительная авторизация
        $this->userId = 1;
        $authIdentity = UserAuthIdentity::findIdentity($this->userId);
        Yii::$app->user->login($authIdentity, 60*60);
        //

        $befRes = parent::beforeAction($action);
        if (!$this->hasAccess()) {
            return $this->render('/main/page403');
        }

        return $befRes;
    }

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
        $this->userArr['concatName'] = $this->userArr['name'] . ' (' . $this->userArr['email'] . ')';

        return $this->userArr;
    }

    protected function hasAccess(): bool
    {
        $userArr = $this->getCurrentUser();

        return !empty($userArr);
    }

    // parent default behaviour
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
            if (empty($this->allowedUnAuthActions) || !in_array($this->action->id, $this->allowedUnAuthActions)) {
                if (!$this->hasAccess()) {
                    $this->layout = 'main';
                    return $this->redirect('/main/page403');
                }
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

    protected function ajaxErr($message)
    {
        return [
            'result' => AjaxHelper::AJAX_RESPONSE_ERR,
            'message' => $message,
        ];
    }
}
