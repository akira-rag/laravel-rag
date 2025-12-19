# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).


## 0.1.0 (2025-12-19)

### Features

* add AskPayload and IngestPayload data classes with tenant scoping support ([69b317c](https://github.com/akira-rag/laravel-rag/commit/69b317c88e687b6050789d99dc4488bceab352fd))
* add backup, restore, export, and import PDF commands with observability metrics ([30cd450](https://github.com/akira-rag/laravel-rag/commit/30cd45047ba0b2dfefc69702d66496ae8e92d818))
* add CLI commands documentation and update navigation links ([fa8d9bf](https://github.com/akira-rag/laravel-rag/commit/fa8d9bff87e4022289fe08f7b098685621cc31da))
* add initial migration and models for RAG schema ([6d41a95](https://github.com/akira-rag/laravel-rag/commit/6d41a95343683f6552f1b0bdad4a41e887caff77))
* add InvalidPayload exception and Prism utility class for code highlighting ([11aaf26](https://github.com/akira-rag/laravel-rag/commit/11aaf26c6d13b63d5820c6f571a25d7c212bed37))
* add new commands for RAG: ingest, reembed, and stats ([9988fd5](https://github.com/akira-rag/laravel-rag/commit/9988fd513cc56f89582b09a36b9abc4fd06ed63f))
* add observability configuration for structured logging and metrics collection ([df06d44](https://github.com/akira-rag/laravel-rag/commit/df06d443700e9d8fb335d35357255eb167f09195))
* add RagInstallCommand for interactive installation and configuration of Akira RAG ([b47fda3](https://github.com/akira-rag/laravel-rag/commit/b47fda3c6b24f3f0c70ebb1a1847930544961fca))
* enhance rag:install command with additional options for tenancy, migration, and GitHub star ([9b3e979](https://github.com/akira-rag/laravel-rag/commit/9b3e979de7b751fa6a178a73359695618787324d))
* implement RagManager, RagQuery, RagQueryChunk, and RagService classes for document ingestion and querying ([037a49d](https://github.com/akira-rag/laravel-rag/commit/037a49d92a2707db938a97a06ea7119420b8abff))
* implement tenancy configuration and add tenant resolver classes ([98c2221](https://github.com/akira-rag/laravel-rag/commit/98c2221309ba96f89f89b007775692f4dc95d5ec))

### Bug Fixes

* add generic error patterns to baseline and disable reportUnmatchedIgnoredErrors ([551a3ee](https://github.com/akira-rag/laravel-rag/commit/551a3eeb0e3c1c8e185837b497e70bed3a3b316a))
* add phpstan baseline to address type issues in commands and models ([f4fc69d](https://github.com/akira-rag/laravel-rag/commit/f4fc69da571124d90ccb7b1fe578a805135d74ef))
* add type safety for command option/argument handling to satisfy CI PHPStan checks ([d5a3b05](https://github.com/akira-rag/laravel-rag/commit/d5a3b051e2e0f6f34c91d073240b171cd98eaea7))
* add type validation for command arguments to satisfy CI PHPStan ([5ae0768](https://github.com/akira-rag/laravel-rag/commit/5ae07680ba68624e05812f5a5497e3cb840d3cc3))
* add type validation for path argument in RagImportPdfCommand and RagRestoreCommand ([d690a22](https://github.com/akira-rag/laravel-rag/commit/d690a2278cf8336240eacacfedc492a7a1ab3cd6))
* clean up phpstan baseline and improve type hints in commands ([102ffb0](https://github.com/akira-rag/laravel-rag/commit/102ffb026521efd2de90a8fb3ba4179a4443250d))
* disable treatPhpDocTypesAsCertain and regenerate baseline ([8460d08](https://github.com/akira-rag/laravel-rag/commit/8460d08b59bc648430abc5a788158d22435b5d06))
* escape regex patterns correctly in baseline ([56f1db1](https://github.com/akira-rag/laravel-rag/commit/56f1db184b97d75f845ea9e6d5dac6a33c0d9826))
* explicitly specify phpstan configuration file in test:types script ([022c4b2](https://github.com/akira-rag/laravel-rag/commit/022c4b2a0a8d96625dff1839494d3ac19b9089ff))
* improve type hints and error handling in commands ([4c7ad30](https://github.com/akira-rag/laravel-rag/commit/4c7ad30089a55596b54e6785f429e64010ae7501))
* include phpstan baseline and enhance static analysis configuration ([63d0a85](https://github.com/akira-rag/laravel-rag/commit/63d0a8552fb9fac1bc9332d6f9d75a08395916ac))
* standardize SQL statement quotes and improve tenant resolver configuration ([f867b94](https://github.com/akira-rag/laravel-rag/commit/f867b94435044dd21a9d0bebaa30b6766c1b9648))
* track phpstan.neon in version control ([11bd261](https://github.com/akira-rag/laravel-rag/commit/11bd261150155608deb7be8f2c510d504441b37a))
* update composer dependencies and improve type hints in commands ([9b535a9](https://github.com/akira-rag/laravel-rag/commit/9b535a93c61d37620d0b7d9c8739c6e5f837fc3b))
* update phpstan level and improve type assertions in RagManager and RagStatsCommand ([2386e11](https://github.com/akira-rag/laravel-rag/commit/2386e11b502339cc25862923d2bdf4209451b13e))
* update phpstan level and refactor casts methods in models ([3140ba9](https://github.com/akira-rag/laravel-rag/commit/3140ba95c0ec56b392f037399a307bae50a18f93))
* use helper method for type-safe option handling in commands ([f259ce4](https://github.com/akira-rag/laravel-rag/commit/f259ce4d2fdacbb4687fd5f32e0e84b086e49ca7))
* use PHPDoc type assertion for command arguments ([f168091](https://github.com/akira-rag/laravel-rag/commit/f16809167940e6885105238062673dd41f5e54cb))
* use specific baseline entries instead of regex patterns for CI errors ([6051ee9](https://github.com/akira-rag/laravel-rag/commit/6051ee9054bad067b7df845b41981ad453bdf31a))

### Documentation

* add installation and configuration documentation for Akira RAG package ([bea4396](https://github.com/akira-rag/laravel-rag/commit/bea439631e691248dc10c25c73356b11b5bdd83c))
* update CLI commands documentation with new commands and detailed usage ([4724dcd](https://github.com/akira-rag/laravel-rag/commit/4724dcd598ce16bf431b3c531fcfc68c9c560aa3))

### Code Refactoring

* enhance PHPDoc annotations for method parameters in Rag facade and update phpstan baseline ([bb423e8](https://github.com/akira-rag/laravel-rag/commit/bb423e8e032676c1c61d4e32d262b273b297b4e0))
* enhance type hinting and improve code clarity in command classes ([6bf7dc4](https://github.com/akira-rag/laravel-rag/commit/6bf7dc4a841599f0835d074eaf28e6ce9784ad6c))
* improve code readability and consistency in various classes ([6e3287f](https://github.com/akira-rag/laravel-rag/commit/6e3287fdad83bea2ebd406db087c283aa057ca92))
* improve output and retain option handling in RagBackupCommand ([48dd5ef](https://github.com/akira-rag/laravel-rag/commit/48dd5ef4e9042e537484505c9f48e5897c346e5c))
* introduce stringOption method for type-safe option handling in commands ([ccd7775](https://github.com/akira-rag/laravel-rag/commit/ccd7775432473fd162cb6a582e0d7cebbd3fb640))
* move stringOption method to improve code organization ([c6c02aa](https://github.com/akira-rag/laravel-rag/commit/c6c02aa30795881d3f91a9433be546fd47b7e50c))
* relocate stringOption and stringArgument methods for improved organization ([90f7ce0](https://github.com/akira-rag/laravel-rag/commit/90f7ce0d688bbc5b8ba5e4ff0f5793a8c701ef76))
* remove stringOption method and replace assertions with type casting for improved clarity ([7325afd](https://github.com/akira-rag/laravel-rag/commit/7325afd513446698724c05d0f76b33c8e6182900))
* remove tests directory from rector configuration ([bb34262](https://github.com/akira-rag/laravel-rag/commit/bb34262fe6ad6321c92e246d62ec24826f857bf7))
* remove tests directory from rector configuration ([81f2090](https://github.com/akira-rag/laravel-rag/commit/81f2090833afa0bc642ccccfbc30618ca75a8a20))
* replace stringOption and stringArgument with option method for improved type safety ([c622511](https://github.com/akira-rag/laravel-rag/commit/c6225114f25aa97ffb869a0931b7ef8abcf158c1))
* simplify encryption option handling in RagBackupCommand ([30fc580](https://github.com/akira-rag/laravel-rag/commit/30fc5801c2f1f06b5e9b9cecdd2270553c9d3333))
* update .gitignore and run-tests.yml for improved dependency management ([bb67470](https://github.com/akira-rag/laravel-rag/commit/bb67470c6217cd9cbab96f4effd40a2de9fda044))
* update namespaces from Rag to Akira and adjust service provider configuration ([7853c8a](https://github.com/akira-rag/laravel-rag/commit/7853c8a9f017062ab4b086c9b78279bd4a1d61a3))
* update phpstan command in composer.json and include tests directory in rector configuration ([2a4aa6f](https://github.com/akira-rag/laravel-rag/commit/2a4aa6fa95026c59ae7b456eab1f20d200710b5e))
