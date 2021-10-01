create table if not exists tasklists
(
    guid uuid
        constraint tasklists_pk
            primary key,
    title text
);
