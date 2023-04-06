CREATE TABLE IF NOT EXISTS `ent_pickpoint_points`
(
    `ID`                        INT(11) NOT NULL,
    `CODE`               VARCHAR(100) NOT NULL,
	`BITRIX_CODE`               VARCHAR(100) NOT NULL,
	`FULL_ADDRESS`              VARCHAR(255) NOT NULL,
    `ADDRESS_REGION`            VARCHAR(255) NOT NULL,
    `ADDRESS_LAT`               DOUBLE NOT NULL DEFAULT '0',
    `ADDRESS_LNG`               DOUBLE NOT NULL DEFAULT '0',
    PRIMARY KEY (`ID`),
	INDEX BITRIX_CODE (`BITRIX_CODE`)
);

CREATE TABLE IF NOT EXISTS `ent_dellin_points`
(
    `ID`                        INT(11) NOT NULL,
    `CODE`               VARCHAR(100) NOT NULL,
    `BITRIX_CODE`               VARCHAR(100) NOT NULL,
    `PHONE_NUMBER`              VARCHAR(100) NOT NULL,
    `WORK_TIME`                 VARCHAR(255) NOT NULL,
    `FULL_ADDRESS`              VARCHAR(255) NOT NULL,
    `STREET_KLADR`              VARCHAR(100) NOT NULL,
    `ADDRESS_LAT`               DOUBLE NOT NULL DEFAULT '0',
    `ADDRESS_LNG`               DOUBLE NOT NULL DEFAULT '0',
    PRIMARY KEY (`ID`),
    INDEX BITRIX_CODE (`BITRIX_CODE`)
    );