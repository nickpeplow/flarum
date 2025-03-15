# Flarum Database Schema Documentation

This document describes the complete database schema for the Flarum forum application.

## Database Information

- **Database Name**: flarium
- **Database Engine**: MySQL
- **MySQL Version**: 5.7.39
- **Character Set**: utf8mb4
- **Collation**: utf8mb4_unicode_ci

## Table Overview

The database contains 27 tables:

### User Management
- `users`
- `access_tokens`
- `api_keys`
- `email_tokens`
- `password_tokens`
- `login_providers`
- `registration_tokens`
- `unsubscribe_tokens`

### Content Management
- `discussions`
- `posts`
- `flags`
- `post_likes`
- `post_mentions_user`
- `post_mentions_post`
- `post_mentions_group`
- `post_mentions_tag`

### Categorization
- `tags`
- `discussion_tag`
- `tag_user`

### Relationships
- `discussion_user`
- `post_user`
- `group_user`

### Permissions
- `groups`
- `group_permission`

### System
- `settings`
- `migrations`
- `notifications`

## Detailed Table Schemas

### `access_tokens`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| id | int(10) unsigned | NO | PRI | NULL | auto_increment |
| token | varchar(40) | NO | UNI | NULL |  |
| user_id | int(10) unsigned | NO | MUL | NULL |  |
| last_activity_at | datetime | YES |  | NULL |  |
| created_at | datetime | NO |  | NULL |  |
| type | varchar(100) | NO | MUL | NULL |  |
| title | varchar(150) | YES |  | NULL |  |
| last_ip_address | varchar(45) | YES |  | NULL |  |
| last_user_agent | varchar(255) | YES |  | NULL |  |

#### Foreign Keys

| Column | References |
|--------|------------|
| user_id | `users`.`id` |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | id |
| access_tokens_token_unique | BTREE | Yes | token |
| access_tokens_user_id_foreign | BTREE | No | user_id |
| access_tokens_type_index | BTREE | No | type |

### `api_keys`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| key | varchar(100) | NO | UNI | NULL |  |
| id | int(10) unsigned | NO | PRI | NULL | auto_increment |
| allowed_ips | varchar(255) | YES |  | NULL |  |
| scopes | varchar(255) | YES |  | NULL |  |
| user_id | int(10) unsigned | YES | MUL | NULL |  |
| created_at | datetime | NO |  | NULL |  |
| last_activity_at | datetime | YES |  | NULL |  |

#### Foreign Keys

| Column | References |
|--------|------------|
| user_id | `users`.`id` |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | id |
| api_keys_key_unique | BTREE | Yes | key |
| api_keys_user_id_foreign | BTREE | No | user_id |

### `discussion_tag`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| discussion_id | int(10) unsigned | NO | PRI | NULL |  |
| tag_id | int(10) unsigned | NO | PRI | NULL |  |
| created_at | timestamp | YES |  | CURRENT_TIMESTAMP |  |

#### Foreign Keys

| Column | References |
|--------|------------|
| discussion_id | `discussions`.`id` |
| tag_id | `tags`.`id` |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | discussion_id, tag_id |
| discussion_tag_tag_id_foreign | BTREE | No | tag_id |

### `discussion_user`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| user_id | int(10) unsigned | NO | PRI | NULL |  |
| discussion_id | int(10) unsigned | NO | PRI | NULL |  |
| last_read_at | datetime | YES |  | NULL |  |
| last_read_post_number | int(10) unsigned | YES |  | NULL |  |
| subscription | enum('follow','ignore') | YES |  | NULL |  |

#### Foreign Keys

| Column | References |
|--------|------------|
| discussion_id | `discussions`.`id` |
| user_id | `users`.`id` |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | user_id, discussion_id |
| discussion_user_discussion_id_foreign | BTREE | No | discussion_id |

