# OpenRouter Integration for Flarum Automation

This directory contains utility classes for integrating OpenRouter API into the Flarum Automation dashboard.

## What is OpenRouter?

OpenRouter is a unified API that provides access to various AI models (like Claude, GPT-4, etc.) through a single endpoint. This allows us to use different AI models for different purposes without changing our code.

## Configuration

The OpenRouter integration is configured through the `.env` file in the root of the `automate` directory. Make sure to set the following variables:

```
# OpenRouter API Key (required)
OPENROUTER_API_KEY=your_api_key_here

# Model configurations
# Content generation model (for shorter creative content, completions)
OPENROUTER_CONTENT_MODEL=anthropic/claude-3-haiku

# Research model (for more detailed research, analysis, longer responses)
OPENROUTER_RESEARCH_MODEL=anthropic/claude-3-opus

# Default settings
OPENROUTER_MAX_TOKENS=1024
OPENROUTER_TEMPERATURE=0.7
```

You can sign up for an OpenRouter API key at [https://openrouter.ai](https://openrouter.ai).

## Available Utilities

### DotEnv

The `DotEnv` class provides a simple way to load configuration variables from `.env` files. It supports:

- Loading variables from a specified file
- Retrieving specific variables with default values
- Checking if variables exist
- Getting all variables

Example usage:

```php
$dotenv = new \Utils\DotEnv();
$apiKey = $dotenv->get('OPENROUTER_API_KEY');
```

### OpenRouter

The `OpenRouter` class provides a simple interface for interacting with the OpenRouter API. It supports:

- Generating content using different models
- Extracting content from responses
- Retrieving available models

Example usage:

```php
$openRouter = new \Utils\OpenRouter();

// Generate content using the content model
$response = $openRouter->generate('Tell me a joke about programming', 'content');

// Extract the generated text
$content = $openRouter->extractContent($response);

// Display the content
echo $content;
```

## Available Features

The OpenRouter integration provides the following features:

1. **Test Page**: You can test the OpenRouter integration at `/openrouter-test`. This page allows you to test different models and prompts.

2. **AI Content Generation**: The AI Content Generator page at `/ai-generation` provides a user-friendly interface for generating different types of content:
   - Summarizing text
   - Expanding ideas
   - Detailed analysis (using the research model)
   - Extracting keywords

## Customizing Prompts

The `AiGenerationController` contains predefined prompts for each generation type. You can customize these prompts by modifying the `switch` statement in the `generate` method.

## Adding More Generation Types

To add more generation types:

1. Add a new option in the `index.php` view's select input
2. Add a new case in the `switch` statement in the `generate` method of `AiGenerationController`
3. Add a new label in the `$typeLabels` array in the view

## Troubleshooting

If you encounter issues with the OpenRouter integration:

1. Verify your API key is correct in the `.env` file
2. Check the model names are valid and available on OpenRouter
3. Test the connection using the test page at `/openrouter-test`
4. Check the server logs for any error messages

For more information on available models and parameters, see the [OpenRouter API documentation](https://openrouter.ai/docs). 