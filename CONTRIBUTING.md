# Contributing to Monthli

Thanks for considering contributing! 🎉

## How to contribute

1. Fork the repository and create a new feature branch.
   git checkout -b feature/my-feature

2. Coding standards
   - Run Laravel Pint before committing:
     composer pint
   - Run static analysis:
     vendor/bin/phpstan analyse --level=max app
   - Write tests for new features.

3. Testing
   Run the full test suite:
   php artisan test

4. Pull request
   - Describe your changes clearly.
   - Add screenshots or GIFs if UI-related.
   - Link related issues.

## Guidelines
- Keep PRs small and focused.
- Avoid breaking backward compatibility.
- Discuss large features in GitHub Discussions before coding.

## Reporting issues
- Use the Issues tab for bugs/feature requests.
- Provide CSV samples (scrubbed data) when reporting import bugs.

💡 Pro tip: Start with "good first issues" or help us improve docs/tests.
