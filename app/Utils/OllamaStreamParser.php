<?php

namespace App\Utils;
use Psr\Http\Message\StreamInterface;

class OllamaStreamParser
{
    /**
     * Extracts text from a stream.
     *
     * @param StreamInterface $stream
     * @return string
     */
    public static function extractTextFromStream(StreamInterface $stream): string
    {
        $body = '';
        while (!$stream->eof()) {
            $body .= $stream->read(1024);
        }

        $content = '';

        foreach (explode("\n", $body) as $line) {
            if (trim($line) === '') continue;

            $json = json_decode($line, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($json['response'])) {
                $content .= $json['response'];
            }
        }

        return trim($content);
    }
}
