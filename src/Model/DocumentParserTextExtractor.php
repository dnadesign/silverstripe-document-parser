<?php

use LukeMadhanga\DocumentParser;

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
     * @param string $path
     * @return string
     */
    public function getContent($path): string
    {
        $documentParser = new DocumentParser();
        try {
            return strip_tags(str_replace('<', ' <', $documentParser::parseFromFile($path)));
        } catch (Exception $e) {
            SS_Log::log(
                sprintf(
                    '[DocumentParserTextExtractor] Error extracting text from "%s" (message: %s)',
                    $path,
                    $e->getMessage()
                ),
                SS_Log::NOTICE
            );
        }

        return '';
    }
}
