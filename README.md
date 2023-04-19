# Extended Copier

Codeception extension extending WPBrowser's Copier extension to allow for more advanced copying of files and directories.

It adds the following features:

- Copying of files and directories from the same source to different destinations.
- Creates not existent parent directories of the destination if not existing, instead of failing.

## Installation

```bash
composer require --dev publishpress/codeception-extension-extended-copier
```

## Usage

```yaml
extensions:
    enabled:
        - PublishPress\Codeception\Extension\ExtendedCopier
    config:
        PublishPress\Codeception\Extension\ExtendedCopier:
            files:
                0: "directory1:%WP_ROOT_FOLDER%/wp-content/plugins/directory1"
                1: "directory2:%WP_ROOT_FOLDER%/wp-content/plugins/directory2"
                2: "directory2:%WP_ROOT_FOLDER%/wp-content/plugins/directory3"
```
