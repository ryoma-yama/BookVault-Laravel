## Internationalization (i18n) Policy
- We use `laravel-react-i18n`.
- The `fallbackLocale` is set to `en`, so **`lang/en.json` is not required**.
- Write English labels directly as keys in the `t()` function, e.g., `t('Read more')`.
- Only update `lang/ja.json` for Japanese translations. Do not create or restore `en.json`.
