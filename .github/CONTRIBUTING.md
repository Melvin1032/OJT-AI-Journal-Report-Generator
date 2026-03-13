# Contributing Guidelines

## Branching Strategy

This project follows a **Feature Branch Workflow**:

### Branch Naming Convention

- `feature/` - New features (e.g., `feature/add-user-auth`)
- `fix/` - Bug fixes (e.g., `fix/login-error`)
- `hotfix/` - Urgent production fixes
- `docs/` - Documentation changes
- `refactor/` - Code refactoring
- `test/` - Adding tests

### Workflow

1. **Create a branch** from `main`:
   ```bash
   git checkout main
   git pull origin main
   git checkout -b feature/your-feature-name
   ```

2. **Make changes** and commit:
   ```bash
   git add .
   git commit -m "feat: Add your feature description"
   ```

3. **Push your branch**:
   ```bash
   git push -u origin feature/your-feature-name
   ```

4. **Create a Pull Request** on GitHub:
   - Describe your changes
   - Test thoroughly
   - Request review (if applicable)

5. **After merging**, delete the branch:
   ```bash
   git checkout main
   git pull origin main
   git branch -d feature/your-feature-name
   git push origin --delete feature/your-feature-name
   ```

## Commit Message Format

We use [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>: <short description>

<optional body>

<optional footer>
```

### Types

- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation changes
- `style:` - Formatting, no code logic change
- `refactor:` - Code refactoring
- `test:` - Adding tests
- `chore:` - Maintenance tasks

### Examples

```
feat: Add search functionality
fix: Resolve login timeout issue
docs: Update installation guide
refactor: Simplify database queries
chore: Update dependencies
```

## Before Pushing

- [ ] Test your changes locally
- [ ] Ensure no sensitive data (API keys, passwords) is committed
- [ ] Run any available tests
- [ ] Update documentation if needed

## Security Notes

- **Never commit `.env` file** - It contains API credentials
- **Never commit database files** - They may contain user data
- **Never commit upload files** - They are user-generated content
- Check `.gitignore` to see what's excluded
