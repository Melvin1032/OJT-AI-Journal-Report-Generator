# Git Branching Cheatsheet

## 🌿 Daily Workflow

### Starting a New Task

```bash
# 1. Update your main branch
git checkout main
git pull origin main

# 2. Create a new branch
git checkout -b feature/your-task-name

# 3. Start working...
```

### During Development

```bash
# Check current branch
git branch

# See changes
git status
git diff

# Stage and commit
git add .
git commit -m "feat: Your commit message"

# Push to GitHub
git push -u origin feature/your-task-name
```

### Completing a Task

```bash
# 1. Push final changes
git push origin feature/your-task-name

# 2. Go to GitHub and create Pull Request
#    https://github.com/your-username/repo/pulls

# 3. After merging, update main locally
git checkout main
git pull origin main

# 4. Delete the feature branch
git branch -d feature/your-task-name
git push origin --delete feature/your-task-name
```

---

## 🔧 Common Commands

### Branch Operations

```bash
# List all branches
git branch

# List all branches (including remote)
git branch -a

# Create new branch
git checkout -b feature/new-feature

# Switch to branch
git checkout feature/new-feature

# Create and switch in one command
git checkout -b fix/bug-fix

# Delete local branch
git branch -d feature/old-feature

# Delete remote branch
git push origin --delete feature/old-feature

# Rename current branch
git branch -m new-name
```

### Undo Operations

```bash
# Unstage a file (keep changes)
git reset HEAD filename

# Discard local changes in a file
git checkout -- filename

# Undo last commit (keep changes)
git reset --soft HEAD~1

# Undo last commit (discard changes)
git reset --hard HEAD~1

# Abort a merge
git merge --abort
```

### Viewing History

```bash
# See commit history
git log --oneline

# See last 5 commits
git log -5

# See graph of all branches
git log --oneline --graph --all

# See what changed in a commit
git show commit-hash
```

---

## 🚨 Emergency Commands

### I committed to main by mistake!

```bash
# Create a branch from current state
git checkout -b backup-branch

# Go back to main
git checkout main

# Reset main to before the commit
git reset --hard HEAD~1

# Continue work on the backup branch
git checkout backup-branch
```

### I want to abandon all changes!

```bash
# Discard ALL local changes
git reset --hard HEAD

# Clean untracked files (CAREFUL!)
git clean -fd
```

### I pushed something I shouldn't have!

```bash
# Fix locally first, then force push
git commit --amend -m "Corrected message"
git push -f origin branch-name

# OR remove sensitive file from history
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch path/to/file" \
  --prune-empty --tag-name-filter cat -- --all
git push -f origin main
```

---

## ✅ Pre-Push Checklist

Before pushing to `main`:

- [ ] All changes tested locally
- [ ] No `.env` or sensitive files staged
- [ ] Commit messages follow convention
- [ ] Code works as expected
- [ ] No console errors
- [ ] Database migrations run (if any)

---

## 🎯 Quick Reference

| Task | Command |
|------|---------|
| Start new feature | `git checkout -b feature/name` |
| Save progress | `git add . && git commit -m "message"` |
| Share work | `git push -u origin branch-name` |
| Update from main | `git checkout main && git pull` |
| Finish feature | Create PR on GitHub → Merge → Delete branch |

---

## 📚 Resources

- [Git Documentation](https://git-scm.com/doc)
- [Conventional Commits](https://www.conventionalcommits.org/)
- [GitHub Flow](https://guides.github.com/introduction/flow/)
- [Git Branching Guide](https://git-scm.com/book/en/v2/Git-Branching)
