<?php

namespace App\Services;

use App\Models\YourModel;
use Exception;

class YourService
{
    // API метод передачи данных из .csv файла в таблицу, задание 1
    public function insertDataFromCSV($file)
    {
        $handle = fopen($file, "r");

        if ($handle !== false) {
            $insertQuery = [];
            try {
                $db->beginTransaction();

                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $splitData = $this->splitRow($data);
                    $insertQuery[] = [
                        'number' => $splitData['number'],
                        'name' => $splitData['name']
                    ];
                }
                
                YourModel::insert($insertQuery);
                $db->commit();
                fclose($handle);

                return ['success' => true];
            } catch (Exception $e) {
                $db->rollBack();
                fclose($handle);

                return ['success' => false, 'error_message' => $e->getMessage()];
            }
        }

        return ['success' => false];
    }

    // API метод рассылки данных из таблицы, задание 2
    public function sendDataFromTable()
    {
        $lastProcessedNumber = $this->loadLastProcessedNumber();
        $errorOccurred = false;

        try {
            $db->beginTransaction();
            $rows = YourModel::where('number', '>', $lastProcessedNumber)->get();

            foreach ($rows as $row) {
                $result = $this->sendMethod($row->number, $row->name);
                if ($result === false) {
                    $errorOccurred = true;
                    $lastProcessedNumber = $row->number;
                    break;
                }
            }

            if ($errorOccurred) {
                $db->rollBack();
                return ['success' => false, 'last_processed_number' => $lastProcessedNumber];
            }
            
            $db->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'error_message' => $e->getMessage()];
        }
    }

    // Метод сохранения последнего обработанного номера в файл
    private function saveLastProcessedNumber($number)
    {
        file_put_contents('last_processed_number.txt', $number);
    }

    // Метод загрузки последнего обработанного номера из файла
    private function loadLastProcessedNumber()
    {
        if (file_exists('last_processed_number.txt')) {
            return file_get_contents('last_processed_number.txt');
        }

        return '';
    }

    // Метод разбивки строки с разделителем ',' на две части
    private function splitRow($str)
    {
        $parts = explode(',', $str);
        $number = $parts[0] ?? '';
        $name = $parts[1] ?? '';

        return ['number' => $number, 'name' => $name];
    }

    // Метод отправки
    private function sendMethod($number, $name)
    {
        // код отправки

        return true;
    }
}

?>