<?php

declare(strict_types=1);

use Akira\Rag\Exceptions\CacheException;
use Akira\Rag\Exceptions\ChunkingException;
use Akira\Rag\Exceptions\ChunkNotFoundException;
use Akira\Rag\Exceptions\ConfigurationException;
use Akira\Rag\Exceptions\DatabaseException;
use Akira\Rag\Exceptions\DocumentNotFoundException;
use Akira\Rag\Exceptions\EmbeddingException;
use Akira\Rag\Exceptions\ExportException;
use Akira\Rag\Exceptions\ImportException;
use Akira\Rag\Exceptions\InvalidPayload;
use Akira\Rag\Exceptions\InvalidQuestionException;
use Akira\Rag\Exceptions\RetrievalException;
use Akira\Rag\Exceptions\TenantResolverException;

describe('CacheException', function () {
    it('creates cacheStoreFailed exception', function () {
        $exception = CacheException::cacheStoreFailed('test-key', 'connection timeout');

        expect($exception)->toBeInstanceOf(CacheException::class)
            ->and($exception->getMessage())->toBe("Failed to store cache for key 'test-key': connection timeout");
    });

    it('creates cacheRetrievalFailed exception', function () {
        $exception = CacheException::cacheRetrievalFailed('test-key', 'serialization error');

        expect($exception)->toBeInstanceOf(CacheException::class)
            ->and($exception->getMessage())->toBe("Failed to retrieve cache for key 'test-key': serialization error");
    });

    it('creates invalidCacheConfiguration exception', function () {
        $exception = CacheException::invalidCacheConfiguration('missing driver');

        expect($exception)->toBeInstanceOf(CacheException::class)
            ->and($exception->getMessage())->toBe('Invalid cache configuration: missing driver');
    });
});

describe('ChunkNotFoundException', function () {
    it('creates withId exception', function () {
        $exception = ChunkNotFoundException::withId('chunk-123');

        expect($exception)->toBeInstanceOf(ChunkNotFoundException::class)
            ->and($exception->getMessage())->toBe("Chunk with ID 'chunk-123' was not found in the knowledge base.");
    });

    it('creates forDocument exception', function () {
        $exception = ChunkNotFoundException::forDocument('doc-123');

        expect($exception)->toBeInstanceOf(ChunkNotFoundException::class)
            ->and($exception->getMessage())->toBe("No chunks found for document with ID 'doc-123'.");
    });
});

describe('ChunkingException', function () {
    it('creates invalidTokenCount exception', function () {
        $exception = ChunkingException::invalidTokenCount(100, 200);

        expect($exception)->toBeInstanceOf(ChunkingException::class)
            ->and($exception->getMessage())->toBe('Invalid chunking configuration: target_tokens (100) must be greater than overlap_tokens (200).');
    });

    it('creates chunkingFailed exception', function () {
        $exception = ChunkingException::chunkingFailed('unknown error');

        expect($exception)->toBeInstanceOf(ChunkingException::class)
            ->and($exception->getMessage())->toBe('Failed to chunk content: unknown error');
    });

    it('creates emptyContentProvided exception', function () {
        $exception = ChunkingException::emptyContentProvided();

        expect($exception)->toBeInstanceOf(ChunkingException::class)
            ->and($exception->getMessage())->toBe('Cannot chunk empty content. Please provide valid content.');
    });

    it('creates invalidChunkSize exception', function () {
        $exception = ChunkingException::invalidChunkSize(5000, 100, 4000);

        expect($exception)->toBeInstanceOf(ChunkingException::class)
            ->and($exception->getMessage())->toBe('Invalid chunk size: 5000. Chunk size must be between 100 and 4000.');
    });
});

describe('ConfigurationException', function () {
    it('creates missingConfiguration exception', function () {
        $exception = ConfigurationException::missingConfiguration('api_key');

        expect($exception)->toBeInstanceOf(ConfigurationException::class)
            ->and($exception->getMessage())->toBe("Missing required configuration: 'api_key'. Please check your configuration file.");
    });

    it('creates invalidConfiguration exception', function () {
        $exception = ConfigurationException::invalidConfiguration('timeout', 'must be integer');

        expect($exception)->toBeInstanceOf(ConfigurationException::class)
            ->and($exception->getMessage())->toBe("Invalid configuration for 'timeout': must be integer");
    });

    it('creates modelNotConfigured exception', function () {
        $exception = ConfigurationException::modelNotConfigured('embedding');

        expect($exception)->toBeInstanceOf(ConfigurationException::class)
            ->and($exception->getMessage())->toBe('No embedding model configured. Please set a valid model in your configuration.');
    });
});

