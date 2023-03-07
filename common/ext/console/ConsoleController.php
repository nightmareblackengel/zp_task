<?php
namespace common\ext\console;

use common\models\redis\CronDelayMsgRunner;
use yii\console\Controller;

class ConsoleController extends Controller
{
    // необходимо использовать, если запуски будут происходить с "рабочего" докера
    protected function checkIfCorrectDockerRun(): bool
    {
        try {
            CronDelayMsgRunner::getStorage()->connectionTimeout = 2;
            CronDelayMsgRunner::getStorage()->open();
        } catch (\Exception $ex) {
            return false;
        }

        return true;
    }

    protected function setMaxTimeAndMemory(?int $time = null, ?string $memory = null)
    {
        if (null !== $time) {
            set_time_limit($time);
        }
        if (null !== $memory) {
            ini_set('memory_limit', $memory);
        }

        return true;
    }
}
