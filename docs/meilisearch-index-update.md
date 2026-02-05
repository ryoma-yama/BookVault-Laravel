# Meilisearch Index Update Guide

## Overview

This guide explains how to update the Meilisearch search index after implementing the book copy filtering feature.

## Background

The Book model now includes a `has_valid_copies` flag in its searchable array, which indicates whether a book has at least one non-discarded copy. Additionally, the BookCopy model now automatically touches the parent Book model when created, updated, or deleted, ensuring the search index stays in sync.

## When to Update the Index

You need to update the search index in the following scenarios:

1. **After deploying this feature for the first time** - Existing books need to have their `has_valid_copies` flag added to the index
2. **When migrating data** - If you bulk update BookCopy records
3. **When the index becomes out of sync** - Rarely, but if you notice discrepancies

## How to Update the Index

### Option 1: Re-import all Books (Recommended)

This is the safest option as it completely rebuilds the index with the latest data:

```bash
# Clear the existing index and re-import all books
php artisan scout:flush "App\Models\Book"
php artisan scout:import "App\Models\Book"
```

### Option 2: Update in Batches

If you have a large dataset and want to update without downtime:

```bash
# This will update the index for all books in batches
php artisan scout:import "App\Models\Book"
```

## Automatic Synchronization

Once the index is updated, the following automatic synchronization mechanisms are in place:

1. **When a BookCopy is created**: The parent Book's `updated_at` timestamp is updated, triggering a search index update
2. **When a BookCopy is updated**: Same as above
3. **When a BookCopy is deleted**: Same as above
4. **When a Book is created/updated**: The search index is automatically updated by Laravel Scout

## Filtering in Queries

The application now filters books to show only those with valid copies:

1. **Regular list view**: Uses `Book::hasValidCopies()` scope with Eloquent
2. **Search queries**: Uses Scout search with the scope applied to the query builder

## Driver-Specific Notes

### Meilisearch Driver
- The `has_valid_copies` flag is included in the searchable array
- Supports full-text search across all fields including the flag
- Recommended for production use

### Database Driver
- The `has_valid_copies` flag is NOT included (it's a computed field)
- Uses LIKE queries for searching
- Suitable for testing or small datasets

## Verification

To verify the index is working correctly:

1. Create a book without any copies
2. Access the book list - the book should NOT appear
3. Add a valid copy (with `discarded_date = null`)
4. The book should now appear in the list
5. Set the copy's `discarded_date` to a date
6. The book should disappear from the list again

## Troubleshooting

If books are not appearing or disappearing as expected:

1. Check your `SCOUT_DRIVER` environment variable
2. Verify Meilisearch is running (if using that driver)
3. Check the application logs for any Scout-related errors
4. Manually verify the data:
   ```sql
   SELECT b.id, b.title, COUNT(bc.id) as valid_copies
   FROM books b
   LEFT JOIN book_copies bc ON b.id = bc.book_id AND bc.discarded_date IS NULL
   GROUP BY b.id, b.title;
   ```
5. Re-run the import command to rebuild the index

## Performance Considerations

- The automatic touch relationship adds minimal overhead (a single timestamp update)
- Bulk operations on BookCopy should still be fast
- If importing thousands of books, use the `scout:import` command with appropriate batching
- Consider using Scout's queue feature for large datasets:
  ```env
  SCOUT_QUEUE=true
  ```