describe('DatabaseException', function () {
    it('creates transactionFailed exception', function () {
        $exception = DatabaseException::transactionFailed('deadlock found');

        expect($exception)->toBeInstanceOf(DatabaseException::class)
            ->and($exception->getMessage())->toBe('Database transaction failed: deadlock found');
    });

    it('creates queryFailed exception', function () {
        $exception = DatabaseException::queryFailed('User', 'syntax error');

        expect($exception)->toBeInstanceOf(DatabaseException::class)
            ->and($exception->getMessage())->toBe('Failed to execute query on User: syntax error');
    });

    it('creates connectionFailed exception', function () {
        $exception = DatabaseException::connectionFailed('host unreachable');

        expect($exception)->toBeInstanceOf(DatabaseException::class)
            ->and($exception->getMessage())->toBe('Database connection failed: host unreachable');
    });
});

describe('DocumentNotFoundException', function () {
    it('creates withId exception', function () {
        $exception = DocumentNotFoundException::withId('doc-123');

        expect($exception)->toBeInstanceOf(DocumentNotFoundException::class)
            ->and($exception->getMessage())->toBe("Document with ID 'doc-123' was not found in the knowledge base.");
    });

    it('creates withHash exception', function () {
        $exception = DocumentNotFoundException::withHash('hash-123');

        expect($exception)->toBeInstanceOf(DocumentNotFoundException::class)
            ->and($exception->getMessage())->toBe("Document with hash 'hash-123' was not found in the knowledge base.");
    });
});

describe('EmbeddingException', function () {
    it('creates generationFailed exception', function () {
        $exception = EmbeddingException::generationFailed('api error');

        expect($exception)->toBeInstanceOf(EmbeddingException::class)
            ->and($exception->getMessage())->toBe('Failed to generate embedding: api error');
    });

    it('creates invalidDimensions exception', function () {
        $exception = EmbeddingException::invalidDimensions(1536, 1024);

        expect($exception)->toBeInstanceOf(EmbeddingException::class)
            ->and($exception->getMessage())->toBe('Invalid embedding dimensions. Expected 1536, but got 1024.');
    });

    it('creates modelNotConfigured exception', function () {
        $exception = EmbeddingException::modelNotConfigured();

        expect($exception)->toBeInstanceOf(EmbeddingException::class)
            ->and($exception->getMessage())->toBe('Embedding model is not configured. Please set a valid model in the configuration.');
    });
});

describe('ExportException', function () {
    it('creates exportFailed exception', function () {
        $exception = ExportException::exportFailed('disk full');

        expect($exception)->toBeInstanceOf(ExportException::class)
            ->and($exception->getMessage())->toBe('Failed to export knowledge base: disk full');
    });

    it('creates invalidExportFormat exception', function () {
        $exception = ExportException::invalidExportFormat('xml');

        expect($exception)->toBeInstanceOf(ExportException::class)
            ->and($exception->getMessage())->toBe("Invalid export format: 'xml'. Only 'json' format is currently supported.");
    });

    it('creates encryptionFailed exception', function () {
        $exception = ExportException::encryptionFailed('key invalid');

        expect($exception)->toBeInstanceOf(ExportException::class)
            ->and($exception->getMessage())->toBe('Failed to encrypt export: key invalid');
    });

    it('creates compressionFailed exception', function () {
        $exception = ExportException::compressionFailed('unknown algo');

        expect($exception)->toBeInstanceOf(ExportException::class)
            ->and($exception->getMessage())->toBe('Failed to compress export: unknown algo');
    });

    it('creates fileWriteFailed exception', function () {
        $exception = ExportException::fileWriteFailed('/tmp/export.json', 'permission denied');

        expect($exception)->toBeInstanceOf(ExportException::class)
            ->and($exception->getMessage())->toBe("Failed to write export file to '/tmp/export.json': permission denied");
    });
});

describe('ImportException', function () {
    it('creates importFailed exception', function () {
        $exception = ImportException::importFailed('data corrupted');

        expect($exception)->toBeInstanceOf(ImportException::class)
            ->and($exception->getMessage())->toBe('Failed to import knowledge base: data corrupted');
    });

    it('creates fileNotFound exception', function () {
        $exception = ImportException::fileNotFound('/tmp/import.json');

        expect($exception)->toBeInstanceOf(ImportException::class)
            ->and($exception->getMessage())->toBe("Import file not found: '/tmp/import.json'");
    });

    it('creates invalidFileFormat exception', function () {
        $exception = ImportException::invalidFileFormat('/tmp/import.txt', 'json');

        expect($exception)->toBeInstanceOf(ImportException::class)
            ->and($exception->getMessage())->toBe("Invalid file format for '/tmp/import.txt'. Expected json format.");
    });

    it('creates decryptionFailed exception', function () {
        $exception = ImportException::decryptionFailed('wrong password');

        expect($exception)->toBeInstanceOf(ImportException::class)
            ->and($exception->getMessage())->toBe('Failed to decrypt import file: wrong password');
    });

    it('creates decompressionFailed exception', function () {
        $exception = ImportException::decompressionFailed('crc error');

        expect($exception)->toBeInstanceOf(ImportException::class)
            ->and($exception->getMessage())->toBe('Failed to decompress import file: crc error');
    });

    it('creates invalidBackupContent exception', function () {
        $exception = ImportException::invalidBackupContent('missing header');

        expect($exception)->toBeInstanceOf(ImportException::class)
            ->and($exception->getMessage())->toBe('Invalid backup content: missing header');
    });
});

