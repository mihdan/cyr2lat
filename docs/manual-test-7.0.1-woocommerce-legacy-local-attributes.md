# Cyr-To-Lat 7.0.1 Manual Test: Legacy WooCommerce Local Attributes

Этот сценарий вручную проверяет регрессию 7.0.1 для старых WooCommerce variable products, созданных в Cyr-To-Lat 6.8.0 с локальным кириллическим attribute.

Проверяемый баг: старый товар хранит local attribute key как URL-encoded Cyrillic (`%d1%86...`). В Cyr-To-Lat 7.0.0 frontend мог получить рассинхрон между form key, `available_variations` и cart/session validation. В Cyr-To-Lat 7.0.1 frontend contract должен быть согласован на `attribute_czvet`, а cart item должен переживать переход в корзину и reload.

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
6. Создать вторую variation для проверки `Any`:
   - Attribute `Цвет`: оставить `Any Цвет`.
   - Regular price: `11`.
   - Variation status: enabled/in stock.
   - Сохранить variation.
7. Опубликовать или обновить товар.
8. Открыть товар на frontend.
9. Выбрать `Красный` и добавить в корзину.

Ожидаемо в 6.8.0:

- Товар добавляется в корзину без ошибки.
- В DevTools у select может быть legacy key:

```html
name="attribute_%d1%86%d0%b2%d0%b5%d1%82"
```

## Фаза 2: увидеть регрессию в 7.0.0

Эта фаза нужна, если надо доказать, что manual-сценарий ловит именно исправленную ошибку. Если 7.0.0 уже недоступен, можно пропустить и сразу идти к фазе 3.

1. Не пересоздавать товар.
2. Деактивировать Cyr-To-Lat 6.8.0.
3. Активировать Cyr-To-Lat 7.0.0.
4. Очистить page cache/object cache, если они есть.
5. Открыть тот же товар на frontend в приватном окне или после hard refresh.
6. Открыть DevTools -> Elements.
7. Найти variation select для attribute `Цвет`.
8. В DevTools -> Console выполнить:

```js
const form = document.querySelector('form.variations_form');
const select = form.querySelector('[name^="attribute_"]');
const variations = JSON.parse(form.getAttribute('data-product_variations'));
({
  selectName: select && select.getAttribute('name'),
  variationAttributes: variations.map(v => v.attributes)
});
```

Один из этих вариантов означает регрессию 7.0.0:

```js
{
  selectName: "attribute_czvet",
  variationAttributes: [
    { "attribute_%d1%86%d0%b2%d0%b5%d1%82": "Красный" }
  ]
}
```

или:

```js
{
  selectName: "attribute_czvet",
  variationAttributes: [
    { attribute_czvet: "" }
  ]
}
```

Дополнительный симптом 7.0.0 для variation `Any`:

- После выбора `Красный` товар показывает notice `added to cart`.
- При переходе в cart корзина пустая или товар удален как измененный.

## Фаза 3: проверить тот же товар в 7.0.1

1. Не пересоздавать товар.
2. Деактивировать Cyr-To-Lat 7.0.0, если он был активирован.
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
const select = form.querySelector('[name^="attribute_"]');
const variations = JSON.parse(form.getAttribute('data-product_variations'));
({
  selectName: select && select.getAttribute('name'),
  variationAttributes: variations.map(v => v.attributes)
});
```

Ожидаемо для concrete variation `Красный`:

```js
{
  selectName: "attribute_czvet",
  variationAttributes: [
    { attribute_czvet: "Красный" }
  ]
}
```

Ожидаемо для `Any Цвет` variation:

```js
{
  selectName: "attribute_czvet",
  variationAttributes: [
    { attribute_czvet: "" }
  ]
}
```

Критично:

- Ключ в `data-product_variations` должен совпадать с `select[name]`.
- Для concrete variation значение не должно быть пустым.
- Для `Any` variation пустое значение нормально: оно означает “эта variation принимает любое значение attribute”.

## Фаза 4: пользовательский поток

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

Повторить этот же поток для `Any Цвет` variation:

1. Убедиться, что variation в админке имеет `Any Цвет`.
2. На frontend выбрать `Красный`.
3. Нажать `Add to cart`.
4. Перейти в корзину.
5. Обновить страницу корзины.

Ожидаемо:

- Notice `added to cart` не должен быть ложноположительным.
- В корзине должен быть товар.
- После reload корзины товар не исчезает.

## Как выглядит регрессия

Баг считается воспроизведенным, если после апгрейда на 7.0.1 есть любой из симптомов:

- Select рендерится как `attribute_czvet`, но `data-product_variations` содержит `attribute_%d1...`.
- Concrete variation `Красный` в `data-product_variations` содержит `attribute_czvet: ""`.
- При выборе `Красный` WooCommerce не находит variation, не показывает variation price/availability или пишет `Цвет is a required field`.
- Add to cart показывает success notice, но при переходе в cart корзина пустая.
- После reload cart товар исчезает.

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
cart/session reload keeps the item
```
