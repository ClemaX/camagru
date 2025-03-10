--------------------
---- App Schema ----
--------------------
create schema if not exists camagru;
set search_path to camagru, public;

--- Session
create table session
(
    id              varchar(128)    primary key,
    data            text            not null,
    last_access     bigint                      default null
);

--- Role
create type role_name as enum ('USER', 'ADMIN');

create table role
(
    -- Base properties
    id              int             primary key,
    -- entity_version  int             not null        default 0,
    -- created_at      timestamp       not null        default current_timestamp,
    -- updated_at      timestamp       not null        default current_timestamp,
    -- Role properties
    name            role_name       unique not null,
    description     varchar(128)    not null
);

--- User
create table "user"
(
    -- Base properties
    id                              bigint                  primary key         generated always as identity,
    -- entity_version                  int             not null            default 0,
    -- created_at                      timestamp       not null            default current_timestamp,
    -- updated_at                      timestamp       not null            default current_timestamp,
    -- User properties
    email_address                   varchar(254)            unique not null,
    username                        varchar(16)             unique not null,
    password_hash                   varchar(255),
    is_locked                       boolean                 not null            default true,
    locked_at                       bigint,
    unlock_token                    varchar(255),
    email_change_address            varchar(254),
    email_change_requested_at       bigint,
    email_change_token              varchar(255),
    role_id                         int                     not null,
    -- Constraints
    foreign key(role_id)            references "role"(id)   on delete       cascade
);

--- User - Profile
create table user_profile
(
    -- Base properties
    user_id                 bigint                  primary key,
    -- entity_version          int                     not null        default 0,
    -- created_at              timestamp               not null        default current_timestamp,
    -- updated_at              timestamp               not null        default current_timestamp,
    -- Profile properties
    name                    varchar(50)             not null        default '',
    description             varchar(140)            not null        default '',
    -- Constraints
    foreign key(user_id)    references "user"(id)   on delete       cascade
);

--- User - Settings
create table user_settings
(
    -- Base properties
    user_id                     bigint                  primary key,
    -- Versioned properties
    -- entity_version              int                     not null    default 0,
    -- created_at                  timestamp               not null    default current_timestamp,
    -- updated_at                  timestamp               not null    default current_timestamp,
    -- Settings properties
    comment_notification        boolean                 not null    default true,
    -- Constraints
    foreign key(user_id)        references "user"(id)   on delete   cascade
);

--- Post
create table post
(
    -- Base properties
    id                      bigint                  primary key generated always as identity,
    created_at              bigint                  not null,
    updated_at              bigint                  not null,
    -- Post properties
    title                   varchar(64)             not null,
    description             varchar(512)            not null    default '',
    author_id               bigint                  not null,
    -- Constraints
    foreign key(author_id)  references "user"(id)   on delete   cascade
);

create table post_like
(
    -- Base properties
    created_at              bigint                      not null,
    -- Post Like properties
    post_id                 bigint                      not null,
    author_id               bigint                      not null,
    -- Constraints
    foreign key(post_id)    references post(id)         on delete   cascade,
    foreign key(author_id)  references "user"(id)       on delete   cascade,
    primary key(post_id, author_id)
);

create table post_comment
(
    -- Base properties
    id                      bigint                      primary key generated always as identity,
    created_at              bigint                      not null,
    updated_at              bigint                      not null,
    -- Post Comment properties
    post_id                 bigint                      not null,
    author_id               bigint                      not null,
    subject_id              bigint                                  default null,
    body                    varchar(512)                not null,
    -- Constraints
    foreign key(post_id)    references post(id)         on delete   cascade,
    foreign key(author_id)  references "user"(id)       on delete   cascade,
    foreign key(subject_id) references post_comment(id) on delete   cascade
);

------------------------
---- App Procedures ----
------------------------

-- create procedure user_create(
--     email_address "user".email_address%TYPE,
--     username "user".username%TYPE,
--     description "user_profile".description%TYPE,
--     role_id role.id%TYPE = 1
-- )
-- language plpgsql as $$
-- declare
--     user_id "user".id%TYPE;
-- begin
--     insert into "user" (email_address, username)
--     values (email_address, username)
--     returning id into user_id;

--     insert into user_profile (user_id, name, description)
--     values (user_id, username, description);

--     insert into user_settings (user_id)
--     values (user_id);
-- end;
-- $$;

------------------
---- App Data ----
------------------

--- Roles
insert into role (id, name, description)
values (1, 'USER', 'Camagru User'),
       (2, 'ADMIN', 'Camagru Administrator');

-- Administrator
-- call user_create('admin@camagru.localhost', 'admin', 'Camagru Administrator', 2);

set search_path to public;