describe('InvalidPayload', function () {
    it('creates missingField exception', function () {
        $exception = InvalidPayload::missingField('title');

        expect($exception)->toBeInstanceOf(InvalidPayload::class)
            ->and($exception->getMessage())->toBe("Missing or invalid required field: 'title'. The field must be present and contain a valid value.");
    });

    it('creates tenantIdNotAllowed exception', function () {
        $exception = InvalidPayload::tenantIdNotAllowed();

        expect($exception)->toBeInstanceOf(InvalidPayload::class)
            ->and($exception->getMessage())->toBe('The tenant_id field is not allowed in the payload. Tenant ID is automatically managed by the system.');
    });

    it('creates invalidFieldType exception', function () {
        $exception = InvalidPayload::invalidFieldType('age', 'integer', 'string');

        expect($exception)->toBeInstanceOf(InvalidPayload::class)
            ->and($exception->getMessage())->toBe("Invalid type for field 'age'. Expected integer, but got string.");
    });

    it('creates emptyContent exception', function () {
        $exception = InvalidPayload::emptyContent();

        expect($exception)->toBeInstanceOf(InvalidPayload::class)
            ->and($exception->getMessage())->toBe('Content cannot be empty. Please provide valid content to ingest.');
    });

    it('creates invalidMetadata exception', function () {
        $exception = InvalidPayload::invalidMetadata('not an array');

        expect($exception)->toBeInstanceOf(InvalidPayload::class)
            ->and($exception->getMessage())->toBe('Invalid metadata: not an array');
    });
});

describe('InvalidQuestionException', function () {
    it('creates emptyQuestion exception', function () {
        $exception = InvalidQuestionException::emptyQuestion();

        expect($exception)->toBeInstanceOf(InvalidQuestionException::class)
            ->and($exception->getMessage())->toBe('Question cannot be empty. Please provide a valid question string.');
    });

    it('creates questionTooLong exception', function () {
        $exception = InvalidQuestionException::questionTooLong(100, 150);

        expect($exception)->toBeInstanceOf(InvalidQuestionException::class)
            ->and($exception->getMessage())->toBe('Question is too long. Maximum length is 100 characters, but 150 were provided.');
    });
});

describe('RetrievalException', function () {
    it('creates noResultsFound exception', function () {
        $exception = RetrievalException::noResultsFound('meaning of life');

        expect($exception)->toBeInstanceOf(RetrievalException::class)
            ->and($exception->getMessage())->toBe("No results found for query: 'meaning of life'");
    });

    it('creates retrievalFailed exception', function () {
        $exception = RetrievalException::retrievalFailed('db timeout');

        expect($exception)->toBeInstanceOf(RetrievalException::class)
            ->and($exception->getMessage())->toBe('Failed to retrieve chunks: db timeout');
    });

    it('creates invalidTopK exception', function () {
        $exception = RetrievalException::invalidTopK(-1);

        expect($exception)->toBeInstanceOf(RetrievalException::class)
            ->and($exception->getMessage())->toBe('Invalid top_k value: -1. The value must be a positive integer.');
    });

    it('creates invalidFilters exception', function () {
        $exception = RetrievalException::invalidFilters('syntax error');

        expect($exception)->toBeInstanceOf(RetrievalException::class)
            ->and($exception->getMessage())->toBe('Invalid retrieval filters: syntax error');
    });
});

describe('TenantResolverException', function () {
    it('creates invalidResolverImplementation exception', function () {
        $exception = TenantResolverException::invalidResolverImplementation('MyResolver', 'TenantResolverInterface');

        expect($exception)->toBeInstanceOf(TenantResolverException::class)
            ->and($exception->getMessage())->toBe('Tenant resolver must implement TenantResolverInterface, but MyResolver was provided.');
    });

    it('creates resolverNotConfigured exception', function () {
        $exception = TenantResolverException::resolverNotConfigured();

        expect($exception)->toBeInstanceOf(TenantResolverException::class)
            ->and($exception->getMessage())->toBe('No tenant resolver has been configured. Please set a valid TenantResolver in the service provider.');
    });
});
