CREATE TABLE IF NOT EXISTS `ent_products_subscriptions`
(
    `ID`                        INT(11) NOT NULL AUTO_INCREMENT,
    `PRODUCT_NAME`              VARCHAR(255) NOT NULL,
    `SUBSCRIPTION_CLICKS`       INT(11) NOT NULL,
    PRIMARY KEY(`ID`)
);

DROP TABLE IF EXISTS `ent_products_subscriptions`;