create table users_tasklists
(
    users uuid
        constraint users_tasklists_users_guid_fk
            references users (guid)
            on update cascade on delete cascade,
    tasklists uuid
        constraint users_tasklists_tasklists_guid_fk
            references tasklists (guid)
            on update cascade on delete cascade
);
