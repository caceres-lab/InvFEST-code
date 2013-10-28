CREATE USER 'invfestdb-user'@'%' IDENTIFIED BY 'invfestdb-user';
REVOKE ALL PRIVILEGES ON `INVFEST-DB`.* FROM `invfestdb-user`@`%`;  
SELECT CONCAT("GRANT SELECT ON `INVFEST-DB`.", table_name, " TO `invfestdb-user`@`%`;")
FROM information_schema.TABLES
WHERE table_schema = "INVFEST-DB" AND (table_name <> "user" AND table_name <> "researchs_has_user" AND table_name <> "log_task");
FLUSH PRIVILEGES;


CREATE USER 'invfestdb-lab'@'%' IDENTIFIED BY 'InvFESTLab';
REVOKE ALL PRIVILEGES ON `INVFEST-DB`.* FROM `invfestdb-lab`@`%`;  
SELECT CONCAT("GRANT SELECT ON `INVFEST-DB`.", table_name, " TO `invfestdb-lab`@`%`;")
FROM information_schema.TABLES
WHERE table_schema = "INVFEST-DB" AND (table_name <> "user" AND table_name <> "researchs_has_user" AND table_name <> "log_task");
FLUSH PRIVILEGES;


GRANT USAGE ON `INVFEST-DB-PUBLIC`.* TO 'invfestdb-user'@'' WITH MAX_QUERIES_PER_HOUR 1000;
FLUSH PRIVILEGES;
