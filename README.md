# SameSite=None Compatibility Middleware for Laravel

Provides support for legacy clients when using cookies marked as `SameSite=None; Secure` in Laravel 5.8+

This package implements the first recommendation for handling incompatible clients outlined in [this fantastic web.dev article](https://web.dev/samesite-cookie-recipes/#handling-incompatible-clients) by [@rowan-m](https://github.com/rowan-m). If you're not sure why any of this matters or what is currently changing about the way browsers handle cookies' `SameSite` attribute, let me encourage you to read Rowan's thorough [SameSite explainer](https://web.dev/samesite-cookies-explained/).

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
