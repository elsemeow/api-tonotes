# Tonotes API

_REST API for working with notes_

## Authentication

Tonotes API uses basic authentication to verify access rights to data from a SQL database. To do this, the client must send a username and password in base64, separated by the symbol `:`, in the request header, for example `Authorization: Basic dGVzdDp0ZXN0`.

## API requests

API can handle the following requests:

| Method | URL       | Description                |
| ------ | --------- | -------------------------- |
| GET    | /notes    | Get all notes              |
| GET    | /notes/id | Get a note with a given id |
| POST   | /notes    | Post a new note            |
| PUT    | /notes    | Update note                |
| DELETE | /notes    | Delete note                |

## SQL tables

For this REST API to work, you need to prepare a SQL database with 2 tables: `users`- a table with users and `notes` - a table for notes.

### Structure of the `users` table:

| Name       | Type         | Comparison         | Null | Default | Additionally   | Description                                                                  |
| ---------- | ------------ | ------------------ | ---- | ------- | -------------- | ---------------------------------------------------------------------------- |
| id         | int(11)      |                    | No   | No      | AUTO_INCREMENT | Index, primary key                                                           |
| username   | varchar(100) | utf8mb4_unicode_ci | No   | No      |                | Username                                                                     |
| password   | varchar(255) | utf8mb4_unicode_ci | No   | No      |                | Password                                                                     |
| plan       | varchar(50)  | utf8mb4_unicode_ci | No   | basic   |                | If `basic` - number of requests is limited to 1000 per day, else `unlimited` |
| calls_made | int(11)      |                    | No   | 0       |                | Request counter                                                              |
| time_start | varchar(255) | utf8mb4_unicode_ci | No   | 0       |                | Time of the first request to the database                                    |
| time_end   | varchar(255) | utf8mb4_unicode_ci | No   | 0       |                | Time of the last request to the database                                     |

### Structure of the `notes` table:

| Name      | Type         | Comparison         | Null | Default | Additionally   | Description                                                                |
| --------- | ------------ | ------------------ | ---- | ------- | -------------- | -------------------------------------------------------------------------- |
| id        | int(11)      |                    | No   | No      | AUTO_INCREMENT | Index, primary key                                                         |
| title     | varchar(255) | utf8mb4_unicode_ci | No   | No      |                | Note title                                                                 |
| user_id   | int(11)      |                    | No   | No      |                | id of the user who created the note is used to separate user rights        |
| date_time | varchar(100) | utf8mb4_unicode_ci | No   | No      |                | Time of note creation                                                      |
| todos     | longtext     | utf8mb4_unicode_ci | Yes  | NULL    |                | An array of objects with a TODO list is stored here for a note as a string |

### Importing tables

To import tables, use the `db_tonotes.sql` file in the root directory of this project.

## Setting up access to the database

To API can work with the database is necessary to open a file `config / Database.php` and change the values of these variables:

```php
private $hostName = "HOST_NAME"; // Hostname
private $dbname = "DATABASE_NAME"; // Database name
private $username = "YOUR_USERNAME"; // Database username
private $password = "YOUR_PASSWORD"; // Database password
```

## Used tools

- VSCodium;
- PhpMyAdmin;
- Postman.
