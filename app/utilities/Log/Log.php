<?php

namespace App\utilities\Log;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

final class Log
{
    /**
     * @param string $name
     * @param string $message
     * @param int $level
     * @param string $dateFormat
     * @param string $fileName
     * @param string $output
     * @return void
     */
    public static function logMessage(
        string $name,
        string $message,
        int $level,
        string $dateFormat = "Y-m-d H:i:s",
        string $fileName = '',
        string $output = "%level_name% --> %datetime% --> %message% --> %context% %extra%\n"
    ): void
    {
        if ($name == '') {
            $name = __CLASS__;
        }

        // Создаем форматтер
        $formatter = new LineFormatter($output, $dateFormat);
        //если не указан путь в кач-ве аргумента,то по дефолту указываем /log/log-YYYY-MM-DD.txt
        if ($fileName == '') {
            $currentDay = date('Y-m-d');
            //Создаем директорию log,если ее нет
            if (!is_dir($_SERVER["DOCUMENT_ROOT"].'/log')) {
                mkdir($_SERVER["DOCUMENT_ROOT"].'/log');
            }
            $fileName = "/log-{$currentDay}.txt";
        }
        //полный путь
        $path = (php_sapi_name() !== 'cli')? $_SERVER["DOCUMENT_ROOT"].'/log'.$fileName : getenv('PWD').'/log'.$fileName;
        // Создаем обработчик с указанием пути для хранения логов
        $stream = new StreamHandler($path);

        $stream->setFormatter($formatter);

        // Привязываем его к объекту логгера
        $log = new Logger($name);
        $log->pushHandler($stream);

        //отправляем сообщение в лог
        self::log($log, $message, $level);


    }

    /**
     * @param Logger $logger
     * @param string $message
     * @param int $level
     * @return void
     */
    private static function log(Logger $logger,  string $message, int $level): void
    {
        $params = [];
        //дополнительные параметры сообщения
        if (php_sapi_name() !== 'cli') {
            $params['ip'] = $_SERVER['REMOTE_ADDR'];
            $params['agent'] = $_SERVER['HTTP_USER_AGENT'];
        }
        switch ($level) {
            case 0:
                // Информация для отладки
                $logger->debug($message, $params);
                break;
            case 1:
                //События. Примеры: логи пользователя,sql логи.
                $logger->info($message, $params);
                break;
            case 2:
                //Обычное, но значимое событие
                $logger->notice($message, $params);
                break;
            case 3:
                //Исключительные случаи, не являющиеся ошибками.
                //Примеры: использование устаревших API, неправильное использование API, нежелательные вещи,
                // которые не обязательно являются неправильными.
                $logger->warning($message, $params);
                break;
            case 4:
                //Ошибки рантайма, которые не требуют немедленных действий,
                // но должны логгироваться и отслеживаться
                $logger->error($message, $params);
                break;
            case 5:
                //Критические условия.
                // Пример: компонент приложения недоступен, неожиданное исключение.
                $logger->critical($message, $params);
                break;
            case 6:
                //Действия должны быть приняты немедленно.
                // Пример: весь веб-сайт недоступен, база данных недоступна и т. д.
                $logger->alert($message, $params);
                break;
            case 7:
                //Полный факап,вся система упала
                $logger->emergency($message, $params);
                break;
            default:
                $logger->debug($message, $params);
        }
    }

}