# Test Application
In this application I'm going to describe a part of my  ideas about architecture and implementation of the service.

## Introduction
This service has two endpoints:
* `/event` with POST request method
* `/events` with GET request method

If you want to store data, you should send request with the structure:
* Request must be send to `/event` path
* Must be used POST HTTP method
* In a POST request you should store JSON in a `data` param (POST param)
* JSON must contains three fields as `country`, `event`, `date`
* `country` field must contains [code of country following a ISO-3166-1-alpha-2 standard](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2)
* `event` field must has one from these types: `click`, `view`, `play`
* `date` field must contains date with this structure `YYYY-nn-jj`

If you want to get top countries by event you should use `/events` path:
* Request must be send to `/events` path
* Must be used GET HTTP method
* In a GET request params you can set type of return data from these list: json, csv. For that you must add get parameter with a name `type` and value `json` or `csv`. For example: `/events?type=csv`
* Default type is JSON, if you don't ad param

## Requirements
I've tested with PHP 7.1 and MySQL 5.7 

## How To Use Guid
Install PHP and whatever web server with configured virtual hosts and MySQL (you could use docker and `docker-compose.yml` from this project).
After that write a connection info to in the `App` class in line with `PDO` (I didn't want to fix it, because it was late). 
When you configured it, you'll start use the application, and you will see that all requests to `/event` route are stored to the `events_raw` table.
But `/events` request doesn't return stored results, because it uses cache table. For fill that just execute from a console or web (from 127.0.0.1) of the `cron.php` file.
You can control how often cache would be updated just perform `cron.php`, if you want to refresh cache every 1 minute, just use CRON for it.

## Small Description Of The Implementation
In current implementation I have used just plain PHP (without any external libraries and frameworks) and MySQL.
I haven't added any logging and tests, but it's simple to add.
Current implementation contains two additional exceptions: `BadRequestMethodException` and `ValidationException`.
Also You can find three classes where was implemented all logic:
* `App` is main application class, it has all public methods for `index.php` and `cron.php`, also it contains routing for a controller and actions
* `Controller` is the only class with all actions and validations
* `Job` is class with logic which makes good performance and a little big harder instruction to use it

## Current Architecture
I haven't used any technologies except PHP and MySQL, because it should be the simplest, so MySQL used as a cache.
All request from `/events` route are uses cache table and should be fast enough (if not, we can start to use redis or memcached, for example).
Route `/event` is storing all events to the `events_raw` table, because this approach would be avoid issue with blocked tables/rows.
Cache should be updated by the `Job`, so you could update your cache as often as you want and simple control it.
All requests to the MySQL with blocking shouldn't affect clients or could affect with the smallest action.

## Other Possible Solutions
I could describe a big number of solutions for this task, but I'll do just small part of they.

### Solution Without Backgound Jobs
It's the same solution as current, but all code from job should be implemented in the `/events` action.
It could cache data for a time (for example 1 minute). This solutions is simpler than current, but I haven't used it?
The main goal was performance and as we can see some kind of users will have an issue when cache is regenerating.

### Solution With Update Instead Of RAW Data
So, it's the simplest way to implement. We just need to use `insert into ... on duplicate key update ...` and MySQL will make new events and increment count for this.
Why I haven't used it? Because this solutions is blocking other requests on an update time.
As we know, the main our goal is performance, so this solutions isn't good enough for us. 

## Other Technologies
Every of the solutions could use some additional technologies and improve performance, but it wasn't our goal.
So, it's the reason why I haven't used or described another technologies, but if you'll request me, I can do it.