### `discussions`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| id | int(10) unsigned | NO | PRI | NULL | auto_increment |
| title | varchar(200) | NO | MUL | NULL |  |
| comment_count | int(11) | NO | MUL | 1 |  |
| participant_count | int(10) unsigned | NO | MUL | 0 |  |
| created_at | datetime | NO | MUL | NULL |  |
| user_id | int(10) unsigned | YES | MUL | NULL |  |
| first_post_id | int(10) unsigned | YES | MUL | NULL |  |
| last_posted_at | datetime | YES | MUL | NULL |  |
| last_posted_user_id | int(10) unsigned | YES | MUL | NULL |  |
| last_post_id | int(10) unsigned | YES | MUL | NULL |  |
| last_post_number | int(10) unsigned | YES |  | NULL |  |
| hidden_at | datetime | YES | MUL | NULL |  |
| hidden_user_id | int(10) unsigned | YES | MUL | NULL |  |
| slug | varchar(255) | NO |  | NULL |  |
| is_private | tinyint(1) | NO |  | 0 |  |
| is_approved | tinyint(1) | NO |  | 1 |  |
| is_sticky | tinyint(1) | NO | MUL | 0 |  |
| is_locked | tinyint(1) | NO | MUL | 0 |  |

#### Foreign Keys

| Column | References |
|--------|------------|
| first_post_id | `posts`.`id` |
| hidden_user_id | `users`.`id` |
| last_post_id | `posts`.`id` |
| last_posted_user_id | `users`.`id` |
| user_id | `users`.`id` |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | id |
| discussions_hidden_user_id_foreign | BTREE | No | hidden_user_id |
| discussions_first_post_id_foreign | BTREE | No | first_post_id |
| discussions_last_post_id_foreign | BTREE | No | last_post_id |
| discussions_last_posted_at_index | BTREE | No | last_posted_at |
| discussions_last_posted_user_id_index | BTREE | No | last_posted_user_id |
| discussions_created_at_index | BTREE | No | created_at |
| discussions_user_id_index | BTREE | No | user_id |
| discussions_comment_count_index | BTREE | No | comment_count |
| discussions_participant_count_index | BTREE | No | participant_count |
| discussions_hidden_at_index | BTREE | No | hidden_at |
| discussions_is_sticky_created_at_index | BTREE | No | is_sticky, created_at |
| discussions_is_sticky_last_posted_at_index | BTREE | No | is_sticky, last_posted_at |
| discussions_is_locked_index | BTREE | No | is_locked |
| title | FULLTEXT | No | title |

### `email_tokens`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| token | varchar(100) | NO | PRI | NULL |  |
| email | varchar(254) | NO |  | NULL |  |
| user_id | int(10) unsigned | NO | MUL | NULL |  |
| created_at | datetime | YES |  | NULL |  |

#### Foreign Keys

| Column | References |
|--------|------------|
| user_id | `users`.`id` |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | token |
| email_tokens_user_id_foreign | BTREE | No | user_id |

### `flags`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| id | int(10) unsigned | NO | PRI | NULL | auto_increment |
| post_id | int(10) unsigned | NO | MUL | NULL |  |
| type | varchar(255) | NO |  | NULL |  |
| user_id | int(10) unsigned | YES | MUL | NULL |  |
| reason | varchar(255) | YES |  | NULL |  |
| reason_detail | text | YES |  | NULL |  |
| created_at | datetime | NO | MUL | NULL |  |

#### Foreign Keys

| Column | References |
|--------|------------|
| post_id | `posts`.`id` |
| user_id | `users`.`id` |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | id |
| flags_post_id_foreign | BTREE | No | post_id |
| flags_user_id_foreign | BTREE | No | user_id |
| flags_created_at_index | BTREE | No | created_at |

### `group_permission`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| group_id | int(10) unsigned | NO | PRI | NULL |  |
| permission | varchar(100) | NO | PRI | NULL |  |
| created_at | timestamp | YES |  | CURRENT_TIMESTAMP |  |

#### Foreign Keys

| Column | References |
|--------|------------|
| group_id | `groups`.`id` |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | group_id, permission |

### `group_user`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| user_id | int(10) unsigned | NO | PRI | NULL |  |
| group_id | int(10) unsigned | NO | PRI | NULL |  |
| created_at | timestamp | YES |  | CURRENT_TIMESTAMP |  |

#### Foreign Keys

| Column | References |
|--------|------------|
| group_id | `groups`.`id` |
| user_id | `users`.`id` |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | user_id, group_id |
| group_user_group_id_foreign | BTREE | No | group_id |

### `groups`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| id | int(10) unsigned | NO | PRI | NULL | auto_increment |
| name_singular | varchar(100) | NO |  | NULL |  |
| name_plural | varchar(100) | NO |  | NULL |  |
| color | varchar(20) | YES |  | NULL |  |
| icon | varchar(100) | YES |  | NULL |  |
| is_hidden | tinyint(1) | NO |  | 0 |  |
| created_at | timestamp | YES |  | CURRENT_TIMESTAMP |  |
| updated_at | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | id |

