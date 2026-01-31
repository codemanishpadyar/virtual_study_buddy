<?php
/**
 * StudyBuddy AI - GPT (OpenAI) configuration
 *
 * This file configures GPT to answer user questions and summarize notes.
 *
 * 1. Copy this file to studybuddy_config.php
 * 2. Replace 'your-openai-api-key-here' with your OpenAI API key
 * 3. Get a key at: https://platform.openai.com/api-keys
 *
 * Optional: Set OPENAI_API_KEY in your server environment instead.
 * Optional: Set STUDYBUDDY_OPENAI_MODEL to use a different model (default: gpt-4o-mini).
 */

define('STUDYBUDDY_OPENAI_API_KEY', 'your-openai-api-key-here');

// Model used by GPT to answer questions (default: gpt-4o-mini)
// Other options: gpt-4o, gpt-4o-mini, gpt-4-turbo, gpt-3.5-turbo
define('STUDYBUDDY_OPENAI_MODEL', 'gpt-4o-mini');
