# CLI Scripts

This directory contains command-line scripts for various automation tasks.

## Available Scripts

### 1. Suggest Tags

**File**: `suggest_tags.php`

Suggests tags for untagged keywords using OpenRouter API.

```bash
php public/automate/cli/suggest_tags.php [--limit=N] [--min-confidence=0.7] [--batch-size=5] [--dry-run]
```

Options:
- `--limit=N`: Limit to processing N keywords (default: 100)
- `--min-confidence=0.7`: Minimum confidence threshold (0.0-1.0) for accepting tag suggestions (default: 0.7)
- `--batch-size=5`: Number of keywords to process in a single API request (default: 5)
- `--dry-run`: Run in test mode without updating the database

See the detailed documentation in [README for suggest_tags.php](./README_suggest_tags.md).

### 2. Generate User

**File**: `generate_user.php`

Creates a new user with an AI-generated username.

```bash
php public/automate/cli/generate_user.php [--email=user@example.com] [--password=secret] [--domain=example.com] [--dry-run]
```

Options:
- `--email=user@example.com`: Specify a custom email (optional, will generate one if not provided)
- `--password=secret`: Specify a custom password (optional, will generate one if not provided)
- `--domain=example.com`: Domain to use for generated emails (default: example.com)
- `--dry-run`: Run in test mode without creating the user in the database

#### Examples

1. Generate a user with all random information:
   ```bash
   php public/automate/cli/generate_user.php
   ```

2. Generate a user with a specific email:
   ```bash
   php public/automate/cli/generate_user.php --email=john.doe@example.com
   ```

3. Generate a user with a specific email and password:
   ```bash
   php public/automate/cli/generate_user.php --email=john.doe@example.com --password=secure123
   ```

4. Test the script without creating a user in the database:
   ```bash
   php public/automate/cli/generate_user.php --dry-run
   ```

#### How It Works

1. The script establishes a database connection to the Flarum database.
2. It generates random email and password if not provided as parameters.
3. It uses OpenRouter API to generate a creative, unique username.
4. The generated username is cleaned up to meet forum requirements.
5. The user information is displayed in the console.
6. If not in dry-run mode, the user is created in the database.

#### AI Username Generation

The script uses the following criteria for generating usernames:
- Between 5-15 characters long
- No spaces (replaced with underscores if present)
- No offensive or inappropriate terms
- Memorable and distinct
- Suitable for a general forum community

If the AI username generation fails, the script falls back to a simple random username.

#### Requirements

- OpenRouter API key configured in the `.env` file
- Database connection to a Flarum installation
- PHP 7.4 or higher 