### `login_providers`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| id | int(10) unsigned | NO | PRI | NULL | auto_increment |
| user_id | int(10) unsigned | NO | MUL | NULL |  |
| provider | varchar(100) | NO | MUL | NULL |  |
| identifier | varchar(100) | NO |  | NULL |  |
| created_at | datetime | YES |  | NULL |  |
| last_login_at | datetime | YES |  | NULL |  |

#### Foreign Keys

| Column | References |
|--------|------------|
| user_id | `users`.`id` |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | id |
| login_providers_provider_identifier_unique | BTREE | Yes | provider, identifier |
| login_providers_user_id_foreign | BTREE | No | user_id |

### `migrations`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| id | int(10) unsigned | NO | PRI | NULL | auto_increment |
| migration | varchar(255) | NO |  | NULL |  |
| extension | varchar(255) | YES |  | NULL |  |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | id |

### `notifications`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| id | int(10) unsigned | NO | PRI | NULL | auto_increment |
| user_id | int(10) unsigned | NO | MUL | NULL |  |
| from_user_id | int(10) unsigned | YES | MUL | NULL |  |
| type | varchar(100) | NO |  | NULL |  |
| subject_id | int(10) unsigned | YES |  | NULL |  |
| created_at | datetime | NO |  | NULL |  |
| is_deleted | tinyint(1) | NO |  | 0 |  |
| read_at | datetime | YES |  | NULL |  |
| data | json | YES |  | NULL |  |

#### Foreign Keys

| Column | References |
|--------|------------|
| from_user_id | `users`.`id` |
| user_id | `users`.`id` |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | id |
| notifications_from_user_id_foreign | BTREE | No | from_user_id |
| notifications_user_id_index | BTREE | No | user_id |

### `password_tokens`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| token | varchar(100) | NO | PRI | NULL |  |
| user_id | int(10) unsigned | NO | MUL | NULL |  |
| created_at | datetime | YES |  | NULL |  |

#### Foreign Keys

| Column | References |
|--------|------------|
| user_id | `users`.`id` |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | token |
| password_tokens_user_id_foreign | BTREE | No | user_id |

### `post_likes`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| post_id | int(10) unsigned | NO | PRI | NULL |  |
| user_id | int(10) unsigned | NO | PRI | NULL |  |
| created_at | timestamp | NO |  | CURRENT_TIMESTAMP |  |

#### Foreign Keys

| Column | References |
|--------|------------|
| post_id | `posts`.`id` |
| user_id | `users`.`id` |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | post_id, user_id |
| post_likes_user_id_foreign | BTREE | No | user_id |

### `post_mentions_group`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| post_id | int(10) unsigned | NO | PRI | NULL |  |
| mentions_group_id | int(10) unsigned | NO | PRI | NULL |  |
| created_at | datetime | YES |  | CURRENT_TIMESTAMP |  |

#### Foreign Keys

| Column | References |
|--------|------------|
| mentions_group_id | `groups`.`id` |
| post_id | `posts`.`id` |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | post_id, mentions_group_id |
| post_mentions_group_mentions_group_id_foreign | BTREE | No | mentions_group_id |

### `post_mentions_post`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| post_id | int(10) unsigned | NO | PRI | NULL |  |
| mentions_post_id | int(10) unsigned | NO | PRI | NULL |  |
| created_at | timestamp | YES |  | CURRENT_TIMESTAMP |  |

#### Foreign Keys

| Column | References |
|--------|------------|
| mentions_post_id | `posts`.`id` |
| post_id | `posts`.`id` |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | post_id, mentions_post_id |
| post_mentions_post_mentions_post_id_foreign | BTREE | No | mentions_post_id |

### `post_mentions_tag`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| post_id | int(10) unsigned | NO | PRI | NULL |  |
| mentions_tag_id | int(10) unsigned | NO | PRI | NULL |  |
| created_at | datetime | YES |  | CURRENT_TIMESTAMP |  |

#### Foreign Keys

| Column | References |
|--------|------------|
| mentions_tag_id | `tags`.`id` |
| post_id | `posts`.`id` |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | post_id, mentions_tag_id |
| post_mentions_tag_mentions_tag_id_foreign | BTREE | No | mentions_tag_id |

