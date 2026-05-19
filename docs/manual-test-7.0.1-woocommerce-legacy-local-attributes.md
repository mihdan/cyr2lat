# Cyr-To-Lat 7.0.1 Manual Test: Legacy WooCommerce Local Attributes

Этот сценарий вручную проверяет регрессию 7.0.1 для старых WooCommerce variable products, созданных в Cyr-To-Lat 6.8.0 с локальным кириллическим attribute.

Проверяемый баг: старый товар хранит local attribute key как URL-encoded Cyrillic (`%d1%86...`), а Cyr-To-Lat 7.0.x должен отдать frontend form и `available_variations` с согласованным ключом `attribute_czvet`, не потеряв значение `Красный`.

## Подготовка

- Тестовый WordPress-сайт, не production.
- WooCommerce активирован.
- Тема с обычной WooCommerce product page.
- Cyr-To-Lat 6.8.0 доступен как git tag или установленный zip с wordpress.org.
- Cyr-To-Lat 7.0.1 доступен как текущая ветка/сборка.
- В браузере доступны DevTools.

## Фаза 1: создать старый товар в 6.8.0

1. Активировать Cyr-To-Lat 6.8.0.
2. В админке открыть `Products -> Add New`.
3. Создать variable product:
   - Product name: `Legacy local attribute test`.
   - Product data: `Variable product`.
4. На вкладке `Attributes` добавить custom product attribute:
   - Name: `Цвет`.
   - Values: `Красный | Синий`.
   - Включить `Visible on the product page`.
   - Включить `Used for variations`.
   - Нажать `Save attributes`.
5. На вкладке `Variations` создать variation:
   - Attribute `Цвет`: выбрать `Красный`.
   - Regular price: `10`.
   - Variation status: enabled/in stock.
   - Сохранить variation.
6. Опубликовать или обновить товар.
7. Открыть товар на frontend.
8. Выбрать `Красный` и добавить в корзину.

Ожидаемо в 6.8.0:

- Товар добавляется в корзину без ошибки.
- В DevTools у select может быть legacy key:

```html
name="attribute_%d1%86%d0%b2%d0%b5%d1%82"
```

## Фаза 2: проверить тот же товар в 7.0.1

1. Не пересоздавать товар.
2. Деактивировать Cyr-To-Lat 6.8.0.
3. Активировать Cyr-To-Lat 7.0.1.
4. Очистить page cache/object cache, если они есть.
5. Открыть тот же товар на frontend в приватном окне или после hard refresh.
6. Открыть DevTools -> Elements.
7. Найти variation select для attribute `Цвет`.

Ожидаемо в 7.0.1:

```html
name="attribute_czvet"
data-attribute_name="attribute_czvet"
```

8. В DevTools -> Console выполнить:

```js
const form = document.querySelector('form.variations_form');
JSON.parse(form.getAttribute('data-product_variations')).map(v => v.attributes);
```

Ожидаемо:

```js
[
  {
    attribute_czvet: "Красный"
  }
]
```

Критично: ключ в `data-product_variations` должен совпадать с `select[name]`, а значение не должно быть пустым.

## Фаза 3: пользовательский поток

1. На странице товара выбрать `Красный`.
2. Убедиться, что WooCommerce показывает variation price/availability.
3. Нажать `Add to cart`.
4. Перейти в корзину.
5. Обновить страницу корзины.

Ожидаемо:

- Нет ошибки `Цвет is a required field`.
- Variation добавлена в корзину.
- В корзине отображается выбранное значение `Цвет: Красный`.
- После reload корзины variation остается в корзине.

## Как выглядит регрессия

Баг считается воспроизведенным, если после апгрейда на 7.0.1 есть любой из симптомов:

- Select рендерится как `attribute_czvet`, но `data-product_variations` содержит `attribute_%d1...`.
- `data-product_variations` содержит `attribute_czvet: ""`.
- При выборе `Красный` WooCommerce не находит variation, не показывает variation price/availability или пишет `Цвет is a required field`.
- Add to cart не добавляет товар в корзину.

## Optional: проверить сырые meta

Это необязательная проверка, но она подтверждает, что товар действительно старый.

Через WP-CLI:

```bash
wp post meta get PRODUCT_ID _product_attributes --format=json
wp post meta list VARIATION_ID --keys=attribute_% --format=json
```

Для legacy-товара ожидаемо увидеть старые ключи:

```text
%d1%86%d0%b2%d0%b5%d1%82
attribute_%d1%86%d0%b2%d0%b5%d1%82
```

Важно: 7.0.1 не обязан мигрировать эти meta в базе во время просмотра страницы. Главное, чтобы frontend contract был согласован:

```text
select name = attribute_czvet
data-product_variations attributes = attribute_czvet: Красный
```
