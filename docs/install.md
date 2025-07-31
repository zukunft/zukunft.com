Installation
-------------

At the moment only the [development installation](../README.md) is recommended.

Fix development installation:
-----------------------------

If you get errors or the message "Only admin users are allowed to reset the database" and the database does not you contain any relevant data the best is probably to recreate the database:

   ```bash
   sudo -u postgres psql -d postgres -U postgres -c "DROP DATABASE zukunft;"
   ```
   ```bash
   sudo -u postgres psql -d postgres -U postgres -c "CREATE USER zukunft WITH PASSWORD 'zukunft';"
   ```
   ```bash
   sudo -u postgres psql -d postgres -U postgres -c "CREATE DATABASE zukunft WITH OWNER zukunft ENCODING 'UTF8';"
   ```
   ```bash
   php /var/www/html/test/reset_db.php
   ```

If you get the message that the database cannot be accessed one solution could be to reassign the owner:
   ```bash
   sudo -u postgres psql -d postgres -U postgres -c "ALTER DATABASE zukunft OWNER TO zukunft ENCODING 'UTF8';"
   ```
   ```bash
   php /var/www/html/test/reset_db.php
   ```

To run all build in tests start from bash
   ```bash
   php /var/www/html/test/test.php
   ```

To check open the frontpage again at [localhost/http/view.php]()


Production installation (to be reviewed):
-----------------------------------------

To install this version 0.0.3 use a LAPP or (LAMP for MySQL) server (https://wiki.debian.org/LaMp) and
1) copy all files to the www root path (e.g. /var/www/html/)
2) copy all files of bootstrap 4.1.3 or higher to /var/www/html/external_lib/bootstrap/4.1.3/
3) copy all files of fontawesome to /var/www/html/external_lib/fontawesome/
4) create a user "zukunft_db_root" in Postgres (or MySQL) and remember the password
5) change the password "xxx" in .env with the password used in 2)
6) run the script "src/test/reset_db.php" local on the server and if the result is 0 test errors 0 internal errors delete the script
7) test if the installation is running fine by calling http://yourserver.com/test/test.php
   (until this version 0.0.3 is finished try to run test.php in a terminal in case of errors)

Docker Installation
-------------------

Recommended only for dedicated pod servers due to potential security issues ( https://wiki.debian.org/Docker )

For a quick and easy setup, you can use Docker to run the application. This method ensures consistent environments and easy deployment.

Prerequisites:
- Docker Engine installed on your system
- Docker Compose installed on your system

Steps:
1. Clone the repository:
   ```bash
   git clone -b release https://github.com/zukunft/zukunft.com.git
   cd zukunft.com
   ```

2. (Optional) Create a `.env` file to customize app credentials:
   You can specify app and adminer custom ports if 8080 and 8081 are allocated in
   your system
   ```env
   APP_SERVICE_PORT=8080
   ADMINER_SERVICE_PORT=8081
   PGSQL_HOST=db
   PGSQL_PORT=5432
   PGSQL_DATABASE=zukunft
   PGSQL_USERNAME=zukunft
   PGSQL_PASSWORD=your_secure_password
   PGSQL_ADMIN_USERNAME=postgres
   PGSQL_ADMIN_PASSWORD=admin_password
   ```

3. Start the application:
   ```bash
   docker compose up -d
   ```

4. Initialize the database (first time only):
   ```bash
   docker compose exec app php /var/www/html/test/reset_db.php
   ```

5. Access the application:
    - Main application: http://localhost
    - Test page: http://localhost/test/test.php

The Docker setup includes:
- PHP 8.2 with Apache
- PostgreSQL 14
- All required PHP extensions (pgsql, yaml, curl)
- Automatic database configuration
- Volume persistence for database data

To stop the application:
```bash
docker compose down
```

To view logs:
```bash
docker compose logs -f
```

Target installation
-------------------

In the final version the installation on debian should be

sudo apt-get install zukunftcom

with the options

-p for python (php if not set)
-j for java / jvm based version
-c for C++ / rust based version

After "zukunftcom start" a message should be shown including the pod name. Every critical event,
such as the connection to other pods, should be shown in the console
and beginning with an increasing minute based interval,
but at least once a day a status message should be shown with the system usage and a summery if the usage.


Pod Installation
----------------

To install a pod on a server a solution is to use the
docker installation that will be created (see issue #134)

