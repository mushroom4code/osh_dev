create table if not exists ent_send_push_user
(
    ID               int auto_increment
    primary key,
    USER_ID          int          not null,
    AUTH_TOKEN       varchar(255) not null,
    PUBLIC_KEY       varchar(255) not null,
    END_POINT        text         not null,
    CONTENT_ENCODING varchar(50)  not null
);
