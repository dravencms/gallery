# DravenCMS Gallery

Image-gallery administration and frontend components for DravenCMS. Galleries use the File package for stored media and the Tag package for classification.

## Features

- Gallery and picture administration.
- Upload from managed file structures.
- Picture ordering, captions, activation, and tags.
- Overview and detail frontend components for CMS pages.
- Commands for historical link migration and gallery retention cleanup.
- Provider-defined admin navigation and ACL permissions.

## Installation

```bash
composer require dravencms/gallery
```

The package loader registers the extension, admin presenter, services, and Doctrine mappings. Apply the package schema through the application's migration workflow before using the gallery. Navigation and permission definitions come from providers; assign permissions through the group editor.

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

## Gallery file storage

The gallery creates its root `gallery` file structure when the first picture upload needs it and reuses it on later uploads. No fixture or setup command is needed. Existing file structures and files are retained; a structure named `gallery` below another parent does not replace the gallery root.

## Admin authorization and mutations

Administration uses User 3.1, Admin 3.2 and Application 0.3 guards. Screens and write callbacks enforce the existing provider-defined permissions server-side, including direct URLs and bulk callbacks. Mutating signals require POST and a session-bound action token; synchronize the matching admin assets when upgrading. See the User package's `user:security:migrate` instructions for the password-reset schema change.
