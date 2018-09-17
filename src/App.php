<?php

class App
{
    const EVENT_LIST = ['view', 'play', 'click'];
    const CACHE_NAME = 'get_cache';

    /**
     * @var PDO
     */
    private $conn;

    /**
     * @var Job
     */
    private $job;

    /**
     * @var Controller
     */
    private $controller;

    public function __construct()
    {
        $this->conn = new PDO('mysql:dbname=test;host=127.0.0.1', 'test', 'test');
        $this->job = new Job($this->conn);
        $this->controller = new Controller($this->conn);
    }

    /**
     * @throws \Exceptions\BadRequestMethodException
     * @throws \Exceptions\ValidationException
     */
    public function run()
    {
        switch ($_SERVER['PATH_INFO']) {
            case '/event':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $this->controller->putEvent();
                } else {
                    throw new \Exceptions\BadRequestMethodException('This path supports just POST method');
                }
                break;
            case '/events':
                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    $this->controller->getEvent();
                } else {
                    throw new \Exceptions\BadRequestMethodException('This path supports just GET method');
                }

        }
    }

    /**
     * @throws Exception
     */
    public function runJob()
    {
        $this->job->run();
    }
}