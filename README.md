zukunft.com 

Calculating with RDF data.

The roadmap and issue handling is at the moment at https://zukunft.com/mantisbt . 


To install this version 0.0.1 use a LAMP server (https://wiki.debian.org/LaMp) and
1) copy all files to the www root path (e.g. /var/www/html/)
2) create a user "zukunft_db_root" in MySQL and remember the password
3) execute the script "zukunft_structure.sql" in MySQL to create the database zukunft_structure
4) execute the script "zukunft_init_data.sql" in MySQL to fill the database with the code linked database rows
5) change the password "xxx" in db_link/zu_lib_sql_link.php with the password used in 2)
6) test if the installation is running fine by calling http://yourserver.com/test.php


