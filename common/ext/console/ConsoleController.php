<?php
namespace common\ext\console;

use console\models\redis\CronCheckerForRunStorage;
use DateTime;
use yii\console\Controller;

class ConsoleController extends Controller
{
    // необходимо использовать, если запуски будут происходить с "рабочего" докера
    protected function checkIfCorrectDockerRun(): bool
    {
        try {
            CronCheckerForRunStorage::getStorage()->connectionTimeout = 2;
            CronCheckerForRunStorage::getStorage()->open();
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

    protected function print_time(int $showLog = 1)
    {
        //        if (empty($showLog)) {
        return;
        //        }
        $now = DateTime::createFromFormat('U.u', microtime(true));
        echo PHP_EOL, $now->format("m-d-Y H:i:s.u");
    }
}
