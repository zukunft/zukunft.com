PREPARE db_setup_create_role FROM
'CREATE ROLE ?
    WITH LOGIN CREATEDB NOSUPERUSER NOCREATEROLE INHERIT NOREPLICATION
    CONNECTION LIMIT -1 PASSWORD ?';
