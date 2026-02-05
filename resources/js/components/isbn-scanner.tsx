import { BrowserBarcodeReader } from '@zxing/library';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Camera, X } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { Button } from '@/components/ui/button';

interface IsbnScannerProps {
    onScan: (isbn: string) => void;
    buttonVariant?: 'default' | 'outline' | 'ghost';
    buttonSize?: 'default' | 'sm' | 'lg' | 'icon';
}

function isISBN13(code: string): boolean {
    return (
        code.length === 13 && (code.startsWith('978') || code.startsWith('979'))
    );
}

function extractCameraError(error: unknown): string {
    if (error instanceof Error) {
        const isPermissionError =
            error.message.includes('Permission') ||
            error.message.includes('NotAllowedError');
        return isPermissionError
            ? 'camera_permission_denied'
            : 'camera_generic_error';
    }
    return 'camera_generic_error';
}

export default function IsbnScanner({
    onScan,
    buttonVariant = 'outline',
    buttonSize = 'default',
}: IsbnScannerProps) {
    const { t } = useLaravelReactI18n();
    const [scanning, setScanning] = useState(false);
    const [scannerError, setScannerError] = useState<string | null>(null);
    const scannerRef = useRef<HTMLVideoElement>(null);

    const handleStopScanning = useCallback(() => {
        setScanning(false);
        setScannerError(null);
    }, []);

    useEffect(() => {
        if (!scanning || !scannerRef.current) return;

        let scanner: BrowserBarcodeReader | null = null;
        let isStarted = false;

        const startScanner = async () => {
            scanner = new BrowserBarcodeReader();
            setScannerError(null);

            try {
                await scanner.decodeFromVideoDevice(
                    null, // Device ID (null prioritizes rear camera)
                    scannerRef.current!.id,
                    (result) => {
                        if (result) {
                            const code = result.getText();
                            if (isISBN13(code)) {
                                // Stop camera and process ISBN
                                scanner?.reset();
                                setScanning(false);
                                onScan(code);
                            }
                        }
                    },
                );
                isStarted = true;
            } catch (err) {
                console.error('Scanner error:', err);
                setScannerError(extractCameraError(err));
                setScanning(false);
            }
        };

        startScanner();

        return () => {
            if (scanner && isStarted) {
                scanner.reset();
            }
        };
    }, [scanning, onScan]);

    return (
        <>
            <Button
                type="button"
                variant={buttonVariant}
                size={buttonSize}
                onClick={() => setScanning(true)}
                title={t('Scan ISBN barcode')}
            >
                <Camera className="h-4 w-4" />
                {buttonSize !== 'icon' && (
                    <span className="ml-2">{t('Scan')}</span>
                )}
            </Button>

            {scanning && (
                <div className="bg-opacity-80 fixed inset-0 z-50 flex flex-col items-center justify-center bg-black">
                    <div className="mb-2 text-white">
                        {t('Point camera at ISBN barcode')}
                    </div>
                    <video
                        id="isbn-scanner"
                        ref={scannerRef}
                        className="h-[300px] w-[300px] bg-white"
                    />
                    {scannerError && (
                        <p className="mt-2 text-sm text-red-400">
                            {t(scannerError)}
                        </p>
                    )}
                    <Button
                        onClick={handleStopScanning}
                        variant="ghost"
                        className="mt-4 text-white hover:text-white"
                    >
                        <X className="mr-2 h-4 w-4" />
                        {t('Close')}
                    </Button>
                </div>
            )}
        </>
    );
}
