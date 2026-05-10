# Cyr-To-Lat 7.0 Manual Test Checklist

Этот чеклист покрывает ручную smoke/regression-проверку версии 7.0 по пользовательским потокам: WordPress admin, REST/Gutenberg, медиа, конвертер, WP-CLI и WooCommerce.

Ожидаемые слаги указаны для стандартной таблицы Cyr-To-Lat, например:

- `Й` -> `j`
- `Цвет` -> `czvet`
- `Скамейка.jpg` -> `skamejka.jpg`

## Подготовка

- Установить чистый WordPress-сайт.
- Включить Cyr-To-Lat 7.0.
- Включить pretty permalinks.
- Установить и активировать WooCommerce.
- Для REST-проверок подготовить пользователя с Application Password.
- Для WP-CLI-проверок убедиться, что команда `wp` доступна в окружении сайта.

## WordPress

| Статус | Что тестировать                     | Как тестировать                                                           | Ожидаемый результат                                   |
|--------|-------------------------------------|---------------------------------------------------------------------------|-------------------------------------------------------|
| [ ]    | Запись с кириллическим заголовком   | Posts -> Add New: title `Й`, slug оставить пустым, Publish                | Slug стал `j`, запись открывается по URL              |
| [ ]    | Страница с кириллическим заголовком | Pages -> Add New: title `Й`, slug оставить пустым, Publish                | Slug стал `j`, страница открывается по URL            |
| [ ]    | Ручной латинский slug               | Создать или обновить запись с title `Новый заголовок`, slug `manual-slug` | Slug остался `manual-slug`                            |
| [ ]    | Ручной кириллический slug           | В permalink записи указать slug `Й` и сохранить                           | Slug нормализовался в `j`                             |
| [ ]    | Дубликаты заголовков                | Создать две записи с title `Й`                                            | Slugs стали `j` и `j-2`                               |
| [ ]    | Draft -> Publish                    | Создать draft с title `Й`, затем опубликовать                             | Slug остается корректным и не ломается при публикации |
| [ ]    | Категория                           | Posts -> Categories: name `Й`, slug пустой                                | Slug стал `j`                                         |
| [ ]    | Тег                                 | Posts -> Tags: name `Й`, slug пустой                                      | Slug стал `j`                                         |
| [ ]    | Явный латинский slug термина        | Создать категорию name `Любое`, slug `manual-slug`                        | Slug остался `manual-slug`                            |
| [ ]    | Явный кириллический slug термина    | Создать категорию с name `Любое`, slug `Й`                                | Slug нормализовался в `j`                             |

## Медиа

| Статус | Что тестировать         | Как тестировать                            | Ожидаемый результат                             |
|--------|-------------------------|--------------------------------------------|-------------------------------------------------|
| [ ]    | Кириллическое имя файла | Media -> Add New: загрузить `Скамейка.jpg` | Имя файла и URL содержат `skamejka.jpg`         |
| [ ]    | Пробелы в имени файла   | Загрузить `Привет мир.txt`                 | Имя файла стало `privet-mir.txt`                |
| [ ]    | Несколько расширений    | Загрузить `тест.tar.gz`                    | Имя файла стало `test.tar.gz`                   |
| [ ]    | Регистр расширения      | Загрузить `Й.JPG`                          | Имя файла нормализовано, расширение не ломается |

## REST / Gutenberg

Gutenberg обычно сохраняет записи через REST, поэтому для ручной проверки достаточно одного UI-сценария и одного прямого REST smoke-теста.

| Статус | Что тестировать          | Как тестировать                                                 | Ожидаемый результат                       |
|--------|--------------------------|-----------------------------------------------------------------|-------------------------------------------|
| [ ]    | Gutenberg create post    | В block editor создать запись с title `Й`, slug пустой, Publish | Slug стал `j`                             |
| [ ]    | Gutenberg manual slug    | В block editor указать slug `manual-slug`, затем изменить title | Slug остался `manual-slug`                |
| [ ]    | REST create post         | Выполнить REST-запрос создания записи с title `Й`               | В ответе `"slug":"j"`, запись открывается |
| [ ]    | REST explicit Latin slug | Обновить запись через REST со slug `manual-slug`                | Slug остался `manual-slug`                |

Пример REST-запроса:

```bash
curl -X POST https://site.test/wp-json/wp/v2/posts \
  -u user:application-password \
  -H "Content-Type: application/json" \
  -d "{\"title\":\"Й\",\"status\":\"publish\"}"
```

## Конвертер и WP-CLI

| Статус | Что тестировать                        | Как тестировать                                                                                 | Ожидаемый результат                                               |
|--------|----------------------------------------|-------------------------------------------------------------------------------------------------|-------------------------------------------------------------------|
| [ ]    | Existing posts converter               | Создать или импортировать запись с кириллическим slug, запустить страницу конвертера Cyr-To-Lat | Slug стал латинским                                               |
| [ ]    | Existing terms converter               | Создать термин с кириллическим slug, запустить конвертер                                        | Slug стал латинским                                               |
| [ ]    | WP-CLI regenerate                      | Выполнить `wp cyr2lat regenerate`                                                               | Команда завершается успешно, slugs обновлены                      |
| [ ]    | WP-CLI filtered regenerate             | Выполнить `wp cyr2lat regenerate --post_type=post`                                              | Обрабатываются записи выбранного типа                             |
| [ ]    | WooCommerce attribute migration safety | Проверить, что конвертер не предлагает массовую автоматическую миграцию WooCommerce attributes  | Существующие `pa_*` taxonomies не переименовываются автоматически |

