# CLI Scripts

This directory contains command-line scripts for performing various administrative and maintenance tasks.

## Available Scripts

### Tag Suggestions for Keywords

`suggest_tags.php` - Uses OpenRouter API to suggest appropriate tags for keywords that don't have tags assigned.

#### Usage

```bash
./suggest_tags.php [--limit=N] [--min-confidence=0.N] [--batch-size=N] [--dry-run]
```

#### Options

- `--limit=N`: Maximum number of untagged keywords to process (default: 100)
- `--min-confidence=0.N`: Minimum confidence threshold for tag assignment (default: 0.7)
- `--batch-size=N`: Number of keywords to process in a single API request (default: 5)
- `--dry-run`: Run in simulation mode without making actual database changes

#### Examples

Process untagged keywords with default settings:
```bash
./suggest_tags.php
```

Process up to 20 untagged keywords with a batch size of 10:
```bash
./suggest_tags.php --limit=20 --batch-size=10
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

1. The script identifies keywords in the database that don't have a tag assigned (and aren't marked as 'rejected')
2. Keywords are grouped into batches (default 5 per batch) for efficient processing
3. For each batch, it sends a single request to OpenRouter with:
   - A list of keywords with their IDs
   - A structured JSON representation of available tags and categories
4. The structured tag data includes:
   - Categories (parent tags) always marked as non-assignable and without IDs
   - Child tags with their actual database IDs and marked as assignable
   - A clear hierarchical organization that reflects the tag structure in the database
5. OpenRouter analyzes all keywords in the batch and suggests the most appropriate tag for each
6. The response includes a suggestion for each keyword containing:
   - The keyword ID from the batch
   - The exact tag ID from the database
   - The tag name
   - A confidence score between 0.0 and 1.0
7. The script processes each suggestion and takes one of two actions based on the confidence score:
   - If the confidence score meets the minimum threshold, the script assigns the suggested tag to the keyword and marks it as 'approved'
   - If the confidence score is below the minimum threshold, the keyword is marked with a 'rejected' status and won't be processed again
8. Results are displayed in the console with detailed information about each suggestion
9. Statistics are saved to track usage over time

#### Tag Structure Format

The script organizes tags into a hierarchical JSON structure to clearly show the relationship between categories and their child tags:

```json
{
  "categories": [
    {
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
      "name": "Astrology",
      "description": "The study of celestial bodies' influence on human affairs",
      "is_assignable": false,
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
- Categories do not have ID values in the JSON structure sent to the AI
- Each category contains its child tags in a nested `tags` array 
- Only child tags have ID values and are marked as assignable
- The AI can only choose items where `is_assignable` is `true` and that have an ID field
- This ensures the AI can only suggest specific tags and not parent categories

This approach makes it easy to understand the hierarchical relationships while preventing the AI from suggesting categories by mistake.

#### Batch Processing Efficiency

The script processes keywords in batches to improve efficiency:

1. **Reduced API Calls**: Instead of one API call per keyword, the script now makes one call per batch (default 5 keywords), reducing the number of API calls by 80%.

2. **Faster Processing**: Fewer API calls means less overhead and waiting time, resulting in faster overall processing of keywords.

3. **Cost Efficiency**: Most API providers charge per request, so processing multiple keywords in a single request can significantly reduce costs.

4. **Improved Context**: The AI model sees multiple keywords at once, potentially making more consistent categorization decisions by comparing related keywords.

#### Keyword Status Handling

The script manages keywords based on the AI's confidence in its tag suggestions:

- **Untagged**: Keywords without a tag that have not been processed or didn't receive a valid response
- **Approved**: Keywords that received a tag suggestion with confidence above the minimum threshold
- **Rejected**: Keywords that received a tag suggestion with confidence below the minimum threshold

Rejected keywords are marked with a 'rejected' status in the database and will not be processed in future runs. This prevents the system from repeatedly trying to tag keywords that don't fit well into the available tag structure.

To reset rejected keywords and make them eligible for processing again, you can update their status:
```sql
UPDATE keywords SET status = NULL WHERE status = 'rejected';
```

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
# Run tag suggestion every hour for up to 100 keywords with a batch size of 10
0 * * * * /usr/bin/php /path/to/flarum/public/automate/cli/suggest_tags.php --limit=100 --batch-size=10
``` 