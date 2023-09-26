create table ent_contragent_user_relationships
(
    ID            int auto_increment
        primary key,
    ID_CONTRAGENT int                  not null,
    USER_ID       int                  not null,
    STATUS        tinyint(1) default 0 not null
);

create table ent_contagents
(
    ID_CONTRAGENT     int auto_increment
        primary key,
    STATUS_CONTRAGENT tinyint(1)  default 0                       null,
    STATUS_VIEW       varchar(30) default 'Ожидает подтверждения' not null,
    TYPE              varchar(30)                                 null,
    NAME_ORGANIZATION varchar(250)                                null,
    INN               varchar(20)                                 null,
    RASCHET_CHET      varchar(50)                                 null,
    ADDRESS           text                                        null,
    BIC               varchar(50)                                 null,
    BANK              varchar(200)                                null,
    PHONE_COMPANY     varchar(30)                                 null,
    EMAIL             varchar(200)                                null,
    DATE_INSERT       timestamp   default current_timestamp()     not null,
    DATE_UPDATE       timestamp   default current_timestamp()     null,
    XML_ID            varchar(80)                                 null
);