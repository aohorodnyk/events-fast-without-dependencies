<?php
/**
 * Created by PhpStorm.
 * User: aohorodnyk
 * Date: 9/17/18
 * Time: 23:11
 */

class Controller
{
    /**
     * @var \PDO
     */
    private $conn;

    public function __construct(\PDO $connection)
    {
        $this->conn = $connection;
    }

    /**
     * Put Event action
     *
     * @throws \Exceptions\ValidationException
     * @throws \Exception
     */
    public function putEvent()
    {
        $data = $this->parseAndValidateRequest();

        $sql = <<<SQL
INSERT INTO `events_raw`(`event_date`, `country_code`, `event_name`)
VALUES (:date, :country, :event);
SQL;

        $statement = $this->conn->prepare($sql);
        $statement->bindParam(':date', $data['date']);
        $statement->bindParam(':country', $data['country']);
        $statement->bindParam(':event', $data['event']);

        $result = $statement->execute();

        if ($result === false) {
            throw new \Exception();
        }
        echo json_encode(['status'=>'ok']);
    }

    /**
     * @throws \Exceptions\ValidationException
     */
    public function getEvent()
    {
        if (!empty($_GET['type']) && !in_array($_GET['type'], ['json', 'csv'])) {
            $result = ['status' => 'error', 'errors' => ["Parameter 'type' must contains only 'csv' or 'json'"]];
            throw new \Exceptions\ValidationException(json_encode($result));
        }
        $type = !empty($_GET['type']) ? $_GET['type'] : 'json';

        $name = \App::CACHE_NAME;
        $sql = <<<SQL
select `value`
from `cache`
where `name` = '{$name}'
SQL;
        $statement = $this->conn->query($sql);
        $value = $statement->fetchColumn();

        if (empty($value)) {
            $this->showResult($type, []);
        } else {
            $data = json_decode($value, true);
            $this->showResult($type, $data);
        }
    }

    /**
     * Validate input in the Put Event
     *
     * @return array
     * @throws \Exceptions\ValidationException
     */
    private function parseAndValidateRequest(): array
    {
        $errors = [];
        $result = [];
        if (empty($_POST['data'])) {
            $errors[] = 'Data cannot be empty';
        }
        $tmpData = json_decode($_POST['data'], true);
        if ($tmpData === null) {
            $errors[] = 'JSON request have to be valid';
        }

        if (empty($tmpData['country']) || mb_strlen($tmpData['country']) !== 2) {
            $errors[] = 'Country have to contains two letters';
        }
        $result['country'] = $tmpData['country'];

        if (empty($tmpData['event']) || !in_array($tmpData['event'], \App::EVENT_LIST)) {
            $errors[] = 'Event can be any of: click, play, view';
        }
        $result['event'] = $tmpData['event'];

        if (!$this->isDate($tmpData['date'])) {
            $errors[] = 'Date is not valid';
        }

        if (!empty($errors)) {
            $result = ['status' => 'error', 'errors' => $errors];
            throw new \Exceptions\ValidationException(json_encode($result));
        }
        $result['date'] = $tmpData['date'];

        return $result;
    }

    /**
     * Is Date Valid
     *
     * @param string|null $date
     * @return bool
     */
    private function isDate($date): bool
    {
        $matches = [];
        $pattern = '/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$/';
        if (empty($date)
            || !preg_match($pattern, $date, $matches)
            || !checkdate($matches[3], $matches[2], $matches[1])
        ) {
            return false;
        }
        return true;
    }

    private function showResult(string $type, array $data): void
    {
        if ($type === 'json')
        {
            header('Content-Type: application/json');

            $jsonResult = ['status' => 'ok', 'data' => $data];
            echo json_encode($jsonResult);
        } else {
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename=top.csv");
            header("Pragma: no-cache");
            header("Expires: 0");

            $f = fopen("php://output", 'w');
            fputcsv($f, ['date', 'country', 'name', 'count']);
            foreach ($data as $item) {
                fputcsv($f, $item);
            }
            fclose($f);
        }
    }
}