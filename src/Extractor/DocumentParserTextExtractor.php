<?php

namespace AndrewAndante\SilverStripeDocumentParser\Extractor;

use LukeMadhanga\DocumentParser;
use Psr\Log\LoggerInterface;
use SilverStripe\Assets\File;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\TextExtraction\Extractor\FileTextExtractor;
use Throwable;

class DocumentParserTextExtractor extends FileTextExtractor
{
    public function isAvailable(): bool
    {
        return class_exists(DocumentParser::class);
    }

    public function supportsExtension($extension): bool
    {
        return in_array(strtolower($extension), ['doc', 'docx', 'rtf', 'txt']);
    }

    public function supportsMime($mime): bool
    {
        return in_array(
            strtolower($mime),
            [
                'text/html',
                'text/plain',
                'application/rtf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.oasis.opendocument.text',
                'application/octet-stream',
            ]
        );
    }

    /**
     * Extracts content and then sanitises by using strip_tags()
     *
     * @param File|string $file
     * @return string
     */
    public function getContent($file): string
    {
        $documentParser = new DocumentParser();
        try {
            $path = $file instanceof File ? self::getPathFromFile($file) : $file;
            return strip_tags(str_replace('<', ' <', $documentParser::parseFromFile($path)));
        } catch (Throwable $e) {
            Injector::inst()->get(LoggerInterface::class)->info(
                sprintf(
                    '[DocumentParserTextExtractor] Error extracting text from "%s" (message: %s)',
                    $path ?? 'unknown file',
                    $e->getMessage()
                )
            );
        }

        return '';
    }
}
