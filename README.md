# SameSite=None Compatibility Middleware for Laravel

Provides support for legacy clients when using cookies marked as `SameSite=None; Secure` in Laravel 5.8+

This package implements the first recommendation for handling incompatible clients outlined in [this fantastic web.dev article](https://web.dev/samesite-cookie-recipes/#handling-incompatible-clients) by [Rowan Merewood](https://github.com/rowan-m). If you're not sure why any of this matters or what is currently changing about the way browsers handle cookies' `SameSite` attribute, let me encourage you to read Rowan's thorough [SameSite explainer](https://web.dev/samesite-cookies-explained/).

## How It Works

In the past, a cookie without an explicit `SameSite` value could be written and read in a third-party context (e.g. your app in an iframe on another domain's page) without any restrictions from the browser. For security reasons, the most popular browsers (Chrome, Firefox, Safari, Edge) are now constraining their default cookie behavior so that cookies may only be read in a third-party context if they are marked as `SameSite=None; Secure`.

Ok, so we just need to update how we set those cookies, right? Not so fast. Unfortunately, marking cookies this way can make them [incompatible with older browsers](https://www.chromium.org/updates/same-site/incompatible-clients). And depending on your audience, _a lot_ of people are still using older browsers.

This package employs a workaround to allow you to upgrade your app's cookies to meet current recommendations and still maintain compatibility with legacy browsers by including a fallback for all outgoing cookies marked `SameSite=None; Secure`. So if your app sets a cookie that looks like this:

```
appcookie=value; SameSite=None; Secure
```

This package will add a this fallback cookie in the response that gets sent back to the browser:

```
appcookie__ssn-legacy=value; Secure
```

When your app receives an incoming request, this package will inspect the cookies and promote any fallback cookies that don't have a standard counterpart marked as `SameSite=None; Secure`.

## License

Copyright 2020 Kevin Smith

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

  http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
