/**
 * Domain types for BookVault entities
 * These types represent the core data models used across the application
 */

/**
 * Author entity
 */
export type Author = {
    id: number;
    name: string;
};

/**
 * Tag entity for categorizing books
 */
export type Tag = {
    id: number;
    name: string;
};

/**
 * Book entity representing the full book data structure
 * Used in admin pages and detail views
 */
export type Book = {
    id: number;
    isbn_13: string;
    title: string;
    publisher: string;
    published_date: string;
    description: string;
    google_id?: string;
    image_url?: string;
    authors: Author[];
    tags?: Tag[];
    created_at?: string;
    updated_at?: string;
};

/**
 * Book listing item for public book index
 * Contains authors array and tags
 */
export type BookListItem = {
    id: number;
    title: string;
    authors: Author[];
    publisher: string | null;
    isbn_13: string | null;
    tags: Tag[];
    image_url?: string;
    created_at: string;
};

/**
 * Inventory status for book availability
 */
export type InventoryStatus = {
    total_copies: number;
    borrowed_count: number;
    available_count: number;
};

/**
 * Current loan information
 */
export type CurrentLoan = {
    user: {
        id: number;
        name: string;
    };
    borrowed_date: string;
};

/**
 * Book with inventory details for book detail page
 */
export type BookWithInventory = {
    id: number;
    isbn_13: string;
    title: string;
    publisher: string;
    published_date: string;
    description: string;
    image_url?: string;
    authors: Author[];
    tags: Tag[];
    inventory_status: InventoryStatus;
    current_loans: CurrentLoan[];
};

/**
 * Book copy entity for inventory management
 */
export type BookCopy = {
    id: number;
    book_id: number;
    acquired_date: string;
    discarded_date: string | null;
    created_at: string;
    updated_at: string;
};

/**
 * Google Books API response type
 * Matches the structure returned by GoogleBooksController
 */
export type GoogleBooksApiResponse = {
    google_id: string;
    isbn_13: string | null;
    title: string;
    authors: string[];
    publisher: string;
    published_date: string;
    description: string;
    image_url: string;
};

/**
 * Error response from API endpoints
 */
export type ApiErrorResponse = {
    error: string;
};

/**
 * Generic paginated response type
 * Matches Laravel's pagination structure
 */
export type PaginatedResponse<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

/**
 * Book form data for creating/editing books
 */
export type BookFormData = {
    isbn_13: string;
    title: string;
    publisher: string;
    published_date: string;
    description: string;
    google_id: string;
    image_url: string;
    authors: string[];
};

/**
 * Book filter options for search
 */
export type BookFilters = {
    search?: string;
    author?: string;
    publisher?: string;
    tag?: string;
    sort?: string;
    direction?: 'asc' | 'desc';
};

/**
 * User filter options for admin user management
 */
export type UserFilters = {
    search?: string;
    role?: 'admin' | 'user';
};

/**
 * User list item for admin user management
 * Used in admin user index page
 */
export type UserListItem = {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'user';
    created_at: string;
};

/**
 * Book summary for inventory management
 * Used in book copies management page
 */
export type BookSummary = {
    id: number;
    title: string;
    isbn_13: string;
    publisher: string;
    published_date: string;
};

/**
 * Review entity
 */
export type Review = {
    id: number;
    book_id: number;
    user_id: number;
    comment: string;
    is_recommended: boolean;
    created_at: string;
    user: {
        id: number;
        name: string;
    };
};

/**
 * Review with full book details
 * Used in review list pages where book info is populated
 */
export type ReviewWithBook = {
    id: number;
    comment: string;
    is_recommended: boolean;
    created_at: string;
    book: {
        id: number;
        title: string;
        authors: Author[];
    };
    user: {
        id: number;
        name: string;
    };
};

/**
 * User's own review (simplified version)
 */
export type UserReview = {
    id: number;
    comment: string;
    is_recommended: boolean;
};
