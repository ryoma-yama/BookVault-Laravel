interface PaginationProgressProps {
    currentPage: number;
    perPage: number;
    total: number;
}

export function PaginationProgress({
    currentPage,
    perPage,
    total,
}: PaginationProgressProps) {
    const start = (currentPage - 1) * perPage + 1;
    const end = Math.min(currentPage * perPage, total);

    return (
        <p className="text-sm text-muted-foreground">
            {`${start} - ${end} / ${total}`}
        </p>
    );
}
