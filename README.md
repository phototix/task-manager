# Task Manager for WebbyCMS

A web-based task management system integrated with WhatsApp and AI tools. This project allows users and groups to manage tasks, tickets, contacts, and knowledge base, with automation features such as reminders and announcements.

## Features

- **Task Management**: Create, edit, complete, and delete tasks. Supports priorities, remarks, and scheduling.
- **Calendar View**: Visualize tasks on a calendar.
- **Ticketing System**: Track support tickets and view details.
- **Contacts Management**: Add, update, and delete contacts and groups.
- **Knowledge Base**: CRUD operations for knowledge items per user/group.
- **Group Management**: Edit group info and picture via WhatsApp API.
- **Automated Reminders**: Cron-based reminders for scheduled tasks.
- **Announcements**: Send scheduled announcements to contacts via WhatsApp.
- **API Endpoints**: RESTful APIs for tasks, contacts, tickets, groups, and knowledge.

## Folder Structure

```
.
├── addCron.php
├── calendar.php
├── converTime.php
├── database.sql
├── favicon.ico
├── greeting.php
├── index.php
├── knowledge.php
├── list.png
├── listGroup.php
├── manageContactDetails.php
├── manageContacts.php
├── manageGroup.php
├── manageKnowledge.php
├── README.md
├── sendAnnouncement.php
├── sendReminder.php
├── tasks.php
├── ticket.php
├── updateGroup.php
├── updateGroupPicture.php
├── api/
│   ├── contact.php
│   ├── contactDelete.php
│   ├── contactDetails.php
│   ├── contactsList.php
│   ├── contactUpdate.php
│   ├── group.php
│   ├── index.php
│   ├── knowledge.php
│   ├── tasks.php
│   └── ticket.php
└── config/
    └── database.php
```

## Setup

1. **Clone the repository**
2. **Configure the database**  
   Edit `config/database.php` with your MySQL credentials.
3. **Import the schema**  
   Run the SQL in [`database.sql`](database.sql) to create required tables.
4. **Web Server**  
   Host the repo on Apache/Nginx with PHP 7.4+.
5. **Crontab**  
   Set up cron jobs for reminders and group updates using `addCron.php` and `sendReminder.php`.

## Usage

- Access the main dashboard via `/index.php`
- Use the navigation buttons to manage tasks, tickets, contacts, groups, and knowledge.
- API endpoints are available under `/api/` for integration.

## API Endpoints

- `GET /api/tasks.php?user_id=...` — List tasks for user/group
- `POST /api/tasks.php` — Create task
- `PUT /api/tasks.php?id=...` — Update task
- `DELETE /api/tasks.php?id=...` — Delete task
- `GET /api/knowledge.php?user_id=...` — List knowledge items
- `POST /api/knowledge.php` — Create knowledge item
- `PUT /api/knowledge.php?id=...` — Update knowledge item
- `DELETE /api/knowledge.php?id=...` — Delete knowledge item
- ...and more for contacts, tickets, and groups.

## Integrations

- **WhatsApp API**: Used for sending reminders, announcements, and updating group info.
- **AI Agent**: Group conversations can be captured and used for AI-powered responses.

## License

MIT

## Author

Brandon Chong /