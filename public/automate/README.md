# Keywords Automation Dashboard for Flarum

This dashboard provides tools to manage keywords for your Flarum forum.

## Features

- View, add, edit, and delete keywords
- Approve, reject, or mark keywords as pending
- Filter and search keywords
- Database schema management
- Statistics and system information

## Installation

1. The dashboard files are located in the `public/automate` directory of your Flarum installation.
2. Make sure the database connection is properly configured in your Flarum's `config.php` file.
3. Access the dashboard at: `https://your-forum-url/automate/`

## Setting Up the Database

1. First, visit the Schema Viewer page at: `https://your-forum-url/automate/schema_viewer.php`
2. Click the "Create Keywords Table" button to create the required database table.
3. Once the table is created, you can start managing keywords.

## Usage

- **Dashboard**: View system information and recent keywords
- **Keywords Management**: Add, filter, search, and manage keywords
- **Schema Viewer**: View and manage database schema

## Troubleshooting

If you encounter database connection issues:

1. Check that your Flarum's `config.php` file has the correct database credentials.
2. Visit `https://your-forum-url/db_test.php` to test your database connection.
3. Make sure the MySQL server is running and accessible.

## Structure

- `index.php` - Main dashboard page
- `keywords.php` - Keywords management page
- `schema_viewer.php` - Database schema management
- `models/Keyword.php` - Keyword model class
- `schema/keywords_schema.sql` - SQL schema for the keywords table
- `partials/` - Header and footer files

## Development

If you need to modify the dashboard:

1. The main functionality is in the `Keyword.php` model class.
2. Database connection settings are in `public/db_connection.php`.
3. Each page follows the pattern of including the database connection, loading models, handling form submissions, and then displaying the UI.

## License

This dashboard is released under the same license as your Flarum installation. 