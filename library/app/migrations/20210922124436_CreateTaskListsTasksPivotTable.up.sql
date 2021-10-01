create table tasklists_tasks
(
    tasklist uuid
        constraint tasklists_tasks_tasklists_guid_fk
            references tasklists (guid)
            on update cascade on delete cascade,
    task uuid
        constraint tasklists_tasks_tasks_guid_fk
            references tasks (guid)
            on update cascade on delete cascade
);
