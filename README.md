# JotForm Submission PDF Downloader Script README

## Description

This PHP script automates the retrieval of submissions from a specified JotForm form, downloads the associated PDF documents, renames them based on submission data, and organizes them by clinic and month/year. It's designed to run in a terminal and outputs PDF files for efficient document management and archiving.

The Output will be: DIR/{clinic}/{yyyy-mm}/{file_name}.pdf

## Requirements

- **PHP >= 8.0**
- **Enabled PHP Extensions:**
  - `curl`: For making API requests to Jotform to retrieve submission data. While the script uses PHP's stream context for HTTP requests, ensuring `curl` is enabled can enhance compatibility for related tasks.
  - `json`: For decoding JSON responses from the Jotform API.
  - `mbstring`: For multi-byte string operations, potentially useful in handling file names and data manipulation.
  - `fileinfo`: For handling file operations, especially useful if extending the script's functionality to include MIME type checking.

Ensure these extensions are enabled in your `php.ini` file. To check your current PHP extensions, run `php -m` in your terminal.

## How It Works

1. Constructs HTTP headers and options to make GET requests to the Jotform API.
2. Iterates through submissions in batches, using a loop to manage pagination.
3. For each submission, it extracts the submission date, and other specified details from the form's response to construct folder names and file names for the PDF documents.
4. Organizes the PDF documents into directories structured by clinic and the submission's month/year.
5. Downloads and saves the PDF documents, skipping any that already exist.

## Usage

To run the script, navigate to the directory containing the script in your terminal and execute:

```bash
php script_name.php
