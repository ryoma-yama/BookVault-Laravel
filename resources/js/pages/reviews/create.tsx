import ReviewForm from './form';
import type { Author } from '@/types';

interface Book {
    id: number;
    title: string;
    authors: Author[];
}

interface Props {
    book: Book;
}

export default function ReviewCreate({ book }: Props) {
    return <ReviewForm book={book} />;
}
