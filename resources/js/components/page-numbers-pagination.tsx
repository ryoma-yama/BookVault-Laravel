import { useLaravelReactI18n } from 'laravel-react-i18n';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationLink,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import { buildPaginationUrl } from '@/lib/build-pagination-url';
import { PaginationProgress } from './pagination-progress';

interface PageNumbersPaginationProps {
    currentPage: number;
    lastPage: number;
    perPage: number;
    total: number;
    basePath: string;
    filters?: Record<string, string | undefined>;
    showProgress?: boolean;
}

export function PageNumbersPagination({
    currentPage,
    lastPage,
    perPage,
    total,
    basePath,
    filters,
    showProgress = true,
}: PageNumbersPaginationProps) {
    const { t } = useLaravelReactI18n();

    const buildPageUrl = (page: number): string => {
        return buildPaginationUrl(basePath, page, filters);
    };

    // Generate page number elements
    const renderPageNumbers = () => {
        const pages = [];
        const showPages = 5; // Show at most 5 page numbers
        let startPage = Math.max(1, currentPage - Math.floor(showPages / 2));
        const endPage = Math.min(lastPage, startPage + showPages - 1);

        // Adjust start if we're near the end
        if (endPage - startPage < showPages - 1) {
            startPage = Math.max(1, endPage - showPages + 1);
        }

        // Show first page + ellipsis
        if (startPage > 1) {
            pages.push(
                <PaginationItem key="1">
                    <PaginationLink href={buildPageUrl(1)}>1</PaginationLink>
                </PaginationItem>,
            );
            if (startPage > 2) {
                pages.push(
                    <PaginationItem key="ellipsis-start">
                        <PaginationEllipsis />
                    </PaginationItem>,
                );
            }
        }

        // Show page numbers
        for (let i = startPage; i <= endPage; i++) {
            pages.push(
                <PaginationItem key={i}>
                    <PaginationLink
                        href={buildPageUrl(i)}
                        isActive={i === currentPage}
                    >
                        {i}
                    </PaginationLink>
                </PaginationItem>,
            );
        }

        // Show ellipsis + last page
        if (endPage < lastPage) {
            if (endPage < lastPage - 1) {
                pages.push(
                    <PaginationItem key="ellipsis-end">
                        <PaginationEllipsis />
                    </PaginationItem>,
                );
            }
            pages.push(
                <PaginationItem key={lastPage}>
                    <PaginationLink href={buildPageUrl(lastPage)}>
                        {lastPage}
                    </PaginationLink>
                </PaginationItem>,
            );
        }

        return pages;
    };

    return (
        <div className="flex flex-col items-center gap-4">
            {showProgress && (
                <PaginationProgress
                    currentPage={currentPage}
                    perPage={perPage}
                    total={total}
                />
            )}
            <Pagination>
                <PaginationContent>
                    <PaginationItem>
                        {currentPage > 1 ? (
                            <PaginationPrevious
                                href={buildPageUrl(currentPage - 1)}
                            >
                                {t('Previous')}
                            </PaginationPrevious>
                        ) : (
                            <PaginationPrevious className="pointer-events-none opacity-50">
                                {t('Previous')}
                            </PaginationPrevious>
                        )}
                    </PaginationItem>

                    {renderPageNumbers()}

                    <PaginationItem>
                        {currentPage < lastPage ? (
                            <PaginationNext
                                href={buildPageUrl(currentPage + 1)}
                            >
                                {t('Next')}
                            </PaginationNext>
                        ) : (
                            <PaginationNext className="pointer-events-none opacity-50">
                                {t('Next')}
                            </PaginationNext>
                        )}
                    </PaginationItem>
                </PaginationContent>
            </Pagination>
        </div>
    );
}
