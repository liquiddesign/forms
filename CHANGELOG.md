<!--- BEGIN HEADER -->
# Changelog

All notable changes to this project will be documented in this file.
<!--- END HEADER -->

## [3.0.19](https://github.com/liquiddesign/forms/compare/v3.0.18...v3.0.19) (2026-07-13)

### Bug Fixes

* `lqdForm.js`: compute `emptyOptional` in `Nette.validateControl` for netteForms 2.4 — nette/forms 3.x (PHP) no longer exports the `{op: 'optional'}` marker, so client-side validation of empty optional `:float`/`:integer` fields falsely blocked form submits ("Zadejte platné číslo") on projects pairing PHP nette/forms 3.x with netteForms.js 2.4 ([2.1](https://github.com/liquiddesign/forms/tree/2.1))

---

## [3.0.18](https://github.com/liquiddesign/forms/compare/v3.0.16...v3.0.18) (2026-07-10)

### Bug Fixes

* Guard mutation-selector click in `Forms.addError` when `data-mutation` or mutation input is missing ([2.1](https://github.com/liquiddesign/forms/tree/2.1))

---

## [3.0.12](https://github.com/liquiddesign/forms/compare/v3.0.11...v3.0.12) (2025-10-20)

### Features

* Add recursive form errors collection ([9490bd](https://github.com/liquiddesign/forms/commit/9490bd9f2e1b6b73a9edf7e4b33478e4d268850e))


---

## [3.0.11](https://github.com/liquiddesign/forms/compare/v3.0.10...v3.0.11) (2025-06-12)

### Bug Fixes

* Adding defaults to password form messages ([ab286e](https://github.com/liquiddesign/forms/commit/ab286ee2cbd29aa92a323b8bb084f91490e3683a))

### Chores

* Chore ([81d446](https://github.com/liquiddesign/forms/commit/81d446e45de977eb855e174338a32cc1eb4ee0c0))


---

## [3.0.10](https://github.com/liquiddesign/forms/compare/v3.0.9...v3.0.10) (2025-05-20)

### Bug Fixes


##### Container

* Use 'self' instead of 'static' for better clarity ([50b655](https://github.com/liquiddesign/forms/commit/50b65586e9c468c60ac2a0f2d56da35a77272a65))

### Chores

* Update GitHub Actions to use checkout@v4 and cache@v4 ([072654](https://github.com/liquiddesign/forms/commit/072654bc7b605c1069f9624ee8b655788259cdf4))


---

## [3.0.9](https://github.com/liquiddesign/forms/compare/v3.0.8...v3.0.9) (2025-05-19)

### Features


##### Container

* Add method to create and add a new container ([001610](https://github.com/liquiddesign/forms/commit/00161006cb606213be4b9535dc8d6e16bd282696))


---

## [3.0.8](https://github.com/liquiddesign/forms/compare/v3.0.7...v3.0.8) (2025-02-28)

### Features


##### Lost Password Form

* Store account instance for token update ([b4d11a](https://github.com/liquiddesign/forms/commit/b4d11acc3300eb3775e20dea117f3dd2d22e1bed))

### Styles

* Fix ([ece326](https://github.com/liquiddesign/forms/commit/ece326352593be4533068d955c0658b222381014))


---

## [3.0.7](https://github.com/liquiddesign/forms/compare/v3.0.6...v3.0.7) (2025-01-04)

### Builds

* Update changelog command to use PHP interpreter ([51164b](https://github.com/liquiddesign/forms/commit/51164b0a42d71d052f4717cda9607199b05391c7))
* Update Composer scripts to use [*@php*](https://github.com/php) prefix ([f30753](https://github.com/liquiddesign/forms/commit/f30753d4e6b0fccbea4847ea0b9665337c4f386d))
* Update phpstan/phpstan dependency to ^2.1 ([2a5e8c](https://github.com/liquiddesign/forms/commit/2a5e8cebe18f95e3bf911602079a94248b4a8b9e))


---

## [3.0.6](https://github.com/liquiddesign/forms/compare/v3.0.5...v3.0.6) (2024-11-04)

### Features


##### Upload Handler

* Allow WEBP format to filemanager ([d953da](https://github.com/liquiddesign/forms/commit/d953dae1031663367c95c9f59185989e357f4f89))


---

## [3.0.5](https://github.com/liquiddesign/forms/compare/v3.0.4...v3.0.5) (2024-11-01)

### Features


##### Upload Handler

* Allow WEBP format to filemanager ([07b969](https://github.com/liquiddesign/forms/commit/07b9690e276c8c1e8bee382077daa1cff6311df0))


---

## [3.0.4](https://github.com/liquiddesign/forms/compare/v3.0.3...v3.0.4) (2024-09-27)

### Features

* Add getSubmitterName method to Form class ([77013d](https://github.com/liquiddesign/forms/commit/77013d8d0e29f06041669c0df2903d7bdde7f104))

##### Lost Password Form

* Pass Account to onRecover ([c39189](https://github.com/liquiddesign/forms/commit/c3918962df678778d57488525f68ef164596dabe))


---