### `post_mentions_user`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| post_id | int(10) unsigned | NO | PRI | NULL |  |
| mentions_user_id | int(10) unsigned | NO | PRI | NULL |  |
| created_at | timestamp | YES |  | CURRENT_TIMESTAMP |  |

#### Foreign Keys

| Column | References |
|--------|------------|
| mentions_user_id | `users`.`id` |
| post_id | `posts`.`id` |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | post_id, mentions_user_id |
| post_mentions_user_mentions_user_id_foreign | BTREE | No | mentions_user_id |

### `post_user`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| post_id | int(10) unsigned | NO | PRI | NULL |  |
| user_id | int(10) unsigned | NO | PRI | NULL |  |

#### Foreign Keys

| Column | References |
|--------|------------|
| post_id | `posts`.`id` |
| user_id | `users`.`id` |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | post_id, user_id |
| post_user_user_id_foreign | BTREE | No | user_id |

### `posts`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| id | int(10) unsigned | NO | PRI | NULL | auto_increment |
| discussion_id | int(10) unsigned | NO | MUL | NULL |  |
| number | int(10) unsigned | YES |  | NULL |  |
| created_at | datetime | NO |  | NULL |  |
| user_id | int(10) unsigned | YES | MUL | NULL |  |
| type | varchar(100) | YES | MUL | NULL |  |
| content | mediumtext | YES | MUL | NULL |  |
| edited_at | datetime | YES |  | NULL |  |
| edited_user_id | int(10) unsigned | YES | MUL | NULL |  |
| hidden_at | datetime | YES |  | NULL |  |
| hidden_user_id | int(10) unsigned | YES | MUL | NULL |  |
| ip_address | varchar(45) | YES |  | NULL |  |
| is_private | tinyint(1) | NO |  | 0 |  |
| is_approved | tinyint(1) | NO |  | 1 |  |

#### Foreign Keys

| Column | References |
|--------|------------|
| discussion_id | `discussions`.`id` |
| edited_user_id | `users`.`id` |
| hidden_user_id | `users`.`id` |
| user_id | `users`.`id` |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | id |
| posts_discussion_id_number_unique | BTREE | Yes | discussion_id, number |
| posts_edited_user_id_foreign | BTREE | No | edited_user_id |
| posts_hidden_user_id_foreign | BTREE | No | hidden_user_id |
| posts_discussion_id_number_index | BTREE | No | discussion_id, number |
| posts_discussion_id_created_at_index | BTREE | No | discussion_id, created_at |
| posts_user_id_created_at_index | BTREE | No | user_id, created_at |
| posts_type_index | BTREE | No | type |
| posts_type_created_at_index | BTREE | No | type, created_at |
| content | FULLTEXT | No | content |

### `registration_tokens`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| token | varchar(100) | NO | PRI | NULL |  |
| payload | text | YES |  | NULL |  |
| created_at | datetime | YES |  | NULL |  |
| provider | varchar(255) | NO |  | NULL |  |
| identifier | varchar(255) | NO |  | NULL |  |
| user_attributes | text | YES |  | NULL |  |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | token |

### `settings`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| key | varchar(100) | NO | PRI | NULL |  |
| value | text | YES |  | NULL |  |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | key |

### `tag_user`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| user_id | int(10) unsigned | NO | PRI | NULL |  |
| tag_id | int(10) unsigned | NO | PRI | NULL |  |
| marked_as_read_at | datetime | YES |  | NULL |  |
| is_hidden | tinyint(1) | NO |  | 0 |  |

#### Foreign Keys

| Column | References |
|--------|------------|
| tag_id | `tags`.`id` |
| user_id | `users`.`id` |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | user_id, tag_id |
| tag_user_tag_id_foreign | BTREE | No | tag_id |

