-- Comodojo SQL Dump
-- Comodojo Spare Parts
-- version __CURRENT_VERSION__
-- http://www.comodojo.net
--
-- 2012 comodojo.org (info@comodojo.org)
--
--
-- This is a sql dump you can use to create main database
-- and statistics table (the only one that simpleDataRestDispatcher
-- needs in package version)
--
-- Here the table declaration
--

CREATE DATABASE comodojo_services;
USE comodojo_services;

-- If you want also to create user comodojo and grant all privileges on comodojo database:
CREATE USER 'comodojo'@'localhost' IDENTIFIED BY 'password';
GRANT ALL ON comodojo_services.* to 'comodojo'@'localhost' IDENTIFIED BY 'password';

--
-- Statistics table
--

DROP TABLE IF EXISTS comodojo_statistics;

CREATE TABLE comodojo_statistics (
id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
timestamp INTEGER(64) NOT NULL,
service VARCHAR(32) NOT NULL,
address VARCHAR(32) NOT NULL,
userAgent TEXT NOT NULL,
PRIMARY KEY (id)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- Example table used by "example_database_based_service" service
--

CREATE TABLE comodojo_example (
id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
timestamp INTEGER(64) NOT NULL,
top INTEGER NOT NULL,
middle INTEGER NOT NULL,
bottom INTEGER NOT NULL,
PRIMARY KEY (id)
) ENGINE=MyISAM;

--
-- Example values for example table :)
--

INSERT INTO comodojo_example VALUES (null,"957024000",10,243,50);
INSERT INTO comodojo_example VALUES (null,"988560000",20,343,342);
INSERT INTO comodojo_example VALUES (null,"1020096000",30,154,543);
INSERT INTO comodojo_example VALUES (null,"1051632000",820,244,30);
INSERT INTO comodojo_example VALUES (null,"1083254400",145,100,987);
INSERT INTO comodojo_example VALUES (null,"1114790400",643,433,324);
INSERT INTO comodojo_example VALUES (null,"1146326400",164,233,123);
INSERT INTO comodojo_example VALUES (null,"1177862400",143,344,30);
INSERT INTO comodojo_example VALUES (null,"1209484800",433,444,342);
INSERT INTO comodojo_example VALUES (null,"1241020800",145,221,164);
INSERT INTO comodojo_example VALUES (null,"1272556800",341,514,316);
INSERT INTO comodojo_example VALUES (null,"1304092800",624,415,462);
INSERT INTO comodojo_example VALUES (null,"1335715200",341,945,273);
INSERT INTO comodojo_example VALUES (null,"1337715200",31,94,173);