create table if not exists users
(
    guid uuid
        constraint users_pk
            primary key,
    login text not null,
    password text not null,
    token text
);

create unique index if not exists users_login_uindex
    on users (login);

create unique index if not exists users_token_uindex
    on users (token);
