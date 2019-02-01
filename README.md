# Cyr-To-Lat

Converts Cyrillic characters in post, page and term slugs to Latin characters. Useful for creating human-readable URLs.

![](./assets/banner-772x250.png)

## Features

* Automatically converts existing post, page and term slugs on activation
* Saves existing post and page permalinks integrity
* Performs transliteration of attachment file names
* Includes Russian, Ukrainian, Bulgarian and Georgian characters
* Transliteration table can be customized without editing the plugin by itself

## Installation

```
git clone https://github.com/mihdan/cyr2lat.git
cd cyr2lat
composer install --no-dev
cd src
yarn
yarn run build:prod
```

## Development

```
git clone https://github.com/mihdan/cyr2lat.git
cd cyr2lat
composer install
cd src
yarn
yarn run build:dev
```

## License

The WordPress Plugin Cyr-To-Lat is licensed under the GPL v2 or later.

> This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 2, as published by the Free Software Foundation.

> This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

> You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

A copy of the license is included in the root of the plugin’s directory. The file is named `LICENSE`.

## Important Notes

### Licensing

The WordPress Plugin Cyr-To-Lat is licensed under the GPL v2 or later; however, if you opt to use third-party code that is not compatible with v2, then you may need to switch to using code that is GPL v3 compatible.

# Credits

The current version of the Cyr-To-Lat was developed by Sergey Biryukov and Mikhail Kobzarev.

Contributors: [SergeyBiryukov](https://github.com/SergeyBiryukov), [mihdan](https://github.com/mihdan), [karevn](https://github.com/karevn), [webvitaly](https://github.com/webvitaly), [kagg-design](https://github.com/kagg-design).