# CLI Scripts

This directory contains command-line scripts for performing various administrative and maintenance tasks.

## Available Scripts

### Tag Suggestions for Keywords

`suggest_tags.php` - Uses OpenRouter API to suggest appropriate tags for keywords that don't have tags assigned.

#### Usage

```bash
./suggest_tags.php [--limit=N] [--min-confidence=0.N] [--dry-run]
```

#### Options

- `--limit=N`: Maximum number of untagged keywords to process (default: 1)
- `--min-confidence=0.N`: Minimum confidence threshold for tag assignment (default: 0.7)
- `--dry-run`: Run in simulation mode without making actual database changes

#### Examples

Process a single untagged keyword (default):
```bash
./suggest_tags.php
```

Process up to 10 untagged keywords:
```bash
./suggest_tags.php --limit=10
```

Lower the confidence threshold to 60%:
```bash
./suggest_tags.php --min-confidence=0.6
```

Test the script without making database changes:
```bash
./suggest_tags.php --limit=5 --dry-run
```

#### How It Works

1. The script identifies keywords in the database that don't have a tag assigned
2. For each keyword, it sends a request to OpenRouter with:
   - The keyword text
   - A structured JSON representation of available tags and categories
3. The structured tag data includes:
   - Categories (parent tags) that may or may not be assignable
   - Child tags that are always assignable
   - A clear hierarchical organization that reflects the tag structure in the database
4. OpenRouter analyzes the keyword and suggests the most appropriate tag
5. The response includes:
   - The exact tag ID from the database
   - The tag name
   - A confidence score between 0.0 and 1.0
6. If the confidence score meets the minimum threshold, the script assigns the suggested tag to the keyword
7. Results are displayed in the console with detailed information about each suggestion
8. Statistics are saved to track usage over time

#### Tag Structure Format

The script organizes tags into a hierarchical JSON structure to clearly show the relationship between categories and their child tags:

```json
{
  "categories": [
    {
      "id": 9,
      "name": "Signs & Messages",
      "description": "Discover various ways the divine and spiritual realms communicate",
      "is_assignable": false,
      "tags": [
        {
          "id": 10,
          "name": "Angel Signs & Symbols",
          "description": "Interpreting physical signs and symbols from angels",
          "is_assignable": true
        },
        {
          "id": 11,
          "name": "Synchronicities",
          "description": "Meaningful coincidences and their spiritual significance",
          "is_assignable": true
        }
      ]
    },
    {
      "id": 12,
      "name": "Astrology",
      "description": "The study of celestial bodies' influence on human affairs",
      "is_assignable": true,
      "tags": [
        {
          "id": 20,
          "name": "Zodiac Signs",
          "description": "The twelve astrological signs and their meanings",
          "is_assignable": true
        }
      ]
    }
  ]
}
```

In this structure:
- Each category contains its child tags in a nested `tags` array
- Both categories and tags have an `is_assignable` flag that indicates whether they can be selected
- The AI will only choose items where `is_assignable` is `true`

This approach makes it easy to understand the hierarchical relationships while clearly indicating which items can be assigned to keywords.

#### Statistics Tracking

The script maintains a statistics file (`tag_suggestion_stats.json`) to track usage over time:
- Total number of tags successfully assigned
- Date and time of the last run
- History of all runs, including:
  - Date and time
  - Number of keywords processed
  - Number of tags assigned
  - Minimum confidence threshold used

This allows monitoring the effectiveness of the tag suggestion system over time.

#### Requirements

- Active OpenRouter API key configured in the application's .env file
- At least one tag must exist in the database
- At least one untagged keyword must exist in the database

#### Running with Cron

You can automate tag suggestions by adding this script to your crontab:

```bash
# Run tag suggestion every hour for up to 20 keywords
0 * * * * /usr/bin/php /path/to/flarum/public/automate/cli/suggest_tags.php --limit=20
``` 