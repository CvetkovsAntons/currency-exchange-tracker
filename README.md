# Currency Exchange Tracker

---

### **Table of contents**


- [Introduction](#introduction)
- [How to set up a project](#1-how-to-set-up-a-project)
- [Available commands](#2-available-commands)
    - [Docker commands](#docker-commands)
    - [Application commands](#application-commands)
    - [Worker commands](#worker-commands)
- [Project structure](#3-project-structure)
- [API Usage Example](#4-api-usage-example)
- [Running tests](#5-running-tests)
- [Troubleshooting / FAQ](#6-troubleshooting--faq)
- [License / Author / Credits](#7-license--author--credits)

---

### **Introduction**

The project goal is to create a backend Symfony application that will 
provide possibility to track and manage currency exchange rates.

Application provides tools for:
- Persisting exchange rates between currency pairs (e.g. USD -> EUR).
- Storing historical exchange rate data.
- Managing tracked currency pairs via console commands.
- Updating exchange rates automatically every minute.
- Exposing a JSON API to retrieve exchange rate information (based on stored data) with date-time precision.

Application is integrated with a free third-party API: **FreeCurrencyAPI** (https://freecurrencyapi.com/).

---

### 1. How to set up a project
1. Install `Docker` (https://docs.docker.com/engine/install/)
2. Clone repository
    ```shell
    git clone git@github.com:CvetkovsAntons/currency-exchange-tracker.git
    cd currency-exchange-tracker
    ```
3. Clone `.env.sample` to `.env`
    ```shell
    cp .env.sample .env
    ```
4. Configure environment variables in `.env` file. `CURRENCY_API_KEY` is required
    ```.dotenv
    CURRENCY_API_KEY=your_currency_api_key_here
    ```
5. Run docker compose
   - Using Make (recommended):
       ```shell
       make start
       ```
   - Without Make:
       ```shell
       docker compose up -d
       ```
6. Check if the app is running at http://localhost:8080/health-check

---

### 2. **Available commands**

## **Important**
It is recommended to run all commands from inside the container!

But, if you want to be able to run them outside the container, 
then you'll need to change database URL inside the `.env` file:
- From:
    ```dotenv
    DATABASE_URL="pgsql://${DATABASE_USER}:${DATABASE_PASSWORD}@postgres:5432/${DATABASE}?serverVersion=16&charset=utf8"
    ```
- To:
    ```dotenv
    DATABASE_URL="pgsql://${DATABASE_USER}:${DATABASE_PASSWORD}@127.0.0.1:5432/${DATABASE}?serverVersion=16&charset=utf8"
    ```
  
---

## **Docker commands**
### Start project
- Using Make (recommended):
    ```shell
    make start
    ```
- Without Make:
    ```shell
    docker compose up -d
    ```
### Stop project
- Using Make (recommended):
    ```shell
    make stop
    ```
- Without Make:
    ```shell
    docker compose stop
    ```
### Restart project
- Using Make (recommended):
    ```shell
    make restart
    ```
- Without Make:
    ```shell
    docker compose restart
    ```
  
---

## **Application commands**
### Open application container terminal
- Using Make (recommended):
    ```shell
    make app-shell
    ```
- Without Make:
    ```shell
    docker exec -it currency-exchange-app bash
    ```
### Run application tests
- Using Make (recommended):
    ```shell
    make app-tests-run
    ```
- Without Make:
    ```shell
    docker exec -it currency-exchange-app php bin/phpunit
    ```
### Add exchange rate tracking 
- Using Make (recommended):
    ```shell
    make app-exchange-rate-sync
    ```
- Without Make:
    ```shell
    docker exec -it currency-exchange-app php bin/console app:exchange-rate:sync
    ```
### Remove exchange rate tracking
- Using Make (recommended):
    ```shell
    make app-exchange-rate-remove
    ```
- Without Make:
    ```shell
    docker exec -it currency-exchange-app php bin/console app:exchange-rate:stop-tracking
    ```
### List exchange rates in real-time
- Using Make (recommended):
    ```shell
    make app-exchange-rate-list
    ```
- Without Make:
    ```shell
    docker exec -it currency-exchange-app php bin/console app:exchange-rate:list
    ```
### List all saved currencies
- Using Make (recommended):
    ```shell
    make app-currency-list
    ```
- Without Make:
    ```shell
    docker exec -it currency-exchange-app php bin/console app:currency:list
    ```
### List of historical exchange rates
- Using Make (recommended):
    ```shell
    make app-history-list
    ```
- Without Make:
    ```shell
    docker exec -it currency-exchange-app php bin/console app:exchange-rate-history:list
    ```

---  

## **Worker commands**
### Open worker container terminal
- Using Make (recommended):
    ```shell
    make worker-shell
    ```
- Without Make:
    ```shell
    docker exec -it currency-exchange-worker bash
    ```
### Start workers
- Using Make (recommended):
    ```shell
    make worker-start
    ```
- Without Make:
    ```shell
    docker compose up -d worker
    ```
### Stop workers
After command successful execution worker container will be stopped
- Using Make (recommended):
    ```shell
    make worker-stop
    ```
- Without Make:
    ```shell
    docker exec -it currency-exchange-worker php bin/console messenger:stop-workers
    ```
### Restart workers
After command successful execution worker container will be stopped
- Using Make (recommended):
    ```shell
    make worker-stop
    ```
- Without Make:
    ```shell
    docker compose restart worker
    ```
  
---

### 3. **Project structure**
- `src/`: Main application logic
  - `Command/`: Symfony console commands (e.g. currency sync).
  - `Controller/`: Application controllers (e.g. JSON API endpoints).
  - `DataFixtures/`: (Optional) Doctrine fixtures for preloading test or demo data.
  - `Dto/`: Data Transfer Objects used for input/output mapping.
  - `Entity/`: Doctrine entity definitions representing database tables.
  - `Enum/`: Enumerations for strict value control (e.g. argument types).
  - `Exception/`: Custom exception classes for domain-specific error handling.
  - `Factory/`: Entity creation factories.
  - `Provider/`: Classes responsible for fetching data from external sources (e.g. FreeCurrencyAPI).
  - `Repository/`: Custom Doctrine repository classes for database access.
  - `Scheduler/`: Symfony Scheduler tasks.
  - `Service/`: Application and domain service logic.
  - `Trait/`: Shared reusable traits across services.
  - `Kernel.php`: The application kernel (entry point for Symfony).
  - `Schedule.php`: Registers tasks and periodic execution logic.
- `tests/`: Unit and integration tests
  - `Command/`: Tests for Symfony console commands.
  - `Controller/`: Tests for HTTP endpoints.
  - `Factory/`: Test for application factories.
  - `Internal/`: Internal utilities and helpers for testing.
  - `Provider/`: Tests for external API integrations or mock services.
  - `Repository/`: Tests for custom Doctrine repository methods.
  - `Service/`: Tests for application service layer logic.
  - `bootstrap.php`: PHPUnit bootstrap file for test setup
- `docker/`: Docker configuration (Dockerfiles, Nginx config, etc.)
    - `app/`: Container's Dockerfile and entrypoint.
    - `worker/`: Container's Dockerfile and entrypoint.
    - `nginx/`: Nginx configuration file.
- `public/`: Public entry point (used by Nginx)
- `config/`: Symfony configuration
- `migrations/`: Doctrine database migrations

---

### 4. **API Usage Example**

## GET: `/health-check`
### Description
Returns a simple status response to confirm that the application is running and able to serve requests.

### Example call
```shell
  curl "localhost:8080/health-check"
```
### Example response
```json
{
    "status": "ok"
}
```

---

## GET: `/exchange-rate`
### Description
Returns the most recent exchange rate saved in the database for the specified currency pair closest to the given date.

### Query parameters
- `from`: Source currency code (e.g. USD)
- `to`: Target currency code (e.g. EUR)
- `datetime`: Optional ISO 8601 datetime for historical rate (e.g. 2025-06-30 12:00:00)

### Example call
```shell
  curl "localhost:8080/exchange-rate?from=EUR&to=USD&datetime=2025-06-30 12:00:00"
```
### Example response
```json
{
    "from": "EUR",
    "to": "USD",
    "rate": "1.1729653007",
    "datetime": "2025-06-30 12:00:00"
}
```

---

### 5. **Running tests**
All tests use a separate test database (`--env=test`). This ensures production data is not affected.

To run tests:
- Using Make (recommended):
    ```shell
    make app-tests-run
    ```
- Without Make:
    ```shell
    docker exec -it currency-exchange-app php bin/phpunit
    ```

---

### 6. **Troubleshooting / FAQ**

Anticipate common issues, such as:

- **Vendor folder is empty in container but not on host:**  
  Make sure you install Composer dependencies _inside the container_ or mount them correctly.

- **Worker not starting after `stop` command:**  
  Run `docker compose up -d worker` to restart the worker container.

- **Database errors on startup:**  
  Ensure `.env` is properly configured and PostgreSQL is accessible at `postgres:5432`.
---

### 7. **License / Author / Credits**

## Author

Created by Antons Cvetkovs - [GitHub Profile](https://github.com/CvetkovsAntons)

## License

This project was developed as part of a technical assessment and is intended for demonstration purposes only.
Use or redistribution without permission is not allowed.
