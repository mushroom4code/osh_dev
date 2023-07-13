CREATE TABLE IF NOT EXISTS `ent_pickpoint_points`
(
    `ID`                        INT(11) NOT NULL,
    `CODE`                      VARCHAR(100) NOT NULL,
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
    `CODE`                      VARCHAR(100) NOT NULL,
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

CREATE TABLE IF NOT EXISTS `ent_russianpost_points`
(
    `ID`                        INT(11) NOT NULL,
    `INDEX`                     VARCHAR(100) NOT NULL,
    `BITRIX_CODE`               VARCHAR(100) NOT NULL,
    `WORK_TIME`                 VARCHAR(255),
    `FULL_ADDRESS`              VARCHAR(255) NOT NULL,
    `ADDRESS_LAT`               DOUBLE NOT NULL DEFAULT '0',
    `ADDRESS_LNG`               DOUBLE NOT NULL DEFAULT '0',
    `IS_PVZ`                    VARCHAR(100) NOT NULL,
    `IS_ECOM`                   VARCHAR(100) NOT NULL,
    PRIMARY KEY (`ID`),
    INDEX BITRIX_CODE (`BITRIX_CODE`)
    );

CREATE TABLE IF NOT EXISTS `ent_fivepost_points`
(
    `ID`                        INT(11) NOT NULL auto_increment,
    `POINT_GUID`                CHAR(36) NOT NULL,
    `BITRIX_CODE`               VARCHAR(100) NOT NULL,
    `NAME`                      VARCHAR(50) NOT NULL,
    `PARTNER_NAME`              VARCHAR(50) NOT NULL,
    `TYPE`                      VARCHAR(15) NOT NULL,
    `ADDITIONAL`                VARCHAR(150) NULL,
    `WORK_HOURS`                VARCHAR(2000) NOT NULL,
    `FULL_ADDRESS`              VARCHAR(255) NOT NULL,
    `ADDRESS_COUNTRY`           VARCHAR(30) NOT NULL,
    `ADDRESS_ZIP_CODE`          VARCHAR(10) NOT NULL,
    `ADDRESS_REGION`            VARCHAR(50) NOT NULL,
    `ADDRESS_REGION_TYPE`       VARCHAR(30) NULL,
    `ADDRESS_CITY`              VARCHAR(60) NOT NULL,
    `ADDRESS_CITY_TYPE`         VARCHAR(10) NULL,
    `ADDRESS_STREET`            VARCHAR(50) NOT NULL,
    `ADDRESS_HOUSE`             VARCHAR(15) NOT NULL,
    `ADDRESS_BUILDING`          VARCHAR(10) NULL,
    `ADDRESS_LAT`               DOUBLE NOT NULL DEFAULT '0',
    `ADDRESS_LNG`               DOUBLE NOT NULL DEFAULT '0',
    `ADDRESS_METRO_STATION`     VARCHAR(50) NULL,
    `LOCALITY_FIAS_CODE`        CHAR(36) NOT NULL DEFAULT '',
    `MAX_CELL_WIDTH`            INT(6) NOT NULL DEFAULT '0',
    `MAX_CELL_HEIGHT`           INT(6) NOT NULL DEFAULT '0',
    `MAX_CELL_LENGTH`           INT(6) NOT NULL DEFAULT '0',
    `MAX_CELL_WEIGHT`           INT(11) NOT NULL DEFAULT '0',
    `MAX_CELL_DIMENSIONS_HASH`  INT(11) NOT NULL DEFAULT '0',
    `RETURN_ALLOWED`            CHAR(1) NULL DEFAULT 'N',
    -- `TIMEZONE`                  VARCHAR(30) NOT NULL,
    `PHONE`                     VARCHAR(30) NOT NULL,
    `CASH_ALLOWED`              CHAR(1) NOT NULL DEFAULT 'N',
    `CARD_ALLOWED`              CHAR(1) NOT NULL DEFAULT 'N',
    `LOYALTY_ALLOWED`           CHAR(1) NULL DEFAULT 'N',
    `EXT_STATUS`                VARCHAR(20) NOT NULL,
    `DELIVERY_SL`               VARCHAR(255) NULL,
    `LASTMILEWAREHOUSE_ID`      CHAR(36) NOT NULL,
    `LASTMILEWAREHOUSE_NAME`    VARCHAR(50) NOT NULL,
    `RATE`                      VARCHAR(2000) NOT NULL,

    PRIMARY KEY (`ID`),
    INDEX IX_ENT_FIVEPOST_POINTS_POINT_GUID (`POINT_GUID`),
    INDEX IX_ENT_FIVEPOST_POINTS_LOCALITY_FIAS_CODE (`LOCALITY_FIAS_CODE`),
    INDEX IX_ENT_FIVEPOST_POINTS_MAX_CELL_DIMENSIONS_HASH (`MAX_CELL_DIMENSIONS_HASH`)
);

CREATE TABLE IF NOT EXISTS `ent_profiles_addresses`
(
    `ID`                        INT(11) NOT NULL AUTO_INCREMENT,
    `PROFILE_ID`                INT(11) NOT NULL,
    `USER_ID`                   INT(11) NOT NULL,
    `ADDRESS`               VARCHAR(255) NOT NULL,
    PRIMARY KEY (`ID`)
);

CREATE TABLE IF NOT EXISTS `ent_profiles_properties`
(
    `ID`                        INT(11) NOT NULL AUTO_INCREMENT,
    `SAVED_PROFILE_ID`          INT(11) NOT NULL,
    `PROPERTY_ID`               INT(11) NOT NULL,
    `CODE`                      VARCHAR(255) NOT NULL,
    `VALUE`                     VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`SAVED_PROFILE_ID`) REFERENCES ent_profiles_addresses (`ID`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `ent_oshisha_saved_deliveries`
(
    `ID`                        INT(11) NOT NULL AUTO_INCREMENT,
    `ZONE`                      VARCHAR(100) NOT NULL,
    `LATITUDE`                  VARCHAR(100) NOT NULL,
    `LONGITUDE`                 VARCHAR(100) NOT NULL,
    `DISTANCE`                  VARCHAR(100) NOT NULL,
    PRIMARY KEY (`ID`)
);