# Contributing

Contributions are **welcome** and will be fully **credited**.

Please read and understand the contribution guide before creating an issue or pull request.

## Etiquette

This project is open source, and as such, the maintainers give their free time to build and maintain the source code
held within. They make the code freely available in the hope that it will be of use to other developers. It would be
extremely unfair for them to suffer abuse or anger for their hard work.

Please be considerate towards maintainers when raising issues or presenting pull requests. Let's show the
world that developers are civilized and selfless people.

It's the duty of the maintainer to ensure that all submissions to the project are of sufficient
quality to benefit the project. Many developers have different skills, strengths, and weaknesses. Respect the maintainer's decision, and do not be upset or abusive if your submission is not used.

## Viability

When requesting or submitting new features, first consider whether it might be useful to others. Open
source projects are used by many developers, who may have entirely different needs to your own. Think about
whether or not your feature is likely to be used by other users of the project.

## Procedure

Before filing an issue:

- Attempt to replicate the problem, to ensure that it wasn't a coincidental incident.
- Check to make sure your feature suggestion isn't already present within the project.
- Check the pull requests tab to ensure that the bug doesn't have a fix in progress.
- Check the pull requests tab to ensure that the feature isn't already in progress.

Before submitting a pull request:

- Check the codebase to ensure that your feature doesn't already exist.
- Check the pull requests to ensure that another person hasn't already submitted the feature or fix.

## Requirements

If the project maintainer has any additional requirements, you will find them listed here.

- **Code style** - Run `composer lint` ([Pint](https://laravel.com/docs/pint), Laravel preset) before
  sending a pull request. `composer analyse` runs PHPStan, `composer refactor` runs Rector.

- **Rebuild the stylesheets** - Touching anything under `resources/css/` means the four compiled
  editions are stale. Run `npm run build` and commit the result: `resources/dist/` is tracked on
  purpose, so that `composer require` alone is enough to install the theme. The Tailwind version is
  pinned exactly for this reason — do not loosen it without rebuilding.

- **An edition never stays silent where another speaks** - A `--press-rail-*` token declared by one
  edition must be declared by all four, in the same bucket (`:root` or `.dark`). Silence is not a
  fallback to the engine's default: a compiled theme bakes one edition into `:root` and the runtime
  switch overlays a second one after it, so the silent edition inherits the declaring edition's
  value. That is how Telex's white-flattened logo ended up invisible on Broadsheet's pale bar.
  `tests/PresetTokenParityTest.php` enforces this for the tokens that have already bitten us; the
  rest of the surface is still open, so check by hand as well.

  The two buckets are separate on purpose. `:root` and `.dark` carry the same specificity, and the
  preset is loaded after the engine, so declaring a token only in `:root` silently kills the
  engine's `.dark` value for it — declare both or neither.

- **Add tests!** - Your patch won't be accepted if it doesn't have tests.

- **Document any change in behaviour** - Make sure the `README.md` and any other relevant documentation are kept up-to-date.

- **Consider our release cycle** - We try to follow [SemVer v2.0.0](https://semver.org/). Randomly breaking public APIs is not an option.

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please [squash them](https://www.git-scm.com/book/en/v2/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages) before submitting.

**Happy coding**!