### `tags`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| id | int(10) unsigned | NO | PRI | NULL | auto_increment |
| name | varchar(100) | NO |  | NULL |  |
| slug | varchar(100) | NO | UNI | NULL |  |
| description | text | YES |  | NULL |  |
| color | varchar(50) | YES |  | NULL |  |
| background_path | varchar(100) | YES |  | NULL |  |
| background_mode | varchar(100) | YES |  | NULL |  |
| is_primary | tinyint(1) | NO |  | 0 |  |
| position | int(11) | YES |  | NULL |  |
| parent_id | int(10) unsigned | YES | MUL | NULL |  |
| default_sort | varchar(50) | YES |  | NULL |  |
| is_restricted | tinyint(1) | NO |  | 0 |  |
| is_hidden | tinyint(1) | NO |  | 0 |  |
| discussion_count | int(10) unsigned | NO |  | 0 |  |
| last_posted_at | datetime | YES |  | NULL |  |
| last_posted_discussion_id | int(10) unsigned | YES | MUL | NULL |  |
| last_posted_user_id | int(10) unsigned | YES | MUL | NULL |  |
| icon | varchar(100) | YES |  | NULL |  |
| created_at | timestamp | YES |  | CURRENT_TIMESTAMP |  |
| updated_at | timestamp | YES |  | CURRENT_TIMESTAMP | on update CURRENT_TIMESTAMP |

#### Foreign Keys

| Column | References |
|--------|------------|
| last_posted_discussion_id | `discussions`.`id` |
| last_posted_user_id | `users`.`id` |
| parent_id | `tags`.`id` |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | id |
| tags_slug_unique | BTREE | Yes | slug |
| tags_parent_id_foreign | BTREE | No | parent_id |
| tags_last_posted_user_id_foreign | BTREE | No | last_posted_user_id |
| tags_last_posted_discussion_id_foreign | BTREE | No | last_posted_discussion_id |

### `unsubscribe_tokens`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| id | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| user_id | int(10) unsigned | NO | MUL | NULL |  |
| email_type | varchar(255) | NO | MUL | NULL |  |
| token | varchar(100) | NO | UNI | NULL |  |
| unsubscribed_at | timestamp | YES |  | NULL |  |
| created_at | timestamp | YES |  | NULL |  |
| updated_at | timestamp | YES |  | NULL |  |

#### Foreign Keys

| Column | References |
|--------|------------|
| user_id | `users`.`id` |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | id |
| unsubscribe_tokens_token_unique | BTREE | Yes | token |
| unsubscribe_tokens_user_id_index | BTREE | No | user_id |
| unsubscribe_tokens_email_type_index | BTREE | No | email_type |
| unsubscribe_tokens_token_index | BTREE | No | token |
| unsubscribe_tokens_user_id_email_type_index | BTREE | No | user_id, email_type |

### `users`

#### Columns

| Column | Type | Nullable | Key | Default | Extra |
|--------|------|----------|-----|---------|-------|
| id | int(10) unsigned | NO | PRI | NULL | auto_increment |
| username | varchar(100) | NO | UNI | NULL |  |
| email | varchar(254) | NO | UNI | NULL |  |
| is_email_confirmed | tinyint(1) | NO |  | 0 |  |
| password | varchar(100) | NO |  | NULL |  |
| avatar_url | varchar(100) | YES |  | NULL |  |
| joined_at | datetime | YES | MUL | NULL |  |
| last_seen_at | datetime | YES | MUL | NULL |  |
| marked_all_as_read_at | datetime | YES |  | NULL |  |
| read_notifications_at | datetime | YES |  | NULL |  |
| discussion_count | int(10) unsigned | NO | MUL | 0 |  |
| comment_count | int(10) unsigned | NO | MUL | 0 |  |
| preferences | json | YES |  | NULL |  |
| read_flags_at | datetime | YES |  | NULL |  |
| suspended_until | datetime | YES |  | NULL |  |
| suspend_reason | text | YES |  | NULL |  |
| suspend_message | text | YES |  | NULL |  |

#### Indexes

| Name | Type | Unique | Columns |
|------|------|--------|--------|
| PRIMARY | BTREE | Yes | id |
| users_username_unique | BTREE | Yes | username |
| users_email_unique | BTREE | Yes | email |
| users_joined_at_index | BTREE | No | joined_at |
| users_last_seen_at_index | BTREE | No | last_seen_at |
| users_discussion_count_index | BTREE | No | discussion_count |
| users_comment_count_index | BTREE | No | comment_count |

## Relationships

The database follows a relational structure where:
- Users create discussions and posts
- Discussions contain posts
- Tags categorize discussions
- Groups manage permissions
- Various token tables handle authentication and security functions

## Notes

This schema follows typical forum database design patterns with:
- Core content tables (discussions, posts)
- User management (users, authentication)
- Categorization system (tags)
- Permission system (groups, permissions)
- Relationship tracking (mentions, likes)
