# Ticketing System

## Overview

This repository contains a ticketing system designed to handle a variety of requests, including:

- Computer Equipment Assistance, Repairs, and Troubleshooting
- Daily Time Record (DTR) Queries
- Graphic Layout Requests
- ID Requests

The system allows users to submit tickets for these services and track their status through a user-friendly interface. Administrators can manage and resolve tickets, ensuring efficient handling of all types of requests.

## Installation

### Prerequisites
- PHP >= 8.3
- WSL for [Windows](https://learn.microsoft.com/en-us/windows/wsl/install)
- Docker
- Git
- Laravel Sail [Shell Alias](https://laravel.com/docs/11.x/sail#configuring-a-shell-alias)

### Configuration
1. **Clone the Repository**
   ```bash
   git clone https://github.com/eeneg/ticketing-mis.git ticket
   cd ticket
   ```

2. **Install Dependencies**
    ```bash
    composer install --ignore-platform-reqs
    ```

2. **Copy Environment File**
   ```bash
   cp .env.example .env
   ```

3. **Generate Application Key**
   ```
   php artisan key:generate
   ```

4. **Start the Containers**

   Ensure no conflicting ports are running.
   ```
   sail up -d
   ```
   Visit `http://localhost` to access the application.


## ER Diagram
```mermaid
erDiagram
    Request ||--|{ Attachment: contains
    Request ||--|{ Label: has
    User ||--|{ Assignee: is
    Request ||--|{ Action: has
    Organization ||--|{ User: has
    Category ||--|{ Subcategory: has
    Organization ||--|{ Category: has
    Category ||--|{ Tag: has
    Subcategory ||--|{ Tag: has
    Subcategory ||--|{ Request: has
    Category ||--|{ Request: has
    Assignee ||--|{ Request: has
    User ||--|{ Request: makes
    User ||--|{ Action: responds
    Action ||--|{ Attachment: contains
    Label }|--|| Tag: has
    Subcategory ||--|{ Template: has
    Dossier ||--|{ Note: has
    Dossier ||--|{ Record: has
    Request ||--|{ Record: has


User {
    ulid id pk
    string name
    string email
    string avatar
    string number
    string designation
    string role
    string purpose
    string password
    ulid organization_id fk
}

Organization {
    ulid id pk
    string name
    string code
    string logo
    string address
    string room
    string building
    json settings
}

Category {
    ulid id pk
    string name
    ulid organization_id fk
}

Subcategory {
    ulid id pk
    string name
    ulid category_id fk
}

Tag {
    ulid id pk
    string name
    string color
    ulid organization_id fk
    ulidmorphs taggable fk
}

Request {
    ulid id pk
    string class
    string code
    string subject
    text body
    ulid organization_id fk
    ulid category_id fk
    ulid subcategory_id fk
    ulid user_id fk
    ulid from_id fk
    text remarks
    int priority
    int difficulty
    datetime availability
}

Action {
    ulid id pk
    ulid request_id fk
    ulid user_id fk
    text remarks
    string status
    string resolution
    string system
    datetime time
}

Assignee {
    ulid id pk
    ulid request_id fk
    ulid user_id fk
    ulid assigner_id fk
}

Attachment {
    ulid id pk
    json files
    json paths
    ulidmorphs attachable fk
}

Template {
    ulid id pk
    string class
    text content
    ulid subcategory_id fk
}

Label {
    ulid id pk
    ulid request_id fk
    ulid tag_id fk
}

Dossier {
    ulid id pk
    ulid organization_id fk
    ulid user_id fk
}

Record {
    ulid id pk
    ulid dossier_id fk
    ulid request_id fk
    ulid user_id fk
}

Note {
    ulid id pk
    text content
    ulidmorphs notable fk
    ulid user_id
}
