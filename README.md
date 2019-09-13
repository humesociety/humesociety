# The Hume Society Web Site

The Hume Society web site is a Symfony 4.3 application. Standard practices are followed as much as possible. See [https://symfony.com](https://symfony.com).

## `assets`

Source SCSS and JS files are stored here, and compiled by Encore to the `public/build` directory. See also the `webpack.config.js` file in the root directory. This is all Encore business as usual; see [https://symfony.com/doc/current/frontend.html](https://symfony.com/doc/current/frontend.html).

## `bin`

Symfony tools for development. This is from the standard installation; nothing in here has been touched.

## `config`

Symfony business as usual, very little here has been touched. `services.yaml` defines some application parameters, some of which are also made available in `packages\twig.yaml`. The firewall is configured in `packages\security.yaml`.

## `public`

Source SCSS and JS files are compiled to the `public\build` directory, as already noted. Some images are also stored here. `index.php` is Symfony business as usual.

## `src`

Most files of importance are in here. For the most part this all follows standard Symfony practice, so the files should all be self-explanatory. Note, however, that I divert from the standard directory structure in one notable respect: Rather than having separate `Entity`, `Repository`, and `Form` folders, I put all of these things in the `Entity` folder, organised into subfolders for each entity. This the `Entity\User` directory contains the main `User` class, alongside a `UserRespository`, a `UserHandler` (for `User`-related business logic), and any `User`-related forms.

## `templates`

Twig template files. These are divided into two subdirectories, `templates\admin` for the admin area of the site, and `templates\site` for the public area of the site. Each has its own `base.twig` file, and then various other files that extend the base.

## `uploads`

Files uploaded through the application are stored here: conference images and documents (in `uploads\conferences`), images for the web pages (in `uploads\images`), and society reports (in `uploads\reports`).
