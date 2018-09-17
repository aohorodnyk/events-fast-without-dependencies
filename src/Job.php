<?php

class Job
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
     * @throws \Exception
     */
    public function run()
    {
        $sql =<<<SQL
INSERT INTO events(event_date, country_code, event_name, `count`)
SELECT event_date, country_code, event_name, count(*) as `count`
FROM events_raw er
WHERE event_date >= DATE(NOW()) - INTERVAL 1 DAY
GROUP BY event_date, country_code, event_name
ON DUPLICATE KEY UPDATE `count` = `count`
SQL;
        $statement = $this->conn->query($sql);
        $result = $statement->execute();

        if ($result === false) {
            throw new \Exception();
        }

        $sql =<<<SQL
SELECT e.event_date, e.country_code, e.event_name, e.count
FROM events e,
     (SELECT country_code, SUM(`count`) as c FROM events GROUP BY `country_code` ORDER BY c DESC LIMIT 5) tc
WHERE event_date >= DATE(NOW()) - INTERVAL 7 DAY AND e.country_code IN (tc.country_code)
ORDER BY event_date ASC, `count` DESC, e.country_code ASC
SQL;
        $statement = $this->conn->query($sql);
        $result = $statement->fetchAll();

        if ($result === false) {
            throw new \Exception();
        }

        $sql =<<<SQL
INSERT INTO cache(`name`, `value`)
VALUES (:name, :value) ON DUPLICATE KEY UPDATE `value` = :value;
SQL;
        $name = \App::CACHE_NAME;
        $statement = $this->conn->prepare($sql);
        $statement->bindParam(':name', $name);
        $statement->bindParam(':value', json_encode($result));
        $result = $statement->execute();
        if ($result === false) {
            throw new \Exception();
        }
    }
}