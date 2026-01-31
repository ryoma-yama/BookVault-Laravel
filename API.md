# Tag Management & Review API Documentation

This document describes the API endpoints for tag management and review/rating features.

## Books API

### List Books
```
GET /api/books
```
Query parameters:
- `tags[]` - Filter by tag name(s)

Response: Paginated list of books with tags, reviews, and statistics (average_rating, review_count)

### Get Book
```
GET /api/books/{id}
```
Response: Single book with tags, reviews, and statistics

### Create Book (Authenticated)
```
POST /api/books
```
Body:
```json
{
  "isbn_13": "9781234567890",
  "title": "Book Title",
  "publisher": "Publisher Name",
  "published_date": "2024-01-01",
  "description": "Book description",
  "google_id": "optional-google-id",
  "tags": ["Tag1", "Tag2"]
}
```

### Update Book (Authenticated)
```
PUT /api/books/{id}
```
Body: Same as create (all fields optional)

### Delete Book (Authenticated)
```
DELETE /api/books/{id}
```

## Reviews API

### List Reviews
```
GET /api/reviews
```
Query parameters:
- `book_id` - Filter by book ID

Response: Paginated list of reviews with user and book information

### Get Review (Authenticated)
```
GET /api/reviews/{id}
```

### Create Review (Authenticated)
```
POST /api/reviews
```
Body:
```json
{
  "book_id": 1,
  "content": "Review content (min 10 chars, max 1000 chars)",
  "rating": 5
}
```
Validation:
- `rating`: Integer between 1 and 5
- `content`: String, minimum 10 characters, maximum 1000 characters

### Update Review (Authenticated, Owner Only)
```
PUT /api/reviews/{id}
```
Body:
```json
{
  "content": "Updated review content",
  "rating": 4
}
```

### Delete Review (Authenticated, Owner Only)
```
DELETE /api/reviews/{id}
```

## Tags API

### List Tags
```
GET /api/tags
```
Response: List of all tags with book count

### Get Tag
```
GET /api/tags/{id}
```
Response: Single tag with associated books

## Authorization

- Book CRUD operations require authentication
- Review creation requires authentication
- Review update/delete requires authentication AND ownership
- Tag operations are read-only via API (tags are created automatically when assigning to books)

## Data Integrity

- Deleting a book cascades to its reviews and detaches its tags
- Deleting a user cascades to their reviews
- Tag names are unique across the system
