```php
  function reImage($matches) {
    $content = $matches[1];
    $align = $matches[5];
    $href = $matches[6];
  }
```

```html
  <em>příklad</em>: **this is not strong**
```

```
  <em>příklad</em>: **this is not strong**
```

```
  some div...

  /---div
    vnořený div
  \---

  Texy je sexy!
```

