# DravenCMS Gallery

Image-gallery administration and frontend components for DravenCMS. Galleries use the File package for stored media and the Tag package for classification.

## Features

- Gallery and picture administration.
- Upload from managed file structures.
- Picture ordering, captions, activation, and tags.
- Overview and detail frontend components for CMS pages.
- Commands for historical link migration and gallery retention cleanup.
- Admin menu and ACL fixtures.

## Installation

```bash
composer require dravencms/gallery
```

The package loader registers the extension, admin presenter, services, and Doctrine mappings. Apply the package schema through the application's migration workflow and load fixtures for the default admin menu and permissions.

## Frontend Integration

With `dravencms/structure` installed, the package exposes Gallery Overview and Gallery Detail component factories to the CMS. Their `ICmsActionOption` configuration selects the content rendered by each placement.

Application code can also inject `OverviewFactory` or `DetailFactory` directly when it already has the corresponding CMS action option.

## Maintenance Commands

Keep only a defined number of newest galleries:

```bash
php bin/console gallery:gallery:clean 20
```

Migrate historical direct picture-file relationships to `StructureFileLink` records:

```bash
php bin/console gallery:gallery:migrate-link
```

Both commands modify persisted gallery/file relationships. Back up the database and file data before using them on existing installations.

## License

This package is licensed under the LGPL-3.0-only license.
