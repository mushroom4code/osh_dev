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