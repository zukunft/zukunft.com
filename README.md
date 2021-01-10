zukunft.com 

Calculating with RDF data.


To install this version 0.0.1 use a LAMP server (https://wiki.debian.org/LaMp) and
1) copy all files to the www root path (e.g. /var/www/html/)
2) copy all files of bootstrap 4.1.3 or higer to /var/www/html/lib_external/bootstrap/4.1.3/
3) copy all files of fontawesome to /var/www/html/lib_external/fontawesome/
4) create a user "zukunft_db_root" in MySQL and remember the password
5) execute the script "zukunft_structure.sql" in MySQL to create the database zukunft_structure
6) execute the script "zukunft_init_data.sql" in MySQL to fill the database with the code linked database rows
7) change the password "xxx" in db_link/zu_lib_sql_link.php with the password used in 2)
8) test if the installation is running fine by calling http://yourserver.com/http/test.php
