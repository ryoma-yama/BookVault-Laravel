import ReviewForm from './form';
import type { Author } from '@/types';

interface Book {
    id: number;
    title: string;
    authors: Author[];
}

interface Review {
    id: number;
    comment: string;
    is_recommended: boolean;
    book: Book;
}

interface Props {
    review: Review;
}

export default function ReviewEdit({ review }: Props) {
    return <ReviewForm review={review} />;
}
