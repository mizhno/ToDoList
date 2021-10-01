create table if not exists tasks
(
    guid uuid
        constraint tasks_pk
            primary key,
    title text not null,
    status text not null,
    create_time timestamp not null,
    complete_time timestamp
);