## WooCommerce

| Статус | Что тестировать                         | Как тестировать                                                                   | Ожидаемый результат                                              |
|--------|-----------------------------------------|-----------------------------------------------------------------------------------|------------------------------------------------------------------|
| [ ]    | Simple product slug                     | Products -> Add New: title `Й`, Publish                                           | Product slug стал `j`                                            |
| [ ]    | Product category                        | Products -> Categories: name `Й`, slug пустой                                     | Slug стал `j`                                                    |
| [ ]    | Product tag                             | Products -> Tags: name `Й`, slug пустой                                           | Slug стал `j`                                                    |
| [ ]    | Global attribute create                 | Products -> Attributes: name `Цвет`, slug пустой                                  | Attribute slug стал `czvet`, taxonomy `pa_czvet`                 |
| [ ]    | Global attribute edit label             | Переименовать label attribute `Цвет` в `Размер`, slug не менять                   | Slug остался `czvet`, label изменился                            |
| [ ]    | Global attribute explicit Cyrillic slug | Создать attribute с explicit slug `Цвет`                                          | Slug нормализовался в `czvet`                                    |
| [ ]    | Global attribute term                   | В attribute `Цвет` добавить term `Й`                                              | Term slug стал `j`                                               |
| [ ]    | Frontend global filter                  | Назначить товару `Цвет: Й`, открыть shop/filter query `filter_czvet=j`            | Товар находится, taxonomy key не портится                        |
| [ ]    | Local product attribute                 | В товаре добавить local attribute `Цвет`, options `Красный \| Синий`, сохранить   | Label и options остаются кириллицей, внутренний key нормализован |
| [ ]    | Variable product local attribute        | Создать variable product с local attribute `Цвет`, включить use for variations    | Variations создаются и сохраняются корректно                     |
| [ ]    | Variation attribute reload              | Сохранить variation value `Красный`, перезагрузить страницу редактирования товара | Значение variation осталось выбранным                            |
| [ ]    | Frontend add to cart                    | На frontend выбрать `Красный` и добавить variation в корзину                      | Нет ошибки обязательного attribute, товар попал в cart           |
| [ ]    | Cart/session reload                     | Обновить страницу корзины или восстановить сессию                                 | Variation остается в корзине, выбранное значение не потерялось   |
| [ ]    | Product AJAX attribute save             | Сохранить attributes через WooCommerce AJAX save attributes flow                  | Local attribute key нормализован, global `pa_*` не поврежден     |
| [ ]    | Product full admin save                 | Сохранить товар через обычную форму редактирования                                | Local/global attributes сохраняются корректно                    |
| [ ]    | WooCommerce REST/API save               | Создать или обновить product через WooCommerce REST API с local attribute `Цвет`  | Сохранение проходит, attribute keys не конфликтуют               |

## Мультиязычность

Если сайт использует Polylang или WPML, выполнить отдельную проверку locale/table selection.

| Статус | Что тестировать           | Как тестировать                                                                                  | Ожидаемый результат                 |
|--------|---------------------------|--------------------------------------------------------------------------------------------------|-------------------------------------|
| [ ]    | Polylang/WPML post locale | Создать запись на языке, для которого ожидается отдельная таблица транслитерации                 | Slug построен по правильной таблице |
| [ ]    | Polylang/WPML term locale | Создать термин на выбранном языке                                                                | Slug построен по правильной таблице |
| [ ]    | REST multilingual save    | Создать или обновить запись через REST с языковыми данными, если это поддержано настройкой сайта | Slug использует ожидаемую таблицу   |

## Минимальный релизный smoke

Если времени мало, пройти этот минимальный набор:

- [ ] WordPress post с title `Й` -> slug `j`.
- [ ] WordPress page с title `Й` -> slug `j`.
- [ ] Category/tag с name `Й` -> slug `j`.
- [ ] Media upload `Скамейка.jpg` -> `skamejka.jpg`.
- [ ] REST create post с title `Й` -> slug `j`.
- [ ] WooCommerce simple product с title `Й` -> slug `j`.
- [ ] WooCommerce global attribute `Цвет` -> slug `czvet`, taxonomy `pa_czvet`.
- [ ] WooCommerce variable product с local attribute `Цвет`.
- [ ] Frontend add-to-cart для variation `Красный`.
- [ ] Cart/session reload сохраняет variation.
- [ ] Converter обновляет существующие post/term slugs.
- [ ] `wp cyr2lat regenerate` завершается успешно.
- [ ] WooCommerce `pa_*` taxonomies не мигрируются автоматически.
