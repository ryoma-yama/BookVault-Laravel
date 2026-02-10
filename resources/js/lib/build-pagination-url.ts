/**
 * Build a pagination URL with query parameters
 *
 * @param basePath - The base path for the URL (e.g., '/' or '/admin/books')
 * @param page - The page number
 * @param filters - Optional filters to include in the query string (undefined values are omitted)
 * @returns The complete URL with query parameters
 */
export function buildPaginationUrl(
    basePath: string,
    page: number,
    filters?: Record<string, string | undefined>,
): string {
    const params = new URLSearchParams();
    params.set('page', page.toString());

    if (filters) {
        Object.entries(filters).forEach(([key, value]) => {
            if (value) {
                params.set(key, value);
            }
        });
    }

    return `${basePath}?${params.toString()}`;
}
